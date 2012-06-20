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
	
*	At current, user needs to be logged in to be able to use the features of this controller.
**/
class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		
		include_once( APPPATH.'constants/_constants.inc');
		include_once( APPPATH.'constants/clientsidedata.inc');
		$this->load->helper('cookie');
				
		$this->load->model('login_model');
		$this->load->model('Academic_model');
		$this->load->model('Account_model');
		$this->load->model('Booking_model');
		$this->load->model('BrowserSniff_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('CoordinateSecurity_model');
		$this->load->model('email_model');
		$this->load->model('Event_model');
		$this->load->model('Guest_model');
		$this->load->model('MakeXML_model');
		$this->load->model('ndx_model');
		$this->load->model('ndx_mb_model');
		$this->load->model('Payment_model');
		$this->load->model('Permission_model');
		$this->load->model('Seat_model');		
		$this->load->model('Slot_model');		
		$this->load->model('TicketClass_model');
		$this->load->model('TransactionList_model');
		$this->load->model('UsefulFunctions_model');
		$this->load->library('email');		
		$this->load->library('encrypt');		
		$this->load->library('bookingmaintenance');		
		$this->load->library('functionaccess');		
		$this->load->library('seatmaintenance');		
		
		if( !$this->login_model->isUser_LoggedIn() )
		{				
			$this->clientsidedata_model->setRedirectionURLAfterAuth( $_SERVER[ 'REDIRECT_QUERY_STRING' ] );
			redirect('SessionCtrl/authenticationNeeded');
		}
		
	} //construct
	
	function index()
	{		
		redirect( 'EventCtrl/book' );		
	}//index
	
	private function assembleDataForManageBookingPending_ViewDetais(
		$paymentMode, $oldShowtimeID, $oldTicketClassGroupID, $oldTicketClassUniqueID
	)
	{	/** 
		*	@DEPRECATED 17JUN2012-1434
		*	@created 12MAR2012-1738 
		*	@description Stores details of the manage booking activity.
		*/
		$appendThisSessionActivityDataEntry  = PAYMENT_MODE.'='.$paymentMode.';'.OLD_SHOWTIME_ID.'='. $oldShowtimeID.';';
		$appendThisSessionActivityDataEntry .= OLD_SHOWTIME_TC_GROUP_ID.'='.$oldTicketClassGroupID.';'.OLD_SHOWTIME_TC_UNIQUE_ID.'=';
		$appendThisSessionActivityDataEntry .= $oldTicketClassUniqueID.';';		
		$this->clientsidedata_model->appendSessionActivityDataEntryLong( $appendThisSessionActivityDataEntry );	
	}// assembleDataForManageBookingPending_ViewDetais
		
		
	private function assembleRelevantDataOfNewSeats( $eventID, $newShowingTimeID, $terminateOnNone = true )
	{
		/*	@DEPRECATED 16JUN2012-2154
			@created 08MAR2012-1719
			@purpose Returns an Array representation of the new seats chosen for the guests.
		*/
		$newSeatsUUID   = $this->clientsidedata_model->getManageBookingNewSeatUUIDs();
		$newSeatsMatrix = $this->clientsidedata_model->getManageBookingNewSeatMatrix();
		if( $newSeatsUUID === false or $newSeatsMatrix === false )
		{
			if( $terminateOnNone )
			{
				echo "ERROR_At-assembleRelevantDataOfNewSeats<br/>No new seat found. Transaction is moot and academic.";
				die('');
			}else{
				return false;
			}
		}
		$newSeatsGuestUUID_tokenized   = explode('_', $newSeatsUUID);
		$newSeatsGuestMatrix_tokenized = explode('_', $newSeatsMatrix);
		$newSeatsInfoArray = Array();
		for( $x =0, $y = count($newSeatsGuestUUID_tokenized); $x<$y; $x++ )
		{
			$letsDivorce = explode('-', $newSeatsGuestMatrix_tokenized[$x] );
			if( is_array( $letsDivorce ) && count( $letsDivorce ) === 2 )
			{
				$newSeatsInfoArray[ $newSeatsGuestUUID_tokenized[$x] ] = Array(
					'Matrix_x'   => $letsDivorce[0],
					'Matrix_y'   => $letsDivorce[1],
					'visual_rep' => $this->Seat_model->getVisualRepresentation(
											$letsDivorce[0],
											$letsDivorce[1],
											$eventID,
											$newShowingTimeID
									)
				);
			}
		}
		return $newSeatsInfoArray;
	}//assembleRelevantDataOfNewSeats(..)
	
	private function assembleForWritingDataOfNewSeats( $seatInfoArray )
	{
		/**
			Created 11MAR2012-1810
			@purpose Parses the array of seats with the guest's UUID as key for writing
						into cookies, for use in next pages.
			@param $seatInfoArray Format			
					[ UUID ]
					  ['Matrix_x'] => x
					  ['Matrix_y'] => y			
		*/
		$guestUUIDs = "";
		$seatMatrix = "";
		foreach( $seatInfoArray as $key => $value )
		{
			$guestUUIDs .= $key.'_';
			$seatMatrix .= $value['Matrix_x'].'-'.$value['Matrix_y'].'_';
		}
		// remove the trailing underscores
		$guestUUIDs = substr( $guestUUIDs, 0, strlen( $guestUUIDs )-1 );
		$seatMatrix = substr( $seatMatrix, 0, strlen( $seatMatrix )-1 );
		
		$this->clientsidedata_model->setManageBookingNewSeatUUIDs( $guestUUIDs, 3600 );
		$this->clientsidedata_model->setManageBookingNewSeatMatrix( $seatMatrix, 3600 );
	}//assembleForWritingDataOfNewSeats(..)
	
	private function assembleUnavailableSeatTableForManageBooking( $guestObj )
	{
		/** 
		*	@created 11MAR2012-2350 Actually matagal na nga din, refactored lang
		*   @description When managing booking and some seats are NOT available, this outputs a table
						in HTML showing the relevant info.
		*	@remarks The class "center_purest" of element <table> is dependent on 'body_all.css'
		*	@lastreviewed 16JUN2012-1227
		**/
		$tableProper = '<table class="center_purest" >';
		foreach(  $guestObj as $eachSlot2 )
		{
			$tableProper .= '<tr>';
			$tableProper .= '<td style="width: 90%; ; overflow: auto;">';
			$tableProper .= $eachSlot2->Lname.", ".$eachSlot2->Fname." ".$eachSlot2->Mname;
			$tableProper .= '</td>'; 
			$tableProper .= '<td>';
			$tableProper .= $this->Seat_model->getVisualRepresentation(
				$eachSlot2->Seat_x,
				$eachSlot2->Seat_y,
				$eachSlot2->EventID,
				$newShowtimeID
			 );
			$tableProper .= '</td>';
			$tableProper .= '</tr>';
		}
		$tableProper .= '</table>';
		
		return $tableProper;
	}//assembleUnavailableSeatTableForManageBooking(..)
	
	private function prepPurchaseCookies( $purchases )
	{
		/*	ON HOLD 09JUN2012-1347
		*	Purchases section
		*   We need to specify the purchase ID number so as for booking Step 6 display
		*  ( getUnpaidPurchases will return nothing because such purchases will be marked as paid/pending payment )
		*  before the page is displayed
		*/				
		$purchaseCount = count( $purchases );		
		$purchases_str = "";
		
		if( $purchaseCount === 0 ) return true;
		// tokenize and set in cookie, the purchases to be displayed in the page. 
		/*
			String structure of unencrypted cookie 'purchases_identifiers':
				
				a = { YYYYYY };
				b = { YYYYYY | YYYYY;a };
				
				Separated by semicolons:
			    
				Ex1: a
				Ex2: abaaaaabaaaa
				
				Live: 921093;213214;1094242;
							
			Y..Y	 - Min length of 2, UniqueID of purchase
		*/
		foreach( $purchases as $singlePurchase  ) $purchases_str .= ( $singlePurchase->UniqueID.";" );
		$purchases_str = substr($purchases_str, 0, strlen($purchases_str)-1 );	//remove trailing colon

		return $purchases_str;
	}//prepPurchaseCookies(..)
	
	private function prepSeatVisualData( $sendSeatInfoToView  )
	{
		$seatInfo_str = "";
		foreach( $sendSeatInfoToView as  $uuid => $singleSeatAssignment ) $seatInfo_str .= ( $singleSeatAssignment ).".";
		$seatInfo_str = substr( $seatInfo_str, 0, strlen( $seatInfo_str ) - 1 );	// remove trailing dot
		return $seatInfo_str;
	}//prepSeatVisualData(..)

	private function getSeatRepresentationsOfGuests( $eventID, $showtimeID, $guest_arr,
		$ticketClassGroupID = NULL, $ticketClassUniqueID = NULL
	)
	{
		/**
		*	@created 03MAR2012-1147
		*	@description Gets seat representations of guests.
		*	@history 11MAR2012-1441 Added params $ticketClassGroupID, $ticketClassUniqueID		
		**/
		$seatDetailsOfGuest = Array();
		foreach( $guest_arr as $singleGuest )
		{
			$seatVisualRepStr = false;
			$seatMatrixRepObj = false;

			if( $ticketClassGroupID != NULL and $ticketClassUniqueID != NULL )
			{
				 $slotObj = $this->Slot_model->getSlotAssignedToUser_MoreFilter(
					 $eventID, 
					 $showtimeID,
					 $ticketClassGroupID, 
					 $ticketClassUniqueID,
					 $singleGuest->UUID 
				);
				$seatMatrixRepObj = Array(
					'Matrix_x' => (is_null ($slotObj->Seat_x) ) ? "" : $slotObj->Seat_x,
					'Matrix_y' => (is_null ($slotObj->Seat_y) ) ? "" : $slotObj->Seat_y
				);
			}else{
				$seatMatrixRepObj = $this->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );
			}
			if( $seatMatrixRepObj !== false ){	// there is seat assigned for this user
				if( is_null($slotObj->Seat_x) or  is_null($slotObj->Seat_y) )
				{
					 $seatVisualRepStr = "NONE";
				}else{
					$seatVisualRepStr = $this->Seat_model->getVisualRepresentation(
						$seatMatrixRepObj['Matrix_x'],
						$seatMatrixRepObj['Matrix_y'],
						$eventID,
						$showtimeID
					);
				}
			}
			$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(
				'matrix_x' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_x'] : ""  ),
				'matrix_y' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_y'] : ""  ),
				'visual_rep' =>  $seatVisualRepStr
			);
		}
		return $seatDetailsOfGuest;
	}//getSeatRepresentationsOfGuests(..)
	
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
			$oldSlot = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
				$eventID, 
				$oldShowtimeID, 
				$oldTicketClassGroupID, 
				$oldTicketClassUniqueID, 
				$eachGuest->UUID
			);
			// Now, assign a reserved slot of the newly selected ticket class to guest.
			$this->Slot_model->assignSlotToGuest(
				$eventID,
				$newShowtimeID,
				$slotUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
			);
			// if ticket class is not changed then we have to do automatic seat assignment
			// of seats from the current showtime to the new one whenever allowed.
			log_message("DEBUG","EventCtrl::immediatelyAssignSlotsAndSeats_MidManageBooking isTicketClassChanged: " . intval( $isTicketClassChanged ) );
			if( !$isTicketClassChanged )
			{
				$isSeatAssignedPreviously = !(  is_null($oldSlot->Seat_x) or  is_null($oldSlot->Seat_y) );
				if( $isSeatAssignedPreviously )
				{
					// check first if seats are available
					$seatCheckResult = $this->Seat_model->isSeatAvailable(
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$eventID,
						$newShowtimeID
					 );
					$isSeatAvailable = ( $seatCheckResult['boolean'] !== false );
				}else{
					$isSeatAvailable == FALSE;
				}
				if( $isSeatAvailable )
				{
					// Assign the same seat to the new slot as with the old slot
					$this->Guest_model->assignSeatToGuest( 
						$eachGuest->UUID, 
						$oldSlot->Seat_x,
						$oldSlot->Seat_y,
						$eventID,
						$newShowtimeID
					);			
					// mark the seat in the new showing time as assigned
					$this->Seat_model->markSeatAsPendingPayment( 
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
					$guest_seat_not_available[ $oldSlot->Assigned_To_User ] = $oldSlot;
				}
			}
			$seatInfoArray[ $eachGuest->UUID ] = Array(
				'Matrix_x' => ($isSeatAvailable) ? $oldSlot->Seat_x : "",
				'Matrix_y' => ($isSeatAvailable) ? $oldSlot->Seat_y : ""
			);
		}//foreach guest												
		$this->assembleForWritingDataOfNewSeats( $seatInfoArray );
		return $guest_seat_not_available;
	}//immediatelyAssignSlotsAndSeats_MidManageBooking(..)
	
	private function isBookingSeatsAvailableNewShowtime( $bookingNumber, $showtimeID )
	{
		/**
		*	@DEPRECATED-TO-BE 18JUN2012-2148
		*	@created 06MAR2012-1617. 
		*	@description Gets slots of a booking and check if the seats in the new showtime corresponding to the
				current booking are available.
		*	@returns Array:
				If all specified seats are available in the new showtime:
					 Array(
						'boolean' => true,
						'guest_no_seat' => <empty array>
					);
				If there are specified seats NOT available in the new showtime:
					 Array(
						'boolean' => false,
						'guest_no_seat' => array of MYSQL_OBJS of the `event_slot` for the guest
					);
				If there's some error in processing PHP will die.
		*	@called by EventCtrl/managebooking_changeshowingtime_process2
		**/
		$returnThis = Array(
			'boolean' => true,
			'guest_no_seat' => Array()
		);
		
		$guestSlots = $this->Slot_model->getSlotsUnderThisBooking( $bookingNumber );
		if( $guestSlots === false )
		{
			die('Critical error when checking for seat availability:<br/><br/>INVALID_BOOKING_NUMBER.');
		}
		foreach( $guestSlots as $eachSlot )
		{
			if( is_null($eachSlot->Seat_x) or is_null($eachSlot->Seat_y) ) continue;
			$seatCheckResult = $this->Seat_model->isSeatAvailable(
				$eachSlot->Seat_x,
				$eachSlot->Seat_y,
				$eachSlot->EventID,
				$showtimeID
			 );
			if( $seatCheckResult['boolean'] === false )
				if( $seatCheckResult['throwException'] === null )
				{
					$returnThis['guest_no_seat'][] = $eachSlot;
				}else{
					die('Critical error when checking for seat availability:<br/><br/>'.$seatCheckResult['throwException']);
				}
		}
		
		if( count($returnThis['guest_no_seat']) > 0 ){ $returnThis['boolean'] = false; }
		echo log_message('DEBUG','isBookingSeatsAvailableNewShowtime(..) : ' .  count($returnThis['guest_no_seat']) );
		return $returnThis;
	}//isBookingSeatsAvailableNewShowtime(..)
		
	
	private function setBookingCookiesOuter( $eventID, $showtimeID, $slots, 
		$bookingNumber = 'XXXXX')	
	{
		/*
			@DEPRECATED 12JUN2012-1934
			@created 22FEB2012-2248			
			@purpose This handles setting of cookies that are needed to display info on the pages.
			@params {} - Obviously.
		*/
		  $showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
		  if( $showtimeObj === false )		// counter check against spoofing
		  {
			 // no showing time exists
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time not found. Are you hacking the app?  :-D "; //4031
			 $this->load->view( 'errorNotice', $data );
			 return false;
		  }	  	 
		  $eventInfo = $this->Event_model->getEventInfo( $eventID );      // get major info of this event
		  $ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID ); 
		  if( $ticketClasses === false )  // counter check against spoofing
		  {
			 // no ticket classes exist
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time marked as for sale but there isn't any ticket class yet."; //5051
			 $this->load->view( 'errorNotice', $data );  
			return false;		 
		  }
		  //Cookie part		
		  $cookie_values = Array(
			 $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $eventInfo->Name, 
			 $showtimeObj->StartDate, $showtimeObj->StartTime, $showtimeObj->EndDate, $showtimeObj->EndTime,
			 $slots, $eventInfo->Location,  $bookingNumber, '-1', '-1', '-1'
		  );			 
		  $this->clientsidedata_model->setBookingCookies( $cookie_values );		  		  
	}//setBookingCookiesOuter(..)
	
	private function setBookingCookiesOuterServer( $eventID, $showtimeID, $slots, $bookingNumber = 'XXXXX')
	{
		/*
			@created 09JUN2012-1326 			
			@purpose Deprecates setBookingCookiesOuter(). This will now be used from now on.
				Arose due to cookie setting bug when introducing change payment mode feature.
			@params {} - Obviously.
		*/
		  $showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
		  if( $showtimeObj === false )		// counter check against spoofing
		  {
			 // no showing time exists
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time not found. Are you hacking the app?  :-D "; //4031
			 $this->load->view( 'errorNotice', $data );
			 return false;
		  }	  	 
		  $eventInfo = $this->Event_model->getEventInfo( $eventID );      // get major info of this event
		  $ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID ); 
		  if( $ticketClasses === false )  // counter check against spoofing
		  {
			 // no ticket classes exist
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/></br>Showing time marked as for sale but there isn't any ticket class yet."; //5051
			 $this->load->view( 'errorNotice', $data );  
			return false;		 
		  }
		  $guid = $this->UsefulFunctions_model->guid();
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
		  log_message( 'DEBUG', 'precreate server outer' );		  
		  return $this->ndx_model->create( $cookie_values );		  		  
		  log_message( 'DEBUG', 'postrecreate server outer' );
	}//setBookingCookiesOuterServer(..)
	
	private function setManageBookingCookiesOuterServer( $entries )
	{
		if( $this->ndx_mb_model->create( $entries ) )
		{
			$this->clientsidedata_model->setManageBookingCookiesOnServerUUIDRef( $entries[0] );
			return TRUE;
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
		redirect('EventCtrl/book_forward');
	}//book(..)
	
	function book_forward()
	{
		/*
			@created 29DEC2011-2048			
			@description Hotspot for refactoring (i.e., in Event_model, create a single function
			that gets all info - via the use of SQL joins. :D )
		*/
		$configuredEventsInfo = array();
		
		// get all events first		
		$allEvents = $this->Event_model->getAllEvents();		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->Event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->Event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view			
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		$this->clientsidedata_model->setSessionActivity( BOOK, STAGE_BOOK_1_FORWARD );
		$this->load->view( "book/bookStep1", $data );		
	}//book_forward()
	
	function preclean()
	{
		/*
			@created 22APR2012-1454
			@purpose The defaulted bookings clean-up tool will be run on each booking got from the DB. If current
				user is an admin, all bookings (whether belonging to him or not are got), if he is not, then
				only those belonging to him are got.
		*/
		$userAccountNum = $this->clientsidedata_model->getAccountNum();
		$bookings = false;
		$redirect_to = $this->clientsidedata_model->getRedirectionURLAfterAuth();
		if( $this->Permission_model->isAdministrator( $userAccountNum ) ){
			$bookings = $this->Booking_model->getAllBookings( false, false );
		}else{
			$bookings = $this->Booking_model->getAllBookings( $userAccountNum, false );
		}
			
		if( $bookings !== false )
		{
		  foreach( $bookings as $singleBooking )
		  {
			/*
			  This checks if there are bookings marked as PENDING-PAYMENT' and yet
			  not able to pay on the deadline - thus forfeited now.
		    */
			$this->bookingmaintenance->cleanDefaultedBookings( $singleBooking->EventID, $singleBooking->ShowingTimeUniqueID ); 
		  }
		}
		if( $redirect_to === FALSE )			
			redirect('/');
		else
			$this->clientsidedata_model->deleteRedirectionURLAfterAuth();
			redirect( $redirect_to );
	}//preclean
	
   function book_step2( $bookingNumber = false, $ticketClassSelectionEssentials = null )
   {
	  /*
		@created 30DEC2011-1855
				
		@history 04MAR2012-1441 Added param $bookingNumber
		@history 08MAR2012-0030 Added param $ticketClassSelectionEssentials 
		
		@purpose Cleans defaulted bookings and/or slots if any, and reserves slots (all classes) on the event
			being booked.
				
		* Parameters are entertained only when session activity is MANAGE_BOOKING
		@param $bookingNumber 					Obviously
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
	  if( $isActivityManageBooking ) $this->ndx_mb_model->updateNewUUID( $guid_mb, $guid );
	  /*
		 This checks if there are bookings marked as PENDING-PAYMENT' and yet
		 not able to pay on the deadline - thus forfeited now.
	  */
	  $this->bookingmaintenance->cleanDefaultedBookings( $eventID, $showtimeID ); 
	  
	  // now ticket classes proper
	  $showtimeObj   = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
	  $ticketClasses = $this->TicketClass_model->getTicketClassesOrderByPrice( $eventID, $showtimeObj->Ticket_Class_GroupID );
	  
	  /*
		Check if there are event_slots (i.e., records in `event_slot` ) that the status
		is 'BEING_BOOKED' but lapsed already based on the ticket class' holding time.
	  */	  
	  $this->bookingmaintenance->cleanDefaultedSlots( $eventID, $showtimeID, $ticketClasses );
	  
	  $grandserialized_slots_uuid_str = "";	 
      foreach( $ticketClasses as $singleClass )
      {            
         $serializedClass_Slot_UUID = "";
                     
         $eachClassSlots = $this->Slot_model->getSlotsForBooking( $slots, $eventID, $showtimeID, $showtimeObj->Ticket_Class_GroupID, $singleClass->UniqueID );
         if( $eachClassSlots === false ){
			if( $isActivityManageBooking )
			{				
				$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );				
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
		$this->ndx_mb_model->updateGoShowtime( $guid_mb, ( $showtimeID === intval( $currentBookingInfo->SHOWTIME_ID) ) ? MB_STAGESTAT_PASSED : MB_STAGESTAT_CHANGED );
	  }
	  $this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB2_SELECT_TICKETCLASS_FW : STAGE_BOOK_2_FORWARD );		// our activity tracker	
	  $this->clientsidedata_model->setBookingProgressIndicator( 2 );
      redirect( 'EventCtrl/book_step2_forward' );
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
		
		$showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 	// counter check against spoofing
		if( $showtimeObj === false )
		{						 
			 $this->load->view( 'errorNotice', $this->bookingmaintenance->assembleShowtime404() );
			 return false;
		}
									
		/* <area id="bstep2_fw_eventinfoleft_info_get" > */  {
			if( $isActivityManageBooking ){
			$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
				$eventID, 
				$currentBookingInfo->TICKET_CLASS_GROUP_ID,
				$currentBookingInfo->TICKET_CLASS_UNIQUE_ID
			);
			$data['existingPayments'] = $this->Payment_model->getSumTotalOfPaid( @$currentBookingInfo->BOOKING_NUMBER , NULL );
			}
		}
		// </area>
		$data['bookingObj'] = $this->Booking_model->getBookingDetails(  @$bookingInfo->BOOKING_NUMBER );
		$data['bookingInfo'] = $bookingInfo;
		$data['ticketClasses'] = $this->TicketClass_model->getTicketClasses( $eventID, $showtimeObj->Ticket_Class_GroupID );
		$data['eventInfo'] 	   = $this->Event_model->getEventInfo( $eventID );
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
		$selectedTicketClass = $this->TicketClass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		$allOtherClasses     = $this->TicketClass_model->getTicketClassesExceptThisUniqueID( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		if( $selectedTicketClass === false )
		{
			 $this->cancelBookingProcess();
			 $this->load->view( 'errorNotice', $this->bookingmaintenance->assembleTicketClassOnDB404() );
			 return false;
		}
		$chosen_class_slot_UUIDs_str = $this->UsefulFunctions_model->getValueOfWIN5_Data( $ticketClassUniqueID, $bookingInfo->SLOTS_UUID );
		$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( $ticketClassUniqueID, $bookingInfo->SLOTS_UUID );
		$this->ndx_model->updateSlotsUUID( $guid, $chosen_class_slot_UUIDs_str );
		
		//$this->Slot_model->freeSlotsBelongingToClasses( $allOtherClasses );		// since we now don't care about these, free so.
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
			redirect( 'EventCtrl/managebooking_changeshowingtime_process2' );
		}else{
			redirect( 'EventCtrl/book_step3_forward' );
		}
	}//book_step3()
			
	function book_step3_forward(){
		$guid;
		$bookingInfo;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		if( !$this->functionaccess->preBookStep3FWCheck( $bookingInfo, STAGE_BOOK_3_FORWARD ) ) return FALSE;
		$data[ 'bookingInfo' ] = $bookingInfo;
		$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
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
		$ticketClassObj       = $this->TicketClass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUID );
		
		$bookingNumber          = $this->Booking_model->generateBookingNumber();
		$bookingPaymentDeadline = $this->Event_model->getShowingTimePaymentDeadline( $eventID, $showtimeID );
		
		// create booking "upper" details
		$this->Booking_model->createBookingDetails( 
			$bookingNumber,
			$eventID,
			$showtimeID,
			$ticketClassGroupID,
			$ticketClassUID,
			$this->clientsidedata_model->getAccountNum()
		);
		// now, create entries for the charges.
		$this->Payment_model->createPurchase( 
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
			
			$this->Guest_model->insertGuestDetails(
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
		$data['guests'] = $this->Guest_model->getGuestDetails( $bookingNumber );
		$x = 0;
		foreach( $data['guests'] as $eachGuest )
		{
			$this->Slot_model->assignSlotToGuest(
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
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD );		// our activity tracker
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
			
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_CLASS_1_FORWARD );		// our activity tracker
			redirect( 'AcademicCtrl/associateClassToBooking' );
		}else{
			redirect( 'EventCtrl/book_step4_forward' );
		}
	}//book_step4
			
	function book_step4_forward(){
		$bookingInfo;				// holds MYSQL_OBJ cookie-on-server for the changes in booking
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
		$data['guests'] 	  = $this->Guest_model->getGuestDetails( $bookingNumber );
		if( $isActivityManageBooking )
		{
			$guid_mb       = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
			$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
			$isShowtimeChanged 	  = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
			$isTicketClassChanged = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
		
			$data['guestSeatDetails'] = $this->seatmaintenance->getExistingSeatData_ForManageBooking(
				$data['guests'],
				$eventID,
				$showtimeID,
				$isTicketClassChanged
			);
			$data['existingPayments'] = $this->Payment_model->getSumTotalOfPaid( $bookingNumber , NULL );
		}
		$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
				$eventID, 
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID
			);
		$data['isTicketClassChanged'] = $isTicketClassChanged;
		$data[ 'bookingInfo' ] = $bookingInfo;
		$data[ 'isActivityManageBooking' ] = $isActivityManageBooking;
		$this->load->view( 'book/bookStep4', $data);
	}//book_step4_forward
	
	function book_step5()
	{
		/*
			Created 13FEB2012-2334
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
		/*
			Format of $seat_assignments
			"uuid" => { guests' uuid }
			"x"    => the submitted seat data for x
			"y"    => the submitted seat data for y
			"old_st" => Array(
					// Seat assignments for the old showtime, got from DB 
					"x"
					"y"
				);
			"new_st" => Array(
					// Seat assignments that were *automatically* assigned to the new showtime, if any, e, got from DB
					"x"
					"y"
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
		$guestObj = $this->Guest_model->getGuestDetails( $bookingNumber );
		if( $isActivityManageBooking ) $unpaidPurchasesArray = $this->Payment_model->getUnpaidPurchases( $bookingNumber );
		$this->clientsidedata_model->updateSessionActivityStage( 
			( $isActivityManageBooking ) ? STAGE_MB3_SELECT_SEAT_PR : STAGE_BOOK_5_PROCESS
		);
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
		if( $this->Event_model->isSeatSelectionRequired( $eventID, $showtimeID ) )
		{			
			foreach( $seat_assignments as $key => $single_data  )
			{				
				if( $single_data[ "x" ] == SEAT_COORD404 or $single_data[ "y" ] == SEAT_COORD404 )
				{	
					// reset our activity tracker so they can submit to this again
					$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
					echo $this->MakeXML_model->XMLize_AJAX_Response(						
							"error", 
							"error", 
							"SEAT_REQUIRED",
							0, 
							"Seat selection is required for all guests.",
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
						$seat_assignments[ $key ][ 'obj' ] =  $this->Seat_model->getSingleActualSeatData(
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
						echo $this->MakeXML_model->XMLize_AJAX_Response(
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
						echo $this->MakeXML_model->XMLize_AJAX_Response(
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
		
		/* <area id="book_step5_pr_seat_occupy_check" > */ {
		$seat_occupy_check = $this->seatmaintenance->areSeatsOccupied( $seat_assignments, $eventID, $showtimeID );
		if( $seat_occupy_check[0] === TRUE )
		{
			// reset our activity tracker so they can submit to this again
			$this->clientsidedata_model->updateSessionActivityStage( ($isActivityManageBooking) ? STAGE_MB3_SELECT_SEAT_FW : STAGE_BOOK_4_FORWARD );
			echo $this->MakeXML_model->XMLize_AJAX_Response(					
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
		
		/* <area id="book_step5_pr_get_old_seat_ass" > */{
		if( $isActivityManageBooking )
		{
			$currentBookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
			$bookingInfo = $this->ndx_model->get( $m_bookingInfo->NEW_UUID );
			for( $x = 0; $x < $slots; $x++ )
			{
				$slot_old_st = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$currentBookingInfo->EVENT_ID,
					$currentBookingInfo->SHOWTIME_ID,
					$currentBookingInfo->TICKET_CLASS_GROUP_ID,
					$currentBookingInfo->TICKET_CLASS_UNIQUE_ID,
					$seat_assignments[ $x ][ "uuid" ]
				);
				$slot_new_st = $this->Slot_model->getSlotAssignedToUser_MoreFilter(
					$bookingInfo->EVENT_ID,
					$bookingInfo->SHOWTIME_ID,
					$bookingInfo->TICKET_CLASS_GROUP_ID,
					$bookingInfo->TICKET_CLASS_UNIQUE_ID,
					$seat_assignments[ $x ][ "uuid" ]
				);
				if( is_null($slot_old_st->Seat_x) or is_null($slot_old_st->Seat_y) )
				{
					$seat_assignments[ $x ][ "old_st" ]= FALSE;
				}else{
					$seat_assignments[ $x ][ "old_st" ][ "x" ] = $slot_old_st->Seat_x;
					$seat_assignments[ $x ][ "old_st" ][ "y" ] = $slot_old_st->Seat_y;
				}
				if( is_null($slot_new_st->Seat_x) or is_null($slot_new_st->Seat_y) )
				{
					$seat_assignments[ $x ][ "new_st" ]= FALSE;
				}else{
					$seat_assignments[ $x ][ "new_st" ][ "x" ] = $slot_new_st->Seat_x;
					$seat_assignments[ $x ][ "new_st" ][ "y" ] = $slot_new_st->Seat_y;
				}
			}//for
		}//$isActivityManageBooking
		}
		// </area>
		
		/*<area id="bookstep5_pr_seat_finally_assign_db" > */ {
		/* 
			For each seat submitted (chosen by the user), get its visual representation
			and mark it as assigned
		*/
		for( $x = 0; $x < $slots; $x++ )
		{	
			if( !(  $seat_assignments[ $x ][ "x" ] !== SEAT_COORD404 and  $seat_assignments[ $x ][ "y" ] !== SEAT_COORD404 ) ) continue;
			// seat coordinate is not equal to 404 indicator so, continue checking
			$newSeatAction = 1;			// default action on what to do with the (new) seats submitted
			$visualRep = $this->Seat_model->getVisualRepresentation(
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
						if( $userHasOldSeat ) $this->Seat_model->markSeatAsAvailable(	//old seat
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
						if( $userHasAutoAssignedSeat ) $this->Seat_model->markSeatAsAvailable(	//auto seat
							$eventID,
							$showtimeID,
							$seat_assignments[ $x ][ "new_st" ][ "x" ], 
							$seat_assignments[ $x ][ "new_st" ][ "y" ]
						);
						$newSeatAction = 2;
					}
				}
			}
			if( $newSeatAction === 1 )
			{
				$this->Seat_model->markSeatAsAssigned(
					$eventID,
					$showtimeID,
					$seat_assignments[ $x ][ 'x' ],
					$seat_assignments[ $x ][ 'y' ]
				);
			}else{
				$this->Seat_model->markSeatAsPendingPayment(
					$eventID, 
					$showtimeID,
					$seat_assignments[ $x ][ 'x' ],
					$seat_assignments[ $x ][ 'y' ],
					$unpaidPurchasesArray[0]->Deadline_Date." ".$unpaidPurchasesArray[0]->Deadline_Time
				);
			}
			$this->Guest_model->assignSeatToGuest( 
				$seat_assignments[ $x ][ 'uuid' ],
				$seat_assignments[ $x ][ 'x' ],
				$seat_assignments[ $x ][ 'y' ],
				$eventID,
				$showtimeID
			);
		}
		}
		// </area>
		
		/*<area id="book-step5-pr-pre-fw-prerequisite" made="07JUN2012-1755" > */ {
		//duplicates with area#pre-book-step5-fw-prerequisite
		$billingInfo  = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$purchases    = $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$totalCharges = $billingInfo[ AKEY_AMOUNT_DUE ];
		$purchaseCount = count( $purchases );		
		$this->ndx_model->updatePurchaseID( $guid, $this->prepPurchaseCookies( $purchases ) );
		$this->ndx_model->updateVisualSeatData( $guid, $this->prepSeatVisualData( $sendSeatInfoToView  ) );		
		}
		//</area>
		
		/* <area id="book-step5-pr-conclusion" > */ {
		$redirectURL = "EventCtrl/";
		if( $isActivityManageBooking ){
			if( $isComingFromTicketClass )
			{
				$redirectURL .= "managebooking_confirm";
			}else{
				$redirectURL .= "managebooking_changeseat_complete/".$processedSeats;
			}
		}else{
			$redirectURL .= ( ( $totalCharges === FREE_AMOUNT ) ? 'book_step6' : "book_step5_forward" );
		}
		if( $totalCharges > FREE_AMOUNT ) $this->clientsidedata_model->setBookingProgressIndicator( 5 );
		$this->clientsidedata_model->updateSessionActivityStage( ( $isActivityManageBooking ) ? STAGE_MB4_CONFIRM_PR : STAGE_BOOK_5_FORWARD );	
		echo $this->MakeXML_model->XMLize_AJAX_Response(	
			"ajax", 
			"success", 
			"PROCEED",
			0, 
			"Taking you to the next page, please wait..",
			base_url().$redirectURL
		);
		log_message("DEBUG", "From book_step5 redirecting to " . base_url().$redirectURL );
		return TRUE;
		}
		//<area>
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
		$data['guests'] 		 = $this->Guest_model->getGuestDetails( $bookingNumber );	
		$data['seatVisuals']     = $this->Seat_model->make_array_visualSeatData( $data['guests'] , $bookingInfo->VISUALSEAT_DATA );
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent( $eventID, $showtimeID, FALSE, $excludePaymentMode );
		$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
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
		// to be accessed in forward page
		$paymentChannel_obj      = $this->Payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );
		if( $paymentChannel_obj === FALSE )
		{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assemblePaymentChannel404() );
		}
		$clientUUIDs = explode( "_" ,$bookingInfo->SLOTS_UUID );	// serialize guest UUIDs
		$this->clientsidedata_model->setPaymentChannel( $paymentChannel );
		$this->Payment_model->setPaymentModeForPurchase( $bookingNumber, $paymentChannel, NULL );		
		$eventObj                = $this->Event_model->getEventInfo( $eventID );
		$data['guests']          = $this->Guest_model->getGuestDetails( $bookingNumber );
		$data['singleChannel']   = $paymentChannel_obj;
		}
		// </area>
		
		/* <area id="book_step6_pr_process_payment_etc" > */ {
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
			so we can continue here.
		*/
		if( !$this->bookingmaintenance->react_on_pay_and_confirm(
				$response_pandc,
				'EventCtrl/book_step5_forward',
				STAGE_BOOK_5_FORWARD
			 )
		){
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
		foreach( $data['guests'] as $singleGuest)
		{
			$msgBody = "";
			$emailResult = false;
			$this->email_model->initializeFromSales( TRUE );
			
			$this->email_model->from( 'DEFAULT', 'DEFAULT');
			$this->email_model->to( $singleGuest->Email ); 						

			$this->email_model->subject('Show Itinerary Receipt ' . $bookingNumber );
			$msgBody = 'Your booking is pending.';
			$msgBody .= 'Kang song dae guk.<br/>';
			$msgBody .= 'Kim jong il\r\n';
			$msgBody .= 'Kim jong un\n';
			$msgBody .= 'We are in the process of starting our email module so no more info provided on this mail. HAHAHA.';	
			$this->email_model->message( $msgBody );			
			$emailResult = $this->email_model->send();
			log_message('DEBUG', 'Email to guest '. $singleGuest->Email . ' : ' .intval( $emailResult ) );
			
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_FORWARD ); // our activity tracker
		$this->clientsidedata_model->setBookingProgressIndicator( 6 );
		}
		// </area>
		redirect( 'EventCtrl/book_step6_forward' );
	}//book_step6
	
	function book_step6_forward()
	{	
		/**
		*	@created 28FEB2012-1420
		*	@description Moved from book_step6, majority.
				Processes the HTML page to be outputted upon the conclusion of
				the Purchase or booking of a ticket/Posting of a reservation
		**/
		$paymentChannel;
		$paymentChannel_obj;
		$eventID;
		$showtimeID;
		$bookingNumber;
		$guid;
		$bookingInfo;
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		// access validity check				
		if( !$this->functionaccess->preBookStep6FWCheck( $bookingInfo, STAGE_BOOK_6_FORWARD ) ) return false;
		
		$bookingNumber           = $bookingInfo->BOOKING_NUMBER;
		$eventID                 = $bookingInfo->EVENT_ID;
		$showtimeID              = $bookingInfo->SHOWTIME_ID;
		$slots					 = $bookingInfo->SLOT_QUANTITY;
		$ticketClassUniqueID	 = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$billingInfo  			 = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$totalCharges 			 = $this->clientsidedata_model->getPurchaseTotalCharge();
		$paymentChannel 		 = ( $totalCharges === FREE_AMOUNT ) ? FACTORY_AUTOCONFIRMFREE_UNIQUEID : $this->clientsidedata_model->getPaymentChannel();
						
		$paymentChannel_obj    = $this->Payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );
		$data['singleChannel'] = $paymentChannel_obj;
		$data['guests']        = $this->Guest_model->getGuestDetails( $bookingNumber );
		$data['purchases']     = ($billingInfo[ AKEY_PAID_PURCHASES_ARRAY ] === FALSE ) ? $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ] : $billingInfo[ AKEY_PAID_PURCHASES_ARRAY ];
		$data['pddate']        = $data['purchases'][0]->Deadline_Date;
		$data['pdtime']        = $data['purchases'][0]->Deadline_Time;
		$data['seatVisuals']   = $this->Seat_model->make_array_visualSeatData( $data['guests'], $bookingInfo->VISUALSEAT_DATA );
		$data['bookingInfo']   = $bookingInfo;
		$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
				$eventID, 
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$ticketClassUniqueID
		);
		
		if( $paymentChannel_obj->Type == "COD" )
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
				echo '<a href="'.base_url().'EventCtrl/book_step5_forward">Go back to Payment modes</a>';
			}
		}else
		if( $paymentChannel_obj->Type == "FREE" )
		{			
			$this->load->view( 'confirmReservation/confirmReservation02-free', $data );			
		}else{
			echo "PAYMENT_MODE ERROR: Caanot determine payment mode type."; // EC 5111			
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
		if( !$this->Booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{
			echo "ERROR_NO-PERMISSION_This booking is not under you."; // EC 4102
			return false;
		}
		$argumentArray = Array( 'bool' => true, 'Status2' => "FOR-DELETION" );		
		$this->bookingmaintenance->deleteBookingTotally_andCleanup( $bookingNumber, $argumentArray );
		$this->clientsidedata_model->deleteBookingCookies();
		if( !$this->input->is_ajax_request() ){
			redirect('EventCtrl/manageBooking');
		}else{
			echo "OKAY_SUCCESS";
			return true;
		}
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
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		
		$sessionActivity    = $this->clientsidedata_model->getSessionActivity();
		$eventID            = $bookingInfo->EVENT_ID;
		$ticketClassGroupID = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$bookingNumber      = $bookingInfo->BOOKING_NUMBER;
		$ticketClassUniqueID = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		
		//access validity check
		//$this->functionaccess->preCancelBookingProcesss( $eventID, $ticketClassGroupID, STAGE_BOOK_2_PROCESS );	
		$bookingStage = $this->clientsidedata_model->getSessionActivityStage();
		if( $bookingStage < STAGE_BOOK_4_FORWARD )
		{
			/*
				At this stage,
				no other info has been written to the database, except that slots for all
				the ticket classes of  a showing time has been marked as 'BEING_BOOKED'.
				
				Get ticket classes since we have reserved X slots for each ticket classes of 
				the showing time concerned. Then pass to the free-er function. 
			*/
			$ticketClasses = $this->TicketClass_model->getTicketClasses( $eventID, $ticketClassGroupID );
			$this->Slot_model->freeSlotsBelongingToClasses( $ticketClasses );
			$param1 = ( $bookingStage < STAGE_BOOK_3_PROCESS ) ? $ticketClassUniqueID : FALSE;
			$this->bookingmaintenance->freeSlotsBelongingToClasses_NDX( $param1, $bookingInfo->SLOTS_UUID );
		}else{			
			/*  Review 23APR2012-0314 - Why is this here?
				if( $this->functionaccess->isActivityManageBooking() )
			*/ 
			$this->bookingmaintenance->deleteBookingTotally_andCleanup( $bookingNumber, NULL );
		}// end if( $bookingStage < STAGE_BOOK_4_FORWARD )
		$this->ndx_model->delete( $guid );
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		if( !$this->input->is_ajax_request() ) redirect('/');
		else		
			return true;
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
		$preCheck = $this->functionaccess->preBookCheckAJAXUnified( Array( $bNumber ), false, STAGE_CONFIRM_1_FORWARD, true );
		if( strpos( $preCheck, "ERROR" ) === 0 )
		{
			// This outputs errors like this is not accessible to non-event mgr users.. bla bla.
			$breakThem = explode('|', $preCheck );
			echo $this->MakeXML_model->XMLize_AJAX_Response( 
					"error", "error", "GENERIC_ERROR", 0, $breakThem[1], ""
			);
			return false;
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_CONFIRM_2_PROCESS );
		$bNumberExistent = $this->Booking_model->doesBookingNumberExist( $bNumber );
		if( $bNumberExistent )
		{
			$singleBooking = $this->Booking_model->getBookingDetails( $bNumber );
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
				if( $this->Booking_model->isBookingExpired( $bNumber, $singleState[0], $singleState[1]  ) )
				{
					echo $this->MakeXML_model->XMLize_AJAX_Response( 
						"error", "deadline lapsed", "BOOKING_DEADLINE_LAPSED", 0, "The deadline of payment/confirmation for the specified booking has passed and as such slots and seats are now forfeited.", ""  //1005
					);
					return false;
				}
			}
			$guestDetails   = $this->Guest_model->getGuestDetails( $bNumber );
			$bookingDetails = $this->Booking_model->getBookingDetails( $bNumber );
			$this->setBookingCookiesOuterServer(
				$bookingDetails->EventID,
				$bookingDetails->ShowingTimeUniqueID,
				count( $guestDetails ),
				$bNumber
			);
			// after the preceeding function call, the cookies-on-server UUID ref is now available	
			$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();			
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_CONFIRM_2_FORWARD );
			echo $this->MakeXML_model->XMLize_AJAX_Response( 
					"error", "proceed", "BOOKING_CONFIRM_CLEARED", 0, "The booking is cleared to undergo confirmation.", ""  //1006
			);
			return true;
		}else{
			echo $this->MakeXML_model->XMLize_AJAX_Response( 
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
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );		
		//access validity check
		if( ! $this->functionaccess->preConfirmStep2FWCheck( $bookingInfo, STAGE_CONFIRM_2_FORWARD ) ) return FALSE;
		// var assignments
		$bNumber			 = $bookingInfo->BOOKING_NUMBER;		
		$bookingDetails   	 = $this->Booking_model->getBookingDetails( $bNumber );		
		$billingInfoArray    = $this->bookingmaintenance->getBillingRelevantData( $bNumber );
		// to continue or not?
		if( $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ] === false ){
			$data['theMessage'] = "There are no pending payments for this booking/It has been confirmed already."; //1004
			$data['redirect'] = 0;
			$data['noButton'] = FALSE;
			$this->load->view( 'successNotice', $data );
			return true;
		}
		foreach( $billingInfoArray as $key => $value ) $data[ $key ] = $value;
		// for view
		$data[ 'bookingInfo' ]        = $bookingInfo;
		$data[ 'guests' ]             = $this->Guest_model->getGuestDetails( $bNumber );
		$data[ 'seatVisuals' ] 		  = $this->getSeatRepresentationsOfGuests( 
			$bookingDetails->EventID,
			$bookingDetails->ShowingTimeUniqueID,
			$data['guests'],
			$bookingDetails->TicketClassGroupID,
			$bookingDetails->TicketClassUniqueID
		);
		$data['singleChannel']  =  $this->Payment_model->getSinglePaymentChannel(
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
		$bNumber;
		$accountNum;
		
		if( !$this->input->is_ajax_request() ) redirect( '/' );
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );				
		$accountNum = $this->clientsidedata_model->getAccountNum();
		$isThisForManageBooking = $this->Booking_model->isBookingUpForChange( $bookingInfo->BOOKING_NUMBER );
		
		//access validity check		
		if(!$this->functionaccess->preConfirmStep3PRCheck( $accountNum, $bookingInfo, STAGE_CONFIRM_2_FORWARD ) ) return false;
						
		/* <area id="confirm_step3_nextgen_proper_payment_process" > */{
		$billingInfoArray    = $this->bookingmaintenance->getBillingRelevantData( $bookingInfo->BOOKING_NUMBER );
		$infoArray = Array(
			"eventID" => $bookingInfo->EVENT_ID,
			"showtimeID" => $bookingInfo->SHOWTIME_ID,
			"ticketClassGroupID" => $bookingInfo->TICKET_CLASS_GROUP_ID,
			"ticketClassUniqueID" => $bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		if( $isThisForManageBooking )
		{
			$infoArray[ "transactionID" ] = $this->UsefulFunctions_model->getValueOfWIN5_Data( 
				'transaction', 
				$billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Comments
			);
		}
		$response_pandc = $this->bookingmaintenance->pay_and_confirm(
			$bookingInfo->BOOKING_NUMBER,
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
			echo "OK|Successfully processed and confirmed payment.";
			$this->clientsidedata_model->updateSessionActivityStage( -1 );
		}else{
			$sendback = "ERROR|". $response_pandc[ "code" ]."|". $response_pandc[ "message" ];
			if( isset( $response_pandc["misc"] ) ) foreach( $response_pandc["misc"] as $val ) $sendback .= ( $val."|" ); 
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
			$eventID = $this->clientsidedata_model->getEventID();
			// let's get the last uniqueID for the showing times of this event if ever
			$lastUniqueID = $this->Event_model->getLastShowingTimeUniqueID( $eventID );
			
			// now, with the data, create showings and insert them to the database			
			$this->Event_model->createShowings( $lastUniqueID, $eventID );
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
		$unconfiguredShowingTimes = $this->Event_model->getUnconfiguredShowingTimes( $eventID );
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
			$this->Event_model->setShowingTimeConfigStat( 
				$eventID,
				$key,
				"BEING_CONFIGURED"
			);
			
			//set slots of the showing times to the new one
			$this->Event_model->setShowingTimeSlots( 
				$eventID,
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
		if( $this->session->userdata( 'createEvent_step' ) !== 5 ){
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$data['beingConfiguredShowingTimes'] =  $this->Event_model->getBeingConfiguredShowingTimes( $eventID );
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
		$lastTicketClassGroupID = $this->TicketClass_model->getLastTicketClassGroupID( $eventID );
		$lastTicketClassGroupID++;
		// CODE MISSING: database checkpoint
		$this->db->trans_start();
		for( $x = 0; $x < $classesCount; $x++ )
		{			
			$databaseSuccess = $this->TicketClass_model->createTicketClass(
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
		$this->Event_model->setShowingTimeTicketClass( $eventID, $lastTicketClassGroupID );
		
		/*
			For each showing time being configured, create actual slots.
		*/
		foreach( $data['beingConfiguredShowingTimes'] as $eachShowingTime )
		{
			$thisST_ticketClasses = $this->TicketClass_model->getTicketClasses( 
				$eventID, 
				$lastTicketClassGroupID 
			);
			foreach( $thisST_ticketClasses as $eachTicketClass )
			{			
				$this->Slot_model->createSlots( 
					$eachTicketClass->Slots,
					$eventID,
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
		$eventID =  $this->clientsidedata_model->getEventID();
		
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $eventID );
		$this->db->trans_start();
		foreach( $beingConfiguredShowingTimes as $eachSession )
		{
			//update the seat map of the showing time
			$this->Event_model->setShowingTimeSeatMap( $this->input->post( 'seatmapUniqueID' ), $eventID, $eachSession->UniqueID );
			// duplicate seat pattern to the table containing actual seats
			$this->Seat_model->copyDefaultSeatsToActual( $this->input->post( 'seatmapUniqueID' ) );
			// update the eventID and UniqueID of the newly duplicated seats
			$this->Seat_model->updateNewlyCopiedSeats( $eventID,  $eachSession->UniqueID );
			// get the ticket classes of the events being configured
			$ticketClasses_obj = $this->TicketClass_model->getTicketClasses( $eventID,  $eachSession->Ticket_Class_GroupID );
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
		$eventID =  $this->clientsidedata_model->getEventID();
		//	Page access eligibility check		
		if( $this->session->userdata( 'createEvent_step' ) !== 6 )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$doesFreeTC_Exist = $this->TicketClass_model->isThereFreeTicketClass( $eventID );			
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannels( $doesFreeTC_Exist );
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $eventID );
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
		
		if( !$this->Event_model->setParticulars( $eventID ) )
		{
			echo "Create Step 7 Set Particulars Fail.";
			die();
		}
		$beingConfiguredShowingTimes = $this->Event_model->getBeingConfiguredShowingTimes( $eventID );		
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
						$eventID,
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."
					);
					$this->Payment_model->createPaymentChannelPermission( 
						$this->session->userdata( 'accountNum' ),
						$eventID,
						$singleBCST->UniqueID,
						$eachPosted,
						"Wala namang comment."					
					);
				}			
			}
		}//end foreach ($paymentChannels...
		
		$this->Event_model->stopShowingTimeConfiguration( $eventID );	// now mark these as 'CONFIGURED'		
		// get still unconfigured events
		$stillUnconfiguredEvents = $this->Event_model->getUnconfiguredShowingTimes(  $eventID  );
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
	
	function modify_select()
	{		
		/* @deprecated
			Created 16MAR2012-1520
		*/
		//START-PART copied from book(
		$configuredEventsInfo = array();
		
		// get all events first		
		$allEvents = $this->Event_model->getAllEvents();		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->Event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->Event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view			
		$data['configuredEventsInfo'] =  $configuredEventsInfo;			
		//END-PART copied from book(
		$this->clientsidedata_model->setSessionActivity( 'FINALIZE_BOOK', 1 );		
		$this->load->view( "modifyEvent/modifyEvent01", $data );
	}
			
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
		$allConfiguredShowingTimes = $this->Event_model->getConfiguredShowingTimes( $eventID , true);
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
		$xmlResult = $this->MakeXML_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
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
		$allConfiguredShowingTimes = $this->Event_model->getForCheckInShowingTimes( $eventID );
		if( $allConfiguredShowingTimes === false )
		{
			echo "ERROR_No showing time for checking-in yet.";
			return false;
		}
		$xmlResult = $this->MakeXML_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
		echo $xmlResult;
		return true;		
	}//getForCheckInShowingTimes(..)
	
	function manage()
	{
		/**
		*	@created 20DEC2011-1423
		*	@description Manages events/showing times.
		**/
		$data['events']   = $this->Event_model->getAllEvents();
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
		$okayBookings = $this->Booking_model->getAllBookings( $this->clientsidedata_model->getAccountNum() );
		$guestCount = Array();
		$ticketClassesName = Array();
		$data = Array( 'bookings' => false );
			
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
		$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, STAGE_MB0_HOME );
		$this->load->view( 'manageBooking/manageBooking01', $data );
	}//manageBooking(..)
	
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
		/*
			Compare stage number. If user clicked "Change seat" in manage booking home page, stage number
			now should be STAGE_MB0_PREP_FW, else the STAGE_MB3_SELECT_SEAT_1_PR that is set by other
			functions before they redirect here.
		*/
		if( $this->functionaccess->sessionActivity_x[0] !== STAGE_MB0_PREP_FW ){
			if( !$this->functionaccess->preManageBookingChangeSeatCheck( $bookingNumber, $m_bookingInfo, STAGE_MB3_SELECT_SEAT_1_PR ) ) return FALSE;
		}
		}
		// </area>
		
		$isComingFromTicketClass = $this->bookingmaintenance->isComingFromTicketClass( $m_bookingInfo );
		log_message( 'DEBUG', 'Reached  managebooking_changeseat(). Is coming from ticket class? '.intval($isComingFromTicketClass) );
		
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false )  die( 'Booking does not exist' );
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_FW ); 
		$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_PASSED );
		$this->clientsidedata_model->setBookingProgressIndicator( 4 );
		redirect( 'EventCtrl/book_step4_forward' );
	}//managebooking_changeseat
	
	function managebooking_changeseat_process()
	{
		/**
		*	@DEPRECATED 16JUN2012-1603
		**/
		die("16JUN2012-1603: This function is now deprecated in favor of book_step5.");
	}//managebooking_changeseat_process

	function managebooking_changeseat_complete( $processedSeats = 0 )
	{
		/**
		*	@created <i don't remember>
		*	@description Displays the result of "Change seat" functionality when clicked immediately 
				in Manage Booking home page.
		**/
		$data[ 'theMessage' ] = ($processedSeats == 0) ? "No changes to seats have been made." : "The seats have been changed.";
		$data[ 'redirect' ] = 2;
		$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
		$data[ 'defaultAction' ] = 'Manage Booking';
		$this->load->view( 'successNotice', $data );
		$this->postManageBookingCleanup();
		return false;
	}
	
	function mb_prep( $bb_sent = FALSE, $go_next_sent = FALSE )
	{
		/**
		*	@created 14JUN2012-1339
		*	@description This is the gateway to the features of manage booking.
				Here we set the cookie-on-server data we need then redirect the
				user to the appropriate pages.
		**/
		$guid_current;
		$guid_mb;
		$bookingObj;
		$bookingNumber = ( $bb_sent === FALSE ) ? $this->input->post( 'bookingNumber' ) : mysql_real_escape_string( $bb_sent );
		$redirectTo    = ( $go_next_sent === FALSE ) ? $this->input->post( 'next' ) : url_encode( $go_next_sent  );
		$accountNum;
		$slots;
		$go_showtime = 0;
		$go_ticketclass = 0;
		$go_seat = 0;
		$go_payment = 0;
		
		if( !$this->functionaccess->preManageBookCheckUnified( Array( ), STAGE_MB0_HOME , TRUE ) ) return FALSE;
		switch( $redirectTo )
		{
			case "managebooking_changeshowingtime" : $go_showtime = MB_STAGESTAT_SHOULDPASS; break;
		}
		$accountNum  = $this->clientsidedata_model->getAccountNum();
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		if( $bookingObj === FALSE )
		{
			echo "ERROR_BOOKING404";
			return false;
		}
		if( !$this->Booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{
			echo "ERROR_NO-PERMISSION_This booking is not under you."; // EC 4102
			return false;
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB0_PREP_PR );
		$guid_mb = $this->UsefulFunctions_model->guid();
		$slots = count( $this->Guest_model->getGuestDetails( $bookingNumber ) );
		$this->setBookingCookiesOuterServer( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID, $slots, $bookingNumber );
		$guid_current = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$this->ndx_model->updateTicketClassUniqueID( $guid_current, $bookingObj->TicketClassUniqueID );
		$this->setManageBookingCookiesOuterServer( Array( $guid_mb, $go_showtime, $go_ticketclass, $go_seat, $go_payment, $guid_current, NULL ) );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB0_PREP_FW );
		redirect( 'EventCtrl/' . $redirectTo );
	}// mb_prep(..)
	
	function managebooking_changeshowingtime()
	{
		/*
			Created 03MAR2012-1613
		*/
		
		$bookingObj;
		$bookingInfo;
		$m_bookingInfo;
		$guestCount;
		$eventObj;
		$configuredEventsInfo = Array();
		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		
		if( !$this->clientsidedata_model->getSessionActivityStage() === STAGE_MB1_SELECT_SHOWTIME_FW
		){
			if( !$this->functionaccess->preManageBookingChangeShowtimeCheck( STAGE_MB0_PREP_FW, $m_bookingInfo ) ) return false;
			$this->ndx_mb_model-> updateGoShowtime( $guid_mb, MB_STAGESTAT_PASSED );
		}
		$bookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingInfo->BOOKING_NUMBER );
		if( $bookingObj === false )
		{
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBooking404() );
			return false;
		}
		if( $this->Event_model->isShowtimeOnlyOne( $bookingObj->EventID ) )
		{
			// so user cannot access this feature because no other showing time to change.
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleShowtimeChangeDenied() );
			return false;
		}else{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB1_SELECT_SHOWTIME_PR );
			$data['configuredEventsInfo'] = Array( $this->Event_model->getEventInfo( $bookingObj->EventID ) );
			$data['existingShowtimeID']   = $bookingObj->ShowingTimeUniqueID;
			$data['guestCount']           = count( $this->Guest_model->getGuestDetails( $bookingInfo->BOOKING_NUMBER ) );
			$data['bookingNumber']        = $bookingInfo->BOOKING_NUMBER;
			$data['currentShowingTime']   = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB1_SELECT_SHOWTIME_FW );
			$this->clientsidedata_model->setBookingProgressIndicator( 1 );
			$this->load->view( 'manageBooking/manageBooking02_selectShowingTime.php', $data );
		}
	}//managebooking_changeshowingtime
			
	function managebooking_changeshowingtime_process()
	{
		/*
			Created 04MAR2012-1339
		*/
		$bookingNumber;
		$guid_mb;
		$m_bookingInfo;
		$eventID;
		$showtimeID;
		$slots;

		$guid_mb = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo = $this->ndx_mb_model->get( $guid_mb );
		$showtimeID = $this->input->post( 'showingTimes' );
		
		// ACCESS validity check
		if(	!$this->functionaccess->preManageBookingChangeShowtimePRCheck( $showtimeID, $m_bookingInfo , STAGE_MB1_SELECT_SHOWTIME_FW ) ) return false;	
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB2_SELECT_TICKETCLASS_1_PR );
		$bookingInfo = $this->ndx_model->get( $m_bookingInfo->CURRENT_UUID );
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
			$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, 0 );
			$this->ndx_mb_model->delete( $guid_mb );
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
		$bookingPaymentDeadline = $this->Event_model->getShowingTimePaymentDeadline( $eventID, $newShowtimeID );		
		}
		//</area>

		/*
			Now see if we have to have other charges.
		*/			
		// get ticket class objects first. They contain the prices.
		$oldTicketClassObj = $this->TicketClass_model->getSingleTicketClass(
			$eventID,
			$oldTicketClassGroupID,
			$oldTicketClassUniqueID
		);
		$newTicketClassObj = $this->TicketClass_model->getSingleTicketClass( 
			$eventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID
		);
		// assemble information for record in purchase table
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB3_SELECT_SEAT_1_PR );
		if( $isShowtimeChanged )
		{
			$oldShowtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $oldShowtimeID );
			$newShowtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $newShowtimeID );
			$oldShowtimeChargeDescriptor = $oldShowtimeObj->UniqueID." ( ".$oldShowtimeObj->StartDate." ".$oldShowtimeObj->StartTime." - ";
			if($oldShowtimeObj->EndDate !=  $oldShowtimeObj->StartDate ) $oldShowtimeChargeDescriptor .= $oldShowtimeObj->EndDate." "; 
			$oldShowtimeChargeDescriptor .= $oldShowtimeObj->EndTime.' ) ';
			
			$newShowtimeChargeDescriptor = $newShowtimeObj->UniqueID." ( ".$newShowtimeObj->StartDate." ".$newShowtimeObj->StartTime." - ";
			if($newShowtimeObj->EndDate !=  $newShowtimeObj->StartDate ) $newShowtimeChargeDescriptor .= $newShowtimeObj->EndDate." "; 
			$newShowtimeChargeDescriptor .= $newShowtimeObj->EndTime.' ) ';
		}
		// now, create entries for the charges.
			$guestObj = $this->Guest_model->getGuestDetails_UUID_AsKey( $bookingNumber );
			// Immediately assign the new available seats to the guests whose seats are still available.
			// returns an array of guests's slots whose seats are not available in the new showing time.
			$guest_no_seat = $this->immediatelyAssignSlotsAndSeats_MidManageBooking(
				$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID,
				$oldTicketClassUniqueID, $newShowtimeID, $newTicketClassUniqueID,
				$bookingInfo->SLOTS_UUID, $isTicketClassChanged, $bookingPaymentDeadline
			);
		if( $isTicketClassChanged )
		{
			if( $isShowtimeChanged )
			{
				$this->Payment_model->createPurchase(
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					$slots,
					0, 		// future rebooking fee
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
			}
			$this->Payment_model->createPurchase(
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
			redirect( 'EventCtrl/managebooking_changeseat' );
		}else{
			/*  $isTicketClassChanged is false.
			    Arriving here, $isShowtimeChanged is true 'coz we have filtered out earlier
				in this function if both are false. 
			*/
			$this->ndx_mb_model->updateGoSeat( $guid_mb, MB_STAGESTAT_CANPASS );
			// check if seats with the same coordinates in the new showing time are still available
			
			if( count($guest_no_seat) == 0 )
			{
				// create purchased item that signifies change of show time. This could also be a
				// future fee item.
				$this->Payment_model->createPurchase(	
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					$slots,
					0,
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
				// create new purchase item for the tickets in that new showing time.
				$this->Payment_model->createPurchase(
					$bookingNumber,
					"TICKET",
					"NONE",
					$slots,
					$slots * floatval( $newTicketClassObj->Price),
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);				
				$this->load->view( 'confirmationNotice', $this->bookingmaintenance->assembleManageBookingChangeSeatOpt() );
				return true;
			}else{
				/*
					Coming in here, some seats of the guests under the booking in question is
					not available in the target new showtime (maybe they are occupied now by others).
				*/
				/* <area id="mb_cst_pr2_assemble_someseat_not_msg" > */ {
				$changeSeatCaption = 2;
				$theMessage = "The seats of the following guests are not available in the new showing time";
				$theMessage .= " you have selected.<br/>";
				$theMessage2 = "<br/>Do you want to continue and select other seat(s) for these guests? Selecting No will cancel this process and rollback all changes.";			
				$tableProper = $this->assembleUnavailableSeatTableForManageBooking( $guest_no_seat );
				
				//	Assemble data for sending to view page.				
				$data['title'] = 'Oops, some technicality';
				$data['theMessage'] = $theMessage.$tableProper.$theMessage2;
				$data['yesURI'] = base_url().'EventCtrl/managebooking_changeseat';
				$data['noURI'] = base_url().'EventCtrl/manageBooking_cancel';
				$data['formInputs'] = Array( PIND_SEAT_SAME_TC_NO_MORE_USER_NOTIFIED => '1' );
				/*
					Some cookies needed for displaying output and monitoring progress.
				*/
				$this->clientsidedata_model->appendSessionActivityDataEntry( 'changeSeatCaption', $changeSeatCaption );
				$this->load->view( 'confirmationNotice', $data );
				return false;
				}
				// </area>
			}
		}// $isTicketClassChanged is false.
	}//managebooking_changeshowingtime_processs2(..)
		
	function managebooking_cancel()
	{
		die('Feature disabled for maintenance');
		$newTicketClassGroupID = $this->clientsidedata_model->getTicketClassGroupID();
		$eventID =  $this->clientsidedata_model->getEventID();
		$newShowingTimeID = $this->clientsidedata_model->getShowtimeID();
		$newTicketClassUniqueID = $this->clientsidedata_model->getTicketClassUniqueID();
		$isComingFromTicketClass = ( intval($this->clientsidedata_model->getSessionActivityDataEntry( 'ticketclass' ))===1 );
		$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
		$bookingNumber = $this->clientsidedata_model->getBookingNumber();
		
		if( $isComingFromTicketClass )
		{			
			$allOtherClasses;
			if($newTicketClassUniqueID === false ) break;
			$ticketClass_SlotsReserved = $this->TicketClass_model->getTicketClassesExceptThisUniqueID( $eventID, $newTicketClassGroupID, $newTicketClassUniqueID );
			if( intval($newTicketClassUniqueID) === -1 )	// ticket class is not changed
			{
				$ticketClass_SlotsReserved[] = $this->TicketClass_model->getSingleTicketClass( $eventID, $newTicketClassGroupID, $newTicketClassUniqueID );
			}			
			$this->Slot_model->freeSlotsBelongingToClasses( $ticketClass_SlotsReserved );	
		}
		$unpaidPurchasesArray = $this->Payment_model->getUnpaidPurchases( $bookingNumber );
		if( $unpaidPurchasesArray !== false ) 
			foreach( $unpaidPurchasesArray as $purchase ) $this->Payment_model->deleteSinglePurchase( $bookingNumber, $purchase->UniqueID );
		$this->postManageBookingCleanup();
		echo 'Cancellation okay.<br/><br/><a href="'.base_url().'EventCtrl/manageBooking">Back to Manage Booking</a>';
	}
	
	function managebooking_cancelchanges()
	{	
		die('Feature disabled for maintenance');
		$promptedIndicator = 'mb_cancelchanges_prompted';
		$bookingNumber = $this->input->post( 'bookingNumber' );
		
		if( $this->input->post( $promptedIndicator ) === false )
		{
			$data['title'] = 'Be careful on what you wish for ...';
			$data['theMessage'] = "Are you sure you want to cancel the changes you have made to this booking?";
			$data['theMessage'] .= "<br/><br/>Doing so will revert it to former state.";
			$data['yesURI'] = base_url().'EventCtrl/managebooking_cancelchanges';
			$data['noURI'] = base_url().'EventCtrl/manageBooking';
			$data['formInputs'] = Array( 
				 $promptedIndicator => '1',
				 'bookingNumber' => $bookingNumber
			);
			$this->load->view( 'confirmationNotice', $data );
			return false;
		}
		if( $this->bookingmaintenance->cancelPendingChanges( $bookingNumber, 2 ) )
		{		
			
			$data['theMessage'] = "The changes were cancelled and booking reverted to its original state.";			
			$data['redirectURI'] = base_url().'EventCtrl/manageBooking';			
			$data['defaultAction'] = 'Manage Booking';			
			$this->load->view( 'successNotice', $data );
		}else{
			$data['error'] = "CUSTOM";
			$data['theMessage'] = "Something went wrong while cancelling changes to this booking.";			
			$data['redirectURI'] = base_url().'EventCtrl/manageBooking';			
			$data['defaultAction'] = 'Manage Booking';
			$this->load->view( 'errorNotice', $data );		
		}
	}//managebooking_cancelchanges()
	
	function managebooking_manageclasses()
	{
		echo "Feature coming soon";
	}
	
	function managebooking_changepaymentmode( $bookingNumber_sent = false )
	{			
		$bookingNumber;
		$bookingObj;
		$accountNum;
		$guid;
		die('Feature disabled for maintenance');
		/*if(  $bookingNumber_sent === false )
		{	// booking number is not specified in the URL so invalid
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleManageBookingParamAbsent() );
			return false;
		}*/
		
		//$bookingNumber = mysql_real_escape_string( $bookingNumber_sent );
		$bookingNumber = $this->input->post( 'booking_number' );
		$bookingObj    = $this->Booking_model->getBookingDetails( $bookingNumber );
		$accountNum    = $this->clientsidedata_model->getAccountNum();
		$guestObj      = NULL;		
		$sendSeatInfoToView = Array();
		$bookInstanceEncryptionKey;
		
		if( $bookingObj === false )
		{			
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBooking404() );
			return false;
		}
		if( !$this->Booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{			
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assembleGenericBookingChangeDenied() );
			return false;
		}		
		$guestObj = $this->Guest_model->getGuestDetails( $bookingNumber );		
		foreach( $guestObj as $singleGuest )
		{
			$guestSlotObj = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
				$bookingObj->EventID, 
				$bookingObj->ShowingTimeUniqueID,
				$bookingObj->TicketClassGroupID, 
				$bookingObj->TicketClassUniqueID,
				$singleGuest->UUID
			);			
			$sendSeatInfoToView[ $singleGuest->UUID ] = $this->Seat_model->getVisualRepresentation( 
				$guestSlotObj->Seat_x, 
				$guestSlotObj->Seat_y,
				$bookingObj->EventID,
				$bookingObj->ShowingTimeUniqueID
			);
		}
		$sendSeatInfoToView = $this->bookingmaintenance->getSendSeatInfoToViewData( $bookingNumber );		
		$this->setBookingCookiesOuterServer( $bookingObj->EventID, 
			$bookingObj->ShowingTimeUniqueID,
			count($guestObj),
			$bookingObj->bookingNumber		
		);				
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		//<area id="pre-book-step5-fw-prerequisite" made="07JUN2012-1635" >
		//duplicates with book-step5-pre-fw-prerequisite
		$billingInfo  = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$purchases    = $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$totalCharges = $billingInfo[ AKEY_AMOUNT_DUE ];
		$purchaseCount = count( $purchases );		
		$this->ndx_model->updateVisualSeatData( $guid, $this->prepSeatVisualData( $sendSeatInfoToView  ) );
		$this->clientsidedata_model->setPurchaseCount( $purchaseCount );
		$this->clientsidedata_model->setPurchaseTotalCharge( $totalCharges );
		//</area>
		$this->clientsidedata_model->setPaymentModeExclusion( $purchases[0]->Payment_Channel_ID );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );		
		$this->clientsidedata_model->setBookingProgressIndicator( 5 );				
		//redirect('SessionCtrl/gagita');
		redirect( 'EventCtrl/book_step5_forward' );					
	}//managebooking_changepaymentmode(..)
	
	function mb_bridge()
	{	/**
		*	@created 18JUN2012-1719
		*	@description Bridges manage booking activities (i.e., when user is asked to choose a
				new seat or not. If not bridge immediately to manage booking confirm ).
		**/
		$stage = $this->clientsidedata_model->getSessionActivityStage();
		switch( $stage )
		{
			case 211:	$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB4_CONFIRM_FW );
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
		$bookingInfo        = NULL;
		$currentBookingInfo = NULL;
		$guid_mb;
		$m_bookingInfo;

		// access validity check
		$this->functionaccess->__reinit();
		$guid_mb            = $this->clientsidedata_model->getManageBookingCookiesOnServerUUIDRef();
		$m_bookingInfo      = $this->ndx_mb_model->get( $guid_mb );
		if( $this->functionaccess->sessionActivity_x[1] != STAGE_MB4_CONFIRM_FW ){
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
		$bookingObj    = $this->Booking_model->getBookingDetails( $bookingNumber );
		$bookingGuests = $this->Guest_model->getGuestDetails( $bookingNumber );
		$oldGuestSeatVisualRep = $this->getSeatRepresentationsOfGuests(
			 $eventID,
			 $oldShowtimeID,
			 $bookingGuests,
			 $oldTicketClassGroupID,
			 $oldTicketClassUniqueID
		);
		$newGuestSeatVisualRep = $this->getSeatRepresentationsOfGuests(
			 $eventID,
			 $newShowtimeID,
			 $bookingGuests,
			 $newTicketClassGroupID,
			 $newTicketClassUniqueID
		); 
		$eventObj          = $this->Event_model->getEventInfo( $eventID );
		$oldShowingTimeObj = $this->Event_model->getSingleShowingTime( $eventID, $oldShowtimeID );
		$newShowingTimeObj = $this->Event_model->getSingleShowingTime( $eventID, $newShowtimeID );
		$newSeatsInfoArray = $this->assembleRelevantDataOfNewSeats( $eventID, $newShowtimeID, false );
        // some boolean info
		$isShowtimeChanged 	  = $this->bookingmaintenance->isShowtimeChanged( $m_bookingInfo );
		$isTicketClassChanged = $this->bookingmaintenance->isTicketClassChanged( $m_bookingInfo );
		// Now calculate purchases
		$paymentChannels = NULL;		
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );	
		if( $billingInfoArray[ AKEY_AMOUNT_DUE ] > FREE_AMOUNT )
		{
			$paymentChannels = $this->Payment_model->getPaymentChannelsForEvent(
				$eventID, 
				$newShowtimeID,
				FALSE
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
		$data['oldTicketClassName'] = $this->TicketClass_model->getSingleTicketClassName(
			$eventID,
			$oldTicketClassGroupID, 
			$oldTicketClassUniqueID 
		);
		$data['newTicketClassName'] =$this->TicketClass_model->getSingleTicketClassName(
			$eventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID 
		);
		$data['isShowtimeChanged'] = $isShowtimeChanged;
		$data['isTicketClassTheSame'] = !$isTicketClassChanged;
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB4_CONFIRM_FW );
		$this->clientsidedata_model->setBookingProgressIndicator( 5 );
		$this->load->view( 'manageBooking/manageBookingConfirm', $data );
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
		$bookingObj 			= $this->Booking_model->getBookingDetails( $bookingNumber );
		$bookingGuests 			= $this->Guest_model->getGuestDetails( $bookingNumber );
		$oldGuestSeatVisualRep 	= $this->getSeatRepresentationsOfGuests( 
			$eventID, 
			$oldShowtimeID, 
			$bookingGuests,
			$oldTicketClassGroupID,
			$oldTicketClassUniqueID
		);
		$newSeatsInfoArray      = $this->getSeatRepresentationsOfGuests( 
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
		log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : User does not have to pay for this change? '.intval( $noPendingPayment  ) );		
		}
		//</area>
	
		/* <area id="mb_finalize_payment_processing_proper" > */{
		$transactionID = $this->TransactionList_model->createNewTransaction(
			$this->clientsidedata_model->getAccountNum(),
			'TICKET_CLASS_UPGRADE',
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
			$this->Payment_model->updatePurchaseComments(
				$bookingNumber,
				$singlePurchase->UniqueID,
				'transaction='.strval( $transactionID )
			);
		}
		$this->Booking_model->updateBookingDetails(
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
				'EventCtrl/managebooking_confirm',
				STAGE_MB4_CONFIRM_FW
			 )
		){
			log_message('DEBUG', 'MANAGE BOOKING FINALIZE: false returned by react_on_pay_and_confirm: ' . $response_pandc["code"] );
			return FALSE;
		}
		}
		//</area>
		log_message('DEBUG', 'MANAGE BOOKING FINALIZE: GOING TO FORWARD' );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB9_FINAL_FW );
		redirect( 'EventCtrl/managebooking_finalize_forward');
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
		if( !$this->functionaccess->preManageBookingFinalizeFW( $m_bookingInfo, STAGE_MB9_FINAL_FW ) ) return FALSE;
		}
		//</area>
		
		/* <area id="mb_fin_fw_essential_var_init" > */{
		$eventID          = $bookingInfo->EVENT_ID;
		$bookingNumber    = $bookingInfo->BOOKING_NUMBER;
		$newShowingTimeID = $bookingInfo->SHOWTIME_ID;
		$newTicketClassGroupID = $bookingInfo->TICKET_CLASS_GROUP_ID;
		$newTicketClassUniqueID = $bookingInfo->TICKET_CLASS_UNIQUE_ID;
		$oldShowingTimeID       = $this->clientsidedata_model->getSessionActivityDataEntry( 'oldShowtimeID' );
		$oldTicketClassGroupID 	= $this->clientsidedata_model->getSessionActivityDataEntry( 'oldTicketClassGroupID' );
		$oldTicketClassUniqueID = $this->clientsidedata_model->getSessionActivityDataEntry( 'oldTicketClassUniqueID' );
		}
		//</area>
		
		/* <area id="mb_fin_fw_essential_obj_assignments" > */{
		$bookingObj = $this->Booking_model->getBookingDetails( $eventID );
		$eventObj   = $this->Event_model->getEventInfo( $eventID  );
		$bookingGuests = $this->Guest_model->getGuestDetails( $bookingNumber );
		$oldShowingTimeObj = $this->Event_model->getSingleShowingTime( $eventID, $oldShowingTimeID );
		$newShowingTimeObj = $this->Event_model->getSingleShowingTime( $eventID, $newShowingTimeID );		
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$noPendingPayment = ( $billingInfoArray[ AKEY_AMOUNT_DUE ] === FREE_AMOUNT );
		$paymentMode = $noPendingPayment ? $billingInfoArray[ AKEY_PAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID 
						 :  $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID ;	
		$oldGuestSeatVisualRep = $this->getSeatRepresentationsOfGuests(
			 $eventID,
			 $newShowingTimeID,
			 $bookingGuests,
			 $newTicketClassGroupID,
			 $newTicketClassUniqueID
		);
		$newSeatsInfoArray = $this->assembleRelevantDataOfNewSeats( $eventID, $newShowingTimeID, false );
		}
		// </area>
		
		/* <area id="mb_fin_fw_load_data_for_view" > */{
		$data['bookingNumber']  = $bookingNumber;
		$data['oldSeatVisuals'] = $oldGuestSeatVisualRep;
		$data['newShowingTime'] = $newShowingTimeObj;
		$data['singleEvent']    = $eventObj;
		$data['guestCount']     = count( $bookingGuests );
		$data['guests']         = $bookingGuests;
		$data['currentShowingTime'] = $oldShowingTimeObj ;
		$data['newSeatData']        = $newSeatsInfoArray;
		$data['unpaidPurchasesArray'] = $billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$data['paidPurchasesArray']   = $billingInfoArray[ AKEY_PAID_PURCHASES_ARRAY ];
		$data['unpaidTotal']    = $billingInfoArray[ AKEY_UNPAID_TOTAL ];
		$data['paidTotal']      = $billingInfoArray[ AKEY_PAID_TOTAL ];
		$data['amountDue']      = $billingInfoArray[ AKEY_AMOUNT_DUE ];				
		$data['paymentDeadline'] = Array(
			'date' => (isset($data['unpaidPurchasesArray'][0]->Deadline_Date) ) ? $data['unpaidPurchasesArray'][0]->Deadline_Date : NULL,
			'time' => (isset($data['unpaidPurchasesArray'][0]->Deadline_Time) ) ? $data['unpaidPurchasesArray'][0]->Deadline_Time : NULL
		);	
		$data['singleChannel'] = $this->Payment_model->getSinglePaymentChannel(
			$eventID, 
			$newShowingTimeID, 
			$paymentMode
		);		
		$data['oldTicketClassName'] = $this->TicketClass_model->getSingleTicketClassName(
			0, 
			$oldTicketClassGroupID, 
			$oldTicketClassUniqueID 
		);
		$data['newTicketClassName'] = $this->TicketClass_model->getSingleTicketClassName(
			$bookingInfo->EVENT_ID, 
			$bookingInfo->TICKET_CLASS_GROUP_ID, 
			$bookingInfo->TICKET_CLASS_UNIQUE_ID
		);
		$data['bookingInfo'] = $bookingInfo;
		}
		// </area>
		
		/* <area id="mb_fin_fw_conclusion" > */{
		if( $noPendingPayment ){
			$this->load->view( 'successNotice', $this->bookingmaintenance->assembleBookingChangeOkay() );
			//$this->postManageBookingCleanup();
		}else{
			$this->load->view( 'manageBooking/manageBookingFinalize_COD', $data );
		}
		}
		//</area>
	}// managebooking_finalize_forward()
	
	function managebooking_pendingchange_viewdetails()
	{
		//die('Feature disabled for maintenance');
		$bookingNumber = $this->input->post( 'bookingNumber' );
		if( $bookingNumber === false ) die( 'Booking number needed. ');		
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB8_PENDINGCHANGEVD_PR );
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$rollbackData = $this->bookingmaintenance->getSlotRollbackDataOfPurchase( $billingInfoArray['unpaidPurchasesArray'][0] );
		if( $rollbackData !== FALSE )
		{
			$this->assembleDataForManageBookingPending_ViewDetais(
				$billingInfoArray['unpaidPurchasesArray'][0]->Payment_Channel_ID,
				$rollbackData[ 'oldShowtimeID' ],
				$rollbackData[ 'oldTicketClassGroupID' ],
				$rollbackData[ 'oldTicketClassUniqueID' ]
			);
		}
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		$guestObj   = $this->Guest_model->getGuestDetails( $bookingNumber );
		$this->setBookingCookiesOuterServer( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID, count( $guestObj ), $bookingNumber );
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$this->ndx_model->updateTicketClassUniqueID( $guid, $bookingObj->TicketClassUniqueID );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MB8_PENDINGCHANGEVD_FW );
		redirect('EventCtrl/managebooking_finalize_forward');
	}//managebooking_pendingchange_viewdetails()
	
	function managebooking_upgradeticketclass()
	{			
		/**
		*	@created 08MAR2012-0021
		*	@description Direct handler of upgrading ticket class when clicked in manage booking section.
				First attempt too on all lowercase in a function name.
		**/
		die('Feature disabled for maintenance');
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$sessionActivity =  $this->clientsidedata_model->getSessionActivity();		
		if( ( $sessionActivity[0] == MANAGE_BOOKING and $sessionActivity[1] === 0 ) === false or
			$bookingNumber === false 
		)
		{
			//die('gaga');
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 
		if( $bookingObj === false )
		{
			die("ERROR_No booking found for this booking number. Are you trying to hack the app?");
		}
		if(!$this->clientsidedata_model->changeSessionActivityDataEntry( 'ticketclass', 1 )){	// update the indicator if ticket class was passed
			$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, 0, "showingtime=0;ticketclass=0;seat=0;newcost=0;" );
			$this->clientsidedata_model->deleteBookingCookies();
			die('INTERNAL-SERVER-ERROR: Activity data cookie manipulated. Please start over by refreshing Manage Booking page.');
		}
		/*
			Check if current ticket class of booking is the highest already.
		*/
		$mostExpensiveTicketClassObj = $this->TicketClass_model->getMostExpensiveTicketClass(
			$bookingObj->EventID, $bookingObj->TicketClassGroupID
		);
		if( $mostExpensiveTicketClassObj !== false and
			intval($mostExpensiveTicketClassObj->UniqueID) === intval($bookingObj->TicketClassUniqueID)
		)
		{
			$data[ 'error' ] = "CUSTOM";
			$data[ 'theMessage' ] = "You are already booked in the top ticket class. There's no other class to upgrade anymore.";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
			$data[ 'defaultAction' ] = 'Manage Booking';
			$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, 0, "showingtime=0;ticketclass=0;seat=0;newcost=0;" );
			$this->clientsidedata_model->deleteBookingCookies();			
			$this->load->view( 'errorNotice', $data );
			return true;
		}
		/*
			Call function book_step2 instead of usual redirect. It's okay
			since we have set manage-booking specific data already.
		*/
		$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, STAGE_BOOK_2_FORWARD );
		$this->book_step2( $bookingNumber, Array( 
				$bookingObj->EventID,
				$bookingObj->ShowingTimeUniqueID,
				count( $this->Guest_model->getGuestDetails( $bookingNumber ) )
			) 
		);
	}//managebooking_upgradeticketclass
		
	function postManageBookingCleanup( $redirect = false )
	{		
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		$this->clientsidedata_model->deleteBookingCookies();
		$this->postBookingCleanup();
	}
	
	function postBookingCleanup( $doNotRedirect = false )
	{
		/**
		*	@created 19FEB2012-1751
		*	@description Does everything that should be done after a user successfully books,
			like clearing cookies
		**/		
		$redirectTo = $this->session->userdata( AUTH_THEN_REDIRECT );				
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		
		/* <area id="postbookcleanup_still_cookie_based" > */{
		delete_cookie( MANAGE_BOOKING_NEW_SEAT_UUIDS );				
		delete_cookie( MANAGE_BOOKING_NEW_SEAT_MATRIX );
		delete_cookie( 'debug-PRE' );
		delete_cookie( 'debug-POST' );	
		}
		// </area>		
		$this->clientsidedata_model->deleteBookingProgressIndicator();
		$this->clientsidedata_model->deletePurchaseTotalCharge();
		$this->clientsidedata_model->deletePaymentChannel( );
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
	}	
	
	function resume_booking()
	{
		/**
		*	@created 08JUN2012-1500
		*
		**/
		die('Feature coming later');
	}
} //class
?>
