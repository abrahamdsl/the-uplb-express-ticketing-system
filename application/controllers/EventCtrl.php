<?php
/*
CREATED 28 NOV 2011 2035
*/
class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->helper('cookie');
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
		$this->load->library('encrypt');		
		if( !$this->login_model->isUser_LoggedIn() ){		
			redirect('/SessionCtrl');
		}
	} //construct
	
	function index()
	{		
		redirect( 'EventCtrl/book' );		
	}//index
	
	private function deleteBookingTotally_andCleanup( $bookingNumber, $expiryCleanup = NULL )
	{
		/*
			Created 25FEB2012-1312
			
			Formerly contained within 'cancelBookingProcess()' but moved here to accomodate
			deletion of booking data because of expired payment period
		*/			
		$guestObjArray = $this->Guest_model->getGuestDetails( $bookingNumber );
		$bookingStage = 0; 
		
		if( $expiryCleanup === NULL ) $bookingStage = intval($this->session->userdata( 'book_step' ) );		
		
		// CODE MISSING: DB Checkpoint		
		
		foreach( $guestObjArray as $eachGuest )
		{
			$eventSlot = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );			
			if( $eventSlot === false ) continue;					
			if( ($expiryCleanup != NULL and $expiryCleanup['bool'])or 
				$bookingStage > 3 )
			{				
				
				$this->Seat_model->markSeatAsAvailable(
					$eventSlot->EventID,
					$eventSlot->Showtime_ID,
					$eventSlot->Seat_x,
					$eventSlot->Seat_y
				);
				
			}//end if
			$this->Slot_model->setSlotAsAvailable( $eventSlot->UUID );			
		}
		if( ( $expiryCleanup['bool'] and $expiryCleanup['Status2'] === "FOR-DELETION" ) 
			or $bookingStage > 4 )
		{
			$this->Payment_model->deleteAllBookingPurchases( $bookingNumber );	
		}
		if( ( $expiryCleanup['bool'] and $expiryCleanup['Status2'] === "FOR-DELETION" ) 
			or $bookingStage > 3 )
		{
			$this->Booking_model->deleteAllBookingInfo( $bookingNumber );
		}		
		// CODE MISSING: DB Commit
		$this->session->unset_userdata( 'book_step' );
	}//deleteBookingTotally_andCleanup
	
		
	
	private function decodeSeatVisualCookie( $bookInstanceEncryptionKey )
	{
		$array_result = Array(
			'boolean' => false,
			'textStatus' => 'ERROR',
			'textStatusFriendly' => NULL
		);		
		$decodedSeatVisualCookie = explode('|',$this->encrypt->decode( $this->input->cookie('visualseat_data') ) );
		if( count( $decodedSeatVisualCookie ) !== 2 )
		{
			$array_result['textStatus'] = "input error";
			$array_result['textStatusFriendly'] = "seat cookie manipulated.";
			return $array_result;
		}		
		if( $decodedSeatVisualCookie[0] != $bookInstanceEncryptionKey )
		{			
			$array_result['textStatus'] = "input error";
			$array_result['textStatusFriendly'] = "Session hijack attempt detected - bookInstanceEncryption Key incorrect.";
			return $array_result;
		}
		$visualSeats_splitted = explode('.', $decodedSeatVisualCookie[1] );
		$seatVisuals = Array();
		foreach( $visualSeats_splitted as $eachRelationship )
		{
			$mainSub = explode( '_', $eachRelationship );
			$seatVisuals[ $mainSub[0] ] = $mainSub[1];
		}
		$array_result['boolean'] = true;
		$array_result['textStatus'] = "okay";
		$array_result['textStatusFriendly'] = $seatVisuals;
		return $array_result;
	}//decodeSeatVisualCookie
	
	private function decodePurchaseCookie_And_GetObjs( $bookInstanceEncryptionKey )
	{
			$array_result = Array(
				'boolean' => false,
				'textStatus' => 'ERROR',
				'textStatusFriendly' => NULL
			);
			$decodedPurchaseCookie = explode('|',$this->encrypt->decode( 
				$this->input->cookie('purchases_identifiers') ) 
			);
			if( count( $decodedPurchaseCookie ) !== 2 )
			{				
				$array_result['textStatus'] = "input error";
				$array_result['textStatusFriendly'] = "purchase cookie manipulated.";
				return $array_result;
			}		
			if( $decodedPurchaseCookie[0] != $bookInstanceEncryptionKey )
			{				
				$array_result['textStatus'] = "input error";
				$array_result['textStatusFriendly'] = "Session hijack attempt detected - bookInstanceEncryption Key incorrect.";
				return $array_result;
			}			
			$purchasesID_splitted = explode('.', $decodedPurchaseCookie[1] );
			$array_result['textStatusFriendly'] = Array();		
			foreach( $purchasesID_splitted as $eachRelationship )
			{
				/* 
					Index of $mainSub
					1 - booking number | 2 - unique ID
				*/
				$mainSub = explode( '-', $eachRelationship );
				$array_result['textStatusFriendly'][] = $this->Payment_model->getSinglePurchase( $mainSub[0], $mainSub[1] );
			}
			$array_result['boolean'] = true;
			$array_result['textStatus'] = "success";
			return $array_result;
	}//decodePurchaseCookie_And_GetObjs(..)
	
	private function encryptPurchaseCookies( $bookInstanceEncryptionKey, $bookingNumber, $purchases )
	{
		/*
		*	Purchases section
		*   We need to specify the purchase ID number so as for booking Step 6 display
		*  ( getUnpaidPurchases will return nothing because such purchases will be marked as paid/pending payment )
		*  before the page is displayed
		*/				
		$purchaseCount = count( $purchases );
		if( purchaseCount === 0 ) return true;
		$purchases_str = "";
		// tokenize and set in cookie, the purchases to be displayed in the page. 
		foreach( $purchases as $singlePurchase  )
			 $purchases_str .= ( $singlePurchase->BookingNumber."-".$singlePurchase->UniqueID."_" );
		$purchases_str = substr($purchases_str, 0, strlen($purchases_str)-1 );	//remove trailing underscore
		/*
			String structure of unencrypted cookie 'purchases_identifiers':
				
				AAAAAAAAAA|XXXXX_YY-XXXXX_YY.....XXXXXX_YY
			
			AAAA.AAA - Min length of 7 ata, unique identifier for this session
			XXX..XX  - Min length of 5, booking number
			Y..Y	 - Min length of 2, UniqueID of purchase
		*/
		$purchases_encrypted = $this->encrypt->encode($bookInstanceEncryptionKey."|".$purchases_str);
		$cookie = array(
			'name'   => 'purchases_identifiers',
			'value'  => $purchases_encrypted,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		return true;
	}//encryptPurchaseCookies
	
	private function encryptSeatVisualCookie( $bookInstanceEncryptionKey, $sendSeatInfoToView )
	{
		$sendSeatInfoToView;	
		$seatInfo_str = "";
		foreach( $sendSeatInfoToView as  $uuid => $singleSeatAssignment )
		{
			$seatInfo_str .= ( $uuid."_".$singleSeatAssignment ).".";
		}
		$seatInfo_str = substr( $seatInfo_str, 0, strlen($seatInfo_str)-1 );	// remove trailing pipe
		$seatInfo_str_encrypted = $this->encrypt->encode($bookInstanceEncryptionKey."|".$seatInfo_str);
		$cookie = array(
			'name'   => 'visualseat_data',
			'value'  => $seatInfo_str_encrypted,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
	}//encryptSeatVisualCookie(..)
	
	private function confirmSlotsOfThisBooking( $bookingNumber )
	{
		/*
			Created 02MAR2012-2200 
			
			Created so as to separate writing to database so as to confirm slots from
			just getting visual infos of seats ( so, this was taken from $this->getSeatVisual_Guests() ).
		*/		
		// CODE-MISSING: DATABASE CHECKPOINT
		$guest_arr = $this->Guest_model->getGuestDetails( $bookingNumber );
		foreach( $guest_arr as $eachGuest )
		{
			$eSlotObject = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
			$this->Slot_model->setSlotAsBooked( $eSlotObject->UUID );
		}
		// CODE-MISSING: DATABASE COMMIT
	}//confirmSlotsOfThisBooking(..)
	
	private function getSeatVisual_Guests( $guest_arr )
	{
		/*	DEPRECATED | In favor of $this->getSeatRepresentationsOfGuests()
		
			Created 22FEB2012-2313. Moved from book_step6 to be used for confirm reservation.
		
			Iterate through each guest details, by their UUIDs, get the slot record 
			assigned to them, get the seat pointer, then by that pointer together with
			some cookies, access the visual representation of the seat by accessing the seat record.
		*/
		$guestSeats = Array();
		
		foreach( $guest_arr as $eachGuest )
		{
			// get slot record, as the seat pointer is there
			$eSlotObject = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
			//$this->Slot_model->setSlotAsBooked( $eSlotObject->UUID );
			$eSeatObject = $this->Seat_model->getSingleActualSeatData( 
				$eSlotObject->Seat_x, 
				$eSlotObject->Seat_y,
				$this->input->cookie( 'eventID' ), 
				$this->input->cookie( 'showtimeID' )
			);
			$guestSeats[ $eachGuest->UUID ] = ( $eSeatObject->Visual_row."-".$eSeatObject->Visual_col );
		}
		
		return $guestSeats;
	}//getSeatVisual_Guests( $guest_arr )
	
	private function getSeatRepresentationsOfGuests( $eventID, $showtimeID, $guest_arr )
	{
		/*
			Created 03MAR2012-1147
		*/
		$seatDetailsOfGuest = Array();			
		foreach( $guest_arr as $singleGuest )
		{
			$seatVisualRepStr = false;
			$seatMatrixRepObj = false;
			
			$seatMatrixRepObj = $this->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );								
			if( $seatMatrixRepObj !== false ){	// there is seat assigned for this user				
				$seatVisualRepStr = $this->Seat_model->getVisualRepresentation(
					$seatMatrixRepObj['Matrix_x'],
					$seatMatrixRepObj['Matrix_y'],
					$eventID,
					$showtimeID
				);
			}
			$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(
				'matrix_x' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_x'] : ""  ),
				'matrix_y' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_y'] : ""  ),
				'visual_rep' => ( ($seatMatrixRepObj !== false) ? $seatVisualRepStr : ""  )
			);
		}
		return $seatDetailsOfGuest;
	}//getSeatRepresentationsOfGuests(..)
	
	
	private function processPayment( $bNumber )
	{	
		/*
			Created 28FEB2012-1148
			
			Moved from confirm_step3,so this can be used in BookStep6 when 
			there are no charges (FREE ).
		*/
		$result = Array(
			'boolean' => FALSE,
			'status' => 'ERROR',
			'message' => 'Something went wrong.'
		);
		$userPermitted;
		$totalCharges = 0.0;
		$bookingDetails = $this->Booking_model->getBookingDetails( $bNumber );
		$unpaidPurchases = $this->Payment_model->getUnpaidPurchases( $bNumber );
				
		if( $unpaidPurchases === false )
		{			
			$result['status'] = "ERROR";
			$result['message'] = "Already paid.";
			return $result;
		}
		$userPermitted = $this->Account_model->isUserAuthorizedPaymentAgency(
				$this->session->userdata( 'accountNum' ),
				$bookingDetails->EventID,
				$bookingDetails->ShowingTimeUniqueID,
				$unpaidPurchases[0]->Payment_Channel_ID
		);		
		if( $userPermitted['value'] === false ){			
			$result['status'] = "ERROR";
			$result['message'] = "You do not have permission to confirm a reservation for this event.<br/><br/>*";
			$result['message'] .= $userPermitted['comment'];
			return $result;			
		}
		$totalCharges = $this->Payment_model->getSumTotalOfUnpaid( $bNumber, $unpaidPurchases );
		$paymentID = $this->Payment_model->createPayment( $bNumber, $totalCharges, $unpaidPurchases[0]->Payment_Channel_ID );
		if( $paymentID !== false )
		{
			foreach($unpaidPurchases as $singlePurchase)
			{
				$this->Payment_model->setAsPaid( $bNumber, $singlePurchase->UniqueID, $paymentID );
			}
		}
		$this->confirmSlotsOfThisBooking( $bNumber );
		$this->Booking_model->markAsPaid( $bNumber );
		$result['boolean'] = TRUE;
		$result['status'] = "OKAY";
		$result['message'] = "Succesfully proccessed payment.";
		return $result;	
	}//processPayment(..)
	
	private function setBookingCookiesOuter( $eventID, $showtimeID, $slots, $bookingNumber = 'XXXXX' )	
	{
		/*
			Created 22FEB2012-2248
			
			This handles setting of cookies that are needed to display info on the pages.			
		*/
		  $showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
		  if( $showtimeObj === false )		// counter check against spoofing
		  {
			 // no showing time exists
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time not found. Are you hacking the app?  :-D ";
			 $this->load->view( 'errorNotice', $data );
			 return false;
		  }	  	 
		  $eventInfo = $this->Event_model->getEventInfo( $eventID );      // get major info of this event
		  $ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID ); 
		  if( $ticketClasses === false )  // counter check against spoofing
		  {
			 // no ticket classes exist
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time marked as for sale but there isn't any ticket class yet.";
			 $this->load->view( 'errorNotice', $data );  
			return false;		 
		  }      
		  //Cookie part		  
		  $cookie_values = Array( 
			 $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $eventInfo->Name, 
			 $showtimeObj->StartDate, $showtimeObj->StartTime, $showtimeObj->EndDate, $showtimeObj->EndTime,
			 $slots, $eventInfo->Location,  $bookingNumber, '-1', '-1', '-1'
		  );
		  $this->Event_model->setBookingCookies( $cookie_values );
	}//setBookingCookiesOuter(..)
	
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
		//$data['showingTimes'] = $showingTimes;
		$data['configuredEventsInfo'] =  $configuredEventsInfo;		

		$this->session->set_userdata( 'book_step', 1 );	// our activity tracker		
		$this->load->view( "book/bookStep1", $data );		
	}//book(..)
	
	function book_step2()
   {
      /*
         Created 30DEC2011-1855
      */
	  $bookInstanceEncryptionKey;
	  $bookStep =  $this->session->userdata( 'book_step' );
      $eligbilityIndicator = "CCDB7X";
      $eventID = $this->input->post( 'events' );
      $showtimeID = $this->input->post( 'showingTimes');
      $slots = $this->input->post( 'slot' );
      $eventInfo;
      $cookie_names;
      $cookie_values;
      $expiryCleanup = Array();
	  
      /*  Validate if form submitted has the correct data.
	  *
	  *	 Faulty if no posted data or the session data 'book_step' is false or less than 1
	  *
	  */
      if( $eventID === false or $showtimeID === false or $slots === false or
		  intval( $bookStep ) < 1 or $bookStep === false
	  )
      {
         $data['error'] = "NO_DATA";         
         $this->load->view( 'errorNotice', $data );
      }
      if( $this->setBookingCookiesOuter( $eventID, $showtimeID, $slots, null ) === false ) return false;
      
	  /*
		 This checks if there are bookings marked as PENDING-PAYMENT' and yet
		 not able to pay on the deadline - thus forfeited now.
	  */
	  $defaultedBookings = $this->Booking_model->getPaymentPeriodExpiredBookings( $eventID, $showtimeID );
	  if( $defaultedBookings !== false )
	  {
		foreach( $defaultedBookings as $eachBooking )
		{	
			if( $eachBooking->Status != "EXPIRED" ) $this->Booking_model->markAsExpired_New( $eachBooking->bookingNumber );
			/*
				This will free the slots and seats being tied to this booking, so can be used later.
			*/
			$this->deleteBookingTotally_andCleanup( 
				$eachBooking->bookingNumber,
				Array(
					'bool' => true,
					'Status2' => $eachBooking->Status2
				)
			);
		}
	  }//if	  
	  $bookInstanceEncryptionKey = rand( 9928192, 139124824 );
	  $this->session->set_userdata( 'bookInstanceEncryptionKey', $bookInstanceEncryptionKey );
		
	  // now ticket classes proper
	  $showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
	  $ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID ); 
	  
	  /*
		This part checks if there are event_slots (i.e., records in `event_slot` ) that the status
		is 'BEING_BOOKED' but lapsed already based on the ticket class' holding time.
	  */
	  // This next line gets all records marked as 'BEING_BOOKED' - judgment in the (next) if statement
	  $beingBookedSlots = $this->Slot_model->getBeingBookedSlots( $eventID, $showtimeID );
	  
	  if( $beingBookedSlots !== FALSE )	// there are slots being booked
	  {			
			/*
				This variable is for booking numbers that are already processed.
				Because we are examining slot by slot, there might be at least two slots under
				one booking number. By checking this array, it can be found if the booking
				number was processed earlier, so no need to proceed.
			*/
			$defaultedBookingNumbers = Array();	
			
			foreach( $beingBookedSlots as $eachSlot )
			{
				$assignedToUserAlready = FALSE;
								
				if( $this->Slot_model->isSlotBeingBookedLapsedHoldingTime(
						$eachSlot, 
						$ticketClasses[ $eachSlot->Ticket_Class_UniqueID ]
					) == FALSE 
				)
				{
					continue;	// slot is still not lapsing holding time
				}else{
					/* 	
						By reaching this part, it means the holding time has elapsed.
					*/
					// The column `Assigned_To_User` has a string of length at least 1 when the slot is assigned already.
					$assignedToUserAlready = ( strlen($eachSlot->Assigned_To_User) > 0 );
					if( $assignedToUserAlready  )
					{
						$guestObj = $this->Guest_model->getSingleGuest( $eachSlot->Assigned_To_User );
						// see comment near $defaultedBookingNumbers declaration for explanation						
						if( in_array( $guestObj->bookingNumber, $defaultedBookingNumbers ) ) continue;	
						$this->deleteBookingTotally_andCleanup( 
							$guestObj->bookingNumber,
							Array(
								'bool' => true,
								'Status2' => 'NOT-YET-NOTIFIED'
							)
						);			
						$this->Booking_model->markAsHoldingTimeLapsed_New( $guestObj->bookingNumber );
						$defaultedBookingNumbers[] = $guestObj->bookingNumber;
					}else{
						// no entry in `booking_details` and `booking_guests` yet, so just mark it as available
						$this->Slot_model->setSlotAsAvailable( $eachSlot->UUID );
					}
				}
			}//foreach
	  }//if	  
      foreach( $ticketClasses as $singleClass )
      {            
         $serializedClass_Slot_UUID = "";
                     
         $eachClassSlots = $this->Slot_model->getSlotsForBooking( $slots, $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $singleClass->UniqueID );            
         if( $eachClassSlots === false ) continue;
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
      }      	  
	  $this->session->set_userdata( 'book_step', 2 );	// our activity tracker	        
      redirect( 'EventCtrl/book_step2_forward' );
   }//book_step2(..) 
	
	function book_step2_forward()
	{
		$eventID;
		$showtimeID;
		$showtimeObj;
		$bookStep;
		
		$bookStep = $this->session->userdata( 'book_step' );		
		if( $bookStep !=2 or $bookStep  === false )
		{
			$data['error'] = "NO_DATA";         
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$eventID = $this->input->cookie( 'eventID' );
		$showtimeID = $this->input->cookie( 'showtimeID');	
		$showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 	// counter check against spoofing
		if( $showtimeObj === false )
		{
			echo 'Internal Server Error.<br/>Showing time specified not found. Are you trying to hack the app?';
			die();
		}
		$data['ticketClasses'] = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID );
		$data['eventInfo'] = $this->Event_model->getEventInfo( $eventID );
		$data['showtimeObj'] = $showtimeObj;					  	  
		$this->load->view( 'book/bookStep2', $data );	
	}//book_step2_forward(..)
				
	function book_step3()
	{		
		$eventID;
		$ticketClassGroupID;
		$ticketClassUniqueID;
		$bookStep;
		
		$bookStep = $this->session->userdata( 'book_step' );
		$eventID = $this->input->cookie( 'eventID' );
		$ticketClassGroupID = $this->input->cookie( 'ticketClassGroupID' );
		$ticketClassUniqueID = $this->input->post( 'selectThisClass' );
		
		/*	
		*	Check if this page can be accessed already.		
		**/
		if( $eventID === false or $ticketClassGroupID === false or 
			$ticketClassUniqueID === false or		
	  	    $bookStep  < 2 or $bookStep === false
		 )
		 {
			 $data['error'] = "NO_DATA";         
			 $this->load->view( 'errorNotice', $data );
			 return false;
		 }	
		$selectedTicketClass = $this->TicketClass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		$allOtherClasses = $this->TicketClass_model->getTicketClassesExceptThisUniqueID( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		if( $selectedTicketClass === false /*or $allOtherClasses === false*/ ) // 08FEB2012-2145, turned into comment condition somewhat ambiguous
		{			
			 $data['error'] = "NO_DATA";         
			 $this->load->view( 'errorNotice', $data );
			 return false;
		}
		$this->Slot_model->freeSlotsBelongingToClasses( $allOtherClasses );		// since we now don't care about these, free so.
		// now set the uniqueID of the ticketclass
		$cookie = array(
				'name'   => 'ticketClassUniqueID',
				'value'  => $ticketClassUniqueID,
				'expire' => '3600'				// change later to how long ticketclass hold time
		);
		$this->input->set_cookie($cookie);
			
		$newSessionData = array(
            'eligibilityIndicator'  =>  $this->CoordinateSecurity_model->createActivity( "BOOK", "3", "int" )
        );
		$this->session->set_userdata( $newSessionData );
		$this->session->set_userdata( 'book_step', 3 );	// our activity tracker		
		redirect( 'EventCtrl/book_step3_forward' );
	}//book_step3()
			
	function book_step3_forward(){
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
		$this->load->view( 'book/bookStep3' );
	}//book_step3_forward
	
	function book_step4()
	{		
		$slots = intval( $this->input->cookie( "slots_being_booked" ) );
		$ticketClassUID = $this->input->cookie( 'ticketClassUniqueID' );
		$guestUUIDs = $this->input->cookie( $ticketClassUID."_slot_UUIDs" );	
		$guestUUIDs_tokenized = explode('_', $guestUUIDs );
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
		
		$data['guests'] = $this->Guest_model->getGuestDetails( $bookingNumber );
		$x = 0;
		foreach( $data['guests'] as $eachGuest )
		{
			$this->Slot_model->assignSlotToGuest(
				$this->input->cookie( 'eventID' ),
				$this->input->cookie( 'showtimeID' ),
				$guestUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
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
		$this->session->set_userdata( 'book_step', 4 );	// our activity tracker		
		redirect( 'EventCtrl/book_step4_forward' );
	}//book_step4
			
	function book_step4_forward(){
		
		$uuidEligibility;
		$x = 0;
		$eventID  = $this->input->cookie( 'eventID' );
		$showtimeID = $this->input->cookie( 'showtimeID' );
		$bookStep = $this->session->userdata( 'book_step' );
		$activity = $this->session->userdata( 'activity_name' );
		$activity_stage = $this->session->userdata( 'activity_stage' );
		
		$data['manageBooking_chooseSeat'] = ( $activity == "manageBooking" and $activity_stage == 4 );		
		if( ($bookStep == 4 or $bookStep !== false or $data['manageBooking_chooseSeat'] ) === FALSE  )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// end: access validity indicator
					
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );				
		
		//$data['manageBooking_chooseSeat'] = false;
		if( $activity == "manageBooking" and $activity_stage == 4 )
		{
			
			/*
				This means user already booked and why he is on this page is that he is changing
				his booking - changing seats.
			*/
			$seatDetailsOfGuest = Array();			
			foreach( $data['guests'] as $singleGuest )
			{
				$seatVisualRepStr = false;
				$seatMatrixRepObj = false;
				
				$seatMatrixRepObj = $this->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );								
				if( $seatMatrixRepObj !== false ){	// there is seat assigned for this user				
					$seatVisualRepStr = $this->Seat_model->getVisualRepresentation(
						$seatMatrixRepObj['Matrix_x'],
						$seatMatrixRepObj['Matrix_y'],
						$eventID,
						$showtimeID
					);
				}
				$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(
					'Matrix_x' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_x'] : ""  ),
					'Matrix_y' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_y'] : ""  ),
					'visual_rep' => ( ($seatMatrixRepObj !== false) ? $seatVisualRepStr : ""  )
				);
			}
			$data['guestSeatDetails'] = $seatDetailsOfGuest;
			

		}		
		$this->load->view( 'book/bookStep4', $data);
	}//book_step4_forward
	
	function book_step5()
	{
		/*
			Created 13FEB2012-2334
		*/
		$bookInstanceEncryptionKey;
		$slots;
		$x;
		$uuidEligibility;
		$sendSeatInfoToView = Array();		
		$totalCharges = 0;
		$bookingNumber;
		
		// start: access validity indicator		
		$slots =  $this->input->cookie( 'slots_being_booked' );						
		if( $this->session->userdata( 'book_step' ) != 4 or
			$this->session->userdata( 'book_step' ) === false
		){			
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// end: access validity indicator
		$bookingNumber = $this->input->cookie( 'bookingNumber' );
		$bookInstanceEncryptionKey = $this->session->userdata( 'bookInstanceEncryptionKey' );
		$slots = null;
		$slots = intval( $this->input->cookie( 'slots_being_booked' ) );
		/*
			For each seat submitted (chosen by the user), get its visual representation
			and mark it as assigned
		*/
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
		$purchases = $this->Payment_model->getUnpaidPurchases( $bookingNumber );
		$this->encryptPurchaseCookies( $bookInstanceEncryptionKey, $bookingNumber, $purchases );
		$this->encryptSeatVisualCookie( $bookInstanceEncryptionKey, $sendSeatInfoToView );
		$totalCharges = $this->Payment_model->sumTotalCharges( $purchases );
		// since these are sensitive, might as well include in session cookie
		$this->session->set_userdata( 'purchases_count',  $purchaseCount );				
		$this->session->set_userdata( 'totalCharges', $totalCharges );		
		$this->session->set_userdata( 'book_step', 5 );	// our activity tracker		
		
		if( $totalCharges === 0.0 )		
			redirect( 'EventCtrl/book_step6' );
		else
			redirect( 'EventCtrl/book_step5_forward' );
	}//book_step5(..)
	
	function book_step5_forward()
	{
		$bookInstanceEncryptionKey;
		$totalCharges;
		$bookStep = intval($this->session->userdata( 'book_step' ));
		
		if( $bookStep < 5 )
		{
			die("Not yet here! ");
		}else
		if( $bookStep > 5 )
		{
			redirect( 'EventCtrl/book_step'.$bookStep.'_forward' );
		}
		
		$bookInstanceEncryptionKey = $this->session->userdata( 'bookInstanceEncryptionKey' );
		$totalCharges = $this->session->userdata( 'totalCharges' );
																	
		//	Decoding purchase identifiers		
		$decodingPurchaseCookieObj = $this->decodePurchaseCookie_And_GetObjs( $bookInstanceEncryptionKey );		
		if( $decodingPurchaseCookieObj['boolean'] )
		{
			$data['purchases'] = $decodingPurchaseCookieObj['textStatusFriendly'];
		}else{
			die( var_dump( $decodingPurchaseCookieObj ) );
		}
		
		//	Decoding seat visual cookie		
		$decodingSeatVisualsCookieObj = $this->decodeSeatVisualCookie( $bookInstanceEncryptionKey );
		if($decodingSeatVisualsCookieObj['boolean'] )
		{
			$data['seatVisuals'] = $decodingSeatVisualsCookieObj['textStatusFriendly'];
		}else{
			die( var_dump( $decodingSeatVisualsCookieObj ) );
		}		
		
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent(
			$this->input->cookie( 'eventID' ), 
			$this->input->cookie( 'showtimeID' ),
			( $totalCharges === 0.0 )
		);
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );		
		$this->load->view( 'book/bookStep5', $data );
	}// book_step5_forward(..)
	
	function book_step6()
	{
		//die( var_dump( $_POST ) );
		$clientUUIDs;
		$guestSeats = null;
		$totalCharges;
		$clientUUIDs;
		$paymentChannel;
		$paymentChannel_obj;
		
		$bookInstanceEncryptionKey = $this->session->userdata( 'bookInstanceEncryptionKey' );
		$totalCharges = floatval($this->session->userdata( 'totalCharges' ));
		//echo var_dump( $totalCharges );
		$paymentChannel = ( $totalCharges === 0.0 ) ? 0 : intval($this->input->post( 'paymentChannel' )) ;
		// start: access validity indicator
		if( $paymentChannel === false or  $this->session->userdata( 'book_step') !== 5 )
		{			
			$this->postBookingCleanup( TRUE );
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// start: end validity indicator		
		$clientUUIDs = explode( "_" , $this->input->post(  $this->input->cookie( 'ticketClassUniqueID' )."_slot_UUIDs"  ) );
		$paymentChannel = null;
		$paymentChannel = intval( $this->input->post( 'paymentChannel' ) );
		$this->session->set_userdata('paymentChannel', $paymentChannel); // to be accessed in forward page
		$this->Payment_model->setPaymentModeForPurchase( $this->input->cookie( 'bookingNumber' ), $paymentChannel );
		$paymentChannel_obj =  $this->Payment_model->getSinglePaymentChannel(
			$this->input->cookie( 'eventID' ),
			$this->input->cookie( 'showtimeID' ),
			$paymentChannel
		);		
		$data['guests'] = $this->Guest_model->getGuestDetails( $this->input->cookie( 'bookingNumber' ) );
		$data['singleChannel'] = $paymentChannel_obj;
		
		
		//	Decoding seat visual cookie		
		$decodingSeatVisualsCookieObj = $this->decodeSeatVisualCookie( $bookInstanceEncryptionKey );
		if($decodingSeatVisualsCookieObj['boolean'] )
		{
			$data['seatVisuals'] = $decodingSeatVisualsCookieObj['textStatusFriendly'];
		}else{
			die( var_dump( $decodingSeatVisualsCookieObj ) );
		}		
		
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent(
			$this->input->cookie( 'eventID' ), 
			$this->input->cookie( 'showtimeID' ),
			( $totalCharges === 0.0 )
		);
		
		if( $totalCharges === 0.0 )
		{
			$processPaymentResultArr = $this->processPayment( $this->input->cookie( 'bookingNumber' ) );
			if( $processPaymentResultArr['boolean'] )
			{
				//hmm? what to do here?
			}else{
				echo var_dump( $processPaymentResultArr ); 
			}
		}
		else
		{			
			$this->Booking_model->markAsPendingPayment( $this->input->cookie( 'bookingNumber' ), "NEW" );
			foreach( $data['guests'] as $eachGuest )
			{
				$slotAssignedObj = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
				$this->Slot_model->setSlotAsPendingPayment( $slotAssignedObj->UUID );
			}
		}
		$this->session->set_userdata( 'book_step', 6 );	// our activity tracker			
		redirect( 'EventCtrl/book_step6_forward' );
	}//book_step6
	
	function book_step6_forward()
	{	
		/*
			Created 28FEB2012-1420
			
			Moved from book_step6, majority.
			Processes the HTML page to be outputted upon the conclusion of
			the Purchase or booking of a ticket/Posting of a reservation
		*/
		$paymentChannel;
		$paymentChannel_obj;
		$eventID;
		$showtimeID;
		$bookingNumber;
		$bookInstanceEncryptionKey;
		
		// validity access indicator: determines if user should be at this page already or not
		if( $this->session->userdata( 'book_step' ) !== 6 )
		{
			
			$this->postBookingCleanup( TRUE );
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// getting relevant data
		$bookInstanceEncryptionKey = $this->session->userdata( 'bookInstanceEncryptionKey' );
		$totalCharges = floatval($this->session->userdata( 'totalCharges' ));
		$paymentChannel = ( $totalCharges === 0.0 ) ? 0 : intval( $this->session->userdata('paymentChannel') );		
		$eventID = $this->input->cookie( 'eventID' );
		$showtimeID = $this->input->cookie( 'showtimeID' );
		$bookingNumber =  $this->input->cookie( 'bookingNumber' );		
		
		$paymentChannel_obj = $this->Payment_model->getSinglePaymentChannel(
			$eventID,
			$showtimeID,
			$paymentChannel
		);		
		$data['singleChannel'] = $paymentChannel_obj;
		$data['guests'] = $this->Guest_model->getGuestDetails( $bookingNumber );
		//	Decoding purchase identifiers		
		$decodingPurchaseCookieObj = $this->decodePurchaseCookie_And_GetObjs( $bookInstanceEncryptionKey );		
		if( $decodingPurchaseCookieObj['boolean'] )
		{
			$data['purchases'] = $decodingPurchaseCookieObj['textStatusFriendly'];
		}else{
			echo "<h1>Notice</h1>";
			echo "<p>You have seen this most probably because you have clicked the<br/>";
			echo "refresh button on your browser. This is not supported. Please go to the";
			echo "\"Manage Booking\" section in the homepage to view the details of your booking";
			echo "</p>";
			die( var_dump( $decodingPurchaseCookieObj ) );
		}
		//	Decoding seat visual cookie		
		$decodingSeatVisualsCookieObj = $this->decodeSeatVisualCookie( $bookInstanceEncryptionKey );
		if($decodingSeatVisualsCookieObj['boolean'] )
			$data['seatVisuals'] = $decodingSeatVisualsCookieObj['textStatusFriendly'];
		else
			die( var_dump( $decodingSeatVisualsCookieObj ) );
		
		if( $paymentChannel_obj->Type == "COD" )
		{
			$this->load->view( 'book/bookStep6_COD', $data);
		}else
		if( $paymentChannel_obj->Type == "ONLINE" )
		{
			// later
			die('payment mode not yet supported');
		}else{
			$this->load->view( 'confirmReservation/confirmReservation02-free', $data );			
		}
	}//book_step6_forward()
	
	function cancelBooking()
	{
		/*
			Created 01MAR2012-2319
		*/
		$bookingNumber;
		$argumentArray;
		$accountNum;
		
		$accountNum = $this->session->userdata( 'accountNum' );
		$bookingNumber = $this->input->post( 'bookingNumber' );
		if( !$this->Booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{
			echo "ERROR_NO-PERMISSION";
			return false;
		}
		$argumentArray = Array( 'bool' => true, 'Status2' => "FOR-DELETION" );		
		$this->deleteBookingTotally_andCleanup( $bookingNumber, $argumentArray );
		if( !$this->input->is_ajax_request() ) redirect('EventCtrl/manageBooking');
		echo "OKAY_SUCCESS";
		return false;
	}//cancelBooking()
	
	function cancelBookingProcess()
	{
		/*
			Created 21FEB2012-1713
		*/
		$guestDetails;
		$eventID;
		$ticketClassGroupID;
		$ticketClasses;
		$bookingStage;
		
		//if( $this->input->is_ajax_request() === false ) redirect('/');		
		$eventID = $this->input->cookie( 'eventID' );
		$ticketClassGroupID = $this->input->cookie( 'ticketClassGroupID' );
		if( $eventID === false or $ticketClassGroupID === false )
		{
			echo "INVALID_DATA-NEEDED";
			return false;
		}		
		$bookingStage = $this->session->userdata( 'book_step' );	
		if( $bookingStage < 3 )
		{
			/*
				Get ticket classes since we have reserved X slots for each ticket classes of 
				the showing time concerned. Then pass to the free-er function.
			*/
			$ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $ticketClassGroupID );
			$this->Slot_model->freeSlotsBelongingToClasses( $ticketClasses );					
		}else{			
			$this->deleteBookingTotally_andCleanup( $this->input->cookie( 'bookingNumber' ), NULL );
		}// end if( $bookingStage < 3 )
		$this->Event_model->deleteBookingCookies();	
		echo "OK";
		return true;
	}//cancelBookingProcess
			
	function confirm()
	{
		/*
			Created 22FEB2012-2157
		*/
		$this->load->view( 'confirmReservation/confirmReservation01' );
	}
	
	function confirm_step2()
	{
		/*
			Created 22FEB2012-2157
		*/
		$bNumber = $this->input->post( 'bookingNumber' );
		$bNumberExistent = $this->Booking_model->doesBookingNumberExist( $bNumber );
		
		if( $bNumberExistent )
		{
			$this->session->set_userdata( 'confirm_bookingNumber', $bNumber );
			$guestDetails = $this->Guest_model->getGuestDetails( $bNumber );
			$bookingDetails = $this->Booking_model->getBookingDetails( $bNumber );
			$this->setBookingCookiesOuter( 
				$bookingDetails->EventID, 
				$bookingDetails->ShowingTimeUniqueID,
				count( $guestDetails ), 
				$bNumber 
			);
			
		}
		echo ( $bNumberExistent === true ? "true" : "false" );
	}
	
	function confirm_step2_forward()
	{
		$bNumber = $this->session->userdata( 'confirm_bookingNumber' );
		$bookingDetails = $this->Booking_model->getBookingDetails( $bNumber );
		$data['purchases'] = $this->Payment_model->getUnpaidPurchases( $bNumber );		
		$data[ 'guests' ] = $this->Guest_model->getGuestDetails( $bNumber  );
		$data[ 'seatVisuals' ] = $this->getSeatRepresentationsOfGuests( 
			$bookingDetails->EventID,
			$bookingDetails->ShowingTimeUniqueID,
			$data['guests'] 
		);
		if( $data['purchases'] === false ){
			echo( 'No pending payments. Good to go already.<br/><br/>' );
			echo('<a href="'.base_url().'">Home</a>' );
			die();
		}
		$paymentChannel_obj =  $this->Payment_model->getSinglePaymentChannel(
			$this->input->cookie( 'eventID' ),
			$this->input->cookie( 'showtimeID' ),
			intval( $data['purchases'][0]->Payment_Channel_ID )
		);				
		$data['singleChannel'] = $paymentChannel_obj;		
		$this->session->set_userdata( 'paymentDeadline_Date', $data['purchases'][0]->Deadline_Date );
		$this->session->set_userdata( 'paymentDeadline_Time',  $data['purchases'][0]->Deadline_Time );
		$this->load->view( 'confirmReservation/confirmReservation02', $data );
	}
	
	function confirm_step3()
	{		
		if( !$this->input->is_ajax_request() ) redirect( '/' );
		
		
		$bNumber = $this->session->userdata( 'confirm_bookingNumber' );
		$result = $this->processPayment( $bNumber );		
		$this->confirmSlotsOfThisBooking( $bNumber );
		echo $result['status']."_".$result['message'];
		
		return $result['boolean'];
	}//confirm_step3()
	
	
		
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
		
		$y = count( $_POST );
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
			/*
				Now, update data for each seat.
			*/
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
		$doesFreeTC_Exist = $this->TicketClass_model->isThereFreeTicketClass( $this->input->cookie( 'eventID' ) );			
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannels( $doesFreeTC_Exist );
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		$this->load->view('createEvent/createEvent_006', $data);				
	}
	
	function create_step7()
	{		
		// START: validation if correct page is being submitted
		$correctPage = true;
		$stillUnconfiguredEvents;
		$beingConfiguredShowingTimes;
		$paymentChannels;
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
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );		
		$paymentChannels = $this->Payment_model->getPaymentChannels( TRUE );
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
					$this->Payment_model->createPaymentChannelPermission( 
						$this->session->userdata( 'accountNum' ),
						$this->input->cookie( 'eventID' ),
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."					
					);
				}			
			}
		}//end foreach ($paymentChannels...
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
		$eventID;
		$excludeShowingTime;
		
		//Added 29JAN2012-1530: user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
				
		$eventID = $this->input->post( 'eventID' );
		$excludeShowingTime = $this->input->post( 'excludeShowingTime' );
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
		if( $excludeShowingTime !== false )
		{
			foreach( $allConfiguredShowingTimes as $key => $singleST )
			{				
				if( intval($singleST->UniqueID) === intval( $excludeShowingTime ) )
				{	
					unset( $allConfiguredShowingTimes[$key] );
					break;
				}
			}
		}
		if( count( $allConfiguredShowingTimes ) == 0 )
		{
			echo "ERROR_No other showing times exist.";
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
	
	function manageBooking()
	{
		/*
			Created 01MAR2012-2219
			
			Steps corresponding to manageBooking
			1 - Choose
			2 - Change showing time
			3 - Ticket Class Upgrade
			4 - Change seat
			
			Session data:
			'manageBooking' - stage
			'manageBooking_finishImmediately' - go to confirmation page immediately upon submitting the form
			'manageBooking_progressCount' 
		*/
		$okayBookings = $this->Booking_model->getPaidBookings( $this->session->userdata('accountNum') );
		$guestCount = Array();
		$ticketClassesName = Array();
		$data = Array( 'bookings' => false );
		
		//die( var_dump( $okayBookings ) );
		//die( var_dump( $_COOKIE ) );
		if( $okayBookings !== false )
		{
			foreach ($okayBookings as $eachBooking)
			{
				$bNumber = $eachBooking->bookingNumber;
				$guestCount[ $bNumber ] = count( $this->Guest_model->getGuestDetails( $bNumber ) );
				$ticketClassObj = $this->TicketClass_model->getSingleTicketClassName( 
					$eachBooking->EventID, 
					$eachBooking->TicketClassGroupID, 
					$eachBooking->TicketClassUniqueID				
				);
				$ticketClassesName[ $eachBooking->EventID ][ $eachBooking->TicketClassGroupID ][ $eachBooking->TicketClassUniqueID ] = $ticketClassObj;
			}
			$data['bookings'] = $okayBookings;
			$data['guestCount'] = $guestCount;
			$data['ticketClassesName'] = $ticketClassesName;
		}
		$this->load->view( 'manageBooking/manageBooking01', $data );
	}//manageBooking(..)

	function manageBooking_changeShowingTime()
	{
		/*
			Created 03MAR2012-1613
		*/
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$bookingObj;
		$guestCount;
		$eventObj;
		$configuredEventsInfo = Array();
		
		if( $bookingNumber === false )
		{
			die( 'ERROR_DATA-NEEDED' );
		}
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false )
		{
			die( 'Booking does not exist' );
		}
		$eventObj = $this->Event_model->getEventInfo( $bookingObj->EventID );
		$configuredEventsInfo[] = $eventObj;
		$data['configuredEventsInfo'] = $configuredEventsInfo;
		$data['existingShowtimeID'] = $bookingObj->ShowingTimeUniqueID;
		if( $this->Event_model->isShowtimeOnlyOne( $bookingObj->EventID ) )
		{
			// so user cannot access this feature because no other showing time to change.
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "There is only one showing time for the event you have booked so you cannot change to another showing time.";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
			$data[ 'defaultAction' ] = 'Manage Booking';
			$this->load->view( 'errorNotice', $data );
			return false;
		}else{
			$this->Event_model->setSessionActivity('manageBooking', 1 );
			$data['currentShowingTime'] = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
			$this->load->view( 'manageBooking/manageBooking02_selectShowingTime.php', $data );
		}		
	}//manageBooking_changeShowingTime
	
	function manageBooking_changeTicketClass()
	{
		die(var_dump($_POST));
	}
	
	function manageBooking_changeSeat()
	{
		/*
			Created 02MAR2012-1257
		*/
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$bookingObj;
		$guestCount;
		
		if( $bookingNumber === false )
		{
			die( 'ERROR_DATA-NEEDED' );
		}
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false )
		{
			die( 'Booking does not exist' );
		}
		$guestCount = count( $this->Guest_model->getGuestDetails( $bookingNumber ) );
		$this->setBookingCookiesOuter( 
			$bookingObj->EventID, 
			$bookingObj->ShowingTimeUniqueID, 
			$guestCount, 
			$bookingNumber 
		);
		/*
			Ticket class uniqueID is still -1 in the earlier part, so now,
			fill it with correct value
		*/
		$cookie = Array(
			'name' => 'ticketClassUniqueID',
			'value' => $bookingObj->TicketClassUniqueID,
			'expire' => 3600				// change later to how long ticketclass hold time
		);
		$this->input->set_cookie($cookie);				
		$this->Event_model->setSessionActivity('manageBooking', 4 );
		redirect( 'EventCtrl/book_step4_forward' );
	}//manageBooking_changeSeat
	
	function manageBooking_changeSeat_process()
	{
		$guestCount = $this->input->cookie( 'slots_being_booked' );
		$eventID = $this->input->cookie( 'eventID' );
		$showtimeID = $this->input->cookie( 'showtimeID' );
		$finishImmediatelySessData = $this->session->userdata( 'manageBooking_finishImmediately ' );
		$processedSeats = 0;
		
		// access validity check first
		if( $guestCount === false or $eventID === false or  $showtimeID === false )
		{
			die( 'required cookie missing');
		}		
		/*
			This decides where to go next at the end of this function
		*/
		if( $finishImmediatelySessData === false ) 
		{
			$finishImmediately  = false;
		}else{
			$finishImmediately = ( boolean ) $finishImmediatelySessData;
		}
						
		// Now process data submitted. Index starts 1 NOT zero.
		for( $x = 1, $y = intval( $guestCount ) + 1; $x < $y; $x++ )
		{
			/*
				Get data sent
			*/
			$guestUUID = $this->input->post( 'g'.$x."_uuid" );
			$seat_old = $this->input->post( 'g'.$x."_seatMatrix_old" );
			$seat = $this->input->post( 'g'.$x."_seatMatrix" );
			
			/*
				Tokenize
			*/
			$seatMatrix_old_tokenized = explode('_', $seat_old );
			$seatMatrix_tokenized = explode('_', $seat );
			
			/*
				If old is not the same as the intended input for the seat chosen, then
				do manipulation.
			*/
			if( $seat_old != $seat )
			{
				$this->Seat_model->markSeatAsAssigned(	// new seat
					$eventID,
					$showtimeID,
					$seatMatrix_tokenized[0], 
					$seatMatrix_tokenized[1]
				);
				$this->Seat_model->markSeatAsAvailable(	//old seat
					$eventID,
					$showtimeID,
					$seatMatrix_old_tokenized[0], 
					$seatMatrix_old_tokenized[1]
				);
				// change seat in guest's record in `event_slot`
				$this->Guest_model->assignSeatToGuest( $guestUUID, $seatMatrix_tokenized[0], $seatMatrix_tokenized[1] );
				$processedSeats++;
			}//if
		}//for		
		$this->Event_model->setSessionActivity('manageBooking', 5 );
		//die( var_dump( $_POST ) );
		$data[ 'theMessage' ] = ($processedSeats == 0) ? "No changes to seats have been made." : "The seats have been changed.";
		$data[ 'redirect' ] = FALSE;
		$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
		$data[ 'defaultAction' ] = 'Manage Booking';
		$this->load->view( 'successNotice', $data );
		/*if( $finishImmediately )
		{
			ON HOLD 03MAR2012
		}*/
		$this->Event_model->setSessionActivity('manageBooking', 0 );
		$this->Event_model->deleteBookingCookies();	
	}//manageBooking_changeSeat_process
	
	function postBookingCleanup( $doNotRedirect = false )
	{
		/*
			Created 19FEB2012-1751
			
			Does anything that should be done after a user successfully books,
			like clearing cookies
		*/
		// deal first with the "X_slot_UUIDs" cookie since it's not included in the pre-determined cookies
		$redirectTo = $this->session->userdata ('redirect_to');		
		delete_cookie( $this->input->cookie( 'ticketClassUniqueID').'_slot_UUIDs'  );		
		$this->Event_model->deleteBookingCookies();
		$this->session->unset_userdata( 'bookInstanceEncryptionKey' );
		$this->session->unset_userdata( 'book_step' );
		if( $doNotRedirect ) return true;
		if( $this->input->is_ajax_request() === FALSE ) {
			if( $redirectTo !== FALSE )
			{
				$this->session->unset_userdata( 'redirect_to' );
				$this->session->unset_userdata( 'bookInstanceEncryptionKey' );
				redirect( $redirectTo );
			}else
				redirect( '/' );
		}
	}	
} //class
?>