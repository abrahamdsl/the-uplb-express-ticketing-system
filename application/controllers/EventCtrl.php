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
		$this->load->model('Booking_model');
		$this->load->model('CoordinateSecurity_model');
		$this->load->model('Event_model');
		$this->load->model('Guest_model');
		$this->load->model('MakeXML_model');
		$this->load->model('Payment_model');
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
		redirect( 'EventCtrl/book' );		
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
      $showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 	// counter check against spoofing
      if( $showtimeObj === false )
      {
         // no showing time exists
		 $data['error'] = "CUSTOM";         
		 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time not found. Are you hacking the app?  :-D ";
         $this->load->view( 'errorNotice', $data );
		 return false;
      }
      $ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID );
      if( $ticketClasses === false )
      {
         // no ticket classes exist
		 $data['error'] = "CUSTOM";         
		 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time marked as for sale but there isn't any ticket class yet.";
         $this->load->view( 'errorNotice', $data );  
		return false;		 
      }         
      $eventInfo = $this->Event_model->getEventInfo( $eventID );      // get major info of this event
      //Cookie part
      $cookie_values = Array( 
         $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $eventInfo->Name, 
         $showtimeObj->StartDate, $showtimeObj->StartTime, $showtimeObj->EndDate, $showtimeObj->EndTime,
         intval( $slots ), $eventInfo->Location, 'XXXXX', '-1'
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
            'expire' => '3600'            // change later to how long ticketclass hold time
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
		// now set the uniqueID of the ticketclass
		$cookie = array(
				'name'   => 'ticketClassUniqueID',
				'value'  => $uniqueID,
				'expire' => '3600'				// change later to how long ticketclass hold time
		);
		$this->input->set_cookie($cookie);
			
		$newSessionData = array(
            'eligibilityIndicator'  =>  $this->CoordinateSecurity_model->createActivity( "BOOK", "3", "int" )
        );
		$this->session->set_userdata( $newSessionData );
		redirect( 'EventCtrl/book_step3_forward' );
	}//book_step3()
	
	function book_step3_cancel()
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
		$this->Booking_model->deleteAllBookingInfo( $this->input->cookie( 'bookingNumber' ) );	// should be restricted to when cancelling on step 4 onwards
		$this->Payment_model->deleteAllBookingPurchases( $this->input->cookie( 'bookingNumber' ) );	// should be restricted to when cancelling on step 4 onwards
		$this->Event_model->deleteBookingCookies();	
		echo "OK";
		return true;
	}//book_step3_cancel
	
	function book_step3_forward(){
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();	
		$uuidEligibility;
		
		// start: access validity indicator
		$uuidEligibility = $this->session->userdata( 'eligibilityIndicator' );
		if(  $uuidEligibility === false or
		    $this->CoordinateSecurity_model->isActivityEqual( $uuidEligibility, "3", "int" ) === false
		){			
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// end: access validity indicator
	
		$data['currentStep'] = 3;								
		$this->load->view( 'book/bookStep3', $data);
	}//book_step3_forward
	
	function book_step4()
	{		
		$slots = intval( $this->input->cookie( "slots_being_booked" ) );
		$ticketClassUID = $this->input->cookie( 'ticketClassUniqueID' );
		$guestUUIDs = $this->input->cookie( $ticketClassUID."_slot_UUIDs" );
		$ticketClassObj = $this->TicketClass_model->getSingleTicketClass( 
			$this->input->cookie( 'eventID' ), 
			$this->input->cookie( 'ticketClassGroupID' ), 
			$ticketClassUID
		);
		
		$bookingNumber = $this->Booking_model->generateBookingNumber();
		$bookingPaymentDeadline = $this->Event_model->getShowingTimePaymentDeadline( $this->input->cookie( 'eventID' ), $this->input->cookie( 'showtimeID' ));
		// create booking "upper" details
		$this->Booking_model->createBookingDetails( 
			$bookingNumber,
			$this->input->cookie( 'eventID' ),
			$this->input->cookie( 'showtimeID' ),
			$this->input->cookie( 'ticketClassGroupID' ),
			$ticketClassUID,
			$this->session->userData('accountNum')
		);
		// now, create entries for the charges.
		$this->Payment_model->createPurchase( 
			$bookingNumber,
			"TICKET",
			$ticketClassObj->Name." Class",
			$slots,
			intval($slots) * intval( $ticketClassObj->Price),
			$bookingPaymentDeadline["date"],
			$bookingPaymentDeadline["time"]
		);
		// now, insert form data submitted
		for( $x = 0; $x < $slots; $x++ )
		{
			$identifier = "g".($x+1)."-";
						
			$this->Guest_model->insertGuestDetails(
					$bookingNumber,
					intval( $this->input->post( $identifier."accountNum" ) ),
					$this->input->post( $identifier."firstName" ),					
					$this->input->post( $identifier."middleName" ),					
					$this->input->post( $identifier."lastName" ),					
					$this->input->post( $identifier."gender" ),					
					$this->input->post( $identifier."cellphone" ),					
					$this->input->post( $identifier."landline" ),					
					$this->input->post( $identifier."email_01" )
			);
		}
		// now set the bookingNumber for cookie access
		$cookie = array(
				'name' => 'bookingNumber',
				'value'  => $bookingNumber,
				'expire' => 3600				// change later to how long ticketclass hold time
		);
		$this->input->set_cookie($cookie);
		
		$newSessionData = array(
            'eligibilityIndicator'  =>  $this->CoordinateSecurity_model->createActivity( "BOOK", "4", "int" ),
			'paymentDeadline_Date' => $bookingPaymentDeadline["date"],
			'paymentDeadline_Time' => $bookingPaymentDeadline["time"]
        );
		$this->session->set_userdata( $newSessionData );
		
		redirect( 'EventCtrl/book_step4_forward' );
	}//book_step4
		
	function book_step4_forward(){
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();	
		$uuidEligibility;
		$x = 0;
		$ticketClassUID = $this->input->cookie( 'ticketClassUniqueID' );
		$guestUUIDs = $this->input->cookie( $ticketClassUID."_slot_UUIDs" );
		$guestUUIDs_tokenized = explode('_', $guestUUIDs );
		
		// start: access validity indicator
		$uuidEligibility = $this->session->userdata( 'eligibilityIndicator' );
		if(  $uuidEligibility === false or
		    $this->CoordinateSecurity_model->isActivityEqual( $uuidEligibility, "4", "int" ) === false
		){			
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// end: access validity indicator
	
		$data['currentStep'] = 4;							
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );
		foreach( $data['guests'] as $eachGuest )
		{
			$this->Slot_model->assignSlotToGuest(
				$this->input->cookie( 'eventID' ),
				$this->input->cookie( 'showtimeID' ),
				$guestUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
			);
		}				
		$this->CoordinateSecurity_model->updateActivity( $this->session->userdata( 'eligibilityIndicator' ), '5' );
		$this->load->view( 'book/bookStep4', $data);
	}//book_step4_forward
	
	function book_step5()
	{
		/*
			Created 13FEB2012-2334
		*/
		//die(var_dump( $_POST ));
		$slots;
		$x;
		$uuidEligibility;
		$sendSeatInfoToView = Array();
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();	
		
		// start: access validity indicator		
		$slots =  $this->input->cookie( 'slots_being_booked' );				
		$uuidEligibility = $this->session->userdata( 'eligibilityIndicator' );
		if(  $uuidEligibility === false or
		     $this->CoordinateSecurity_model->isActivityEqual( $uuidEligibility, "5", "int" ) === false or
			 $slots === false or 
			 count( $_POST ) < 2 
		){			
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// end: access validity indicator
		
		$slots = null;
		$slots = intval( $this->input->cookie( 'slots_being_booked' ) );
		for( $x = 0; $x < $slots; $x++ )
		{
			$seatMatrix = $this->input->post( "g".($x+1)."_seatMatrix" );
			$seatMatrix_tokenized = explode( '_', $seatMatrix );
			$guestUUID = $this->input->post(  "g".($x+1)."_uuid" );
			$sendSeatInfoToView[ $guestUUID ] = $this->Seat_model->getVisualRepresentation(
				$seatMatrix_tokenized[0], 
				$seatMatrix_tokenized[1],
				$this->input->cookie( 'eventID' ),
				$this->input->cookie( 'showtimeID' )
			); 
			$this->Seat_model->markSeatAsAssigned(
				$this->input->cookie( 'eventID' ),
				$this->input->cookie( 'showtimeID' ),
				$seatMatrix_tokenized[0], 
				$seatMatrix_tokenized[1]
			);
			$this->Guest_model->assignSeatToGuest( $guestUUID, $seatMatrix_tokenized[0], $seatMatrix_tokenized[1] );
			
		}
		$data['currentStep'] = 5;
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent( $this->input->cookie( 'eventID' ), $this->input->cookie( 'showtimeID' ) );	
		$data['purchases'] = $this->Payment_model->getUnpaidPurchases( $this->input->cookie( 'bookingNumber' ) );
		$data['seatVisuals'] = $sendSeatInfoToView;
		//die( var_dump( $_POST ) );
		$this->load->view( 'book/bookStep5', $data );
	}
	
	function book_step6()
	{
		//die( var_dump( $_POST ) );
		$clientUUIDs;
		$guestSeats = Array();
		
		$clientUUIDs = explode( "_" , $this->input->post(  $this->input->cookie( 'ticketClassUniqueID' )."_slot_UUIDs"  ) ) ;
		$paymentChannel = $this->input->post( 'paymentChannel' );
		$paymentChannel_obj;
		
		// start: access validity indicator
		if( $paymentChannel === false  )
		{			
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// start: end validity indicator
		
		$paymentChannel = null;
		$paymentChannel = intval( $this->input->post( 'paymentChannel' ) );
		$paymentChannel_obj =  $this->Payment_model->getSinglePaymentChannel(
			$this->input->cookie( 'eventID' ),
			$this->input->cookie( 'showtimeID' ),
			$paymentChannel
		);
		$data['currentStep'] = 6;
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );
		$data['singleChannel'] = $paymentChannel_obj;
		$data['purchases'] = $this->Payment_model->getUnpaidPurchases( $this->input->cookie( 'bookingNumber' ) );		
		$this->Booking_model->markAsPendingPayment( $this->input->cookie( 'bookingNumber' ), "NEW" );
		
		/*
			Iterate through each guest details, by their UUIDs, get the slot record 
			assigned to them, get the seat pointer, then by that pointer together with
			some cookies, access the visual representation of the seat by accessing the seat record.
		*/
		foreach( $data['guests'] as $eachGuest )
		{
			// get slot record, as the seat pointer is there
			$eSlotObject = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
			$eSeatObject = $this->Seat_model->getSingleActualSeatData( 
				$eSlotObject->Seat_x, 
				$eSlotObject->Seat_y,
				$this->input->cookie( 'eventID' ), 
				$this->input->cookie( 'showtimeID' )
			);
			$guestSeats[ $eachGuest->UUID ] = ( $eSeatObject->Visual_row."-".$eSeatObject->Visual_col );
		}
		
		$data[ 'seatVisuals' ] = $guestSeats;
		if( $paymentChannel_obj->Type == "COD" )
		{
			$this->load->view( 'book/bookStep6_COD', $data);
		}else
		if( $paymentChannel_obj->Type == "ONLINE" )
		{
			// later
		}else{
			die( 'Payment mode not yet supported.'  );
		}		
	}
	
	function create()
	{	
		/*
			Set the session data we need first.
		*/
		$newdata = array(
                   'createEvent_step'  => 1,
                   'createEvent_start_date' => date("Y-m-d"),
				   'createEvent_start_time' => date("H:i"),
                   'createEvent_ID' => -1
           );
		$this->session->set_userdata( $newdata );
				
		redirect( 'EventCtrl/create_step1_forward' );
	}//create
	
	function create_step1_forward(){
	
		if( $this->session->userdata( 'createEvent_step' ) === 1 )	$this->load->view( 'createEvent/createEvent_001' );
		else
			redirect( '/EventCtrl/create' );
	}
	
	function create_step2()
	{	
		//if user is accessing this without going to step 1 first, redirect
		if( $this->session->userdata( 'createEvent_step' ) !== 1 or  $this->input->post( 'eventName' ) == FALSE )
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
					
		$this->session->set_userdata( 'createEvent_step', 2 );	// increase our session tracker
		// event name is not that essential so we can just let cookies handle that
		$cookie = array(
			'name'   => "eventName",
			'value'  => $this->input->post( 'eventName' ),
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);							
				
		//now insert basic to DB, the cookie for eventID is also made there
		$this->Event_model->createEvent_basic();			
		redirect( 'EventCtrl/create_step2_forward' );
	}//create_step2;
	
	function create_step2_forward()
	{
		if( $this->session->userdata( 'createEvent_step' ) !== 2 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$this->load->view( 'createEvent/createEvent_002' );		
	}
	
	function create_step3()
	{
		//if user is accessing this without going to step 2 first, redirect
		if( $this->session->userdata( 'createEvent_step' ) !== 2 )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
				
		$schedules = array();
		
		/*	 Check if the fields are included in the submitted data. 	
			11DEC2011-1609: Created		
			19FEB2012 14:20 Changed to BOOLEAN false checking.
		 */
		if( $this->input->post( 'timeFrames_hidden' ) === FALSE or
			 $this->input->post( 'dateFrames_hidden' ) === FALSE
		){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$this->session->set_userdata( 'createEvent_step', 3 );
		$this->session->set_userdata( 'timeFrames_hidden', $this->input->post( 'timeFrames_hidden' ) );
		$this->session->set_userdata( 'dateFrames_hidden', $this->input->post( 'dateFrames_hidden' ) );
		redirect( 'EventCtrl/create_step3_forward' );
	}//create_step3
	
	function create_step3_forward()
	{		
		if( $this->session->userdata( 'createEvent_step' ) !== 3 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// construct schedules and output HTML page
		$data['scheduleMatrix'] = $this->Event_model->constructMatrix();		
		$this->load->view( 'createEvent/createEvent_003', $data );		
	}// create_step3_forward()
	
	function create_step4( $repeat = false )
	{		
		$unconfiguredShowingTimes;		
		$repeatPOST;
		/* we go through here if 
			first time configuring: first time configuration left some
			showing times unconfigured then finished configuring and then there are still 
			unconfigured so go back here
		*/
		
		$repeatPOST =  $this->input->post( 'repeat' );		
		if( !is_bool( $repeatPOST ) )
		{
			if( strtolower( $repeatPOST ) == "true" ) $repeat = true;
		}		
		
		if( !$repeat ){
			if( $this->session->userdata( 'createEvent_step' ) !== 3 )
			{
				$data['error'] = "UNAUTHORIZED_ACCESS";			
				$this->load->view( 'errorNotice', $data );
				return false;
			}		
				
			// let's get the last uniqueID for the showing times of this event if ever
			$lastUniqueID = $this->Event_model->getLastShowingTimeUniqueID( $this->input->cookie('eventID') );
			
			// now, with the data, create showings and insert them to the database			
			$this->Event_model->createShowings( $lastUniqueID, $this->input->cookie('eventID') );
		}//if !$repeat
		$this->session->set_userdata( 'createEvent_step', 4 );
		redirect( 'EventCtrl/create_step4_forward' );
	}//create_step4(..)
	
	function create_step4_forward()
	{
		if( $this->session->userdata( 'createEvent_step' ) !== 4 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		if( $this->input->cookie( 'eventID' ) === FALSE )
		{
			$data['error'] = "CUSTOM";			
			$data['theMessage'] = "COOKIE MANIPULATION DETECTED<br/><br/>Why did you delete your cookie(s)????? Reverting changes made to the database ... ";
			$data['redirect'] = false;
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
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
	}
	
	function create_step5()
	{
		/*
			CREATED 12DEC2011-1604
			
			Assumption for $_POST: last element is 'slots' / not a showing time to be configured
		*/
		$x;		
		$y;
		$slots;
		
		if( $this->session->userdata( 'createEvent_step' ) !== 4 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$y = count($_POST);
		$slots = $this->input->post( 'slots' );
		
		/* correct page submitting if the 'slots' index exists and array size or
		   contents of $_POST is > 1 (i.e., for 'slots' and at least one showing time to configure
		 */
		if( ( $slots and $y > 1 ) === FALSE )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;			
		}
				
		/*			
			make some changes to showing times
		*/
		$x = 0;	// we need these counters / loop vars for considering the 'slots' index of $_POST
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
		$this->session->set_userdata( 'createEvent_step', 5 );
		$this->session->set_userdata( 'slots_per_st', $slots );
		redirect( 'EventCtrl/create_step5_forward' );
	}//create_step5(..)
	
	function create_step5_forward()
	{
		$slots;
		
		if( $this->session->userdata( 'createEvent_step' ) !== 5 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$slots = $this->session->userdata( 'slots_per_st' );									// get slot from session data
		$data['seatMaps'] = $this->Seat_model->getUsableSeatMaps( $slots ); 					// get seat map available
		$data['ticketClasses_default'] = $this->TicketClass_model->getDefaultTicketClasses();	// get ticket classes				
		$data['maxSlots'] = $slots;	
		if( $data['ticketClasses_default'] == NULL ){
			$data['error'] = "CUSTOM";									
			$data['theMessage'] = "INTERNAL SERVER ERROR<br/><br/>Default ticket classes were not found in the database! Please seek administrator help.";
			$data['redirect'] = false;
			$this->load->view( 'errorNotice', $data ) ;
			return FALSE;
		}		
		$this->load->view('createEvent/createEvent_005', $data);				
	}
	
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
		$data['beingConfiguredShowingTimes'] = NULL;
		$seatMap;
		
		if( $this->session->userdata( 'createEvent_step' ) !== 5 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$data['beingConfiguredShowingTimes'] =  $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		// get first seatMap info and unset it from post to not interfere with processing later on
		$seatMap = $this->input->post( 'seatMapPullDown' );
		unset( $_POST['seatMapPullDown'] );
		
		/*
			Iterate through submitted values, tokenize them into respective classes and assign.
		*/
		$x = 0;
		$classesCount = 0;
		foreach( $_POST as $key => $val) // isn't this somewhat a security risk because we don't escape?
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
		$this->db->trans_start();
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
		$this->db->trans_complete();		
		echo "OK-JQXHR"	;
		// no loading of view since this was "ajaxified"
	}//create_step6(..)
	
	function create_step6_seats()
	{
		/*
			Created 04FEB2012-1852
		*/		
		
		if( $this->session->userdata( 'createEvent_step' ) !== 5 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$this->db->trans_start();
		foreach( $beingConfiguredShowingTimes as $eachSession )
		{
			//update the seat map of the showing time
			$this->Event_model->setShowingTimeSeatMap( $this->input->post( 'seatmapUniqueID' ), $this->input->cookie( 'eventID' ), $eachSession->UniqueID );
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
					$sql_command_End = "WHERE `EventID` = ? AND `Showing_Time_ID` = ? AND `Matrix_x` = ? AND `Matrix_y` = ?";
					if( $seatValue === "0" or $seatValue === false )
					{
						// aisle
						$status = -2;
						$this->db->query( 	$sql_command.$sql_command_End, array(
												$status,																								
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
		$this->session->set_userdata( 'createEvent_step', 6 );
		$this->db->trans_complete();
		echo $this->CoordinateSecurity_model->createActivity( 'CREATE_EVENT', 'JQXHR', 'string' );		
	}//create_step6_seats()
	
	function create_step6_forward()
	{
		/*
			Created 04FEB2012-1845
		
			This is created to 'entertain' the request of the client page
			to load entirely the next page.
			
			Changed | 19FEB2012-1507 | Changed page access eligibility check to just accessing session data
		*/			
	
		
		//	Page access eligibility check		
		if( $this->session->userdata( 'createEvent_step' ) !== 6 )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannels();
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$this->load->view('createEvent/createEvent_006', $data);				
	}
	
	function create_step7()
	{		
		// START: validation if correct page is being submitted
		$correctPage = true;
		$stillUnconfiguredEvents;
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$paymentChannels = $this->Payment_model->getPaymentChannels();
		$paymentChannelsPosted = Array(); 
		$x = 0;
		
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
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// END: validation if correct page is being submitted
		
		if( !$this->Event_model->setParticulars( $this->input->cookie( 'eventID' ) ) )
		{
			echo "Create Step 7 Set Particulars Fail.";
			die();
		}
		foreach( $paymentChannels as $singleChannel )
		{
			// If a payment channel was selected, its value in the array should be an integer or not boolean false
			$paymentChannelsPosted[ $x++ ] = $this->input->post( 'pChannel_'.$singleChannel->UniqueID ); 
		}
		foreach( $paymentChannelsPosted as $eachPosted )
		{
			if( $eachPosted !== false )
			{
				foreach( $beingConfiguredShowingTimes as $singleBCST )
				{
					$this->Payment_model->addPaymentChannel_ToShowTime(
						$this->input->cookie( 'eventID' ),
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."
					);
				}			
			}
		}
		$this->Event_model->stopShowingTimeConfiguration( $this->input->cookie( 'eventID' ) );	// now mark these as 'CONFIGURED'		
		// get still unconfigured events
		$stillUnconfiguredEvents = $this->Event_model->getUnconfiguredShowingTimes(  $this->input->cookie( 'eventID' )  );
		if( count( $stillUnconfiguredEvents ) > 0 )
		{											
			$this->load->view('createEvent/stillUnconfiguredNotice' );
		}else{
			$this->load->view('createEvent/allConfiguredNotice' );
		}				
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
		
	function postBookingCleanup()
	{
		/*
			Created 19FEB2012-1751
			
			Does anything that should be done after a user successfully books,
			like clearing cookies
		*/
		// deal first with the "X_slot_UUIDs" cookie since it's not included in the pre-determined cookies
		delete_cookie( $this->input->cookie( 'ticketClassUniqueID').'_slot_UUIDs'  );		
		$this->Event_model->deleteBookingCookies();
		if( $this->input->is_ajax_request() === FALSE )redirect( '/' );
	}	
} //class
?>