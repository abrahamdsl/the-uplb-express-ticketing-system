<?php
/*
CREATED 28 NOV 2011 2035
*/

class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('CoordinateSecurity_model');
		$this->load->model('Event_model');
		$this->load->model('MakeXML_model');
		$this->load->model('Permission_model');
		$this->load->model('Seat_model');		
		$this->load->model('Slot_model');		
		$this->load->model('TicketClass_model');
		$this->load->model('UsefulFunctions_model');
		$this->load->helper('cookie');
		if( !$this->login_model->isUser_LoggedIn() ){		
			redirect('/SessionCtrl');
		}
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	function book()
	{
		/*
			Created 29DEC2011-2048
		*/
		$configuredEventsInfo = array();
		
		$allEvents = $this->Event_model->getAllEvents();		// get all events first		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->Event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->Event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		$data['showingTimes'] = $showingTimes;
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		$data['currentStep'] = 1;
		$this->load->view( "book/bookStep1", $data );		
	}//book(..)
	
	function book_step2()
	{
		/*
			Created 30DEC2011-1855
		*/
		$eligbilityIndicator = "CCDB7X";
		$eventID = $this->input->post( 'events' );
		$showtimeID = $this->input->post( 'showingTimes');
		$slots = $this->input->post( 'slot' );
		$slotDistributionAmongClasses = Array();
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();					
		$eventInfo;
		$cookie_names;
		$cookie_values;
		
		// validate if form submitted has the correct data
		if( $eventID === false or $showtimeID === false or $slots === false )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
		}
		$showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
		if( $showtimeObj === false )
		{
			// no showing time exists
			redirect('/');
		}
		$ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID );
		if( $ticketClasses === false )
		{
			// no ticket classes exist
			redirect('/');
		}			
		$eventInfo = $this->Event_model->getEventInfo( $eventID );		// get major info of this event
		//Cookie part
		$cookie_values = Array( 
			$eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $eventInfo->Name, 
			$showtimeObj->StartDate, $showtimeObj->StartTime, $showtimeObj->EndDate, $showtimeObj->EndTime,
			intval( $slots ), $eventInfo->Location
		);
		$this->Event_model->setBookingCookies( $cookie_values );		
		// now ticket classes proper
		foreach( $ticketClasses as $singleClass )
		{				
			$serializedClass_Slot_UUID = "";
							
			$eachClassSlots = $this->Slot_model->getSlotsForBooking( $slots, $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $singleClass->UniqueID );				
			if( $eachClassSlots === false ){
				//IN future get near expiring, for now sold out
				$slotDistributionAmongClasses[ $singleClass->Name ]  = false;
				continue;
			}
			//serialize UUIDs of slot
			foreach( $eachClassSlots  as $evSlot )
			{
				$serializedClass_Slot_UUID .= ($evSlot->UUID."_");
			}
			// truncate the last underscore
			$serializedClass_Slot_UUID = substr( $serializedClass_Slot_UUID, 0, strlen( $serializedClass_Slot_UUID )-1 );	
			//set UUIDs of the slots
			$cookie = array(
				'name'   => $singleClass->UniqueID."_slot_UUIDs",
				'value'  => $serializedClass_Slot_UUID,
				'expire' => '3600'				// change later to how long ticketclass hold time
			);
			$this->input->set_cookie($cookie);
									
			$slotDistributionAmongClasses[ $singleClass->Name ]  = true; // for view page						
		}
		/*
			The next 3 variables have been set in cookies earlier. The problem is,
			they aren't usable immediately, so just send directly to view the data and 
			PHP will echo it.
		*/
		$data['ticketClasses'] = $ticketClasses;
		$data['slots'] = $slots;
		$data['eventInfo'] = $this->Event_model->getEventInfo( $eventID );
		$data['showtimeObj'] = $showtimeObj;
		$data['ticketClasses_presence'] = $slotDistributionAmongClasses;
		$data['currentStep'] = 2;
		$this->load->view( 'book/bookStep2', $data );
		
	}//book_step2(..)
		
	function book_step3()
	{		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();					
		$eventID = $this->input->cookie( 'eventID' );;
		$ticketClassGroupID = $this->input->cookie( 'ticketClassGroupID' );
		$uniqueID = $this->input->post( 'selectThisClass' );
		
		if( $uniqueID === false )
		{
			redirect('/');
		}
		
		$selectedTicketClass = $this->TicketClass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $uniqueID );
		$allOtherClasses = $this->TicketClass_model->getTicketClassesExceptThisUniqueID( $eventID, $ticketClassGroupID, $uniqueID );
		if( $selectedTicketClass === false /*or $allOtherClasses === false*/ ) // 08FEB2012-2145, turned into comment condition somewhat ambiguous
		{
			redirect('/');
		}
		$this->Slot_model->freeSlotsBelongingToClasses( $allOtherClasses );		// since we now don't care about these, free so.
		
		
		$data['currentStep'] = 3;
		$this->load->view( 'book/bookStep3', $data);
		
		//die( var_dump( $_POST ) );
	}//book_step3()
	
	function book_step3_cancel( )
	{
		/*
			Created 06FEB2012-1647
			
			Called when client suddenly cancels the process of booking. So, we have to free
			the slots we temporarily reserved for him/her.
		*/
		if( $this->input->is_ajax_request() === false ) redirect('/');
		
		$eventID = $this->input->cookie( 'eventID' );
		$ticketClassGroupID = $this->input->cookie( 'ticketClassGroupID' );
		if( $eventID === false or $ticketClassGroupID === false )
		{
			echo "INVALID_DATA-NEEDED";
			return false;
		}
		// get ticket classes since we have reserved X slots for each ticket classes of the showing time bconcerned
		$ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $ticketClassGroupID );
		$this->Slot_model->freeSlotsBelongingToClasses( $ticketClasses );
		$this->Event_model->deleteBookingCookies();		
		echo "OK";
		return true;
	}//book_step3_cancel
	
	function create()
	{				
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		$this->load->view('createEvent/createEvent_001', $data);
	}//create
	
	function create_step2()
	{		
		// cookie does not immediately take effect so just have to pass it
		$data['eventName'] = $this->input->post( "eventName" );		 
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		$cookie = array(
			'name'   => "eventName",
			'value'  => $this->input->post( 'eventName' ),
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		//if user is accessing this directly, redirect
		if( $this->input->post( 'eventName' ) == FALSE )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	
		
		// is it existent
		if( $this->Event_model->isEventExistent( $this->input->post( 'eventName' ) ) )
		{
			$data['error'] = "CUSTOM";	
			$data['redirect'] = false;
			$data['theMessage'] = "There is already an event with the same name. Please choose another one";					
			$this->load->view( 'errorNotice', $data ) ;
			return false;
		}
		
		//now insert basic to DB
		$this->Event_model->createEvent_basic();						
		$this->load->view('createEvent/createEvent_002', $data);		
	}//create_step2;
	
	function create_step3()
	{
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		$schedules = array();
		
		// 11DEC2011-1609: I am using the PHP $_POST acess method instead of CI's
		// 		since I am just to check if the fields are included in the submitted data
		if( !isset( $_POST['timeFrames_hidden'] ) and
			!isset( $_POST['dateFrames_hidden'] ) 
		){
			redirect('/');
		}
				
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['scheduleMatrix'] = $this->Event_model->constructMatrix();
		
		$this->load->view( 'createEvent/createEvent_003', $data );		
	}//create_step3
	
	function create_step4( $repeat = false )
	{		
		$unconfiguredShowingTimes;
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		
		/* we go through here if first time configuring: first time configuration left some
			showing times unconfigured then finished configuring and then there are still 
			unconfigured so go back here
		*/
		$repeatPOST =  $this->input->post( 'repeat' );
		
		if( !is_bool( $repeatPOST ) )
		{
			if( strtolower( $repeatPOST ) == "true" ) $repeat = true;
		}		
		
		if( !$repeat ){
			$cookie = array(
				'name'   => "createEvent_step",
				'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
				'expire' => '3600'
			);
			$this->input->set_cookie($cookie);
			
			
			
			if( $_COOKIE['createEvent_step'] != 3 ){
				/*$data['error'] = "UNAUTHORIZED_ACCESS";									
				$this->load->view( 'errorNotice', $data ) ;
				return false;*/
			}
			// let's get the last uniqueID for this event if ever
			$lastUniqueID = $this->Event_model->getLastShowingTimeUniqueID( $this->input->cookie('eventID') );
			
			// now, with the data, create showings and insert them to the database			
			$this->Event_model->createShowings( $lastUniqueID );
		} //if
		
		// now, get such showings straight from the DB
		$unconfiguredShowingTimes = $this->Event_model->getUnconfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		if( $unconfiguredShowingTimes == NULL )
		{			
			$data['error'] = "CUSTOM";									
			$data['theMessage'] = "INTERNAL SERVER ERROR<br/><br/>No unconfigured showing times got";
			$data['redirect'] = false;
			$this->load->view( 'errorNotice', $data ) ;
			return FALSE;
		}		
		
		$data['unconfiguredShowingTimes'] = $unconfiguredShowingTimes;
		$this->load->view('createEvent/createEvent_004', $data);				
	}//create_step4(..)
	
	function create_step5()
	{
		/*
			CREATED 12DEC2011-1604
			
			Assumption for $_POST: last element is 'slots' / not a showing time to be configured
		*/
		$x;		
		$y = count($_POST);
		$slots = $this->input->post( 'slots' );
		
		/* correct page submitting if the 'slots' index exists and array size or
		   contents of $_POST is > 1 (i.e., for 'slots' and at least one showing time to configure
		 */
		if( !( $slots and $y > 1 ) )
		{
			redirect('/');			
		}
		
		// re-set cookie
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		/*			
			make some changes to showing times
		*/
		$x = 0;	// we need this counters / loop vars for considering the 'slots' index of $_POST
		$y--;
		foreach( $_POST as $key => $val)
		{		
			if( $x == $y ) break;	// this $_POST index now is 'slots'
			
			//set status of the showing times to "being_configured"
			$this->Event_model->setShowingTimeConfigStat( 
				$this->input->cookie( 'eventID' ),
				$key,
				"BEING_CONFIGURED"
			);
			
			//set slots of the showing times to the new one
			$this->Event_model->setShowingTimeSlots( 
				$this->input->cookie( 'eventID' ),
				$key,
				$slots
			);
			
			$x++;
		}//foreach(..)
		// get seat map available
		$data['seatMaps'] = $this->Seat_model->getUsableSeatMaps( $slots );
		
		// get ticket classes		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['ticketClasses_default'] = $this->TicketClass_model->getDefaultTicketClasses();
		$data['maxSlots'] = $slots;
		if( $data == NULL ){
			$data['error'] = "CUSTOM";									
			$data['theMessage'] = "INTERNAL SERVER ERROR<br/><br/>Default ticket classes were not found in the database! Please seek administrator help.";
			$data['redirect'] = false;
			$this->load->view( 'errorNotice', $data ) ;
			return FALSE;
		}		
		$this->load->view('createEvent/createEvent_005', $data);				
	}//create_step5(..)
	
	function create_step6()
	{
		/*
			CREATED 12DEC2011-2109
			
		
		*/		
		$x;
		$classesCount;
		$classes = array();
		$prices = array();
		$slots = array();
		$holdingTime = array();
		$temp = array(); 
		$lastTicketClassGroupID;		
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$seatMap;
		
		// get first seatMap info and unset it from post
		$seatMap = $this->input->post( 'seatMapPullDown' );
		unset( $_POST['seatMapPullDown'] );
		
		//iterate through submitted values, tokenize them into respective classes and assign
		$x = 0;
		$classesCount = 0;
		foreach( $_POST as $key => $val) // isn't this somewhat a security risk?
		{						
				if( $this->UsefulFunctions_model->startsWith( $key, "price" ) )
				{
					$temp = explode("_",$key);
					$prices[ $temp[1] ] = $val;					
				}else
				if( $this->UsefulFunctions_model->startsWith( $key, "slot" ) )
				{					
					$temp = explode("_",$key);
					$slots[ $temp[1] ] = $val;
				}else
				if( $this->UsefulFunctions_model->startsWith( $key, "holdingTime" ) )
				{					
					$temp = explode("_",$key);
					$holdingTime[ $temp[1] ] = $val;
				}
				if( $x % 3 == 0) $classes[ $classesCount++ ] = $temp[1];	// count how many classes			
				$x++;														// loop indicator
		}
								
		$databaseSuccess = TRUE;
		$lastTicketClassGroupID = $this->TicketClass_model->getLastTicketClassGroupID( $this->input->cookie( 'eventID' ) );
		$lastTicketClassGroupID++;
		// CODE MISSING: database checkpoint
		for( $x = 0; $x < $classesCount; $x++ )
		{			
			$databaseSuccess = $this->TicketClass_model->createTicketClass(
				$lastTicketClassGroupID,
				$x+1,
				$this->input->cookie( 'eventID' ),
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
				$data['error'] = "CUSTOM";									
				$data['theMessage'] = "INTERNAL SERVER ERROR<br/><br/>Database insertion failed.";
				$data['redirect'] = false;
				$this->load->view( 'errorNotice', $data ) ;
				return FALSE;
				break;						
			}								
		}//for
		// CODE MISSING: database commit
		
		// now set ticket class's group id for the being configured events
		$this->Event_model->setShowingTimeTicketClass( $this->input->cookie( 'eventID' ), $lastTicketClassGroupID );
		
		/*
			For each showing time being configured, create actual slots.
		*/
		foreach( $data['beingConfiguredShowingTimes'] as $eachShowingTime )
		{
			$thisST_ticketClasses = $this->TicketClass_model->getTicketClasses( $this->input->cookie( 'eventID' ), $lastTicketClassGroupID );						
			foreach( $thisST_ticketClasses as $eachTicketClass )
			{			
				$this->Slot_model->createSlots( 
					$eachTicketClass->Slots,
					$this->input->cookie( 'eventID' ),
					$eachShowingTime->UniqueID,
					$lastTicketClassGroupID,
					$eachTicketClass->UniqueID
				);
			}
		}						
								
		echo "OK-JQXHR"	;
		// no loading of view since this was "ajaxified"
	}//create_step6(..)
	
	function create_step6_seats()
	{
		/*
			Created 04FEB2012-1852
		*/		
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		foreach( $beingConfiguredShowingTimes as $eachSession )
		{
			// duplicate seat pattern to the table containing actual seats
			$this->Seat_model->copyDefaultSeatsToActual( $this->input->post( 'seatmapUniqueID' ) );
			// update the eventID and UniqueID of the newly duplicated seats
			$this->Seat_model->updateNewlyCopiedSeats( $this->input->cookie( 'eventID' ),  $eachSession->UniqueID );
			// get the ticket classes of the events being configured
			$ticketClasses_obj = $this->TicketClass_model->getTicketClasses( $this->input->cookie( 'eventID' ),  $eachSession->Ticket_Class_GroupID );
			// turn the previously retrieved ticket classes into an array accessible by the class name
			$ticketClasses = $this->TicketClass_model->makeArray_NameAsKey( $ticketClasses_obj );
			// get seat map object to access its rows and cols, for use in the loop later
			$seatmap_obj = $this->Seat_model->getSingleMasterSeatMapData( $this->input->post( 'seatmapUniqueID' ) );
			for( $x = 0; $x < $seatmap_obj->Rows; $x++)
			{
				for( $y = 0; $y < $seatmap_obj->Cols; $y++)
				{
					$seatValue = $this->input->post( 'seat_'.$x.'-'.$y );
					$status;
					$ticketClassUniqueID;
					$sql_command = "UPDATE `seats_actual` SET `Status` = ? ";
					$sql_command_End = "WHERE  `Seat_map_UniqueID` = ? AND `EventID` = ? AND `Showing_Time_ID` = ? AND `Matrix_x` = ? AND `Matrix_y` = ?";
					if( $seatValue === "0" or $seatValue === false )
					{
						//unselected
						$status = -2;
						$this->db->query( 	$sql_command.$sql_command_End, array(
												$status,												
												$this->input->post( 'seatmapUniqueID' ),
												$this->input->cookie( 'eventID' ),
												$eachSession->UniqueID,
												$x,
												$y
											)
						);
					}else if( $seatValue === "-1" )
					{
						// no class assigned
						$status = -1;
						$this->db->query( 	$sql_command.$sql_command_End, array(
												$status,												
												$this->input->post( 'seatmapUniqueID' ),
												$this->input->cookie( 'eventID' ),
												$eachSession->UniqueID,
												$x,
												$y
											)
						);
					}else{	// contains class in string
						$status = 0;
						$ticketClassUniqueID = $ticketClasses[ $seatValue ]->UniqueID;
						$this->db->query( 	$sql_command.", `Ticket_Class_GroupID` = ?, `Ticket_Class_UniqueID` = ? ".$sql_command_End,
											array(
												$status,
												$eachSession->Ticket_Class_GroupID,
												$ticketClassUniqueID,
												$this->input->post( 'seatmapUniqueID' ),
												$this->input->cookie( 'eventID' ),
												$eachSession->UniqueID,
												$x,
												$y
											)
						);
					}
				}
			}
		}
		echo $this->CoordinateSecurity_model->createActivity( 'CREATE_EVENT', 'JQXHR', 'string' );		
	}//create_step6_seats()
	
	function create_step6_forward()
	{
		/*
			Created 04FEB2012-1845
		
			This is created to 'entertain' the request of the client page
			to load entirely the next page.
		*/
	
		$pageEligibilityIndicator = "JQXHR";
	
		/*
			Page access eligibility check
		*/
		// does UUID exist,  If not redirect.
		if( $this->CoordinateSecurity_model->doesActivityExist( $this->input->post( 'uuid' ) ) === false ) redirect('/');		
		// UUID exists. Does activity indicator has valid value? If not redirect.
		if( $this->CoordinateSecurity_model->isActivityEqual( $this->input->post( 'uuid' ), $pageEligibilityIndicator , "string" ) === false ) redirect('/' );
		
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();						
		$this->load->view('createEvent/createEvent_006', $data);				
	}
	
	function create_step7()
	{
		//echo( var_dump($_POST ) );
		// START: validation if correct page is being submitted
		$correctPage = true;
		$stillUnconfiguredEvents;
		
		if( !$this->input->post("hidden_selling_dateStart") ) $correctPage = false;
		if( !$this->input->post("hidden_selling_dateEnd") ) $correctPage = false;
		if( !$this->input->post("selling_timeStart") ) $correctPage = false;
		if( !$this->input->post("selling_timeEnd") ) $correctPage = false;
		if( !$this->input->post("deadlineChoose") ) $correctPage = false;
		if( !$this->input->post("bookCompletionTime") ) $correctPage = false;
		if( !$this->input->post("seatNone_StillSell") ) $correctPage = false;
		if( !$this->input->post("confirmationSeatReqd") ) $correctPage = false;
		
		if( !$correctPage )	// invalid page submitting to this or directly accessing
		{
			redirect("/");
		}
		// END: validation if correct page is being submitted
		
		if( !$this->Event_model->setParticulars( $this->input->cookie( 'eventID' ) ) )
		{
			echo "Create Step 7 Set Particulars Fail.";
			die();
		}
		$this->Event_model->stopShowingTimeConfiguration( $this->input->cookie( 'eventID' ) );	// now mark these as 'CONFIGURED'
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		// get still unconfigured events
		$stillUnconfiguredEvents = $this->Event_model->getUnconfiguredShowingTimes(  $this->input->cookie( 'eventID' )  );
		if( count( $stillUnconfiguredEvents ) > 0 )
		{											
			$this->load->view('createEvent/stillUnconfiguredNotice', $data);
		}else{
			$this->load->view('createEvent/allConfiguredNotice', $data);
		}		
		//$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		//$this->load->view('createEvent/createEvent_007', $data);				
		//die( var_dump( $_POST ) );
	}//create_step7

	function deleteEventCompletely()
	{
		$deleteResult;
		//die( var_dump ($_POST ));
		$deleteResult = $this->Event_model->deleteAllEventInfo( $this->input->post( 'eventID' ) );
		if( $deleteResult )
		{
			echo "Success";
		}else{
			echo "Fail";
		}
		
		$this->manage();
	}//deleteEventCompletely
	
	function doesEventExist()
	{
		$name = $this->input->post( 'eventName' ); 
		
		if( $name == NULL) return FALSE;
		return $this->Event_model->isEventExistent( $name );	
	} //doesEventExist
	
	function getConfiguredShowingTimes( $eventID = null )	
	{
		/*
			Created 30DEC2011-1053
		*/
		$allConfiguredShowingTimes;
		
		//Added 29JAN2012-1530: user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
				
		$eventID = $this->input->post( 'eventID' );
		if( $eventID === false )
		{
			echo "INVALID_POST-DATA-REQUIRED";
		}
		$allConfiguredShowingTimes = $this->Event_model->getConfiguredShowingTimes( $eventID , true);
		if( count( $allConfiguredShowingTimes ) == 0 )
		{
			echo "ERROR_No configured showing times.";
			return false;
		}
		$xmlResult = $this->MakeXML_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
		echo $xmlResult;
		return true;		
	}//getConfiguredShowingTimes(..)
	
	function manage()
	{
		//created 20DEC2011-1423
		$data['events'] = $this->Event_model->getAllEvents();
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		
		$this->load->view('manageEvent/home', $data);
	}//manageEvent
		
} //class
?>