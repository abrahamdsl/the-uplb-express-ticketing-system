<?php
/**
*	Event Controller - The Main of them All
* 	CREATED 28 NOV 2011 2035
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains most functionalities regarding an event.
*	At current, checks using functionacces library, infinite redirection *will* happen
	if data is submitted (book proper functions), then suddenly terminated midway
	its processing (before setting that we can now go to the view function). I don't
	know if CodeIgniter has safeguards against this.
*	Update 17JUL2012-1302 : ^ This dilemma will be addressed by the implementation of Air Traffic Control architecture.	
	
*	At current, user needs to be logged in to be able to use the features of this controller.
**/
class eventctrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		ignore_user_abort(true);
		include_once( APPPATH.'constants/_constants.inc');
		include_once( APPPATH.'constants/clientsidedata.inc');
		$this->load->helper('cookie');
		$this->load->model('academic_model');
		$this->load->model('account_model');
		$this->load->model('atc_model');
		$this->load->model('booking_model');
		$this->load->model('browsersniff_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('coordinatesecurity_model');
		$this->load->model('email_model');
		$this->load->model('event_model');
		$this->load->model('guest_model');
		$this->load->model('login_model');
		$this->load->model('makexml_model');
		$this->load->model('ndx_model');
		$this->load->model('ndx_mb_model');
		$this->load->model('payment_model');
		$this->load->model('permission_model');
		$this->load->model('sat_model');
		$this->load->model('seat_model');
		$this->load->model('slot_model');
		$this->load->model('ticketclass_model');
		$this->load->model('transactionlist_model');
		$this->load->model('usefulfunctions_model');
		$this->load->library('airtraffic');
		$this->load->library('airtraffic_v2');
		$this->load->library('email');
		$this->load->library('bookingmaintenance');
		$this->load->library('functionaccess');
		$this->load->library('seatmaintenance');
		$this->load->library('sessmaintain');

		if( !$this->sessmaintain->onControllerAccessRitual() ) return FALSE;
	} //construct
	
	function index()
	{
		redirect( 'eventctrl/book' );
	}//index

	private function assembleUnavailableSeatTableForManageBooking( $guestObj )
	{
		/** 
		*	@created 11MAR2012-2350 Actually matagal na nga din, refactored lang
		*   @description When managing booking and some seats are NOT available, this outputs a table
						in HTML showing the relevant info.
		*	@remarks The class "center_purest" of element <table> is dependent on 'body_all.css'
		*	@revised 27JUN2012-2229
		**/
		$tableProper = '<table class="center_purest" >';
		$temp;
		// due to some inconsistencies with our XML to Array methods, this is a band-aid solution
		if( !isset( $guestObj[0] ) ){
			$temp = $guestObj;
			$guestObj = NULL;
			$guestObj[0] = $temp;
		}
		foreach(  $guestObj as $eachSlot2 )
		{
			$tableProper .= '<tr>';
			$tableProper .= '<td style="width: 90%; ; overflow: auto;">';
			$tableProper .= $eachSlot2['lname'].", ".$eachSlot2['fname']." ".$eachSlot2['mname'];
			$tableProper .= '</td>';
			$tableProper .= '<td>';
			$tableProper .= $eachSlot2['v_rep'];
			$tableProper .= '</td>';
			$tableProper .= '</tr>';
		}
		$tableProper .= '</table>';
		
		return $tableProper;
	}//assembleUnavailableSeatTableForManageBooking(..)
	
	private function prepPurchaseCookies( $purchases )
	{
		/**
		*	@created 09JUN2012-1347
		*	@description We need to specify the purchase ID number so as for booking Step 6 display
			  ( getUnpaidPurchases will return nothing because such purchases will be marked as paid/pending payment )
			  before the page is displayed
		**/
		$purchaseCount = count( $purchases );
		$purchases_str = "";
		log_message('debug','eventctrl::prepPurchaseCookies count: '. $purchaseCount );
		if( $purchases === FALSE or $purchaseCount === 0 ) return true;
		// tokenize and set in cookie, the purchases to be displayed in the page. 
		/*
			String structure of unencrypted cookie 'purchases_identifiers':

				a = { YYYYYY };
				b = { YYYYYY | YYYYY;a };

				Separated by semicolons:
				Ex1: a
				Ex2: abaaaaabaaaa

				Live: 921093;213214;1094242;
			Y..Y	 - Min length of 1, UniqueID of purchase
		*/
		foreach( $purchases as $singlePurchase  ) $purchases_str .= ( $singlePurchase->UniqueID.";" );
		$purchases_str = substr($purchases_str, 0, strlen($purchases_str)-1 );	//remove trailing colon

		return $purchases_str;
	}//prepPurchaseCookies(..)
	
	private function prepSeatVisualData( $sendSeatInfoToView  )
	{
		/**
		*	@todo should you let this stay for optimization or rely completely on library
				seatmaintenance::getSeatRepresentationsOfGuests(..)
		*/
		$seatInfo_str = "";
		foreach( $sendSeatInfoToView as  $uuid => $singleSeatAssignment ) $seatInfo_str .= ( $singleSeatAssignment ).".";
		$seatInfo_str = substr( $seatInfo_str, 0, strlen( $seatInfo_str ) - 1 );	// remove trailing dot
		return $seatInfo_str;
	}//prepSeatVisualData(..)
	
	private function immediatelyAssignSlotsAndSeats_MidManageBooking( 
		$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID, $oldTicketClassUniqueID, 
		$newShowtimeID, $newTicketClassUniqueID, $newSlotUUIDs, $isTicketClassChanged, $bookingPaymentDeadline
	)
	{
		/**
		*	@created 11MAR2012-2307 Actually matagal na, nilabas lang mula sa isang function. Hahaha.
		*	@description Assigns slots and seats during the manage booking process. Also checks
				whether a seat is available for assigning and acts appropriately.
		*	@returns Array of slots whose seat is not available in the new showing time, else empty array.
		*	@revised 19JUN2012-1231
		**/
		// Now, these are the UUIDs of the slots of the ticket class selected. We are going to extract them.
		$slotUUIDs_tokenized = explode('_', $newSlotUUIDs );
		$seatInfoArray = Array();
		$x = 0;
		$guest_seat_not_available = Array();
		
		foreach( $guestObj as $eachGuest )
		{
			$isSeatAvailable = FALSE;
			// Get the currently assigned slot to user.
			$oldSlot = $this->slot_model->getSlotAssignedToUser_MoreFilter( 
				$eventID, 
				$oldShowtimeID, 
				$oldTicketClassGroupID, 
				$oldTicketClassUniqueID, 
				$eachGuest->UUID
			);
			// Now, assign a reserved slot of the newly selected ticket class to guest.
			$this->slot_model->assignSlotToGuest(
				$eventID,
				$newShowtimeID,
				$slotUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
			);
			// if ticket class is not changed then we have to do automatic seat assignment
			// of seats from the current showtime to the new one whenever allowed.
			log_message("DEBUG","eventctrl::immediatelyAssignSlotsAndSeats_MidManageBooking isTicketClassChanged: " . intval( $isTicketClassChanged ) );
			if( !$isTicketClassChanged )
			{
				$isSeatAssignedPreviously = !(  is_null($oldSlot->Seat_x) or  is_null($oldSlot->Seat_y) );
				if( $isSeatAssignedPreviously )
				{
					// check first if seats are available
					$seatCheckResult = $this->seat_model->isSeatAvailable(
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$eventID,
						$newShowtimeID
					 );
					$isSeatAvailable = ( $seatCheckResult['boolean'] !== false );
				}else{
					continue;
				}
				if( $isSeatAvailable )
				{
					// Assign the same seat to the new slot as with the old slot
					$this->guest_model->assignSeatToGuest( 
						$eachGuest->UUID, 
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$eventID,
						$newShowtimeID
					);
					// mark the seat in the new showing time as assigned
					$this->seat_model->markSeatAsPendingPayment( 
						$eventID,
						$newShowtimeID,
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$bookingPaymentDeadline["date"]." ".$bookingPaymentDeadline["time"]
					);
				}else{
					$oldSlot->Fname = $eachGuest->Fname;
					$oldSlot->Mname = $eachGuest->Mname;
					$oldSlot->Lname = $eachGuest->Lname;
					$oldSlot->v_rep = $this->seat_model->getVisualRepresentation(
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$eventID,
						$oldShowtimeID
					 );
				    $guest_seat_not_available[ $oldSlot->Assigned_To_User ] = $oldSlot;
				}
			}
			$seatInfoArray[ $eachGuest->UUID ] = Array(
				'Matrix_x' => ($isSeatAvailable) ? $oldSlot->Seat_x : "",
				'Matrix_y' => ($isSeatAvailable) ? $oldSlot->Seat_y : ""
			);
		}//foreach guest
		return $guest_seat_not_available;
	}//immediatelyAssignSlotsAndSeats_MidManageBooking(..)

	private function setBookingCookiesOuterServer( $eventID, $showtimeID, $slots, $bookingNumber = 'XXXXX')
	{
		/*
			@created 09JUN2012-1326 
			@purpose Sets booking C-O-S. This will now be used from now on. 
				Arose due to cookie setting bug when introducing change payment mode feature.
			@params {} - Obviously.
		*/
		  $showtimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID ); 
		  if( $showtimeObj === false )		// counter check against spoofing
		  {
			 // no showing time exists
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time not found. Are you hacking the app?  :-D "; //4031
			 $this->load->view( 'errorNotice', $data );
			 return false;
		  }	  	 
		  $eventInfo = $this->event_model->getEventInfo( $eventID );      // get major info of this event
		  $ticketClasses = $this->ticketclass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID ); 
		  if( $ticketClasses === false )  // counter check against spoofing
		  {
			 // no ticket classes exist
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time marked as for sale but there isn't any ticket class yet."; //5051
			 $this->load->view( 'errorNotice', $data );  
			return false;		 
		  }
		  $guid = $this->usefulfunctions_model->guid();
		  $this->clientsidedata_model->setBookingCookiesOnServerUUIDRef( $guid );
		  //Cookie part
		  $cookie_values = Array(
			 $guid,
			 $bookingNumber,
			 $eventID,
			 $showtimeID,
			 $showtimeObj->Ticket_Class_GroupID,
			 NULL, //tcg unique ID
			 NULL, //purchase IDS
			 NULL, //slots UUID
			 $slots,
			 NULL, //visual seat data
			 $eventInfo->Name,
			 $showtimeObj->StartDate, $showtimeObj->StartTime, $showtimeObj->EndDate, $showtimeObj->EndTime,
			 $eventInfo->Location
		  );
		  return $this->ndx_model->create( $cookie_values );
	}//setBookingCookiesOuterServer(..)
	
	private function setManageBookingCookiesOuterServer( $entries )
	{
		if( $this->ndx_mb_model->create( $entries ) )
		{
			return $this->clientsidedata_model->setManageBookingCookiesOnServerUUIDRef( $entries[0] );
		}else
			return FALSE;
	}
	
	// END OF PRIVATE FUNCTIONS //
	function book()
	{
		/**
		*	@purpose, just sets the booking progress indicator then redirects to
			forward page.
		**/
		
		$this->clientsidedata_model->setBookingProgressIndicator( 1 );
		redirect('eventctrl/book_forward');
	}//book(..)
	
	function book_forward()
	{
		/*
			@created 29DEC2011-2048	
			@description Hotspot for refactoring (i.e., in event_model, create a single function
			that gets all info - via the use of SQL joins. :D )
		*/
		$configuredEventsInfo = array();
		
		// get all events first
		$allEvents = $this->event_model->getAllEvents();
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->event_model->getReadyForSaleEvents( $allEvents );
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		$this->clientsidedata_model->setSessionActivity( BOOK, STAGE_BOOK_1_FORWARD );
		$this->load->view( "book/bookStep1", $data );
	}//book_forward()

   function book_step2( $bookingNumber = false, $ticketClassSelectionEssentials = null )
   {
	  /*
		@created 30DEC2011-1855
		@history 04MAR2012-1441 Added param $bookingNumber
		@history 08MAR2012-0030 Added param $ticketClassSelectionEssentials 
		
		@purpose Cleans defaulted bookings and/or slots if any, and reserves slots (all classes) on the event
			being booked.

		* Parameters are entertained only when session activity is MANAGE_BOOKING
		@param $bookingNumber 	Obviously
		@param $ticketClassSelectionEssentials  If not null,
		  it means, user is in manage booking and is changing showing time or went straight.
		  It is an Array, with index 0,1,2,3 corresponding to `EventID`, `showingTimes`, `slot` and manage booking cookie UUID
		
		*/	  
		//die('Feature disabled for maintenance. Posted 15JUN2012-0102. Will be back without prior notice.');
		$sessionActivity  = $this->clientsidedata_model->getSessionActivity();     
		$eventID;
		$showtimeID;
		$slots;
		$eventInfo;
		$cookie_names;
		$cookie_values;
		$expiryCleanup = Array();
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		$isThereSlotInSameTicketClass = true;
		$guid;
		$guid_mb = NULL;
	  
		$this->functionaccess->__reinit();
		if( $ticketClassSelectionEssentials === null )
		{
			/*
				This means the activity is purchasing tickets ( a new booking ).
			*/		
			$eventID 	= $this->input->post( 'events' );
			$showtimeID = $this->input->post( 'showingTimes');
			$slots 		= $this->input->post( 'slot' );	
		}else{
			/*
				User switched to another showing time and is choosing a new ticket class or 
				went straight to change ticket class functionality - for the
				same showing time.
			*/
			if( count( $ticketClassSelectionEssentials ) != 4 ) 
			{
				die("ERROR_Invalid data passed to ticket class selection."); //5050
			}
				$eventID 	= $ticketClassSelectionEssentials[0];
				$showtimeID = $ticketClassSelectionEssentials[1];
				$slots 		= $ticketClassSelectionEssentials[2];
				$guid_mb    = $ticketClassSelectionEssentials[3];
		}
		//  Validate if form submitted has the correct data. 
		if( !$this->functionaccess->preBookStep2Check( $eventID, $showtimeID, $slots, 
				( $isActivityManageBooking ) ? STAGE_MB2_SELECT_TICKETCLASS_1_PR : STAGE_BOOK_1_FORWARD 
			) 
		){
			return FALSE;
		}
		$this->clientsidedata_model->updateSessionActivityStage(
			( $isActivityManageBooking ) ? STAGE_MB2_SELECT_TICKETCLASS_2_PR : STAGE_BOOK_2_PROCESS 
		);
	  
	  //This sets the initial cookies we need throughout the process.	  
      if( $this->setBookingCookiesOuterServer( $eventID, $showtimeID, $slots, null ) === false ) return false; 
	  // after the preceeding function call, the cookies-on-server UUID ref is now available
	  $guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
	  if( $guid === FALSE )
	  {
		 die('BOOK_STEP2: UNABLE TO SET GUID');
	  }
	  // set the new cookie-on-server if manage booking: this is the cookie for the intended new showing time
	  if( $isActivityManageBooking ){
		$this->ndx_mb_model->updateNewUUID( $guid_mb, $guid );
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_MB2_SELECT_TICKETCLASS_2_PR );
	  }
	  /*
		 This checks if there are bookings marked as PENDING-PAYMENT' and yet
		 not able to pay on the deadline - thus forfeited now.
	  */
	  $this->bookingmaintenance->cleanDefaultedBookings( $eventID, $showtimeID ); 
	  
	  // now ticket classes proper
	  $showtimeObj   = $this->event_model->getSingleShowingTime( $eventID, $showtimeID ); 
	  $ticketClasses = $this->ticketclass_model->getTicketClassesOrderByPrice( $eventID, $showtimeObj->Ticket_Class_GroupID );
	  
	  /*
		Check if there are event_slots (i.e., records in `event_slot` ) that the status
		is 'BEING_BOOKED' but lapsed already based on the ticket class' holding time.
	  */	  
	  $this->bookingmaintenance->cleanDefaultedSlots( $eventID, $showtimeID, $ticketClasses );
	  
	  $grandserialized_slots_uuid_str = "";	 
      foreach( $ticketClasses as $singleClass )
      {            
         $serializedClass_Slot_UUID = "";
                     
         $eachClassSlots = $this->slot_model->getSlotsForBooking( $slots, $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $singleClass->UniqueID );
         if( $eachClassSlots === false ){
			if( $isActivityManageBooking )
			{
				$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber );
				if( intval( $bookingObj->TicketClassUniqueID) === intval($singleClass->UniqueID) )
				{
					if( $this->input->post( PIND_SLOT_SAME_TC_NO_MORE_USER_NOTIFIED ) === false )
					{
						$data = $this->bookingmaintenance->assembleNoMoreSlotSameTicketClassNotification( $eventID, $showtimeID, $slots, $bookingNumber );
						$this->clientsidedata_model->updateSessionActivityStage( 1 );
						$this->load->view( 'confirmationNotice', $data );
						return false;
					}else{
						$isThereSlotInSameTicketClass = false;
					}
				}
			}else continue;
		 }
         foreach( $eachClassSlots  as $evSlot ) $serializedClass_Slot_UUID .= ($evSlot->UUID."_");        					//serialize UUIDs of slot
         $serializedClass_Slot_UUID = substr( $serializedClass_Slot_UUID, 0, strlen( $serializedClass_Slot_UUID )-1 );      // truncate the last underscore  
		 $grandserialized_slots_uuid_str .= ( $singleClass->UniqueID . "=" . $serializedClass_Slot_UUID . ";" );            // append to the string of to be written on the DB at the end of the outer foreach		 
      }//foreach
	  
	  $this->ndx_model->updateSlotsUUID( $guid,  $grandserialized_slots_uuid_str );

	  if( $isActivityManageBooking )
	  {
		$this->ndx_model->updateBookingNumber( $guid, $bookingNumber );
		$this->clientsidedata_model->setAvailabilityOfSlotInSameTicketClass( intval( $isThereSlotInSameTicketClass ));
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		// set whether showing time is changed or not.
		$this->ndx_mb_model->updateGoShowtime( $guid_mb, ( intval($showtimeID) === intval( $currentBookingInfo->SHOWTIME_ID) ) ? MB_STAGESTAT_PASSED : MB_STAGESTAT_CHANGED );
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_MB2_SELECT_TICKETCLASS_FW );
	  }
	  $this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB2_SELECT_TICKETCLASS_FW : STAGE_BOOK_2_FORWARD );		// our activity tracker	
	  $this->clientsidedata_model->setBookingProgressIndicator( 2 );
      redirect( 'eventctrl/book_step2_forward' );
   }//book_step2(..) 
	
	function book_step2_forward()
	{
		$eventID;
		$showtimeID;
		$showtimeObj;
		$guid;
		$guid_mb;
		$bookingInfo;
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		//  Validate if page is now accessible
		if( !$this->functionaccess->preBookStep2FWCheck( $bookingInfo, ( $isActivityManageBooking )? STAGE_MB2_SELECT_TICKETCLASS_FW : STAGE_BOOK_2_FORWARD ) ) return false;
		
		$eventID     = $bookingInfo->EVENT_ID;
		$showtimeID  = $bookingInfo->SHOWTIME_ID;
		
		$showtimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID ); 	// counter check against spoofing		
		if( $showtimeObj === false )
		{
			 $this->load->view( 'errorNotice', $this->bookingmaintenance->assembleShowtime404() );
			 return false;
		}

		/* <area id="bstep2_fw_eventinfoleft_info_get" > */  {
			if( $isActivityManageBooking ){
			$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
				$eventID, 
				$currentBookingInfo->TICKET_CLASS_GROUP_ID,
				$currentBookingInfo->TICKET_CLASS_UNIQUE_ID
			);
			$data['existingPayments'] = $this->payment_model->getSumTotalOfPaid( @$currentBookingInfo->BOOKING_NUMBER , NULL );
			}
		}
		// </area>
		$data['bookingObj'] = $this->booking_model->getBookingDetails(  @$bookingInfo->BOOKING_NUMBER );
		$data['bookingInfo'] = $bookingInfo;
		$data['ticketClasses'] = $this->ticketclass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID );
		$data['eventInfo'] 	   = $this->event_model->getEventInfo( $eventID );
		$data['showtimeObj']   = $showtimeObj;
		$this->load->view( 'book/bookStep2', $data );
	}//book_step2_forward(..)
				
	function book_step3()
	{			
		$eventID;
		$showtimeID;
		$ticketClassGroupID;
		$ticketClassUniqueID;
		$guid;
		$bookingInfo;
		$guid_mb;
		
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		$ticketClassUniqueID = $this->input->post('selectThisClass');
		
		//	Check if this page can be accessed already.
		if( !$this->functionaccess->preBookStep3PRCheck( $bookingInfo, $ticketClassUniqueID,
				($isActivityManageBooking) ? STAGE_MB2_SELECT_TICKETCLASS_FW : STAGE_BOOK_2_FORWARD
			) 
		) return false;	
		$eventID 			 = $bookingInfo->EVENT_ID;
		$showtimeID			 = $bookingInfo->SHOWTIME_ID;
		$ticketClassGroupID  = $bookingInfo->TICKET_CLASS_GROUP_ID;
		
	    $this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_3_PROCESS );
		$selectedTicketClass = $this->ticketclass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		$allOtherClasses     = $this->ticketclass_model->getTicketClassesExceptThisUniqueID( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		if( $selectedTicketClass === false )
		{
			 $this->cancelBookingProcess();
			 $this->load->view( 'errorNotice', $this->bookingmaintenance->assembleTicketClassOnDB404() );
			 return false;
		}
		$chosen_class_slot_UUIDs_str = $this->usefulfunctions_model->getValueOfWIN5_Data( $ticketClassUniqueID, $bookingInfo->SLOTS_UUID );
		$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( $ticketClassUniqueID, $bookingInfo->SLOTS_UUID );
		$this->ndx_model->updateSlotsUUID( $guid, $chosen_class_slot_UUIDs_str );
		
		/* 
			Now set the uniqueID of the ticketclass.
			Change expiry time later to how long is ticket class holding time.
		 */
		$this->ndx_model->updateTicketClassUniqueID( $guid, $ticketClassUniqueID );
		$this->clientsidedata_model->updateSessionActivityStage( $isActivityManageBooking ? STAGE_MB2_SELECT_TICKETCLASS_3_PR : STAGE_BOOK_3_FORWARD );
		$this->clientsidedata_model->setBookingProgressIndicator( 3 );
		if( $isActivityManageBooking ){
			$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			$isTicketClassChanged = ( intval($currentBookingInfo->TICKET_CLASS_UNIQUE_ID) !== intval($ticketClassUniqueID) );		
			// set whether ticket class is changed or not.
			$this->ndx_mb_model->updateGoTicketClass( $guid_mb, ( $isTicketClassChanged ) ?   MB_STAGESTAT_CHANGED : MB_STAGESTAT_PASSED  );
			redirect( 'eventctrl/managebooking_changeshowingtime_process2' );
		}else{
			redirect( 'eventctrl/book_step3_forward' );
		}
	}//book_step3()
			
	function book_step3_forward(){
		$guid;
		$bookingInfo;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		if( !$this->functionaccess->preBookStep3FWCheck( $bookingInfo, STAGE_BOOK_3_FORWARD ) ) return FALSE;
		$data[ 'bookingInfo' ] = $bookingInfo;
		$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
				$bookingInfo->EVENT_ID, 
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		$this->load->view( 'book/bookStep3', $data );
	}//book_step3_forward
	
	function book_step4()
	{
		$sessionActivity;
		$eventID;
		$ticketClassGroupID;
		$slots;
		$ticketClassUID;
		$guestUUIDs;
		$guestUUIDs_tokenized;
		$ticketClassObj;
		$bookingNumber;
		$bookingPaymentDeadline;
		$showtimeID;
		$uplbConstituent = Array();
		$guest_StudentNumPair = "";
		$guest_EmpNumPair = "";
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
	
		// check if accessible already.
		if( !$this->functionaccess->preBookStep4PRCheck( $bookingInfo, STAGE_BOOK_3_FORWARD ) ) {
			return FALSE;
		}
		
		// get essential data
		$eventID              = $bookingInfo->EVENT_ID;
		$showtimeID           = $bookingInfo->SHOWTIME_ID;
		$ticketClassGroupID   = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$ticketClassUID       = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$slots                = $bookingInfo->SLOT_QUANTITY;
		$guestUUIDs           = $bookingInfo->SLOTS_UUID;
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_PROCESS );
		
		// tokenize guest UUIDs
		$guestUUIDs_tokenized = explode('_', $guestUUIDs );
		$ticketClassObj       = $this->ticketclass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUID );
		
		$bookingNumber          = $this->booking_model->generateBookingNumber();
		$bookingPaymentDeadline = $this->event_model->getShowingTimePaymentDeadline( $eventID, $showtimeID );
		
		// create booking "upper" details
		$this->booking_model->createBookingDetails( 
			$bookingNumber,
			$eventID,
			$showtimeID,
			$ticketClassGroupID,
			$ticketClassUID,
			$this->clientsidedata_model->getAccountNum()
		);
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_4_PROCESS );
		// now, create entries for the charges.
		$this->payment_model->createPurchase( 
			$bookingNumber,
			"TICKET",
			$ticketClassObj->Name." Class",
			$slots,
			$slots * floatval( $ticketClassObj->Price),
			$bookingPaymentDeadline["date"],
			$bookingPaymentDeadline["time"]
		);
		// now, insert form data submitted
		for( $x = 0; $x < $slots; $x++ )
		{
			$guestNum = ($x+1);
			$identifier = "g".$guestNum."-";

			$uplb_tc_studNum = $this->input->post( $identifier."studentNum" );
			$uplb_tc_empNum  = $this->input->post( $identifier."empNum" );
			
			$this->guest_model->insertGuestDetails(
					$bookingNumber,
					$guestNum,
					intval( $this->input->post( $identifier."accountNum" ) ),
					$this->input->post( $identifier."firstName" ),
					$this->input->post( $identifier."middleName" ),
					$this->input->post( $identifier."lastName" ),
					$this->input->post( $identifier."gender" ),
					$this->input->post( $identifier."cellphone" ),
					$this->input->post( $identifier."landline" ),
					$this->input->post( $identifier."email_01" ),
					( strlen( $uplb_tc_studNum ) > UP_STUDENTNUM_MINREQUIREMENT_LEN ) ? $uplb_tc_studNum: NULL,
					( strlen( $uplb_tc_empNum ) > UP_EMPNUM_MINREQUIREMENT_LEN ) ? $uplb_tc_empNum : NULL
			);	

			//append the string containing student num and emp num associations if not false.
			//if( $uplb_tc_studNum !== FALSE ) $guest_StudentNumPair .= ($guestNum.'-'.$uplb_tc_studNum.'_' );
			//if( $uplb_tc_empNum !== FALSE )  $guest_EmpNumPair .= ($guestNum.'-'.$uplb_tc_empNum.'_' );
		}//for

		// get details of newly inserted guests	
		$data['guests'] = $this->guest_model->getGuestDetails( $bookingNumber );
		$x = 0;
		foreach( $data['guests'] as $eachGuest )
		{
			$this->slot_model->assignSlotToGuest(
				$eventID,
				$showtimeID,
				$guestUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
			);
			if( $eachGuest->studentNumber != NULL or $eachGuest->employeeNumber != NULL )
			{
				$guest_StudentNumPair .= ($eachGuest->studentNumber.'_' );
				$guest_EmpNumPair .= ($eachGuest->employeeNumber.'_' );
			}
		}

		// remove trailing underscores
		$guest_StudentNumPair = substr( $guest_StudentNumPair, 0, strlen($guest_StudentNumPair)-1 );
		$guest_EmpNumPair  = substr( $guest_EmpNumPair, 0, strlen($guest_EmpNumPair)-1 );
		
		// now set the bookingNumber for cookie access
		$this->ndx_model->updateBookingNumber( $guid, $bookingNumber );
		
		// Set payment deadline date and time 
		$this->ndx_model->updatePaymentDeadlineDate( $guid, $bookingPaymentDeadline["date"] );
		$this->ndx_model->updatePaymentDeadlineTime( $guid, $bookingPaymentDeadline["time"] );
		
		$this->clientsidedata_model->setBookingProgressIndicator( 4 );
		/*
			Now decide where to go next.
			If any of the strings that collect specified student/employee number aren't blank, then
			go to associateclasstobooking.
		*/
		if( strlen( $guest_StudentNumPair ) > 0  or strlen( $guest_EmpNumPair ) > 0 ) 
		{
			$this->ndx_model->updateUPLBStudentNumData( $guest_StudentNumPair );
			$this->ndx_model->updateUPLBEmployeeNumData( $guest_EmpNumPair );
			
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_CLASS_1_FORWARD );// our activity tracker
			$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_4_CLASS_1_FORWARD );
			redirect( 'academicctrl/associateClassToBooking' );
		}else{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD );	// our activity tracker
			$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_4_FORWARD );
			redirect( 'eventctrl/book_step4_forward' );
		}
	}//book_step4

	function book_step4_forward(){
		$bookingInfo;				// holds MYSQL_OBJ cookie-on-server for the changes in booking
		$currentBookingInfo;
		$bookingNumber;
		$eventID;
		$guid;						// the GUID that refers to $bookingInfo
		$guid_mb;					// the GUID that refers to $m_bookingInfo
		$isActivityManageBooking;
		$isShowtimeChanged 	  = NULL;
		$isTicketClassChanged = NULL;
		$m_bookingInfo;				// holds MYSQL_OBJ cookie-on-server for manage booking
		$showtimeID;

		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		$guid        = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		
		// Access validity check
		if( !$this->functionaccess->preBookStep4FWCheck(
				$bookingInfo, 
				( $isActivityManageBooking ) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD 
			) 
		) return false;	

		$eventID              = $bookingInfo->EVENT_ID;
		$showtimeID           = $bookingInfo->SHOWTIME_ID;
		$bookingNumber        = $bookingInfo->BOOKING_NUMBER;
		$this->seatmaintenance->cleanDefaultedSeats( $eventID, $showtimeID );
		$data['guests'] 	  = $this->guest_model->getGuestDetails( $bookingNumber );
		if( $isActivityManageBooking )
		{
			$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
			$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
			$isShowtimeChanged 	  = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
			$isTicketClassChanged = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
			$data['guestSeatDetails'] = $this->seatmaintenance->getSeatRepresentationsOfGuests( 
				$eventID, $showtimeID, $data['guests'],
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
			);
			$data['existingPayments'] = $this->payment_model->getSumTotalOfPaid( $bookingNumber , NULL );
			$data['isShowtimeChanged'] = $isShowtimeChanged;
			$data['isTicketClassChanged'] = $isTicketClassChanged;
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			if( $isTicketClassChanged ){
				$data['newTCName'] =  $this->ticketclass_model->getSingleTicketClassName( 
					$eventID,
					$bookingInfo->TICKET_CLASS_GROUP_ID,
					$bookingInfo->TICKET_CLASS_UNIQUE_ID
				);
			}
		}
		$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
			$eventID,
			$isActivityManageBooking ? $currentBookingInfo->TICKET_CLASS_GROUP_ID : $bookingInfo->TICKET_CLASS_GROUP_ID  ,
			$isActivityManageBooking ? $currentBookingInfo->TICKET_CLASS_UNIQUE_ID : $bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		$data[ 'bookingInfo' ] = $bookingInfo;
		$data[ 'isActivityManageBooking' ] = $isActivityManageBooking;
		$data[ 'isSeatSelectionRequired' ] = $this->event_model->isSeatSelectionRequired( $eventID, $showtimeID );
		$this->load->view( 'book/bookStep4', $data);
	}//book_step4_forward
	
	function book_step5()
	{
		/**
		*	@created 13FEB2012-2334
		*/
		$slots;
		$x;
		$totalCharges = FREE_AMOUNT;
		$bookingNumber;
		$billingInfo = NULL;
		$eventID;
		$showtimeID;
		$slots;
		$bookingNumber;
		$guid;
		$bookingInfo;
		$guestObj;
		$showtimeNotMeant;
		$ticketClassNotMeant;
		$actStageAfterThis;
		$processingStageNumber;
		/*
			Format of $seat_assignments
			INT => Array(
			"uuid" => { guests' uuid }
			"x"    => the *submitted* seat data for x
			"y"    => the *submitted* seat data for y
			"old_st" => Array(
					// Seat assignments for the old showtime, got from DB 
					"x"
					"y"
				);
			"new_st" => Array(
					// Seat assignments that were *automatically* assigned to the new showtime, if any, got from DB
					"x"
					"y"
				);
			);
		*/
		$seat_assignments = Array();
		$processedSeats = 0;
		$guid_mb;
		$m_bookingInfo;
		$currentBookingInfo;
		$bookingInfo;
		$unpaidPurchasesArray;	// needed for specifying payment deadline for seats - only  used when $isActivityManageBOoking is true.
		
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();

		/* <area id="book_step5_pr_access_elgibility_check" > */ {
		if( !$this->input->is_ajax_request() ){
			//for now, just die, but later, divert to book_step4_forward
			die('This should be only accessible via AJAX request.');
		}
		$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$isComingFromTicketClass = $this->bookingmaintenance->isComingFromTicketClass( $m_bookingInfo );
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		// Access validity indicator	
		$pre_check = $this->functionaccess->preBookStep5PRCheck( 
				$bookingInfo, 
				($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD 
		); 	
		if( !$pre_check ) return FALSE;
		}
		// </area>
		
		/* <area id="book_step5_pr_variable_init" > */ {
		$eventID              = $bookingInfo->EVENT_ID;
		$showtimeID           = $bookingInfo->SHOWTIME_ID;
		$bookingNumber        = $bookingInfo->BOOKING_NUMBER;
		$slots				  = $bookingInfo->SLOT_QUANTITY;
		// get guest details, assumption: no error here (i.e., no entries in `booking_guests`)
		$guestObj = $this->guest_model->getGuestDetails( $bookingNumber );
		if( $isActivityManageBooking ) $unpaidPurchasesArray = $this->payment_model->getUnpaidPurchases( $bookingNumber );
		}
		// </area>

		/* <area id="book_step5_pr_harvest_post_data" > */ {
		// harvest submitted data and format it accordingly
		for( $x = 0; $x < $slots; $x++ )
		{
			$seatMatrix 		  = $this->input->post( "g".($x+1)."_seatMatrix" );
			$seatMatrix_tokenized = explode( '_', $seatMatrix );
			// if guest did not choose seat, submitted data for him is "0" ( not in form of "X_Y" )
			log_message( 'DEBUG', 'guest seat ' . $seatMatrix );
			$guest_chose_seat  =  ( count( $seatMatrix_tokenized ) == 2 );
			// array index starts at zero, but this represents Guest # x + 1
			$seat_assignments[ $x ] = Array(
				"uuid" => $guestObj[ $x ]->UUID,
				"x" => ( $guest_chose_seat ) ? $seatMatrix_tokenized[0] : SEAT_COORD404,
				"y" => ( $guest_chose_seat ) ? $seatMatrix_tokenized[1] : SEAT_COORD404
			);
			$sendSeatInfoToView[ $guestObj[ $x ]->UUID ] = "FALSE";
		}
		}
		//</area>
		
		/* <area id="book_step5_pr_check_seat_select_required" > */{
		if( $this->event_model->isSeatSelectionRequired( $eventID, $showtimeID ) )
		{
			foreach( $seat_assignments as $key => $single_data  )
			{
				if( $single_data[ "x" ] == SEAT_COORD404 or $single_data[ "y" ] == SEAT_COORD404 )
				{	
					// reset our activity tracker so they can submit to this again
					$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
					$this->booking_model->updateBooking2ndStatus(
						$bookingNumber,
						($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD
					);
					echo $this->makexml_model->XMLize_AJAX_Response(
							"error", 
							"seat selection", 
							"SEAT_REQUIRED",
							4600, 
							"You must specify seats for all the guests at this stage. This was enforced by the event manager.",
							""
					);
					return FALSE;
				}
			}
		}
		}
		//</area>
		
		/* <area id="book_step5_pr_check_if_seat_existent" > */{
			foreach( $seat_assignments as $key => $single_data  )
			{
				if(  $single_data[ "x" ] !== SEAT_COORD404 and  $single_data[ "y" ] !== SEAT_COORD404 )
				{	// seat coordinate is not equal to 404 indicator so, continue checking
					$trouble = ( !is_numeric( $single_data[ "x" ] ) or !is_numeric( $single_data[ "y" ] ) );
					/* let's assign the results to the existing array, it will be useful
						for the ticket class check after this. */
					if( !$trouble ){
						$seat_assignments[ $key ][ 'obj' ] =  $this->seat_model->getSingleActualSeatData(
							$single_data[ "x" ],
							$single_data[ "y" ],
							$eventID,
							$showtimeID
						);
						$trouble = ( $seat_assignments[ $key ][ 'obj' ] === FALSE );
					}
					if( $trouble )
					{
						$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
						$this->booking_model->updateBooking2ndStatus(
							$bookingNumber,
							($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD
						);
						echo $this->makexml_model->XMLize_AJAX_Response(
								"error", 
								"error", 
								"INVALID_SEAT_DATA|0",
								0, 
								"",
								""
						);
						// reset our activity tracker so they can submit to this again
						return FALSE;
					}
				}// if !seat 404
			}
		}
		//</area>

		/* <area id="book_step5_pr_check_if_seat_is_tc" > ( i.e. anti-"Chrome's inspect element > Edit attribute " ) */{
			foreach( $seat_assignments as $key => $single_data  )
			{	
				if(  $single_data[ "x" ] !== SEAT_COORD404 and  $single_data[ "y" ] !== SEAT_COORD404 )
				{	// seat coordinate is not equal to 404 indicator so, continue checking
					if( intval($single_data[ 'obj' ]->Ticket_Class_UniqueID) != $bookingInfo->TICKET_CLASS_UNIQUE_ID )
					{					
						log_message( 'DEBUG', "CIRCUMVENT_SEAT_BLOCK A ". intval($single_data[ 'obj' ]->Ticket_Class_UniqueID) . "-". $bookingInfo->TICKET_CLASS_UNIQUE_ID  );
						log_message( 'DEBUG', "CIRCUMVENT_SEAT_BLOCK B ". $single_data[ "x" ] . "-" .$single_data[ "y" ] );
						// reset our activity tracker so they can submit to this again
						$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
						$this->booking_model->updateBooking2ndStatus(
							$bookingNumber,
							($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD
						);
						echo $this->makexml_model->XMLize_AJAX_Response(
								"error", 
								"", 
								"CIRCUMVENT_SEAT_BLOCK",
								0, 
								"",
								""
						);
						return FALSE;
					}
				}// if !seat 404
			}
		}
		//</area>

		/* <area id="book_step5_pr_get_old_seat_ass" > */{
		if( $isActivityManageBooking )
		{
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			$bookingInfo = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
			$this->seatmaintenance->getAllGuestSeatData( $slots, $currentBookingInfo, $bookingInfo, $seat_assignments );
		}//$isActivityManageBooking
		}
		// </area>

		/* <area id="book_step5_pr_seat_occupy_check" > */ {
		$seat_occupy_check = $this->seatmaintenance->areSeatsOccupied( $seat_assignments, $eventID, $showtimeID );
		if( $seat_occupy_check[0] === TRUE )
		{
			// reset our activity tracker so they can submit to this again
			$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
			$this->booking_model->updateBooking2ndStatus(
				$bookingNumber,
				($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD
			);
			echo $this->makexml_model->XMLize_AJAX_Response(
					"error", 
					"error", 
					$seat_occupy_check[1]."|".$seat_occupy_check[2], 
					0, 
					"Seat is occupied",
					""
			);
			return FALSE;
		}
		}
		//</area>

		/*<area id="book-step5-pr-pre-fw-prerequisite-part1" made="16JUL2012-1440" > */ {
		//duplicates with area#pre-book-step5-fw-prerequisite
		$billingInfo  = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$purchases    = $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$totalCharges = $billingInfo[ AKEY_AMOUNT_DUE ];
		$purchaseCount = $purchases === FALSE ? 0 : count( $purchases );
		}
		// </area>

		/* <area id="book-step5-pr-pre-conclusion" > */ {
		$redirectURL = "eventctrl/";
		if( $isActivityManageBooking ){
			if( $isComingFromTicketClass )
			{
				$redirectURL .= "managebooking_confirm";
			}else{
				$redirectURL .= "managebooking_changeseat_complete";
			}
		}else{
			$redirectURL .= ( ( $totalCharges === FREE_AMOUNT ) ? 'book_step6' : "book_step5_forward" );
		}
		// this should be modified such that can be transferred to and done via airtraffic.
		if( $totalCharges > FREE_AMOUNT ) $this->clientsidedata_model->setBookingProgressIndicator( 5 );
		}
		// </area>

		/*<area id="bookstep5_pr_seat_finally_assign_db" > */ {
		/* 
			For each seat submitted (chosen by the user), get its visual representation
			and mark it as assigned
		*/
		if( $isActivityManageBooking ){
			$showtimeNotMeant = ( $m_bookingInfo->GO_SHOWTIME == MB_STAGESTAT_NOTMEANT );
			$ticketClassNotMeant = ($m_bookingInfo->GO_TICKETCLASS == MB_STAGESTAT_NOTMEANT );
		}
		$actStageAfterThis = ( $isActivityManageBooking ) ? STAGE_MB4_CONFIRM_PR : STAGE_BOOK_5_FORWARD;
		$processingStageNumber = ( $isActivityManageBooking ) ? STAGE_MB3_SELECT_SEAT_PR : STAGE_BOOK_5_PROCESS;
		
		// initialize air traffic - i.e., URI and session activity name and stage to be set on success
		$this->airtraffic_v2->initialize( STAGE_BOOK_5_PROCESS, $actStageAfterThis );
		
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, $processingStageNumber );
		for( $x = 0; $x < $slots; $x++ )
		{
			if( !(  $seat_assignments[ $x ][ "x" ] !== SEAT_COORD404 and  $seat_assignments[ $x ][ "y" ] !== SEAT_COORD404 ) ){
				// if goticket class and go showtime are both zero, check if old is not 404 too - if yes, unset those and continue
				if( $isActivityManageBooking and ( $showtimeNotMeant and $ticketClassNotMeant) )
				{
						$userHasOldSeat = !( $seat_assignments[ $x ][ "old_st" ] === FALSE );
						if( $userHasOldSeat ){
							$this->seat_model->markSeatAsAvailable(	//old seat
								$eventID,
								$showtimeID,
								$seat_assignments[ $x ][ "old_st" ][ "x" ], 
								$seat_assignments[ $x ][ "old_st" ][ "y" ]
							);
							$this->guest_model->removeSeatFromGuest( $seat_assignments[ $x ]["uuid"] );
							$processedSeats++;
						}
				}
				continue;
			}
			// seat coordinate is not equal to 404 indicator so, continue checking
			$newSeatAction = 1;			// default action on what to do with the (new) seats submitted
			$visualRep = $this->seat_model->getVisualRepresentation(
				$seat_assignments[ $x ][ "x" ],
				$seat_assignments[ $x ][ "y" ],
				$eventID,
				$showtimeID
			);
			if( $visualRep !== FALSE ) $sendSeatInfoToView[ $seat_assignments[ $x ][ "uuid" ] ] = $visualRep;
			if( $isActivityManageBooking )
			{
				/*
					If old is not the same as the intended input for the seat chosen, then
					do manipulation.
				*/
				if( ($seat_assignments[ $x ][ "x"] ===  $seat_assignments[ $x ][ "old_st" ][ "x" ] and
					 $seat_assignments[ $x ][ "y" ] ===  $seat_assignments[ $x ][ "old_st" ][ "y" ])
					 === FALSE
				){
					if( !$isComingFromTicketClass )
					{
						/*
							User just went straight to change seat feature - no changing of ticket class and
							no change of show involved. So no checking for charges, just shoot away.
						*/		
						$userHasOldSeat = !( $seat_assignments[ $x ][ "old_st" ] === FALSE );
						if( $userHasOldSeat ) $this->seat_model->markSeatAsAvailable(	//old seat
							$eventID,
							$showtimeID,
							$seat_assignments[ $x ][ "old_st" ][ "x" ], 
							$seat_assignments[ $x ][ "old_st" ][ "y" ]
						);
						$processedSeats++;
					}else{
						/*
							Free the seat that has been automatically assigned, if any.
						*/
						$userHasAutoAssignedSeat = !( $seat_assignments[ $x ][ "new_st" ] === FALSE );
						if( $userHasAutoAssignedSeat ) $this->seat_model->markSeatAsAvailable(	//auto seat
							$eventID,
							$showtimeID,
							$seat_assignments[ $x ][ "new_st" ][ "x" ], 
							$seat_assignments[ $x ][ "new_st" ][ "y" ]
						);
						$newSeatAction = 2;
					}
				}else{
					continue;
				}
			}//$isActivityManageBooking
			if( $newSeatAction === 1 )
			{
				$this->seat_model->markSeatAsAssigned(
					$eventID,
					$showtimeID,
					$seat_assignments[ $x ][ 'x' ],
					$seat_assignments[ $x ][ 'y' ]
				);
			}else{
				$this->seat_model->markSeatAsPendingPayment(
					$eventID, 
					$showtimeID,
					$seat_assignments[ $x ][ 'x' ],
					$seat_assignments[ $x ][ 'y' ],
					$bookingInfo->EXPIRE_DATE." ".$bookingInfo->EXPIRE_TIME
				);
			}
			$this->guest_model->assignSeatToGuest( 
				$seat_assignments[ $x ][ 'uuid' ],
				$seat_assignments[ $x ][ 'x' ],
				$seat_assignments[ $x ][ 'y' ],
				$eventID,
				$showtimeID,
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
			);
		}
		$this->booking_model->updateBooking2ndStatus(
			$bookingNumber,
			$actStageAfterThis
		);
		}
		// </area>
		
		/*<area id="book-step5-pr-pre-fw-prerequisite-part2" made="16JUL2012-1448" > */ {
		//duplicates with area#pre-book-step5-fw-prerequisite
		$this->ndx_model->updatePurchaseID( $guid, $this->prepPurchaseCookies( $purchases ) );
		$this->ndx_model->updateVisualSeatData( $guid, $this->prepSeatVisualData( $sendSeatInfoToView  ) );
		}
		// </area>
		
		/* <area id="book-step5-pr-db-decide" made="16JUL2012-1448" > */ {
			// now, seek clearance and decide whether or not to commit or rollback
			if( $this->airtraffic_v2->clearance() ){
				$this->airtraffic_v2->commit();
				log_message('DEBUG','book_step5_pr cleared for take off ' . $this->airtraffic_v2->getGUID() );
				return $this->sessmaintain->assembleProceed( $redirectURL );
			}else{
				$this->airtraffic_v2->rollback();
				log_message('DEBUG','book_step5_pr clearance error '. $this->airtraffic_v2->getGUID() );
				return $this->sessmaintain->assembleATC_V2_ClearanceFail();
			}
		}
		// </area>
	}//book_step5(..)

	function book_step5_forward()
	{
		$guid;
		$bookingInfo;
		$bookingNumber;
		$eventID;
		$showtimeID;
		$excludePaymentMode;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		//access validity check
		if( !$this->functionaccess->preBookStep5FWCheck( $bookingInfo, STAGE_BOOK_5_FORWARD ) ) return false;
		
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID                 = $bookingInfo->EVENT_ID;
		$showtimeID              = $bookingInfo->SHOWTIME_ID;
					
		/*  checks for any payment mode exclusion. The function returns an integer, for cases
			like when we are changing payment modes for a yet-unpaid booking.
		*/
		$excludePaymentMode = $this->clientsidedata_model->getPaymentModeExclusion();
		
		$billingInfo  = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		/*
			Though we could just use $billingInfo[ AKEY_AMOUNT_DUE ] instead of settings
			this thru CI session data, however, in book_step6(), we ought to have first
			the total charges in deciding the payment mode. If we don't call this function,
			we have to get cookie-on-server data first then call bookingmaintenance->getBillingRelevantData().
			But then, this is a circular dependency and wasteful since you have to do
			some pre-checking whether these values are existing and it removes the essence of
			functionaccess->preBookStep6PRCheck(..). 
			Just setting this in CI session cookie and then retrieving from there is 
			easier.
		*/
		$this->clientsidedata_model->setPurchaseTotalCharge(  $billingInfo[ AKEY_AMOUNT_DUE ] );
		$data['bookingInfo']     = $bookingInfo;
		$data['purchases']       = $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$data['total_charges']   = $billingInfo[ AKEY_AMOUNT_DUE ];
		$data['guests'] 		 = $this->guest_model->getGuestDetails( $bookingNumber );	
		$data['seatVisuals']     = $this->seat_model->make_array_visualSeatData( $data['guests'] , $bookingInfo->VISUALSEAT_DATA );
		$data['paymentChannels'] = $this->payment_model->getPaymentChannelsForEvent( $eventID, $showtimeID, FALSE, $excludePaymentMode );
		$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
				$eventID, 
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		
		$this->load->view( 'book/bookStep5', $data );
	}// book_step5_forward(..)
	
	function book_step6()
	{
		/* <area id="book_step6_pr_var_declare" > */ {
		$clientUUIDs;
		$guestSeats = null;
		$totalCharges;
		$clientUUIDs;
		$paymentChannel;
		$paymentChannel_obj;
		$guid;
		$bookingInfo;
		}
		// </area>
		
		/* <area id="book_step6_pr_access_check" > */ {
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		$totalCharges = $this->clientsidedata_model->getPurchaseTotalCharge();
		/*
			If total charges is zero, then payment mode unique_id is 0 (factory default - auto
			confirmation since free), else, the one sent by post.
		*/
		$paymentChannel = ( is_float( $totalCharges ) and $totalCharges === FREE_AMOUNT ) ? FACTORY_AUTOCONFIRMFREE_UNIQUEID : intval($this->input->post( 'paymentChannel' ) ) ;
		// access validity check
		if( !$this->functionaccess->preBookStep6PRCheck( $totalCharges, $bookingInfo, STAGE_BOOK_5_FORWARD ) ) return false;
		}
		// </area>
		
		/* <area id="book_step6_pr_essential_var_init" > */ {
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID                 = $bookingInfo->EVENT_ID;
		$showtimeID              = $bookingInfo->SHOWTIME_ID;
		$slots					 = $bookingInfo->SLOT_QUANTITY;
		$ticketClassUniqueID	 = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$billingInfo  			 = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$totalCharges 			 = $billingInfo[ AKEY_AMOUNT_DUE ];
		}
		// </area>
		
		/* <area id="book_step6_pr_other_processing" > */ {
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_PROCESS );
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_6_PROCESS );
		// to be accessed in forward page
		$paymentChannel_obj      = $this->payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );
		if( $paymentChannel_obj === FALSE )
		{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assemblePaymentChannel404() );
		}
		$clientUUIDs = explode( "_" ,$bookingInfo->SLOTS_UUID );	// serialize guest UUIDs
		$this->clientsidedata_model->setPaymentChannel( $paymentChannel );
		$this->payment_model->setPaymentModeForPurchase( $bookingNumber, $paymentChannel, NULL );
		$eventObj                = $this->event_model->getEventInfo( $eventID );
		$data['guests']          = $this->guest_model->getGuestDetails( $bookingNumber );
		$data['singleChannel']   = $paymentChannel_obj;
		}
		// </area>
		
		/* <area id="book_step6_pr_process_payment_etc" > */ {
		$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_6_PAYMENTPROCESSING );
		$response_pandc = $this->bookingmaintenance->pay_and_confirm(
			$bookingNumber, BOOK, $paymentChannel, $totalCharges, STAGE_BOOK_6_PAYMENTPROCESSING,
			Array(
				"eventID" => $eventID,
				"showtimeID" => $showtimeID,
				"ticketClassGroupID" => $bookingInfo->TICKET_CLASS_GROUP_ID,
				"ticketClassUniqueID" => $ticketClassUniqueID
			)
		);
		/* Now this function is in charge of the results of the earlier function call.
			If no error determined or payment mode is COD, this won't return FALSE
			so we can continue processing.
		*/
		if( !$this->bookingmaintenance->react_on_pay_and_confirm(
				$response_pandc,
				'eventctrl/book_step5_forward',
				STAGE_BOOK_5_FORWARD
			 )
		){
			$this->booking_model->updateBooking2ndStatus( $bookingNumber, STAGE_BOOK_5_FORWARD );
			return FALSE;
		}
		}
		// </area>
		
		/**
		*	By arriving at this stage, dapat FREE or CASH-ON-DELIVERY ang payment mode selected supposedly.
		**/
		/* <area id="book_step6_cod_send_email" > */ {
		/*
			Try to send email.
		*/
		log_message( 'DEBUG', 'Trying to send email to guests - payment pending - guest count: ' . count( $data['guests'] ) );
		$this->bookingmaintenance->sendEmailOnBookSuccess(
			$bookingNumber,
			$data['guests'],
			$totalCharges === FREE_AMOUNT ? 2 : 1
		);
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_FORWARD ); // our activity tracker
		$this->clientsidedata_model->setBookingProgressIndicator( 6 );
		}
		// </area>
		redirect( 'eventctrl/book_step6_forward' );
	}//book_step6
	
	function book_step6_forward( $review_x = FALSE )
	{	
		/**
		*	@created 28FEB2012-1420
		*	@description Moved from book_step6, majority.
				Processes the HTML page to be outputted upon the conclusion of
				the Purchase or booking of a ticket/Posting of a reservation
		* 	@arguments $review - if 'review', it means redirected to here via mb_prep
		**/
		$paymentChannel;
		$paymentChannel_obj;
		$eventID;
		$showtimeID;
		$bookingNumber;
		$guid;
		$bookingInfo;
		$sendSeatInfoToView;
		$review = ( $review_x === FALSE ) ? FALSE : TRUE;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		$isThisMBViewDetails     = ( $this->clientsidedata_model->getMBViewDetailsNewBookingTag() !== FALSE );
		
		// access validity check
		if( !$isThisMBViewDetails ){
			if( !$this->functionaccess->preBookStep6FWCheck( $bookingInfo, STAGE_BOOK_6_FORWARD ) ) return false;
		}
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID                 = $bookingInfo->EVENT_ID;
		$showtimeID              = $bookingInfo->SHOWTIME_ID;
		$slots					 = $bookingInfo->SLOT_QUANTITY;
		$ticketClassUniqueID	 = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$billingInfo  			 = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		// 18NOV2012-1235: $totalCharges still using  ??
		// changed to utilizing $billingInfo
		$totalCharges 			 = ( $review ) ?  $billingInfo[ AKEY_AMOUNT_DUE ] : $this->clientsidedata_model->getPurchaseTotalCharge();
		$paymentChannel 		 = ( $totalCharges === FREE_AMOUNT ) ? FACTORY_AUTOCONFIRMFREE_UNIQUEID : (
						// if we are reviewing this newly, unpaid booking, get the payment channel ID at the concerned field of the first
						// entry of the unpaid purchases array being returned, else, this is a booking in progress and use the cookies.
						( $review ) ? $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID :  $this->clientsidedata_model->getPaymentChannel()
		);
		$paymentChannel_obj    = $this->payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );
		$data['singleChannel'] = $paymentChannel_obj;
		$data['guests']        = $this->guest_model->getGuestDetails( $bookingNumber );
		$data['purchases']     = ($billingInfo[ AKEY_PAID_PURCHASES_ARRAY ] === FALSE ) ? $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ] : $billingInfo[ AKEY_PAID_PURCHASES_ARRAY ];
		$data['pddate']        = $data['purchases'][0]->Deadline_Date;
		$data['pdtime']        = $data['purchases'][0]->Deadline_Time;
		if( $isThisMBViewDetails ){
			// Special section dedicated to feature "Manage Booking > View Details (of a new booking)"
			$seat_assignments = Array();
			$sendSeatInfoToView = Array();
			$x = 0;
			
			foreach( $data['guests'] as $singleGuest ) $seat_assignments[] = Array( "uuid" => $singleGuest->UUID );
			$this->seatmaintenance->getAllGuestSeatData( $bookingInfo->SLOT_QUANTITY, NULL, $bookingInfo, $seat_assignments );
			foreach( $seat_assignments as $singleSeatAss ){
				if( $singleSeatAss[ "new_st" ] !== FALSE ){
					$visualRep = $this->seat_model->getVisualRepresentation(
						$singleSeatAss[ "new_st" ][ "x" ],
						$singleSeatAss[ "new_st" ][ "y" ],
						$eventID,
						$showtimeID
					);
					$sendSeatInfoToView[ $seat_assignments[ $x ][ "uuid" ] ] = ($visualRep === FALSE ) ? "0" : $visualRep;
				}else{
					$sendSeatInfoToView[ $seat_assignments[ $x ][ "uuid" ] ] = "0";
				}
				$x++;
			}
		}//if( $isThisMBViewDetails )
		$data['seatVisuals']   = $this->seat_model->make_array_visualSeatData( 
			$data['guests'],
			$isThisMBViewDetails ? $this->prepSeatVisualData( $sendSeatInfoToView ) : $bookingInfo->VISUALSEAT_DATA
		);
		$data['bookingInfo']   = $bookingInfo;
		$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
				$eventID,
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$ticketClassUniqueID
		);
		if( $paymentChannel_obj->Type == "COD" or $isThisMBViewDetails )
		{
			$this->load->view( 'book/bookStep6_COD', $data);
		}else
		if( $paymentChannel_obj->Type == "ONLINE" )
		{
			/*
				If online payment worked, then booking should not have any more charges as evidenced
				by return of BOOLEAN FALSE of getUnpaidPurchases in bookingmaintenace->getBillingRelevantData(.)
			*/
			if( $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ] === FALSE )
			{
				$this->load->view( 'confirmReservation/confirmReservation02-free', $data );
			}else{				
				$this->clientsidedata_model->setBookingProgressIndicator( 5 );
				// set again to be able to access  payment modes page
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );
				echo "<h1>Notice</h1>";
				echo "<p>There is something fishy with your online payment.<br/><br/> Please use other payment methods.(Please don't refresh page else error).</p>";
				echo "<br/>";
				echo '<a href="'.base_url().'eventctrl/book_step5_forward">Go back to Payment modes</a>';
			}
		}else
		if( $paymentChannel_obj->Type == "FREE" )
		{
			$this->load->view( 'confirmReservation/confirmReservation02-free', $data );			
		}else{
			echo "PAYMENT_MODE ERROR: Cannot determine payment mode type."; // EC 5111			
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
		
		$accountNum    = $this->clientsidedata_model->getAccountNum();
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$isBookingUnderCheck = $this->booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum );
		if( is_null( $isBookingUnderCheck ) )
		{
			echo "ERROR_Booking not found."; // EC 4032
			return false;
		}else
		if( !$isBookingUnderCheck )
		{
			echo "ERROR_NO-PERMISSION_This booking is not under you."; // EC 4102
			return false;
		}
		$argumentArray = Array( 'bool' => true, 'Status2' => "FOR-DELETION" );		
		$guestDetails = $this->guest_model->getGuestDetails( $bookingNumber );
		if( $this->bookingmaintenance->deleteBookingTotally_andCleanup( $bookingNumber, $argumentArray ) )
		{
			$this->bookingmaintenance->sendEmailOnBookSuccess( 
				$bookingNumber, 
				$guestDetails,
				7
			);
		}
		$this->clientsidedata_model->deleteBookingCookies();
		if( !$this->input->is_ajax_request() ){
			redirect('eventctrl/managebooking');
		}else{
			echo "OKAY_SUCCESS";
			return true;
		}
	}//cancelBooking()
	
	function cancelBookingProcess( $decide_to_go_next = TRUE )
	{
		/**
		*	@created 21FEB2012-1713
		*	@description Used when user suddenly cancelled the (manage) booking process - any changes done
				to the DB and cookies are reverted.
		*	@revised <Late June 2012>
		**/
		log_message('DEBUG','eventctrl/cancelBookingProcess accessed');
		$guestDetails;
		$eventID;
		$ticketClassGroupID;
		$ticketClasses;
		$bookingStage;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		
		$sessionActivity    = $this->clientsidedata_model->getSessionActivity();
		$eventID            = $bookingInfo->EVENT_ID;
		$ticketClassGroupID = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$bookingNumber      = $bookingInfo->BOOKING_NUMBER;
		$ticketClassUniqueID = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		
		//access validity check
		//$this->functionaccess->preCancelBookingProcesss( $eventID, $ticketClassGroupID, STAGE_BOOK_2_PROCESS );	
		$bookingStage = $this->clientsidedata_model->getSessionActivityStage();
		if( $bookingStage < STAGE_BOOK_4_FORWARD or  
			( $isActivityManageBooking and $bookingStage == STAGE_MB2_SELECT_TICKETCLASS_FW )
		){
			/*
				At this stage,
				no other info has been written to the database, except that slots for all
				the ticket classes of  a showing time has been marked as 'BEING_BOOKED'.
				
				Get ticket classes since we have reserved X slots for each ticket classes of 
				the showing time concerned. Then pass to the free-er function. 
			*/
			$ticketClasses = $this->ticketclass_model->getTicketClasses( $eventID, $ticketClassGroupID );
			$this->slot_model->freeSlotsBelongingToClasses( $ticketClasses );
			$param1 = ( ($bookingStage < STAGE_BOOK_3_PROCESS) or $isActivityManageBooking ) ? $ticketClassUniqueID : FALSE;
			$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( $param1, $bookingInfo->SLOTS_UUID );
		}else{
			/*
				Coming in here, it is clear that at least one of these is true:
				- Showtime was selected/changed
				- Ticket class was selected/changed
			*/
			if( $isActivityManageBooking ){
				if( $this->functionaccess->isChangingPaymentMode() )
				{
					$this->payment_model->setPaymentModeForPurchase(						
						$bookingNumber,
						$this->clientsidedata_model->getPaymentModeExclusion()
					);
					$this->clientsidedata_model->deletePaymentModeExclusion();
				}else{
					// get cookie-on-server details
					$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
					$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
					$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
					// get guest details
					$guestObj = $this->guest_model->getGuestDetails( $bookingNumber );
					// REMINDER: The supposed new showtime and ticket class area already in the $bookingInfo var now.
					// Now, get the existing seat data
					$slots = count( $guestObj );
					$seat_assignments = Array();
					for( $x = 0; $x < $slots; $x++ ){
						log_message("DEBUG","eventctrl/cancelBookingProcess guest " . $guestObj[ $x ]->UUID);
						$seat_assignments[ $x ][ "uuid" ] = $guestObj[ $x ]->UUID;
					}
					$showtimeNotMeant = ( intval($m_bookingInfo->GO_SHOWTIME) === MB_STAGESTAT_NOTMEANT );
					$ticketClassNotMeant = ( intval($m_bookingInfo->GO_TICKETCLASS) === MB_STAGESTAT_NOTMEANT );
					$seatShouldPass = ( intval($m_bookingInfo->GO_SEAT) === MB_STAGESTAT_PASSED );
					$this->seatmaintenance->getAllGuestSeatData( $slots, $currentBookingInfo, $bookingInfo, $seat_assignments );					
					if( !( $showtimeNotMeant and $ticketClassNotMeant and $seatShouldPass ) )
					{	// we are only in the changing seat portion of manage booking, so setting the seats 
						// as available is not an option.
						foreach( $seat_assignments as $x => $value )
						{	// for each guest, set the new seats assigned to them/they selected as available, if any
							$userHasNewAssignedSeat = !( $seat_assignments[ $x ][ "new_st" ] === FALSE );
							if( $userHasNewAssignedSeat ) $this->seat_model->markSeatAsAvailable(
								$bookingInfo->EVENT_ID,
								$bookingInfo->SHOWTIME_ID,
								$seat_assignments[ $x ][ "new_st" ][ "x" ],
								$seat_assignments[ $x ][ "new_st" ][ "y" ]
							);
						}
					}
					// free the current slots in $m_bookingInfo->NEW_UUID->SLOTS_UUID
					$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( FALSE, $bookingInfo->SLOTS_UUID );
					// delete the current unpaid purchases under the booking number, since such are only generated
					// because of this
					$this->payment_model->deleteUnpaidPurchases( $bookingNumber );
					// 22JUN2012-1522 | Should we still Update `booking_details` back to the old showtime and ticket class?
					// The need only arises when post managebooking_finalize and payment failed because back in managebooking_finalize
					// just before bookingmaintenance::pay_and_confirm() is called, the new booking details is set.
					$this->booking_model->markAsPaid( $bookingNumber );
				}
			}else{
				$this->bookingmaintenance->deleteBookingTotally_andCleanup( $bookingNumber, NULL );
			}
		}// end else
		if( $isActivityManageBooking )
		{
			$this->postManageBookingCleanup();
		}else{
			$this->postBookingCleanup();
		}
		if( $this->input->is_ajax_request() ) return true;
		else{
			if( $decide_to_go_next ) redirect('/');
		}
	}//cancelBookingProcess
			
	function confirm()
	{
		/**
		*	@created 22FEB2012-2157
		*	@description Initial landing page for those COD agents in confirming a booking.
		**/
		$this->clientsidedata_model->setSessionActivity( CONFIRM_RESERVATION, STAGE_CONFIRM_1_FORWARD );
		$this->load->view( 'confirmReservation/confirmReservation01' );
	}
	
	function confirm_step2()
	{
		/**
		*	@created 22FEB2012-2157
		*	@description Checks whether booking number is for confirmation, and user is an authorized payment agency.
				Handles redirection to the next step. AJAX only.
		**/
		$bNumber;
		$preCheck;
		
		if( !$this->input->is_ajax_request() ) redirect('/');		// this is AJAX only		
		$bNumber         = $this->input->post( 'bookingNumber' );
		// access validity function + check if accessible already
		if( !$this->functionaccess->preConfirmStep2PRCheck( $bNumber ) ) return FALSE;
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_CONFIRM_2_PROCESS );
		$bNumberExistent = $this->booking_model->doesBookingNumberExist( $bNumber );
		if( $bNumberExistent )
		{
			$singleBooking = $this->booking_model->getBookingDetails( $bNumber );
			// run defaulted bookings cleaner first so as to check too if this booking is beyond the payment deadline.
			$this->bookingmaintenance->cleanDefaultedBookings( $singleBooking->EventID, $singleBooking->ShowingTimeUniqueID );
			// we do not use $singleBooking as parameter since such booking's `Status` may have been changed by the earlier function call
			$expired_states = Array( 
				0 => Array( 0 => false, 1 => false ),
				1 => Array( 0 => true,  1 => 'NOT-YET-NOTIFIED' ),
				2 => Array( 0 => true,  1 => 'FOR-DELETION' )
			);
			foreach( $expired_states as $key => $singleState )
			{
				if( $this->booking_model->isBookingExpired( $bNumber, $singleState[0], $singleState[1]  ) )
				{
					echo $this->makexml_model->XMLize_AJAX_Response( 
						"error", "deadline lapsed", "BOOKING_DEADLINE_LAPSED", 0, "The deadline of payment/confirmation for the specified booking has passed and as such slots and seats are now forfeited.", ""  //1005
					);
					return false;
				}
			}
			$guestDetails   = $this->guest_model->getGuestDetails( $bNumber );
			$bookingDetails = $this->booking_model->getBookingDetails( $bNumber );
			$this->setBookingCookiesOuterServer(
				$bookingDetails->EventID,
				$bookingDetails->ShowingTimeUniqueID,
				count( $guestDetails ),
				$bNumber
			);
			// after the preceeding function call, the cookies-on-server UUID ref is now available	
			$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
			$this->ndx_model->updateTicketClassUniqueID( $guid, $bookingDetails->TicketClassUniqueID );
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_CONFIRM_2_FORWARD );
			echo $this->makexml_model->XMLize_AJAX_Response( 
					"error", "proceed", "BOOKING_CONFIRM_CLEARED", 0, "The booking is cleared to undergo confirmation.", ""  //1006
			);
			return true;
		}else{
			echo $this->makexml_model->XMLize_AJAX_Response( 
					"error", "not found", "BOOKING_404", 0, "The booking number you have specified is not found in the system.", ""  //4032
			);
			return false;
		}		
	}//confirm_step2()
	
	function confirm_step2_forward()
	{
		/**
		*	@created 22FEB2012-2157
		*	@description Page for the summary of the booking, and where agent can finally confirm.
		**/
		$bNumber;
		$bookingDetails;
		$billingInfoArray;
		$data			  = Array();		
		$guid;
		$bookingInfo;
		$seatVisuals = Array();
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );		
		//access validity check
		if( ! $this->functionaccess->preConfirmStep2FWCheck( $bookingInfo, STAGE_CONFIRM_2_FORWARD ) ) return FALSE;
		// var assignments
		$bNumber			 = $bookingInfo->BOOKING_NUMBER;		
		$bookingDetails   	 = $this->booking_model->getBookingDetails( $bNumber );		
		$billingInfoArray    = $this->bookingmaintenance->getBillingRelevantData( $bNumber );
		// to continue or not?
		if( $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ] === false ){
			$this->postManageBookingCleanup();
			$data['theMessage'] = "There are no pending payments for this booking/It has been confirmed already."; //1004
			$data['redirect'] = 0;
			$data['noButton'] = FALSE;
			$this->load->view( 'successNotice', $data );
			return true;
		}
		// for view	
		foreach( $billingInfoArray as $key => $value ) $data[ $key ] = $value;
		$data['existingTCName'] = $this->ticketclass_model->getSingleTicketClassName( 
			$bookingDetails->EventID,
			$bookingDetails->TicketClassGroupID,
			$bookingDetails->TicketClassUniqueID
		);
		$data[ 'bookingInfo' ]        = $bookingInfo;
		$data[ 'guests' ]             = $this->guest_model->getGuestDetails( $bNumber );
		$seatreps = $this->seatmaintenance->getSeatRepresentationsOfGuests( 
			$bookingDetails->EventID,
			$bookingDetails->ShowingTimeUniqueID,
			$data['guests'],
			$bookingDetails->TicketClassGroupID,
			$bookingDetails->TicketClassUniqueID
		);
		foreach( $seatreps as $uuid => $val ) $seatVisuals[ $uuid ] = $val["visual_rep"];
		$data[ 'seatVisuals' ] 		  = $seatVisuals;
		$data['singleChannel']  =  $this->payment_model->getSinglePaymentChannel(
			$bookingDetails->EventID,
			$bookingDetails->ShowingTimeUniqueID,
			intval($data[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID )
		);
		$this->load->view( 'confirmReservation/confirmReservation02', $data );
	}//confirm_step2_forward()
	
	function confirm_step3()
	{
		/**
		*	@created 22FEB2012-2157
		*	@description Finally writes to the DB regarding a booking's confirmation. AJAX only.
		**/
		$guid;
		$bookingInfo;
		$bookingNumber;
		$accountNum;
		
		if( !$this->input->is_ajax_request() ) redirect( '/' );
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		$accountNum = $this->clientsidedata_model->getAccountNum();
		
		//access validity check		
		if(!$this->functionaccess->preConfirmStep3PRCheck( $accountNum, $bookingInfo, STAGE_CONFIRM_2_FORWARD ) ) return false;
		$bookingNumber = $bookingInfo->BOOKING_NUMBER;
		/* <area id="confirm_step3_nextgen_proper_payment_process" > */{
		$billingInfoArray    = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$infoArray = Array(
			"eventID" => $bookingInfo->EVENT_ID,
			"showtimeID" => $bookingInfo->SHOWTIME_ID,
			"ticketClassGroupID" => $bookingInfo->TICKET_CLASS_GROUP_ID,
			"ticketClassUniqueID" => $bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		$isThisForManageBooking = $this->booking_model->isBookingUpForChange( $bookingNumber );
		if( $isThisForManageBooking )
		{
			$infoArray[ "transactionID" ] = $this->usefulfunctions_model->getValueOfWIN5_Data( 
				'transaction', 
				$billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Comments
			);
		}
		
		// initialize air traffic - i.e., URI and session activity name and stage to be set on success
		if( !$this->airtraffic->initialize( IDLE, -1, '', 'successPaymentAndConfirmed', 30, 1 ) )
		{
			return FALSE;
		}
		$this->db->trans_begin();	// database checkpoint
		$response_pandc = $this->bookingmaintenance->pay_and_confirm(
			$bookingNumber,
			$isThisForManageBooking ? MANAGE_BOOKING :BOOK, 
			NULL,
			$billingInfoArray[ AKEY_AMOUNT_DUE ],
			STAGE_CONFIRM_2_FORWARD,
			$infoArray,
			Array(
				intval( @$billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID )
			)
		);
		if( $response_pandc[ "boolean" ] )
		{
			// now, seek clearance and decide whether or not to commit or rollback
			if( $this->airtraffic->clearance() and $this->airtraffic->terminateService() ){
				$this->db->trans_commit();
				log_message('DEBUG','confirm_step3 cleared for take off ' . $this->airtraffic->getGUID() );
				$this->bookingmaintenance->sendEmailOnBookSuccess(
					$bookingNumber,
					$this->guest_model->getGuestDetails( $bookingNumber ),
					$isThisForManageBooking ? 4 : 2
				);
			}else{
				$this->db->trans_rollback();
				log_message('DEBUG','confirm_step3_final clearance error '. $this->airtraffic->getGUID() );
			}
			$this->airtraffic->deleteXML();
		}else{
			$this->db->trans_rollback();
			return $this->sessmaintain->assembleConfirmStep3Error( $response_pandc );
		}
		}
		// </area>
		return $response_pandc[ "boolean" ];		
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
				
		redirect( 'eventctrl/create_step1_forward' );
	}//create
	
	function create_step1_forward(){
	
		if( $this->session->userdata( 'createEvent_step' ) === 1 )	$this->load->view( 'createEvent/createEvent_001' );
		else
			redirect( '/eventctrl/create' );
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
		if( $this->event_model->isEventExistent( $this->input->post( 'eventName' ) ) )
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
		$this->event_model->createEvent_basic();			
		redirect( 'eventctrl/create_step2_forward' );
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
		redirect( 'eventctrl/create_step3_forward' );
	}//create_step3
	
	function create_step3_forward()
	{		
		if( $this->session->userdata( 'createEvent_step' ) !== 3 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		// construct schedules and output HTML page
		$data['scheduleMatrix'] = $this->event_model->constructMatrix();		
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
			$eventID = $this->clientsidedata_model->getEventID();
			// let's get the last uniqueID for the showing times of this event if ever
			$lastUniqueID = $this->event_model->getLastShowingTimeUniqueID( $eventID );
			
			// now, with the data, create showings and insert them to the database
			$this->event_model->createShowings( $lastUniqueID, $eventID );
		}//if !$repeat
		$this->session->set_userdata( 'createEvent_step', 4 );
		redirect( 'eventctrl/create_step4_forward' );
	}//create_step4(..)
	
	function create_step4_forward()
	{
		if( $this->session->userdata( 'createEvent_step' ) !== 4 ){
			$data['error'] = "NO_DATA";
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$eventID =  $this->clientsidedata_model->getEventID();
		
		if( $eventID === FALSE )
		{
			$data['error'] = "CUSTOM";
			$data['theMessage'] = "COOKIE MANIPULATION DETECTED<br/><br/>Why did you delete your cookie(s)????? Reverting changes made to the database ... ";
			$data['redirect'] = false;
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		// now, get such showings straight from the DB
		$unconfiguredShowingTimes = $this->event_model->getUnconfiguredShowingTimes( $eventID );
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
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
			$this->event_model->setShowingTimeConfigStat( 
				$eventID,
				$key,
				"BEING_CONFIGURED"
			);
			
			//set slots of the showing times to the new one
			$this->event_model->setShowingTimeSlots( 
				$eventID,
				$key,
				$slots
			);
			
			$x++;
		}//foreach(..)		
		$this->session->set_userdata( 'createEvent_step', 5 );
		$this->session->set_userdata( 'slots_per_st', $slots );
		redirect( 'eventctrl/create_step5_forward' );
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
		$data['seatMaps'] = $this->seat_model->getUsableSeatMaps( $slots ); 					// get seat map available
		$data['ticketClasses_default'] = $this->ticketclass_model->getDefaultTicketClasses();	// get ticket classes				
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
		if( $this->session->userdata( 'createEvent_step' ) !== 5 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$data['beingConfiguredShowingTimes'] =  $this->event_model->getBeingConfiguredShowingTimes( $eventID );
		// get first seatMap info and unset it from post to not interfere with processing later on
		$seatMap = $this->input->post( 'seatMapPullDown' );
		unset( $_POST['seatMapPullDown'] );
		/*
			Iterate through submitted values, tokenize them into respective classes and assign.
		*/
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
				// loop indicator
				$x++;
		}
		$databaseSuccess = TRUE;
		$lastTicketClassGroupID = $this->ticketclass_model->getLastTicketClassGroupID( $eventID );
		$lastTicketClassGroupID++;
		$this->db->trans_begin();
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
		$this->event_model->setShowingTimeTicketClass( $eventID, $lastTicketClassGroupID );
		
		/*
			For each showing time being configured, create actual slots.
		*/
		foreach( $data['beingConfiguredShowingTimes'] as $eachShowingTime )
		{
			$thisST_ticketClasses = $this->ticketclass_model->getTicketClasses( 
				$eventID, 
				$lastTicketClassGroupID 
			);
			foreach( $thisST_ticketClasses as $eachTicketClass )
			{
				$this->slot_model->createSlots( 
					$eachTicketClass->Slots,
					$eventID,
					$eachShowingTime->UniqueID,
					$lastTicketClassGroupID,
					$eachTicketClass->UniqueID
				);
			}
		}
		/*if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
		}*/
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
		$beingConfiguredShowingTimes = $this->event_model->getBeingConfiguredShowingTimes( $eventID );
		//$this->db->trans_begin();
		foreach( $beingConfiguredShowingTimes as $eachSession )
		{
			//update the seat map of the showing time
			$this->event_model->setShowingTimeSeatMap( $this->input->post( 'seatmapUniqueID' ), $eventID, $eachSession->UniqueID );
			// duplicate seat pattern to the table containing actual seats
			$this->seat_model->copyDefaultSeatsToActual( $this->input->post( 'seatmapUniqueID' ), $eventID,  $eachSession->UniqueID );
			// get the ticket classes of the events being configured
			$ticketClasses_obj = $this->ticketclass_model->getTicketClasses( $eventID,  $eachSession->Ticket_Class_GroupID );
			// turn the previously retrieved ticket classes into an array accessible by the class name
			$ticketClasses = $this->ticketclass_model->makeArray_NameAsKey( $ticketClasses_obj );
			// get seat map object to access its rows and cols, for use in the loop later
			$seatmap_obj = $this->seat_model->getSingleMasterSeatMapData( $this->input->post( 'seatmapUniqueID' ) );
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
												$eventID,
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
												$eventID,
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
												$eventID,
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
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
		}
		else
		{
			$this->db->trans_commit();
		}
		echo $this->coordinatesecurity_model->createActivity( 'CREATE_EVENT', 'JQXHR', 'string' );
	}//create_step6_seats()
	
	function create_step6_forward()
	{
		/*
			Created 04FEB2012-1845
		
			This is created to 'entertain' the request of the client page
			to load entirely the next page.
			
			Changed | 19FEB2012-1507 | Changed page access eligibility check to just accessing session data
		*/			
		$eventID =  $this->clientsidedata_model->getEventID();
		//	Page access eligibility check
		if( $this->session->userdata( 'createEvent_step' ) !== 6 )
		{
			$data['error'] = "NO_DATA";
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$doesFreeTC_Exist = $this->ticketclass_model->isThereFreeTicketClass( $eventID );
		$data['paymentChannels'] = $this->payment_model->getPaymentChannels( $doesFreeTC_Exist );
		$data['beingConfiguredShowingTimes'] = $this->event_model->getBeingConfiguredShowingTimes( $eventID );
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
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
		
		if( !$this->event_model->setParticulars( $eventID ) )
		{
			echo "Create Step 7 Set Particulars Fail.";
			die();
		}
		$beingConfiguredShowingTimes = $this->event_model->getBeingConfiguredShowingTimes( $eventID );		
		$paymentChannels = $this->payment_model->getPaymentChannels( TRUE );
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
					$this->payment_model->addPaymentChannel_ToShowTime(
						$eventID,
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."
					);
					$this->payment_model->createPaymentChannelPermission( 
						$this->session->userdata( 'accountNum' ),
						$eventID,
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."
					);
				}			
			}
		}//end foreach ($paymentChannels...
		
		$this->event_model->stopShowingTimeConfiguration( $eventID );	// now mark these as 'CONFIGURED'		
		// get still unconfigured events
		$stillUnconfiguredEvents = $this->event_model->getUnconfiguredShowingTimes(  $eventID  );
		if( count( $stillUnconfiguredEvents ) > 0 )
		{
			$this->load->view('createEvent/stillUnconfiguredNotice' );
		}else{
			$this->load->view('createEvent/allConfiguredNotice' );
		}
	}//create_step7
	
	function deleteEventCompletely()
	{
		/**
		*	@revised 04AGU2012-1523
		*/
		if( !$this->permission_model->isEventManager() )
		{
			die("Access denied. You are not an event manager.");
		}
		$deleteResult;
		$deleteResult = $this->event_model->deleteAllEventInfo( $this->input->post( 'eventID' ) );
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
		return $this->event_model->isEventExistent( $name );
	} //doesEventExist

	function getConfiguredShowingTimes( $eventID = null )
	{
		/**
		*	@created 30DEC2011-1053
		*	@description Gets the showing times that are configured for ticket booking.
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
		$allConfiguredShowingTimes = $this->event_model->getConfiguredShowingTimes( $eventID , true);
		if( $allConfiguredShowingTimes === false )
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
		$xmlResult = $this->makexml_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
		echo $xmlResult;
		return true;		
	}//getConfiguredShowingTimes(..)

	function getForCheckInShowingTimes( $eventID = null )
	{
		/**
		*	@created 30DEC2011-1053
		*   @description Gets the showing times that are ready for customer check-in.
		**/
		$allConfiguredShowingTimes;
		$eventID;
		
		//Added 29JAN2012-1530: user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
				
		$eventID = $this->input->post( 'eventID' );
		if( $eventID === false )
		{
			echo "INVALID_POST-DATA-REQUIRED";
		}
		$allConfiguredShowingTimes = $this->event_model->getForCheckInShowingTimes( $eventID );
		if( $allConfiguredShowingTimes === false )
		{
			echo "ERROR_No showing time for checking-in yet.";
			return false;
		}
		$xmlResult = $this->makexml_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
		echo $xmlResult;
		return true;		
	}//getForCheckInShowingTimes(..)
	
	function manage()
	{
		/**
		*	@created 20DEC2011-1423
		*	@description Manages events/showing times.
		*	@revised 04AGU2012-1523
		*/
		if( !$this->permission_model->isEventManager() )
		{
			die("Access denied. You are not an event manager.");
		}
		$data['events']   = $this->event_model->getAllEvents();
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		
		$this->load->view('manageEvent/home', $data);
	}//manageEvent
	
	function managebooking()
	{
		/*
			Created 01MAR2012-2219
			
			Steps corresponding to managebooking
			1 - Choose
			2 - Change showing time
			3 - Ticket Class Upgrade
			4 - Change seat
			
			Session data:
			'managebooking' - stage
			'managebooking_finishImmediately' - go to confirmation page immediately upon submitting the form
			'managebooking_progressCount' 
		*/
		$okayBookings;
		$guestCount = Array();
		$ticketClassesName = Array();
		$data = Array( 'bookings' => false );
			
		if( !$this->functionaccess->preManageBookingCheck() ) return FALSE;
		$okayBookings = $this->booking_model->getAllBookings( $this->clientsidedata_model->getAccountNum() );
		if( $okayBookings !== false )
		{
			foreach ($okayBookings as $eachBooking)
			{
				$bNumber = $eachBooking->bookingNumber;
				$guestCount[ $bNumber ] = count( $this->guest_model->getGuestDetails( $bNumber ) );
				$ticketClassObj = $this->ticketclass_model->getSingleTicketClassName( 
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
		$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, STAGE_MB0_HOME );
		$this->load->view( 'managebooking/managebooking01', $data );
	}//managebooking(..)
	
	function managebooking_changeseat()
	{
		/**
		*	@created 02MAR2012-1257
		**/
		$bookingNumber;
		$bookingObj;
		$currentBookingInfo;
		$guestCount;
		$guid_mb;
		$m_bookingInfo;
		$ticketClassUniqueID;
		$x = 0;
		
		/* <area id="mb_changeseat_access_check" >*/ {
		$this->functionaccess->__reinit();
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$bookingNumber = @$currentBookingInfo->BOOKING_NUMBER;
		$allowed_stages_before_this = Array( STAGE_MB0_PREP_FW, STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL );
		/*
			Compare stage number. 
			- If user clicked "Change seat" in manage booking home page, stage number
				now should be STAGE_MB0_PREP_FW
			- If STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL: user came from "some seats of guests are not available" page.
			- else should STAGE_MB3_SELECT_SEAT_1_PR that is set by other functions before they redirect here.
		*/
		switch( $this->functionaccess->sessionActivity_x[1] ){
			case STAGE_MB0_PREP_FW: // we came from direct clicking from manage booking page so these are necessary.
				$this->setBookingCookiesOuterServer(
					$currentBookingInfo->EVENT_ID,
					$currentBookingInfo->SHOWTIME_ID,
					$currentBookingInfo->SLOT_QUANTITY,
					$bookingNumber
				);
				$guid_new = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
				$this->ndx_model->updateTicketClassUniqueID( $guid_new, $currentBookingInfo->TICKET_CLASS_UNIQUE_ID );
				$this->ndx_mb_model->updateNewUUID( $guid_mb, $guid_new);
				break;
			//no action to take on both
			case STAGE_MB3_SELECT_SEAT_3_PR_CHANGEOPT: break;
			case STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL: break;
			default: 
				if( !$this->functionaccess->preManageBookingChangeSeatCheck( $bookingNumber, $m_bookingInfo, STAGE_MB3_SELECT_SEAT_1_PR ) ) return FALSE;
		}
		}
		// </area>
		
		$isComingFromTicketClass = $this->bookingmaintenance->isComingFromTicketClass( $m_bookingInfo );
		log_message( 'DEBUG', 'Reached  managebooking_changeseat(). Is coming from ticket class? '.intval($isComingFromTicketClass) );
		
		$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false ){
			$this->postManageBookingCleanup();
			die( 'Booking does not exist' );
		}
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_FW ); 
		$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_PASSED );
		$this->clientsidedata_model->setBookingProgressIndicator( 4 );
		redirect( 'eventctrl/book_step4_forward' );
	}//managebooking_changeseat
	
	function managebooking_changeseat_process()
	{
		/**
		*	@DEPRECATED 16JUN2012-1603
		**/
		die("16JUN2012-1603: This function is now deprecated in favor of book_step5.");
	}//managebooking_changeseat_process
	
	function mb_prep( $go_next_sent = FALSE, $bb_sent = FALSE )
	{
		/**
		*	@created 14JUN2012-1339
		*	@description This is the gateway to the features of manage booking.
				Here we set the cookie-on-server data we need then redirect the
				user to the appropriate page.
		**/
		$accountNum;
		$bookingObj;
		$bookingNumber;
		$guid_current;
		$guid_mb;
		$redirectTo;
		$slots;
		$go_seat = 0;
		$go_showtime = 0;
		$go_payment = 0;
		$go_ticketclass = 0;
		$terminated = FALSE;
		$reverse = FALSE;
		$go_MB_COS = TRUE;
		
		// Get booking number and redirection URL, check first if in POST data, else use the strings in the calling URL.
		$bookingNumber = ( $bb_sent === FALSE ) ? $this->input->post( 'bookingNumber' ) : mysql_real_escape_string( $bb_sent );
		$redirectTo    = ( $go_next_sent === FALSE ) ? $this->input->post( 'next' ) : mysql_real_escape_string( $go_next_sent );
		if( !$this->clientsidedata_model->getSessionActivityStage() ===  STAGE_MB0_PREP_FW )
		{
			if( !$this->functionaccess->preManageBookCheckUnified( Array( ), STAGE_MB0_HOME , TRUE ) ) return FALSE;
		}	
		$accountNum  = $this->clientsidedata_model->getAccountNum();
		$bookingObj  = $this->booking_model->getBookingDetails( $bookingNumber );
		if( $bookingObj === false )
		{
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBooking404() );
			return false;
		}
		if( !$this->booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{	// EC 4102
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBookingChangeDenied() );
			return false;
		}
		switch( intval($redirectTo) )
		{
			case 1 : 
				$go_showtime = MB_STAGESTAT_SHOULDPASS; 
				$redirectURI = "managebooking_changeshowingtime";
				
				break;
			case 2 : 
				$go_ticketclass = MB_STAGESTAT_SHOULDPASS; 
				$redirectURI = "managebooking_upgradeticketclass";
				break;
			case 3 : 
				$go_seat = MB_STAGESTAT_SHOULDPASS; 
				$redirectURI = "managebooking_changeseat";
				break;
			case 4 : 
				$redirectURI = "managebooking_manageclasses";
				break;
			case 5 :
				$redirectURI = "viewdetails";
				break;
			case 6 :
				//die( var_dump( $this->booking_model->isBookingUpForChange( $bookingNumber ) ) );
				if( $this->booking_model->isBookingUpForChange( $bookingNumber ) )
				{
					$redirectURI = "managebooking_pendingchange_viewdetails/".$bookingNumber;
					$reverse = TRUE;
				}else{
					$this->clientsidedata_model->setMBViewDetailsNewBookingTag();
					$go_MB_COS = false;
					$redirectURI = "book_step6_forward/review";
					
					/*
					$visualRep = $this->seat_model->getVisualRepresentation(
						$seat_assignments[ $x ][ "x" ],
						$seat_assignments[ $x ][ "y" ],
						$eventID,
						$showtimeID
					);
					if( $visualRep !== FALSE ) $sendSeatInfoToView[ $seat_assignments[ $x ][ "uuid" ] ] = $visualRep;
					*/
			
					$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_FORWARD ); // our activity tracker
					$this->clientsidedata_model->setBookingProgressIndicator( 6 );
					//die("This feature is currently only available for a changed booking.");
				}
				break;
			case 7:
				$redirectURI = "managebooking_cancelchanges"."/".$bookingNumber;
				break;
			case 8:
				$redirectURI = "resume_booking";
				break;
				break;	
			case 404:
				$redirectURI = "managebooking";
				$terminated = TRUE;
				break;
		}
		if( $terminated )
		{
			$this->postManageBookingCleanup();
		}else{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB0_PREP_PR );
			if( $reverse )
			{
				$unpaidP = $this->payment_model->getUnpaidPurchases( $bookingNumber );
				if( $unpaidP === FALSE )
				{
					// reject
					die('case 6 unpaidp');
				}
				$transactionID = $this->usefulfunctions_model->getValueOfWIN5_Data( 'transaction' ,$unpaidP[0]->Comments );
				$rollbackDetails = $this->bookingmaintenance->getSlotRollbackDataOfPurchase( $transactionID );
				if( $rollbackDetails === FALSE)
				{
					// reject
					die('case 6 rollbackdetails');
				}
			}
			$guid_mb = $this->usefulfunctions_model->guid();
			$slots = count( $this->guest_model->getGuestDetails( $bookingNumber ) );
			$this->setBookingCookiesOuterServer( 
				$bookingObj->EventID, 
				$reverse ? $rollbackDetails[ OLD_SHOWTIME_ID ] : $bookingObj->ShowingTimeUniqueID, 
				$slots, 
				$bookingNumber 
			);
			$guid_current = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
			$this->ndx_model->updateTicketClassUniqueID( 
				$guid_current, 
				$reverse ? $rollbackDetails[ OLD_SHOWTIME_TC_UNIQUE_ID ] :  $bookingObj->TicketClassUniqueID 
			);
			if( $go_MB_COS ){
				$this->setManageBookingCookiesOuterServer( Array( $guid_mb, $go_showtime, $go_ticketclass, $go_seat, $go_payment, $guid_current, NULL ) );
			}
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB0_PREP_FW );
		}
		redirect( 'eventctrl/' . $redirectURI );
	}// mb_prep(..)
	
	function managebooking_changeseat_complete( $processedSeats = 0 )
	{
		/**
		*	@created <i don't remember>
		*	@description Displays the result of "Change seat" functionality when clicked immediately 
				in Manage Booking home page.
		**/
		log_message('debug', 'eventctrl/managebooking_changeseat_complete accessed');
		$this->postManageBookingCleanup();
		$data[ 'theMessage' ] = "The seats have been changed, if any.";
		$data[ 'redirect' ] = 2;
		$data[ 'redirectURI' ] = base_url().'eventctrl/managebooking';
		$data[ 'defaultAction' ] = 'Manage Booking';
		$this->load->view( 'successNotice', $data );
		return false;
	}
	
	function managebooking_changeshowingtime()
	{
		/**
		*	@created 03MAR2012-1613
		**/
		$bookingObj;
		$bookingInfo;
		$m_bookingInfo;
		$guestCount;
		$eventObj;
		$configuredEventsInfo = Array();
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		
		if( !$this->clientsidedata_model->getSessionActivityStage() === STAGE_MB1_SELECT_SHOWTIME_FW
		){	// access check
			if( !$this->functionaccess->preManageBookingChangeShowtimeCheck( $m_bookingInfo, STAGE_MB0_PREP_FW ) ) return false;
			$this->ndx_mb_model->updateGoShowtime( $guid_mb, MB_STAGESTAT_PASSED );
		}
		$bookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$bookingObj = $this->booking_model->getBookingDetails( $bookingInfo->BOOKING_NUMBER );
		if( $bookingObj === false )
		{	// booking not found!
			$this->postManageBookingCleanup();
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBooking404() );
			return false;
		}
		if( $this->event_model->isShowtimeOnlyOne( $bookingObj->EventID ) )
		{
			// so user cannot access this feature because no other showing time to change.
			$this->postManageBookingCleanup();
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleShowtimeChangeDenied() );
			return false;
		}else{
			// load showtime selection.
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB1_SELECT_SHOWTIME_PR );
			$data['configuredEventsInfo'] = Array( $this->event_model->getEventInfo( $bookingObj->EventID ) );
			$data['existingShowtimeID']   = $bookingObj->ShowingTimeUniqueID;
			$data['guestCount']           = count( $this->guest_model->getGuestDetails( $bookingInfo->BOOKING_NUMBER ) );
			$data['bookingNumber']        = $bookingInfo->BOOKING_NUMBER;
			$data['currentShowingTime']   = $this->event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB1_SELECT_SHOWTIME_FW );
			$this->booking_model->updateBooking2ndStatus( $bookingInfo->BOOKING_NUMBER, STAGE_MB1_SELECT_SHOWTIME_FW );
			$this->clientsidedata_model->setBookingProgressIndicator( 1 );
			$this->load->view( 'managebooking/managebooking02_selectShowingTime.php', $data );
		}
	}//managebooking_changeshowingtime
			
	function managebooking_changeshowingtime_process( $showtimeID_sent = FALSE )
	{
		/**
		*	@created 04MAR2012-1339
		*	@description Submit page for choosing a new showing time in Manage Booking. However, this could also
				accomodate parameter for showtime ID.
		**/
		$bookingNumber;
		$guid_mb;
		$m_bookingInfo;
		$eventID;
		$showtimeID;
		$slots;

		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$showtimeID = ( $showtimeID_sent === FALSE ) ? $this->input->post( 'showingTimes' ) : $showtimeID_sent;
		
		// ACCESS validity check
		if(	!$this->functionaccess->preManageBookingChangeShowtimePRCheck( $showtimeID, $m_bookingInfo , STAGE_MB1_SELECT_SHOWTIME_FW ) ) return false;	
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB2_SELECT_TICKETCLASS_1_PR );
		$bookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$this->booking_model->updateBooking2ndStatus( $bookingInfo->BOOKING_NUMBER , STAGE_MB2_SELECT_TICKETCLASS_1_PR );
		$this->book_step2( $bookingInfo->BOOKING_NUMBER, Array( $bookingInfo->EVENT_ID, $showtimeID, $bookingInfo->SLOT_QUANTITY, $guid_mb ) );
	}//managebooking_changeshowingtime_processs(..)
	
	function managebooking_changeshowingtime_process2()
	{
		/**
		*	@created 04MAR2012-1455
		*	@description Processes the changes regarding a new showing time and/or ticket class. Slots
				and seats are changed/assigned and new purchase items are created if any.
		*	@revised 16JUN2012-1235
		**/
		
		/* <area id="mb_changest_pr2_var_declares" > */ {
		$guid_mb;				// holder of the GUID that points to the cookie-on-server for manage booking
		$m_bookingInfo;			// cookie-on-server for the manage booking process
		$bookingInfo;			// cookie-on-server for the new/booking modification
		$currentBookingInfo;    // cookie-on-server for the current state of the booking
		$bookingNumber;
		$sessionActivity;
		$guestUUIDs_SeatUnavailable = Array();	// stores guests whose seats are not available in the new showtime
		
		$isShowtimeChanged;
		$isTicketClassChanged;
		$eventID;
		$oldShowtimeID;
		$oldTicketClassGroupID;
		$oldTicketClassUniqueID;
		$newShowtimeID;
		$newTicketClassGroupID;
		$newTicketClassUniqueID;
		$oldTicketClassObj;
		$newTicketClassObj;
		$guestCount;
		$guestObj;				// stores guest info
		// to be used in storing entries in purchase table
		$oldShowtimeObj;
		$newShowtimeObj;
		$oldShowtimeChargeDescriptor = "";
		$newShowtimeChargeDescriptor = "";
		$guest_no_seat;
		}
		//</area>
		
		/* <area id="mb_changest_pr2_access_check_and_var_init" > */ {
		/*
			Gets the data we need: cookie-on-server for manage booking, performs access validity check of
				this function and then get current booking's and the new one. 
		*/
		$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		if(	!$this->functionaccess->preManageBookingChangeShowtimePR2Check( $m_bookingInfo , STAGE_MB2_SELECT_TICKETCLASS_3_PR ) ) return false;
		$isShowtimeChanged 	  = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
		$isTicketClassChanged = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
		if( !$isShowtimeChanged and !$isTicketClassChanged )
		{
			// reset session activity, free the used-up slots, delete the cookies-on-server and load the notice.
			$bookingInfo = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
			$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( FALSE, $bookingInfo->SLOTS_UUID );
			$this->postManageBookingCleanup();
			$this->load->view( 'successNotice', $this->bookingmaintenance->assembleNoChangeInBooking() );
			return true;
		}
		// Get booking details:
		$bookingInfo        = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
		$currentBookingInfo	= $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$eventID 			= $bookingInfo->EVENT_ID;
		$bookingNumber		= $bookingInfo->BOOKING_NUMBER;
		$sessionActivity 	= $this->clientsidedata_model->getSessionActivity();
		$oldShowtimeID          = $currentBookingInfo->SHOWTIME_ID;
		$oldTicketClassGroupID  = $currentBookingInfo->TICKET_CLASS_GROUP_ID;
		$oldTicketClassUniqueID = $currentBookingInfo->TICKET_CLASS_UNIQUE_ID;
		$newShowtimeID 			= $bookingInfo->SHOWTIME_ID;
		$newTicketClassGroupID  = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$newTicketClassUniqueID = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$slots                  = $currentBookingInfo->SLOT_QUANTITY;
		$bookingPaymentDeadline = $this->event_model->getShowingTimePaymentDeadline( $eventID, $newShowtimeID );		
		}
		//</area>

		/*
			Now see if we have to have other charges.
		*/			
		// get ticket class objects first. They contain the prices.
		$oldTicketClassObj = $this->ticketclass_model->getSingleTicketClass(
			$eventID,
			$oldTicketClassGroupID,
			$oldTicketClassUniqueID
		);
		$newTicketClassObj = $this->ticketclass_model->getSingleTicketClass( 
			$eventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID
		);
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_1_PR );
		// assemble information for record in purchase table
		
		
		/* <area id="charge_descriptor" > */ {
		if( $isShowtimeChanged )
		{
			$oldShowtimeObj = $this->event_model->getSingleShowingTime( $eventID, $oldShowtimeID );
			$newShowtimeObj = $this->event_model->getSingleShowingTime( $eventID, $newShowtimeID );
			$oldShowtimeChargeDescriptor = $oldShowtimeObj->UniqueID." ( ".$oldShowtimeObj->StartDate." ".$oldShowtimeObj->StartTime." - ";
			if($oldShowtimeObj->EndDate !=  $oldShowtimeObj->StartDate ) $oldShowtimeChargeDescriptor .= $oldShowtimeObj->EndDate." "; 
			$oldShowtimeChargeDescriptor .= $oldShowtimeObj->EndTime.' ) ';
			
			$newShowtimeChargeDescriptor = $newShowtimeObj->UniqueID." ( ".$newShowtimeObj->StartDate." ".$newShowtimeObj->StartTime." - ";
			if($newShowtimeObj->EndDate !=  $newShowtimeObj->StartDate ) $newShowtimeChargeDescriptor .= $newShowtimeObj->EndDate." "; 
			$newShowtimeChargeDescriptor .= $newShowtimeObj->EndTime.' ) ';
		}
		}
		//</area>
		
		// now, create entries for the charges.
			$guestObj = $this->guest_model->getGuestDetails_UUID_AsKey( $bookingNumber );
			// Immediately assign the new available seats to the guests whose seats are still available.
			// returns an array of guests's slots whose seats are not available in the new showing time.
			$guest_no_seat = $this->immediatelyAssignSlotsAndSeats_MidManageBooking(
				$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID,
				$oldTicketClassUniqueID, $newShowtimeID, $newTicketClassUniqueID,
				$bookingInfo->SLOTS_UUID, $isTicketClassChanged, 
				Array(
					"date" => $bookingInfo->EXPIRE_DATE,
					"time" => $bookingInfo->EXPIRE_TIME
				)
			);
			
		if( $isTicketClassChanged )
		{
			if( $isShowtimeChanged )
			{
				$this->payment_model->createPurchase(
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					$slots,
					0, 		// future rebooking fee
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
			}
			$this->payment_model->createPurchase(
				$bookingNumber,
				( $isShowtimeChanged ) ?  "TICKET" : "CHANGE_TICKET_CLASS",
				"'". (
					( $isShowtimeChanged ) ?  $newTicketClassObj->Name : "To ".$newTicketClassObj->Name."' From '".$oldTicketClassObj->Name 
				) . "' Class",
				$slots,
				$slots * floatval( $newTicketClassObj->Price),
				$bookingPaymentDeadline["date"],
				$bookingPaymentDeadline["time"]
			);
			/*
				Since in this system, different ticket classes mean different seats - user should be redirected
				to seat selection page since a new ticket class was selected.
			*/
			$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_SHOULDPASS );
			redirect( 'eventctrl/managebooking_changeseat' );
		}else{
			/*  $isTicketClassChanged is false.
			    Arriving here, $isShowtimeChanged is true 'coz we have filtered out earlier
				in this function if both are false. 
			*/
			$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_CANPASS );
			// create purchased item that signifies change of show time. This could also be a
				// future fee item.
				$this->payment_model->createPurchase(	
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					1,
					0,
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
				// create new purchase item for the tickets in that new showing time.
				$this->payment_model->createPurchase(
					$bookingNumber,
					"TICKET",
					$newTicketClassObj->Name." Class",
					$slots,
					$slots * floatval( $newTicketClassObj->Price),
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
			// check if seats with the same coordinates in the new showing time are still available
			if( count($guest_no_seat) == 0 )
			{				
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_3_PR_CHANGEOPT );
				redirect('eventctrl/managebooking_changeseat_opt');
			}else{
				/*
					Coming in here, some seats of the guests under the booking in question is
					not available in the target new showtime (maybe they are occupied now by others).
				*/
				if( $this->event_model->isSeatSelectionRequired( $eventID, $newShowtimeID ) )
				{	// since seat selection is required, go straight immediately without asking if they want
					// to choose seat
					$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_SHOULDPASS );
					redirect( 'eventctrl/managebooking_changeseat' );
				}else{
					// convert guest-no-seat info in array to XML so the function to be accessed
					// when redirected will easily be able to re-assemble it.
					$xmlize_result = $this->makexml_model->XMLize_GuestSeatNotAvailable( $guest_no_seat );
					if( $xmlize_result[0] ){
						$this->clientsidedata_model->setGuestNoSeatXMLFile( $xmlize_result[1] );
						$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL );
						redirect('eventctrl/managebooking_changeseat_notall');
					}else{						
						$this->cancelBookingProcess( FALSE );
						die('Error in XMLizing guest seat not available.');
					}
				}
			}
		}// $isTicketClassChanged is false.
	}//managebooking_changeshowingtime_processs2(..)
	
	function managebooking_changeseat_opt()
	{
		/**
		*	@created 28JUN2012-1855
		*	@description Confirmation page on whether the user would like to change
				seats that have been assigned due to manage booking processes.
		**/
		if( !$this->functionaccess->preManageBookingNoSeatAllCheck( 
				STAGE_MB3_SELECT_SEAT_3_PR_CHANGEOPT 
			)
		) return FALSE;
		
		$this->load->view( 'confirmationNotice', $this->bookingmaintenance->assembleManageBookingChangeSeatOpt() );
	}
	
    function managebooking_changeseat_notall()
	{
		/**
		*	@created 27JUN2012-2230
		*	@description Confirmation page on whether the user would like to choose
				other seat(s) for the guest(s) whose seats in the current configuration
				is not available in the new target configuration.
		**/
		if( !$this->functionaccess->preManageBookingNoSeatAllCheck( 
				STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL 
			)
		) return FALSE;
		// get xml file, read its contents, make it to array
		$arrayized = $this->seatmaintenance->arrayizeGuestNoSeatInfo();
		if( $arrayized[0] === FALSE )
		{
			$this->cancelBookingProcess( FALSE );
			die('FATAL ERROR : Cannot find required XML file! MANAGE BOOKING CANCELLED');
		}
		$this->load->view(
			'confirmationNotice', 
			$this->bookingmaintenance->assembleManageBookingGuestSeatNotAvail(
				$this->assembleUnavailableSeatTableForManageBooking( $arrayized[1] )
			)
		);
		return false;
	}//managebooking_changeseat_notall()
	
	function managebooking_cancel()
	{
		die('24JUN2012-1508: This is now deprecated in favor of eventctrl/cancelBookingProcess');
	}
	
	function managebooking_cancelchanges( $bookingNumber = FALSE )
	{	
		/**
		*	@created <i can't remember>
		*	@description Handles cancelling pending changes to a booking and reverts it to the configuration
				before modification.
		*	@revised 24JUN2012-1521
		**/
		//access check
		$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		if(	!$this->functionaccess->preManageBookingChangeShowtimePR2Check( $m_bookingInfo , STAGE_MB0_PREP_FW ) ) return false;
		
		//confirmation first
		if( $this->input->post( PIND_MBCANCELCHANGE_PROMPT ) === false )
		{			
			$this->load->view( 
				'confirmationNotice', 
				$this->bookingmaintenance->assembleCancelChangesConfirmFirst( $bookingNumber )
			);
			return false;
		}
		// cleared for take-off
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MBX_CANCEL_PR );
		$cpc_arr_res = $this->bookingmaintenance->cancelPendingChanges( $bookingNumber, 2 );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MBX_CANCEL_FW );
		$this->postManageBookingCleanup();
		if( $cpc_arr_res["boolean"] )
		{	// activity is success
			$this->load->view( 'successNotice', $this->bookingmaintenance->assembleCancelChangesOK() );
		}else{
			// error
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleCancelChangesFail(
				$cpc_arr_res["code"]." : ".$cpc_arr_res["message"] )
			);		
		}		
	}//managebooking_cancelchanges()
	
	function managebooking_manageclasses()//!!!
	{
		echo "Feature coming soon";
	}
	
	function managebooking_changepaymentmode( $bookingNumber_sent = false )//!!!
	{	
		log_message('DEBUG','eventctrl/managebooking_changepaymentmode triggered.');
		$bookingNumber;
		$bookingObj;
		$accountNum;
		$guid;
		$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		
		$bookingNumber = mysql_real_escape_string( $bookingNumber_sent );
		if( !$this->functionaccess->preManageBookingChangePMode( $bookingNumber, $m_bookingInfo, STAGE_MB9_FINAL_FW ) ){
			$this->postManageBookingCleanup();
			return FALSE;
		}
		$purchases = $this->payment_model->getUnpaidPurchases( $bookingNumber );
		if( $purchases === FALSE )
		{
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleChangePMode_NoDue() );
			return FALSE;
		}
		/*$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber );
		$this->setBookingCookiesOuterServer(
			$bookingObj->EventID,
			$bookingObj->ShowingTimeUniqueID,
			$bookingObj->SLOT_QUANTITY,
			$bookingNumber
		);
		$guid_new = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$this->ndx_model->updateTicketClassUniqueID( $guid_new, $bookingObj->TicketClassUniqueID );
		$this->ndx_mb_model->updateNewUUID( $guid_mb, $guid_new );*/
		$this->clientsidedata_model->setPaymentModeExclusion( $purchases[0]->Payment_Channel_ID );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB4_CONFIRM_FW );
		$this->clientsidedata_model->setBookingProgressIndicator( 5 );
		redirect( 'eventctrl/managebooking_confirm' );					
	}//managebooking_changepaymentmode(..)
	
	function mb_bridge()
	{	
		/**
		*	@created 18JUN2012-1719
		*	@description Bridges manage booking activities (i.e., when user is asked to choose a
				new seat or not. If not bridge immediately to manage booking confirm ).
		**/
		$stage = $this->clientsidedata_model->getSessionActivityStage();
		switch( $stage )
		{
			case STAGE_MB3_SELECT_SEAT_3_PR_CHANGEOPT:	
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB4_CONFIRM_FW );
				$this->functionaccess->redirectBookForward( STAGE_MB4_CONFIRM_FW );
				break;
		}
	}
	
	function managebooking_confirm()
	{
		/**
		*	@created 08MAR2012-0936
		*	@description Displays the summary page of the would-be changes to the booking. 
		**/
		log_message('DEBUG','eventctrl/managebooking_confirm triggered.');
		$bookingInfo        = NULL;
		$currentBookingInfo = NULL;
		$guid_mb;
		$m_bookingInfo;
		$allowed_stages_before_this = Array( STAGE_MB4_CONFIRM_FW, STAGE_MB3_SELECT_SEAT_3_PR_SOMEUNAVAIL );
		// access validity check
		$this->functionaccess->__reinit();
		$guid_mb            = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo      = $this->ndx_mb_model->get( $guid_mb );
		if( !in_array($this->functionaccess->sessionActivity_x[1], $allowed_stages_before_this ) ){
			if( !$this->functionaccess->preManageBookingConfirm( $m_bookingInfo , STAGE_MB4_CONFIRM_PR ) ) return FALSE;
		}
		// get the rest of cookie-on-server
		$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$bookingInfo        = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
		// assign essential vars
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID				 = $bookingInfo->EVENT_ID;
		$newShowtimeID 		     = $bookingInfo->SHOWTIME_ID;
		$newTicketClassGroupID   = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$newTicketClassUniqueID  = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$oldShowtimeID           = $currentBookingInfo->SHOWTIME_ID;
		$oldTicketClassGroupID   = $currentBookingInfo->TICKET_CLASS_GROUP_ID;
		$oldTicketClassUniqueID  = $currentBookingInfo->TICKET_CLASS_UNIQUE_ID;
		// get objects from DB
		$bookingObj    = $this->booking_model->getBookingDetails( $bookingNumber );
		$bookingGuests = $this->guest_model->getGuestDetails( $bookingNumber );
		$oldGuestSeatVisualRep = $this->seatmaintenance->getSeatRepresentationsOfGuests(
			 $eventID,
			 $oldShowtimeID,
			 $bookingGuests,
			 $oldTicketClassGroupID,
			 $oldTicketClassUniqueID
		);
		$newGuestSeatVisualRep = $this->seatmaintenance->getSeatRepresentationsOfGuests(
			 $eventID,
			 $newShowtimeID,
			 $bookingGuests,
			 $newTicketClassGroupID,
			 $newTicketClassUniqueID
		); 
		$eventObj          = $this->event_model->getEventInfo( $eventID );
		$oldShowingTimeObj = $this->event_model->getSingleShowingTime( $eventID, $oldShowtimeID );
		$newShowingTimeObj = $this->event_model->getSingleShowingTime( $eventID, $newShowtimeID );
		
        // some boolean info
		$isShowtimeChanged 	  = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
		$isTicketClassChanged = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
		// Now calculate purchases
		$paymentChannels = NULL;
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );	
		if( $billingInfoArray[ AKEY_AMOUNT_DUE ] > FREE_AMOUNT )
		{
			$this->ndx_mb_model->updateGoPayment( $guid_mb, MB_STAGESTAT_SHOULDPASS );
			$paymentChannels = $this->payment_model->getPaymentChannelsForEvent(
				$eventID, 
				$newShowtimeID,
				FALSE,
				$this->clientsidedata_model->getPaymentModeExclusion()
			);
		}
		// load data for view page
		foreach( $billingInfoArray as $key => $value ) $data[ $key ] = $value;
		$data['bookingNumber'] = $bookingNumber;
		$data['oldSeatVisuals'] = $oldGuestSeatVisualRep;
		$data['singleEvent'] = $eventObj;
		$data['guestCount'] = count( $bookingGuests );
		$data['guests'] = $bookingGuests;
		$data['currentShowingTime'] = $oldShowingTimeObj ;
		$data['newShowingTime'] = $newShowingTimeObj;
		$data['newSeatData'] = $newGuestSeatVisualRep;
		$data['paymentChannels'] = $paymentChannels;
		$data['oldTicketClassName'] = $this->ticketclass_model->getSingleTicketClassName(
			$eventID,
			$oldTicketClassGroupID, 
			$oldTicketClassUniqueID 
		);
		$data['newTicketClassName'] =$this->ticketclass_model->getSingleTicketClassName(
			$eventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID 
		);
		$data['isShowtimeChanged'] = $isShowtimeChanged;
		$data['isTicketClassTheSame'] = !$isTicketClassChanged;
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB4_CONFIRM_FW );
		$this->clientsidedata_model->setBookingProgressIndicator( 5 );
		$this->load->view( 'managebooking/managebookingConfirm', $data );
	}//managebooking_confirm()
	
	function managebooking_finalize()
	{
		/**
		*	@created 08MAR2012-0952
		*	@description Makes the appropriate changes regarding a booking's change. (i.e., making it permanent).
		*		Roughly the equivalent of book_step6()
		**/
		/* <area id="mb_finalize_var_declarations" > */ {
		$guid_mb;
		$m_bookingInfo;
		$currentBookingInfo = NULL;
		$bookingInfo = NULL;
		$bookingObj;
		$noPendingPayment;
		$paymentMode;
		$billingInfoArray;
		$newSlotsUUIDs ;
		$bookingGuests;
		$bookingNumber;
		$eventID				 ;
		$newShowtimeID 		     ;
		$newTicketClassGroupID   ;
		$newTicketClassUniqueID  ;
		$oldShowtimeID           ;
		$oldTicketClassGroupID   ;
		$oldTicketClassUniqueID  ;
		$paymentOkay	= TRUE;
		$isComingFromTicketClass ;
		$isShowtimeChanged 	     ;
		$isTicketClassChanged    ;
		}
		// </area>
		
		/* <area id="mb_finalize_access_check" > */ {
		$paymentMode 			= $this->input->post( 'paymentChannel' );
		$guid_mb       			= $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo 			= $this->ndx_mb_model->get( $guid_mb );
		$this->functionaccess->__reinit();
		// access validity check
		if( $this->functionaccess->sessionActivity_x[1] != STAGE_MB4_CONFIRM_FW ){
			if( !$this->functionaccess->preManageBookingFinalize( $paymentMode, $m_bookingInfo , STAGE_MB4_CONFIRM_PR ) ) return FALSE;
		}
		}
		// </area>
		
		/* <area id="mb_finalize_assign_vars" > */ {
		$currentBookingInfo      = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$bookingInfo             = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID				 = $bookingInfo->EVENT_ID;
		$newShowtimeID 		     = $bookingInfo->SHOWTIME_ID;
		$newTicketClassGroupID   = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$newTicketClassUniqueID  = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$oldShowtimeID           = $currentBookingInfo->SHOWTIME_ID;
		$oldTicketClassGroupID   = $currentBookingInfo->TICKET_CLASS_GROUP_ID;
		$oldTicketClassUniqueID  = $currentBookingInfo->TICKET_CLASS_UNIQUE_ID;
		$isComingFromTicketClass = $this->bookingmaintenance->isComingFromTicketClass( $m_bookingInfo );
		$isShowtimeChanged 	     = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
		$isTicketClassChanged    = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
		//tokenize the slot UUIDs
		$newSlotsUUIDs 			= explode('_', $bookingInfo->SLOTS_UUID );
		$bookingObj 			= $this->booking_model->getBookingDetails( $bookingNumber );
		$bookingGuests 			= $this->guest_model->getGuestDetails( $bookingNumber );
		$oldGuestSeatVisualRep 	= $this->seatmaintenance->getSeatRepresentationsOfGuests( 
			$eventID, 
			$oldShowtimeID, 
			$bookingGuests,
			$oldTicketClassGroupID,
			$oldTicketClassUniqueID
		);
		$newSeatsInfoArray      = $this->seatmaintenance->getSeatRepresentationsOfGuests( 
			$eventID, 
			$newShowtimeID, 
			$bookingGuests,
			$newTicketClassGroupID,
			$newTicketClassUniqueID
		);
		$billingInfoArray       = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$noPendingPayment       = ($billingInfoArray[AKEY_AMOUNT_DUE] === FREE_AMOUNT );
		// re-evaluate payment mode.
		$paymentMode = $noPendingPayment ?  FACTORY_AUTOCONFIRMFREE_UNIQUEID : $paymentMode;
		}
		// </area>
		
		/* <area id="mb_finalize_log_message" > */ {
		if( $newSeatsInfoArray !== false )
		{
			foreach( $newSeatsInfoArray as $key => $value )
			{
				log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : '.$key." : ".$value['matrix_x']."-".$value['matrix_y']." ".$value['visual_rep'] );
			}
		}else{
			log_message('DEBUG', 'User did not choose a new seat at all.' );
		}
		log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : Ended log of new seats info' );
		log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : User has to pay for this change? '.intval( !$noPendingPayment  ) );
		}
		//</area>
	
		/* <area id="mb_finalize_payment_processing_proper" > */{
		$this->ndx_mb_model->updateGoPayment( $guid_mb, MB_STAGESTAT_PASSED );
		$transactionID = $this->transactionlist_model->createNewTransaction(
			$this->clientsidedata_model->getAccountNum(),
			'BOOKING_CHANGE',
			'UPDATED_BOOKING_DETAILS',
			$bookingNumber,
			'Secret!',
			'WIN5',
			Array(
				'oldShowingTime'		 => $oldShowtimeID,
				'oldTicketClassGroupID'  => $oldTicketClassGroupID,
				'oldTicketClassUniqueID' => $oldTicketClassUniqueID,
				'newShowingTime' 		 => $newShowtimeID,
				'newTicketClassGroupID'  => $newTicketClassGroupID,
				'newTicketClassUniqueID' => $newTicketClassUniqueID
			)
		);
		foreach( $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ] as $singlePurchase)
		{
			$this->payment_model->updatePurchaseComments(
				$bookingNumber,
				$singlePurchase->UniqueID,
				'transaction='.strval( $transactionID )
			);
		}
		$this->booking_model->updateBookingDetails(
				$bookingNumber,
				$eventID,
				$newShowtimeID,
				$newTicketClassGroupID,
				$newTicketClassUniqueID
		);
		// set rollback data info
		$response_pandc = $this->bookingmaintenance->pay_and_confirm(
			$bookingNumber, 
			MANAGE_BOOKING, 
			$paymentMode,
			$billingInfoArray[ AKEY_AMOUNT_DUE ],
			STAGE_MB7_PAYMENT_ONLINE_PR,
			Array(
				"eventID" => $eventID,
				"showtimeID" => $newShowtimeID,
				"ticketClassGroupID" => $newTicketClassGroupID,
				"ticketClassUniqueID" => $newTicketClassUniqueID,
				"transactionID" => $transactionID
			)
		);
		if( !$this->bookingmaintenance->react_on_pay_and_confirm(
				$response_pandc,
				'eventctrl/managebooking_confirm',
				STAGE_MB4_CONFIRM_FW
			 )
		){
			log_message('DEBUG', 'MANAGE BOOKING FINALIZE: false returned by react_on_pay_and_confirm: ' . $response_pandc["code"] );
			return FALSE;
		}else{
			$this->bookingmaintenance->sendEmailOnBookSuccess(
				$bookingNumber,
				$bookingGuests,
				$billingInfoArray[ AKEY_AMOUNT_DUE ] === FREE_AMOUNT ? 4 : 3
			);
		}
		}
		//</area>
		log_message('DEBUG', 'MANAGE BOOKING FINALIZE: GOING TO FORWARD' );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB9_FINAL_FW );
		redirect( 'eventctrl/managebooking_finalize_forward');
	}//managebooking_finalize
	
	function managebooking_finalize_forward()
	{
		/**
		*	@created <i can't remember>
		*	@description Displays the final page of manage booking process - the final result whether
				successful or not.
		*	@revised 17JUN2012-1433 MAJOR
		**/
		/* <area id="mb_fin_fw_var_declare" > */{
		// booking cookie-on-server identifier
		$guid;
		// MANAGE booking cookie-on-server identifier
		$guid_mb ;
		// the cookie-on-server for the current booking ( or new booking if there's an old booking )
		$bookingInfo;
		// the cookie-on-server for the old booking if there's an old one
		$bookingInfo_old;
		// speak for themselves
		$bookingNumber = FALSE;
		$eventID = FALSE;
		$m_bookingInfo;				// the MANAGE BOOKING cookie-on-server for the current booking
		$newShowingTimeID = FALSE;
		$newTicketClassGroupID = FALSE;
		$newTicketClassUniqueID = FALSE;
		$oldShowingTimeID = FALSE;
		$oldTicketClassGroupID = FALSE;
		$oldTicketClassUniqueID = FALSE;
		}
		//</area>
		log_message('DEBUG', 'MANAGE BOOKING FINALIZE FORWARD ACCESSED' );
		/* <area id="mb_fin_fw_access_check" > */{
		$guid          = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo   = $this->ndx_model->get( $guid );
		$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$bookingInfo_old = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		if( !$this->functionaccess->preManageBookingFinalizeFW( $m_bookingInfo, STAGE_MB9_FINAL_FW ) ) return FALSE;
		}
		//</area>
		
		/* <area id="mb_fin_fw_essential_var_init" > */{
		$bookingNumber    = $bookingInfo->BOOKING_NUMBER;
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$noPendingPayment = ( $billingInfoArray[ AKEY_AMOUNT_DUE ] === FREE_AMOUNT );
		if( !$noPendingPayment ){
			$eventID          = $bookingInfo->EVENT_ID;
			$newShowingTimeID = $bookingInfo->SHOWTIME_ID;
			$newTicketClassGroupID = $bookingInfo->TICKET_CLASS_GROUP_ID;
			$newTicketClassUniqueID = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
			$oldShowingTimeID       = $bookingInfo_old->SHOWTIME_ID;
			$oldTicketClassGroupID  = $bookingInfo_old->TICKET_CLASS_GROUP_ID;
			$oldTicketClassUniqueID = $bookingInfo_old->TICKET_CLASS_UNIQUE_ID;
		}//if
		}
		//</area>
		
		/* <area id="mb_fin_fw_essential_obj_assignments" > */{
		if( !$noPendingPayment ){
			$bookingObj = $this->booking_model->getBookingDetails( $eventID );
			$eventObj   = $this->event_model->getEventInfo( $eventID  );
			$bookingGuests = $this->guest_model->getGuestDetails( $bookingNumber );
			$oldShowingTimeObj = $this->event_model->getSingleShowingTime( $eventID, $oldShowingTimeID );
			$newShowingTimeObj = $this->event_model->getSingleShowingTime( $eventID, $newShowingTimeID );
			$paymentMode = $noPendingPayment ? $billingInfoArray[ AKEY_PAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID 
							 :  $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID ;
			$oldGuestSeatVisualRep = $this->seatmaintenance->getSeatRepresentationsOfGuests(
				 $eventID,
				 $oldShowingTimeID,
				 $bookingGuests,
				 $oldTicketClassGroupID,
				 $oldTicketClassUniqueID
			);
			$newSeatsInfoArray = $this->seatmaintenance->getSeatRepresentationsOfGuests(
				 $eventID,
				 $newShowingTimeID,
				 $bookingGuests,
				 $newTicketClassGroupID,
				 $newTicketClassUniqueID
			);
		}//if
		}
		// </area>
		//var_dump( $noPendingPayment  );
		//var_dump( $data );
		//die();
		/* <area id="mb_fin_fw_load_data_for_view" > */{
		if( !$noPendingPayment ){
			$data['bookingNumber']  = $bookingNumber;
			$data['oldSeatVisuals'] = $oldGuestSeatVisualRep;
			$data['newShowingTime'] = $newShowingTimeObj;
			$data['singleEvent']    = $eventObj;
			$data['guestCount']     = count( $bookingGuests );
			$data['guests']         = $bookingGuests;
			$data['currentShowingTime'] = $oldShowingTimeObj ;
			$data['newSeatData']        = $newSeatsInfoArray;
			foreach( $billingInfoArray as $key => $val ) $data[ $key ] = $val;
			$data['paymentDeadline'] = Array(
				'date' => (isset($data['unpaidPurchasesArray'][0]->Deadline_Date) ) ? $data['unpaidPurchasesArray'][0]->Deadline_Date : NULL,
				'time' => (isset($data['unpaidPurchasesArray'][0]->Deadline_Time) ) ? $data['unpaidPurchasesArray'][0]->Deadline_Time : NULL
			);	
			$data['singleChannel'] = $this->payment_model->getSinglePaymentChannel(
				$eventID, 
				$newShowingTimeID, 
				$paymentMode
			);		
			$data['oldTicketClassName'] = $this->ticketclass_model->getSingleTicketClassName(
				$eventID,
				$oldTicketClassGroupID, 
				$oldTicketClassUniqueID
			);
			$data['newTicketClassName'] = $this->ticketclass_model->getSingleTicketClassName(
				$bookingInfo->EVENT_ID, 
				$bookingInfo->TICKET_CLASS_GROUP_ID, 
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
			);
		$data['bookingInfo'] = $bookingInfo;
		}//if
		}
		// </area>
		
		/* <area id="mb_fin_fw_conclusion" > */{
		if( $noPendingPayment ){
			$this->postManageBookingCleanup();
			$this->load->view( 'successNotice', $this->bookingmaintenance->assembleBookingChangeOkay() );
		}else{
			$this->load->view( 'managebooking/managebookingFinalize_COD', $data );
			// ajax request should call postManageBookingCleanup() there instead of direct PHP here
			// since if user has the need to refresh - they're dead. It would be not user-friendly
			// to just refer them to manage booking ??
		}
		}
		//</area>
	}// managebooking_finalize_forward()
	
	function managebooking_pendingchange_viewdetails( $bookingNumber_x = FALSE )
	{
		$bookingNumber = mysql_real_escape_string( $bookingNumber_x );
		// access check
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		if( !$this->functionaccess->preManageBookingPendingViewDetails(
				$bookingNumber, $m_bookingInfo, STAGE_MB0_PREP_FW 
			) 
		){
			return FALSE;
		}
		
		$guid_current = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$current_bookingInfo = $this->ndx_model->get( $guid_current );	// just to access slots
		// coming in here, updated na dapat etong booking details with the new st/tc
		$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber );
		$this->setBookingCookiesOuterServer( 
			$bookingObj->EventID, 
			$bookingObj->ShowingTimeUniqueID,
			$current_bookingInfo->SLOT_QUANTITY, 
			$bookingNumber 
		);
		$guid_new = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef(); // get the new B c-o-s
		$this->ndx_model->updateTicketClassUniqueID( $guid_new, $bookingObj->TicketClassUniqueID );
		$this->ndx_mb_model->updateNewUUID( $guid_mb, $guid_new );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB9_FINAL_FW );
		redirect('eventctrl/managebooking_finalize_forward');
	}//managebooking_pendingchange_viewdetails()
	
	function managebooking_upgradeticketclass()
	{			
		/**
		*	@created 08MAR2012-0021
		*	@description Direct handler of upgrading ticket class when clicked in manage booking section.
				First attempt too on all lowercase in a function name.
		*	@revised 21JUN2012-1718
		**/
		$bookingObj;
		$bookingInfo;
		$m_bookingInfo;
		$guestCount;
		$eventObj;
		$configuredEventsInfo = Array();
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		
		//access validity check
		if( !$this->functionaccess->preManageBookingUpgTC_Check( $m_bookingInfo, STAGE_MB0_PREP_FW ) ) return false;
		// signify that we passed this stage
		$this->ndx_mb_model->updateGoTicketClass( $guid_mb, MB_STAGESTAT_SHOULDPASS );		
		// get some objects/info needed
		$bookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		//	Check if current ticket class of booking is the highest already.		
		$mostExpensiveTicketClassObj = $this->ticketclass_model->getMostExpensiveTicketClass(
			$bookingInfo->EVENT_ID, $bookingInfo->TICKET_CLASS_GROUP_ID
		);
		if( $mostExpensiveTicketClassObj !== false and
			intval($mostExpensiveTicketClassObj->UniqueID) === intval( $bookingInfo->TICKET_CLASS_UNIQUE_ID)
		)
		{
			$this->postManageBookingCleanup();
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleTCHighestAlready() );
			return true;
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB1_SELECT_SHOWTIME_FW );
		redirect( 'eventctrl/managebooking_changeshowingtime_process/'.$bookingInfo->SHOWTIME_ID );
	}//managebooking_upgradeticketclass
		
	function postManageBookingCleanup( $redirect = false )
	{		
		/**
		*	@created <i can't remember>
		*	@description Resets the ACTIVITY NAME and NUMBER.
					     Gets the manage booking cookie-on-server, gets its CURRENT_UUID value
						 that points to an entry on a booking cookie-on-server and by that, deletes
						 it, and  then the MB c-o-s is deleted and the control is passed to 
						 $this->postBookingCleanup() which frees and deletes any HTTP cookies
						 and the NEW_UUID (c-o-s for the new setting of the booking) if any.
		*	@remarks THIS SHOULD BE ONLY CALLED WHEN A MANAGE BOOKING PROCESS IS PROPERLY CONCLUDED.
				(i.e. When cancelling the process or starting over, call 
					base_url().eventctrl/mb_prep/404 
				instead. )
		*	@revised 22JUN2012-1300
		**/
		// get MB c-o-s pointer
		//die('17JUL2012-1904 temporarily suspended');
		log_message('DEBUG','eventctrl/postManageBookingCleanup triggered.');
		/* 
			We will use this array to determine if this function's capabilities should be accessed.
			This is because refresh and close is not distinguished back in client-side so we'll judge
			na lang here because of the session activity stage. :'(
		*/ 
		$exempt_stage = Array();
		if( in_array( $this->clientsidedata_model->getSessionActivityStage(), $exempt_stage ) or
			$this->functionaccess->isChangingPaymentMode()
		)
		{
			log_message('DEBUG','eventctrl/postManageBookingCleanup exited due to exemption.');
			return FALSE;
		}
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );    // get MB c-o-s pointer
		$this->ndx_model->delete( $m_bookingInfo->CURRENT_UUID ); // delete B c-o-s
		$this->ndx_mb_model->delete( $guid_mb );				  // delete MB c-o-s
		//delete MB c-o-s pointer in CI's session cookie
		$this->clientsidedata_model->deleteManageBookingCookiesOnServerUUIDRef(); 
		$this->clientsidedata_model->deletePaymentModeExclusion();
		$this->clientsidedata_model->deleteBookingCookies();
		// how about if no seat notice?
		$no_seat_xml_file = $this->clientsidedata_model->getGuestNoSeatXMLFile();
		if( $no_seat_xml_file !== FALSE )
		{
			unlink( $no_seat_xml_file );
			$this->clientsidedata_model->deleteGuestNoSeatXMLFile();
		}
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		$this->postBookingCleanup( !$redirect );
	}//postManageBookingCleanup(..)
	
	function postBookingCleanup( $doNotRedirect = false )
	{
		/**
		*	@created 19FEB2012-1751
		*	@description Does everything that should be done after a user successfully books,
			like clearing cookies and cookie-on-server data
		**/
		//die('17JUL2012-1904 temporarily suspended');
		log_message('DEBUG','eventctrl/ postBookingCleanup triggered.');
		$redirectTo = $this->session->userdata( AUTH_THEN_REDIRECT );
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		
		/* <area id="postbookcleanup_still_cookie_based" > */{
		delete_cookie( MANAGE_BOOKING_NEW_SEAT_UUIDS );	
		delete_cookie( MANAGE_BOOKING_NEW_SEAT_MATRIX );
		delete_cookie( 'debug-PRE' );
		delete_cookie( 'debug-POST' );
		}
		// </area>
		$this->clientsidedata_model->deleteBookingCookiesOnServerUUIDRef();
		$this->clientsidedata_model->deleteBookingProgressIndicator();
		$this->clientsidedata_model->deletePurchaseTotalCharge();
		$this->clientsidedata_model->deleteMBViewDetailsNewBookingTag();
		$this->clientsidedata_model->deletePaymentChannel();
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		$this->ndx_model->delete( $guid );
		if( $doNotRedirect ) return true;
		if( $this->input->is_ajax_request() === FALSE ) {
			if( $redirectTo !== FALSE )
			{	// redirect to specified page
				$this->session->unset_userdata( AUTH_THEN_REDIRECT );
				redirect( $redirectTo );
			}else
				// else, to home
				redirect( '/' );
		}
	}//postBookingCleanup(..)
	
	function resume_booking()//!!!
	{
		/**
		*	@created 08JUN2012-1500
		*
		**/
		die('Feature coming later');
	}
} //class
?>
