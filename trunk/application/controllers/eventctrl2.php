<?php
/*
CREATED 27 MAR 2012 0341

Difference is that this is dedicated mostly to
event manager only, because eventctrl is so ballooned already.
*/
class eventctrl2 extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		
		include_once( APPPATH.'constants/_constants.inc');
				
		$this->load->helper('cookie');
		$this->load->model('login_model');
		$this->load->model('academic_model');
		$this->load->model('account_model');
		$this->load->model('booking_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('coordinatesecurity_model');
		$this->load->model('event_model');
		$this->load->model('Guest_model');
		$this->load->model('makexml_model');
		$this->load->model('payment_model');
		$this->load->model('permission_model');
		$this->load->model('seat_model');
		$this->load->model('slot_model');
		$this->load->model('ticketclass_model');
		$this->load->model('transactionlist_model');
		$this->load->model('usefulfunctions_model');
		$this->load->library('bookingmaintenance');
		$this->load->library('seatmaintenance');
		$this->load->library('sessmaintain');
		$this->load->library('encrypt');

		if( !$this->sessmaintain->onControllerAccessRitual() ) return FALSE;
	} //construct
	
	function index()
	{		
		redirect( 'eventctrl/book' );		
	}//index
	
	function checkAndActOnEventMgr()
	{
		if( !$this->permission_model->isEventManager() )
		{
			$data['error'] = "NO_PERMISSION";					
			$this->load->view( 'errorNotice', $data );			
			return false;
		}
		return true;
	}
	
	function common_pre_check( $eventID, $showtimeID )
	{
		if( !(is_numeric( $eventID ) and is_numeric( $showtimeID ) ) )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}
	
	function manageEvent()
	{
		$eventObj = $this->event_model->getAllEventsRestricted();
		$showingTimes = Array();
		foreach( $eventObj as $singleEvent )
		{
			$showingTimes[ $singleEvent->EventID ] = $this->event_model->getAllShowingTimes(  $singleEvent->EventID );		
		}
		$data['myEvents'] = $eventObj;
		$data['showingTimes'] = $showingTimes;
		$this->load->view( 'manageEvent/manageEvent01', $data );
	}//manageEvent()
	
		
	function reschedule( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['eventObj'] = $this->event_model->getSingleShowingTime( $eventID, $showtimeID );		
		$this->load->view( 'manageEvent/manageEvent02_reschedule.php', $data);
	}//reschedule
	
	function reschedule_process()
	{
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$startDate 		= $this->input->post( 'startDate' );
		$startTime 		= $this->input->post( 'startTime' );
		$endDate 		= $this->input->post( 'endDate' );
		$endTime 		= $this->input->post( 'endTime' );
		
		// !!!! skip form validation first. 27MAR2012
		
		if( $this->event_model-> updateShowingTimeSchedule( $eventID, $showtimeID, $startDate, $startTime, $endDate, $endTime ) )
		{
			$data[ 'theMessage' ] = "Successfully changed the showing date and times.";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}//reschedule_process()

	function seal( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to seal this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all yet unconfirmed bookings <br/>(those who are transferring to this.";
		$data['theMessage'] .= "showing time will be defaulted to their old showing time).";
		$data['yesURI'] = base_url().'eventctrl2/seal_process';
		$data['noURI'] = base_url().'eventctrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function seal_process()
	{	
		/*
			Pending check here if someone is booking a slot, so therefore,
			wait for them before proceeding here.
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
		
		$this->bookingmaintenance->cleanDefaultedBookings( $eventID , $showtimeID );
		$this->bookingmaintenance->cleanDefaultedSlots( $eventID , $showtimeID, NULL );
		if( $this->event_model->setForCheckIn(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully sealed the showing time. Guests can now check-in.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
	
	function straggle( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to start straggling for this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all the slots of guests who are not appearing yet.";
		$data['yesURI'] = base_url().'eventctrl2/straggle_process';
		$data['noURI'] = base_url().'eventctrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function straggle_process()
	{	
		/*
			Pending check here if for late which is essential
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
		
		$this->bookingmaintenance->cleanDefaultedBookings( $eventID , $showtimeID );
		$this->bookingmaintenance->cleanDefaultedSlots( $eventID , $showtimeID, NULL );
		$this->bookingmaintenance->forfeitSlotsOfNoShowGuests( $eventID, $showtimeID );
		if( $this->event_model->setForStraggle(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully set straggling the showing time. Chance customers can now take slots.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
		
	function cancel( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to cancel this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all yet unconfirmed bookings <br/>(those who are transferring to this.";
		$data['theMessage'] .= "showing time will be defaulted to their old showing time).";
		$data['yesURI'] = base_url().'eventctrl2/cancel_process';
		$data['noURI'] = base_url().'eventctrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function cancel_process()
	{	
		/*
			Pending check here if someone is booking a slot, so therefore,
			wait for them before proceeding here.
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
		
		$this->bookingmaintenance->cleanDefaultedBookings( $eventID , $showtimeID );
		$this->bookingmaintenance->cleanDefaultedSlots( $eventID , $showtimeID, NULL );
		if( $this->event_model->setAsCancelled(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully cancelled the showing time.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
	
	function finalize( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to finalize this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all the slots of guests who are not appearing yet and prevent any more changes to this event.";
		$data['yesURI'] = base_url().'eventctrl2/finalize_process';
		$data['noURI'] = base_url().'eventctrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function finalize_process()
	{	
		/*
			Pending check here if for late which is essential
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
				
		if( $this->event_model->setAsFinalized(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully finalized the showing time. No more changes allowed to this one.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
	function manage_tc( $eventID, $showtimeID )
	{
		$slots;
		$singleShowtimeObj = NULL;
		$accountNum;
		$ticketClasses = NULL;
		$sessionActivity;
		
		$this->checkAndActOnEventMgr();
		$accountNum = $this->clientsidedata_model->getAccountNum();
		if( !$this->event_model->doesEventBelongToUser( NULL, $eventID, $accountNum ) ){
			die('Event does not belong to you.');
		}
		
		$singleShowtimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID );
		if( $singleShowtimeObj === false )
		{
			die('Showing time not found.');
		}
		$this->clientsidedata_model->setSessionActivity( 'MANAGE_TICKETCLASS', 2 );
		$this->clientsidedata_model->setEventID( $eventID );
		$this->clientsidedata_model->setShowtimeID( $showtimeID  );
		$this->clientsidedata_model->setTicketClassGroupID( $singleShowtimeObj->Ticket_Class_GroupID  );
		$this->clientsidedata_model->setSlotsBeingBooked( $singleShowtimeObj->Slots );
		redirect('eventctrl2/manage_tc_forward');
	}
	
	function manage_tc_forward()
	{	
		$sessionActivity;
		
		$this->checkAndActOnEventMgr();
		$sessionActivity = $this->clientsidedata_model->getSessionActivity();
		
		if( ( $sessionActivity[0] === "MANAGE_TICKETCLASS" and $sessionActivity[1] === 2 )
			 === FALSE			 
		){
			redirect('eventctrl2/manageEvent');
		}
		
		$eventID    = $this->clientsidedata_model->getEventID();
		$showtimeID = $this->clientsidedata_model->getShowtimeID();
		$eventObj	= $this->event_model->getEventInfo( $eventID );		
		$singleShowtimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID );
		
		$seatMapObj = $this->seat_model->getSingleMasterSeatMapData( $singleShowtimeObj->Seat_map_UniqueID );
		$ticketClasses = $this->ticketclass_model->getTicketClasses( $eventID, $singleShowtimeObj->Ticket_Class_GroupID );
		$ticketClassNotShared = $this->ticketclass_model->isTicketClassGroupOnlyForThisShowtime( $eventID, $showtimeID, $singleShowtimeObj->Ticket_Class_GroupID );
		
		$data['eventObj'] 	   = $eventObj;
		$data['seatMapObj']    = $seatMapObj;
		$data['ticketClasses'] = $ticketClasses;
		$data['tcg_not_shared'] = ($ticketClassNotShared) ? 1 : 0;
		
		$this->load->view('manageEvent/manageEvent02_ticketclass', $data);
	}
	
	function managetc_update()
	{
		//die( var_dump( $_POST ) );
		$eventID    = $this->clientsidedata_model->getEventID();
		$showtimeID = $this->clientsidedata_model->getShowtimeID();
		$sessionActivity;
		$seatMap;
		$this->checkAndActOnEventMgr();
		$currentTicketClassGroupID = $this->clientsidedata_model->getTicketClassGroupID();
		$sessionActivity = $this->clientsidedata_model->getSessionActivity();
		$outputTCGID;
		
		$x;
		$classesCount;
		$classes = array();
		$prices = array();
		$slots = array();
		$holdingTime = array();
		$temp = array(); 
		
		if( ( $sessionActivity[0] === "MANAGE_TICKETCLASS" and $sessionActivity[1] === 2 )
			 === FALSE			 
		){
			echo 'FALSE'; return false;
		}
		
		$seatMap = $this->input->post( 'seatMapPullDown' );
		$share_separate = $this->input->post( 'share_separate' );
		unset( $_POST['seatMapPullDown'] );
		unset( $_POST['share_separate'] );
		
		if( true )
		{	// all statements within this if-statement was taken from eventctrl/create_step6
			$x = 0;
			$classesCount = 0;
			foreach( $_POST as $key_x => $val) // isn't this somewhat a security risk because we don't escape?
			{
					$key = mysql_real_escape_string( $key_x );
					if( $this->usefulfunctions_model->startsWith( $key, "price" ) )
					{
						$temp = explode("_",$key);
						$prices[ $temp[1] ] = $val;					
					}else
					if( $this->usefulfunctions_model->startsWith( $key, "slot" ) )
					{					
						$temp = explode("_",$key);
						$slots[ $temp[1] ] = $val;
					}else
					if( $this->usefulfunctions_model->startsWith( $key, "holdingTime" ) )
					{					
						$temp = explode("_",$key);
						$holdingTime[ $temp[1] ] = $val;
					}
					if( $x % 3 == 0) $classes[ $classesCount++ ] = $temp[1];	// count how many classes			
					$x++;														// loop indicator
			}
		}

		if( $share_separate !== false and intval($share_separate) === 1 )
		{										
			$databaseSuccess = TRUE;
			$lastTicketClassGroupID = $this->ticketclass_model->getLastTicketClassGroupID( $eventID );
			$lastTicketClassGroupID++;
			// CODE MISSING: database checkpoint			
			for( $x = 0; $x < $classesCount; $x++ )
			{			
				$databaseSuccess = $this->ticketclass_model->createTicketClass(
					$lastTicketClassGroupID,
					$x+1,
					$eventID,
					$classes[ $x ],
					$prices[ $classes[ $x ] ],
					$slots[ $classes[ $x ] ],
					"IDK",
					"IDY",
					0,
					$holdingTime[ $classes[ $x ] ]
				);				
				if( !$databaseSuccess ){
					// CODE MISSING:  database rollback
					echo "ERROR_DB-FAIL";
					return FALSE;
					break;						
				}								
			}//for			
			// CODE MISSING: database commit			
			// now set ticket class's group id for the said showing time
			$this->event_model->setSingleShowingTimeTicketClass( $eventID, $showtimeID , $lastTicketClassGroupID );
			$outputTCGID = $lastTicketClassGroupID."_NEW";
		}else{
			$ticketClasses = $this->ticketclass_model->getTicketClasses( $eventID, $currentTicketClassGroupID, true );
			$outputTCGID =  $currentTicketClassGroupID."_SAME";;
					
			foreach( $slots as $key => $val )
			{
				$databaseSuccess = $this->ticketclass_model->updateTicketClass(
					$eventID,
					$currentTicketClassGroupID,
					$ticketClasses[ $key ]->UniqueID,					
					$prices[ $key ],
					$slots[ $key ],
					"IDK",
					"IDY",
					0,
					$holdingTime[  $key ]
				);						
				if( !$databaseSuccess ){
					// CODE MISSING:  database rollback
					echo "ERROR_DB-FAIL";
					return FALSE;
					break;						
				}	
			}
		}
		echo "OKAY_".$outputTCGID;
		return true;
	}//managetc_update(..)
	
	function managetc_update_seats()
	{
		$eventID    = $this->clientsidedata_model->getEventID();
		$showtimeID = $this->clientsidedata_model->getShowtimeID();
		$tcgByPost = $this->input->post('_tcg');
		$tcgByCookie = $this->clientsidedata_model->getTicketClassGroupID();
		$tcgChanged = ( $tcgByPost !== false and ( intval($tcgByPost) === intval($tcgByCookie) ) );
		$seatmapUID = $this->input->post( 'seatmapUniqueID' );		
		
		$result = $this->seatmaintenance->insertSeatsOnEventManipulate( $eventID, $showtimeID, $tcgByPost, $seatmapUID, false, $tcgChanged );
		if( $result )
		{
			echo "OKAY";
			return true;
		}else{
			echo "FAIL";
			return false;
		}
	}
	
	function managetc_success()
	{
		$data[ 'theMessage' ] = "Successfully updated the ticket classes.";
		$data[ 'redirect' ] = TRUE;
		$data[ 'redirectURI' ] = base_url().'eventctrl2/manageEvent';
		$data[ 'defaultAction' ] = 'Manage Event';	
		$this->load->view( 'successNotice', $data );
		return true;
	}
	
}//class
