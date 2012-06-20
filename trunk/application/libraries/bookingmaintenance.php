<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
*	Booking Maintenance Library
* 	Created late March 2012
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This contains utilities regarding bookings. Most of the contents were originally in EventCtrl controller.
*   There are some constants in use in this file. Such should be defined in the Controller where this library
*    is called.
**/

class BookingMaintenance{
	var $bookingNumberGlobal;
	var $CI;
	
	function __construct( $params = NULL )
    {		
		include_once( APPPATH.'constants/_constants.inc');
		$this->CI = & get_instance();
		$this->CI->load->model('Account_model');
		$this->CI->load->model('Booking_model');
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('Event_model');
		$this->CI->load->model('Guest_model');
		$this->CI->load->model('Payment_model');
		$this->CI->load->model('Seat_model');		
		$this->CI->load->model('Slot_model');		
		$this->CI->load->model('TicketClass_model');		
		$this->CI->load->model('TransactionList_model');		
		$this->CI->load->model('UsefulFunctions_model');
        $bookingNumberGlobal = (is_array($params) ) ?  $params[0] : FALSE;
    }
	
	private function properDetermineOnlinePaymentCode( $paymentModeObj )
	{	
		/**
		*	@description Determines which is the online payment processor. Extracts
				WIN5 DATA from the `internal_data` field of `payment_channel`
		*/
		if( $paymentModeObj->internal_data_type == 'WIN5' )
		{
			$processorValue = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( 'processor', $paymentModeObj->internal_data );
			if( $processorValue === false ) return false;
			else{
				switch( strtolower($processorValue) )
				{
					case "paypal"       : return PAYMODE_PAYPAL;
					case "2checkout"    : return PAYMODE_2CO;
					case "moneybookers" : return PAYMODE_MONYEBOOKERS;
					default				: return false;
				}
			}
		}else{			
			//if XML, later.
			return false;
		}
	}// properDetermineOnlinePaymentCode(..)
	
	function assembleBookingChangeOkay()
	{
		return Array(
			'defaultAction' => 'Manage Booking',
			'redirect' => 2,
			'redirectURI' => base_url().'EventCtrl/manageBooking',
			'theMessage' => "The changes to your booking has been successfully made."
		);
	}//assembleBookingChangeOkay()
	
	function assembleGenericBooking404()
	{
		$data = Array();
		$data['defaultAction'] = 'home';
		$data['redirect'] = 2;		
		$data['error'] = 'CUSTOM';		
		$data['redirectURI'] = base_url();		
		$data['theMessage'] = "Booking number is not found in the system.";
		return $data;
	}
		
	function assembleGenericBookingChangeDenied()
	{
		$data = Array();
		$data['defaultAction'] = 'home';
		$data['redirect'] = 2;		
		$data['error'] = 'CUSTOM';		
		$data['redirectURI'] = base_url();		
		$data['theMessage'] = "You have no right to make changes to this booking.";
		$this->load->view( 'errorNotice', $data );
		return $data;
	}
		
	function assembleManageBookingParamAbsent()
	{
		$data = Array();
		$data['defaultAction'] = 'Manage Booking';		
		$data['redirect'] = 2;		
		$data['error'] = 'NO_DATA';		
		$data['redirectURI'] = base_url().'EventCtrl/manageBooking';					
		return $data;
	}
	
	function assembleManageBookingChangeSeatOpt()
	{
		return Array(
			'theMessage' => "Would you still like to change your seats in this new showing time? This can be done later though.",
			'yesURI' => base_url().'EventCtrl/managebooking_changeseat',
			'noURI' => base_url().'EventCtrl/mb_bridge',
			'formInputs' => Array( PIND_CHANGE_SEAT_NOTIFIED => '1' )
		);
	}
	
	function assembleNoChangeInBooking()
	{
		return Array(
			'theMessage' => "No changes in ticket class or showing time detected. Your booking was not modified.",
			'redirect' => 2,
			'redirectURI' => base_url().'EventCtrl/manageBooking',
			'defaultAction' => 'Manage Booking'
		);
	}
	
	function assembleNoMoreSlotSameTicketClassNotification( $eventID, $showtimeID, $slots, $bookingNumber )
	{
		// Error code 2050
		$data = Array();
		$data['title'] = 'Oops, some technicality';
		$data['theMessage'] = 'There are no more slots available for the ticket class in your current booking.<br/><br/>';
		$data['theMessage'] .= 'Would you like to continue by selecting any other ticket class?';
		$data['theMessage'] .= ' Please note that price differences if any will be charged.';
		$data['yesURI'] = base_url().'EventCtrl/managebooking_changeshowingtime_process';
		$data['noURI'] = base_url().'EventCtrl/manageBooking_cancel';
		$data['formInputs'] = Array( 
			PIND_SLOT_SAME_TC_NO_MORE_USER_NOTIFIED => '1',
			'events' => $eventID,							
			'showingTimes' => $showtimeID,
			'slot' => $slots,
			'bookingNumber' => $bookingNumber
		);
		return $data;
	}//assembleNoMoreSlotSameTicketClassNotification(..)
	
	function assembleOnlinePaymentProcessorErrorData( $redirectURI )
	{
		// Error code 5105
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = 'Error getting payment processor data. Please choose another payment method.<br/><br/>';
		$data['theMessage'] .= 'Meanwhile, you are being redirected to the payment mode selection page in 5 seconds ...'; 
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().$redirectURI;		
		return $data;
	}
	
	function assembleOnlinePaymentProcessorNotSupported( $redirectURI, $otherMsgs = "" )
	{
		// Error code 5105
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = 'The online payment processor specified is not supported by this system. Contact the system administrator for this.<br/><br/>';
		$data['theMessage'] .= 'Meanwhile, you are being redirected to the payment mode selection page in 5 seconds ...'; 
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().$redirectURI;		
		return $data;
	}//assembleOnlinePaymentProcessorNotSupported
	
	function assembleErrorPaymentNotification( $redirectURI, $otherMsgs = "" )
	{
		// Error code 5104
		$data = Array();
		$data['error'] = 'CUSTOM';		
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = 'Something went wrong while processing your payment. Please choose another payment mode.<br/><br/>WARNING: Do not refresh the page.';		//5104
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirectURI'] = base_url().$redirectURI;
		return $data;
	}//assembleErrorPaymentNotification()
	
	function assembleErrorOnSlotConfirmation( $redirectURI, $otherMsgs = "" )
	{
		$data = Array();
		$data['error'] = 'CUSTOM';		
		$data['title'] = 'Slot Confirmation Error';
		$data['theMessage'] = 'Your payment was received but something went wrong while confirming your slots. Please choose another payment mode.<br/><br/>WARNING: Do not refresh the page.';		//5104
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirectURI'] = base_url().$redirectURI;
		return $data;
	}
	
	function assemblePaypalPaymentMissingCrucial( $otherMsgs = "" )
	{
		// Error code 5105
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'PayPal Processing Error';
		$data['theMessage'] = 'Some crucial internal data is missing in our records ( like \'merchant_email\' ). Please contact the person in charge of this payment method'; 
		$data['theMessage'] .= '<br/><br/>Meanwhile, please choose another payment method.<br/><br/>DO NOT REFRESH THE PAGE.';
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';		
		return $data;
	}// assemblePaypalPaymentMissingCrucial()
	
	function assemblePaypalPaymentUserCancelledNotification( $otherMsgs = "" )
	{
		// Error code 5105
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = 'You declined to use PayPal for payment.<br/><br/>Please choose another payment mode.'; 
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';
		return $data;
	}//assembleErrorPaymentNotification()
	
	function assemblePaymentChannel404()
	{	
		return Array(
			'error' => "CUSTOM",
			'theMessage' => "Payment mode not found! Are you hacking the app?<br/><br/>Please choose another payment mode.",
			'defaultAction' => 'Payment page',
			'redirect' => 2,
			'redirectURI' => base_url().'EventCtrl/book_step5_forward'
		);
	}//assemblePaymentChannel404()
	
	function assemblePaypalFishy( $otherMsgs = "" )
	{
		// Error code  5103
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = "We have received your payment through PayPal but it did not pass our standards. (i.e., it was held by PayPal";
		$data['theMessage'] .= "pending review for fraud). ";
		$data['theMessage'] .= '<br/><br/>Please choose another payment mode or try again.<br/><br/>Please contact us to refund the amount ';
		$data['theMessage'] .= 'that may have been charged if you want.<br/><br/>';
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';		
		return $data;
	}//assemblePaypalFishy(..)
	
	function assembleShowtime404()
	{	
		return Array(
			'error' => "CUSTOM",
			'theMessage' => "Internal Server Error.<br/>Showing time specified not found. Are you trying to hack the app?"
		);
	}//assembleShowtime404()
	
	function assembleShowtimeChangeDenied()
	{
		return Array(
			'error' => 'CUSTOM',
			'theMessage' => "There is only one showing time for the event you have booked so you cannot change to another showing time.",
			'redirect' => FALSE,
			'redirectURI' => base_url().'EventCtrl/manageBooking',
			'defaultAction' => 'Manage Booking'
		);
	}
	
	function assembleTicketClassOnDB404()
	{
		return Array(
			'error' => "CUSTOM",
			'theMessage' => "INTERNAL SERVER ERROR<br/><br/>Cannot find DB records for the selected ticket class. Please contact the system administrator."
		);
	}//assembleTicketClassOnDB404()
	
	function cancelPendingChanges( $bookingNumberOrObj, $reason = 1 )
	{
		/*
			Called by EventCtrl->{ managebooking_cancelchanges() | book_step2() }
		
			@Created 11MAR2012-1111. Moved from book_step2
			@history 01APR2012-1102 renamed from 'cancelPendingChangesToBooking' to 'cancelPendingChanges'
		
			@purpose This means that the booking is existing and 
			     (1)  was attempted for change for any or all of the following:
						(a) Change of showing time
						(b) ticket class change/upgrade
					  but lapsed on the payment deadline.
				 or
				 (2) The user decided to cancel the changes and restore the booking to the original state.
			
			@param  $bookingNumberOrObj Can be either the booking number in string/int or
											the MYSQL Object of that booking number.			
			@param	$reason	Indicates the reason why it was rolled back. See @purpose
						
		*/
		$billingInfoArray;
		$eachBooking;
		$oldShowtimeID;
		$oldTicketClassGroupID;
		$oldTicketClassUniqueID;
		$reasonText;
		$rollBackInfo;
		$transactionID;
		$transactionFailed;		
		
		
		if( is_string( $bookingNumberOrObj ) )
		{
			$eachBooking = $this->CI->Booking_model->getBookingDetails( $bookingNumberOrObj );
		}else{
			$eachBooking = $bookingNumberOrObj;
		}
		
		switch( $reason )
		{
			case 1: $reasonText = "ROLLBACK-DEADLINE_LAPSED"; break; //error code 2200
			case 2: $reasonText = "ROLLBACK-USER_DO"; break;         // error code 2201
		}
				
		$billingInfoArray = $this->getBillingRelevantData( $eachBooking->bookingNumber );	
		if( count( $billingInfoArray ) > 0 )
		{
			//31mar2012-1758: part is refactorable still : assign this to payment_model
			$transactionID = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( 'transaction' ,$billingInfoArray['unpaidPurchasesArray'][0]->Comments );
			
			if( $transactionID === false )
			{   // error code 5200
				die("ERROR_FATAL ERROR: Cannot find transaction ID when rolling back lapsed change on booking."); //error code 5200
			}
			$transactionFailed 		= $this->CI->TransactionList_model->getTransaction( $transactionID );
			$rollBackInfo 			= $transactionFailed->Data;
			$oldShowtimeID          = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( 'oldShowingTime' , $rollBackInfo );
			$oldTicketClassGroupID  = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_GROUP_ID , $rollBackInfo );
			$oldTicketClassUniqueID = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_UNIQUE_ID , $rollBackInfo );
		}else{
			//error code 5101
			die("ERROR_FATAL ERROR: Billing info for this booking number suddenly became none?");  
		}
		// wait, is this necessary to be in foreach??
		foreach( $billingInfoArray['unpaidPurchasesArray'] as $unpaidPurchase )
		{																				
			$bookingGuest = $this->CI->Guest_model->getGuestDetails( $eachBooking->bookingNumber );
			foreach( $bookingGuest as $eachGuest )
			{
				// Get the new slot assigned to user.				
				$supposedlyNewSlot = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$eachBooking->EventID,
					$eachBooking->ShowingTimeUniqueID,
					$eachBooking->TicketClassGroupID,
					$eachBooking->TicketClassUniqueID,
					$eachGuest->UUID					
				);
				// Free the seat assigned to the supposedly new slot of the user
				$this->CI->Seat_model->markSeatAsAvailable(
					$eachBooking->EventID,
					$eachBooking->ShowingTimeUniqueID,
					$supposedlyNewSlot->Seat_x,
					$supposedlyNewSlot->Seat_y,
					BOOKING_CHANGE_LAPSE_FREED
				);				
				$this->CI->Slot_model->setSlotAsAvailable( $supposedlyNewSlot->UUID );		// obviously
			}
			// Delete this purchase entry
			$this->CI->Payment_model->deleteSinglePurchase( $eachBooking->bookingNumber, $unpaidPurchase->UniqueID );
		}//foreach unpaidPurchase
		// Now, revert the booking to its original
		$this->CI->Booking_model->updateBookingDetails(
			$eachBooking->bookingNumber,
			$eachBooking->EventID,
			$oldShowtimeID,
			$oldTicketClassGroupID,
			$oldTicketClassUniqueID
		);
		$this->CI->Booking_model->markAsPaid( $eachBooking->bookingNumber );
		if( $reason == 1  ) {
			// create notification bla bla
			$this->CI->Booking_model->markAsRolledBack( $eachBooking->bookingNumber );
		}
		$this->CI->TransactionList_model->createNewTransaction(
			$this->CI->session->userdata('accountNum'),
			$reasonText,
			'TICKET_CLASS_UPGRADE',
			$eachBooking->bookingNumber,
			'Secret!',
			'WIN5',
			Array(
				'backToShowingTime'	=> $oldShowtimeID,
				'backToTicketClassGroupID' => $oldTicketClassGroupID,
				'backToTicketClassUniqueID' => $oldTicketClassUniqueID
			)
		);
		return true;
	}//cancelPendingChanges(..)
	
	function cleanDefaultedBookings( $eventID, $showtimeID )
	{	
		/*
			 This checks if there are bookings marked as PENDING-PAYMENT' and yet
			 not able to pay on the deadline - thus forfeited now.
		  */
		 $defaultedBookings;
		 
		 $defaultedBookings = $this->CI->Booking_model->getPaymentPeriodExpiredBookings( $eventID, $showtimeID );
		 if( $defaultedBookings !== false )
		 {
			foreach( $defaultedBookings as $eachBooking )
			{				
				/*
					This will free the slots and seats being tied to this booking, so can be used later.
				*/
				if( $eachBooking->Status2 == "NEW" )
				{
					// This means this is an entirely new booking that is not paid on-time.				
					if( $eachBooking->Status != "EXPIRED" ) $this->CI->Booking_model->markAsExpired_New( $eachBooking->bookingNumber );
					$this->deleteBookingTotally_andCleanup( 
						$eachBooking->bookingNumber,
						Array(
							'bool' => true,
							'Status2' => $eachBooking->Status2
						)
					);
				}else
				if( $eachBooking->Status2 == "MODIFY" )
				{	// An existing booking that there was some change and yet not paid on the deadline
					$this->cancelPendingChanges( $eachBooking, 1 );
				} //if status is modify
		   }//foreach
		}//if	  
	}//cleanDefaultedBookings
	
	function cleanDefaultedSlots( $eventID, $showtimeID, $ticketClassesObjSENT = NULL )
	{
		/*
			@purpose This part checks if there are event_slots (i.e., records in `event_slot` ) that the status
			is 'BEING_BOOKED' but lapsed already based on the ticket class' holding time.
			
			Main difference with $this->cleanDefaultedBookings() is that this is only concerned
			with entries in `event_slot`
			
			@param $eventID    			  :D
			@param $showtimeID 			  :D
			@param $ticketClassesObjSENT  An array of MYSQL OBJECT of entries from table `ticket_class`
		  */
		  // This next line gets all records marked as 'BEING_BOOKED' - judgment in the (next) if statement
		  $beingBookedSlots = $this->CI->Slot_model->getBeingBookedSlots( $eventID, $showtimeID );
		  $ticketClassesObj = NULL;
		  
		  if( $beingBookedSlots === FALSE )	return false; // there are no slots being booked so return now.
			/*
				This variable is for booking numbers that are already processed.
				Because we are examining slot by slot, there might be at least two slots under
				one booking number. By checking this array, it can be found if the booking
				number was processed earlier, so no need to proceed.
			*/
			$defaultedBookingNumbers = Array();	
			$ticketClassesObj = ( is_array($ticketClassesObjSENT) ) ? $ticketClassesObjSENT : $this->CI->TicketClass_model->getTicketClassesOrderByPrice( $eventID, $showtimeObj->Ticket_Class_GroupID );
			
			foreach( $beingBookedSlots as $eachSlot )
			{
				$assignedToUserAlready = FALSE;
								
				if( $this->CI->Slot_model->isSlotBeingBookedLapsedHoldingTime(
						$eachSlot, 
						$ticketClassesObj[ $eachSlot->Ticket_Class_UniqueID ]
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
						$guestObj = $this->CI->Guest_model->getSingleGuest( $eachSlot->Assigned_To_User );
						// see comment near $defaultedBookingNumbers declaration for explanation						
						if( in_array( $guestObj->bookingNumber, $defaultedBookingNumbers ) ) continue;
						$this->deleteBookingTotally_andCleanup(
							$guestObj->bookingNumber,
							Array(
								'bool' => true,
								'Status2' => 'NOT-YET-NOTIFIED'
							)
						);
						$this->CI->Booking_model->markAsHoldingTimeLapsed_New( $guestObj->bookingNumber );
						$defaultedBookingNumbers[] = $guestObj->bookingNumber;
					}else{
						// no entry in `booking_details` and `booking_guests` yet, so just mark it as available
						$this->CI->Slot_model->setSlotAsAvailable( $eachSlot->UUID );
					}
				}
			}//foreach		  
	}//cleanDefaultedSlots
	
	function confirmSlotsOfThisBooking( $bookingNumber, $activity = BOOK, $transactionID = FALSE )
	{
		/**
		*	@Created 02MAR2012-2200 
		*	@purpose Created so as to separate writing to database so as to confirm slots from
			just getting visual infos of seats ( so, this was taken from $this->getSeatVisual_Guests() ).
		*	@history 18JUN2012-1326 Moved from EventCtrl
		**/
		
		// CODE-MISSING: DATABASE CHECKPOINT
		log_message('DEBUG', 'bookingmaintenance\confirmSlotsOfThisBooking() accessed. Trans ID: ' . $transactionID );
		$guest_arr  = $this->CI->Guest_model->getGuestDetails( $bookingNumber );
		$bookingObj = $this->CI->Booking_model->getBookingDetails( $bookingNumber );
		
		if( $activity == MANAGE_BOOKING )
		{	/* CATERS TO MANAGE BOOKING 
				By design, upon reaching here, there are two entries assigned in `event_slot`
				for each guest - as if either his ticket class or showtime was changed
				that would entail a new slot. If not, he should be filtered out
				back in managebooking_changesomething process function. :D
			*/
			log_message('DEBUG', 'bookingmaintenance\confirmSlotsOfThisBooking() IS MANAGE BOOKING');
			$rollbackInfo 		  = $this->getSlotRollbackDataOfPurchase( $transactionID );			
			foreach( $guest_arr as $eachGuest )
			{
				// Get the old slot assigned to user..
				$supposedlyOldSlot = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$bookingObj->EventID,
					$rollbackInfo['oldShowtimeID'],
					$rollbackInfo['oldTicketClassGroupID'],
					$rollbackInfo['oldTicketClassUniqueID'],
					$eachGuest->UUID					
				);
				// .. and mark the old seat as available ..
				if( !(is_null($supposedlyOldSlot->Seat_x) or is_null($supposedlyOldSlot->Seat_y)) ){
					$this->CI->Seat_model->markSeatAsAvailable(
						$bookingObj->EventID,
						$rollbackInfo['oldShowtimeID'],
						$supposedlyOldSlot->Seat_x,
						$supposedlyOldSlot->Seat_y,
						"BOOKING_CHANGE_SUCCESS_FREED"
					);
				}
				// .. and free the old slot.
				$this->CI->Slot_model->setSlotAsAvailable( $supposedlyOldSlot->UUID );
				//	Get the new slot assigned to guest ..
				$newSlot = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$bookingObj->EventID,
					$bookingObj->ShowingTimeUniqueID,
					$bookingObj->TicketClassGroupID,
					$bookingObj->TicketClassUniqueID,
					$eachGuest->UUID					
				);
				// .. mark the new slot as booked ..
				$this->CI->Slot_model->setSlotAsBooked( $newSlot->UUID );
				/*  .. and via extracting the assigned seat, update the new 
					seat whose status is currently -4 to 1 (assigned) by calling 
					the respective function.
				*/
				if( !(is_null($newSlot->Seat_x) or is_null($newSlot->Seat_y)) )
				{
					$this->CI->Seat_model->markSeatAsAssigned(
						$bookingObj->EventID,
						$bookingObj->ShowingTimeUniqueID,
						$newSlot->Seat_x,
						$newSlot->Seat_y, 
						""
					);
				}
			}
			$this->CI->TransactionList_model->createNewTransaction(
				$this->CI->clientsidedata_model->getAccountNum(),
				'BOOKING_CHANGE_CONFIRM',
				'BY_AUTHORIZED_AGENT',
				$bookingNumber,
				'',
				'',
				NULL		
			);
			return true;
		}else{
			log_message('DEBUG', 'bookingmaintenance\confirmSlotsOfThisBooking() IS *NEW* BOOKING');
			// This is an entirely new booking.			
			foreach( $guest_arr as $eachGuest )
			{
				$eSlotObject = $this->CI->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
				$this->CI->Slot_model->setSlotAsBooked( $eSlotObject->UUID );
			}
			return true;
		}
		return false;
		// CODE-MISSING: DATABASE COMMIT
	}//confirmSlotsOfThisBooking(..)
	
	function deleteBookingTotally_andCleanup( $bookingNumber, $expiryCleanup = NULL )
	{
		/*
			Called by EventCtrl->{ book_step2, cancelBooking, cancelBookingProcess )
		
			Created 25FEB2012-1312
			
			Formerly contained within 'cancelBookingProcess()' but moved here to accomodate
			deletion of booking data because of expired payment period
		*/			
		$guestObjArray = NULL;
		$bookingStage  = 0; 
		
		$guestObjArray = $this->CI->Guest_model->getGuestDetails( $bookingNumber );
		if( $expiryCleanup === NULL ){
			$bookingStage = $this->CI->clientsidedata_model->getSessionActivityStage();
		}
		// CODE MISSING: DATABASE Checkpoint
		foreach( $guestObjArray as $eachGuest )
		{
			$eventSlot = $this->CI->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );			
			if( $eventSlot === false ) continue;					
			if( ($expiryCleanup != NULL and $expiryCleanup['bool'])or 
				$bookingStage > STAGE_BOOK_3_FORWARD )
			{											
				$this->CI->Seat_model->markSeatAsAvailable( $eventSlot->EventID, $eventSlot->Showtime_ID, $eventSlot->Seat_x, $eventSlot->Seat_y );				
			}//end if
			$this->CI->Slot_model->setSlotAsAvailable( $eventSlot->UUID );			
		}		
		/*
			Though this first seems to be refactorable, but we have to 
			delete purchases first because it references `booking_info`.
		*/
		if( ( $expiryCleanup['bool'] and $expiryCleanup['Status2'] === "FOR-DELETION" ) 
			or $bookingStage > STAGE_BOOK_4_FORWARD )
		{
			$this->CI->Payment_model->deleteAllBookingPurchases( $bookingNumber );	
		}
		if( ( $expiryCleanup['bool'] and $expiryCleanup['Status2'] === "FOR-DELETION" ) 
			or $bookingStage >  STAGE_BOOK_3_FORWARD )
		{
			$this->CI->Booking_model->deleteAllBookingInfo( $bookingNumber );
		}				
		// CODE MISSING: DB Commit
		return true;
	}//deleteBookingTotally_andCleanup
	
	function determineOnlinePaymentModeCode( $passedData )
	{
		/**
		* @created 25MAY2012-1158
		**/
		$paymentModeObj = NULL;
		if( is_integer( $passedData ) )
		{
			$paymentModeObj = $this->payment_model->getSinglePaymentChannelByUniqueID( $uniqueID );
			if( $paymentModeObj === false ) return false;
		}else{
			$paymentModeObj = $passedData;
			if( !isset( $paymentModeObj->internal_data) ) return false;						
		}
		return $this->properDetermineOnlinePaymentCode( $paymentModeObj );
	}//determineOnlinePaymentModeCode(..)
	
	function forfeitSlotsOfNoShowGuests( $eventID, $showtimeID )
	 {
		/*
			A lot of non-harmful bugs in the functions in booking_model where this is called.
		*/
		$forfeit_noncon = $this->CI->Booking_model->getBookingsForfeitedForCheckIn_NonConsumed( $eventID, $showtimeID );
		$forfeit_partial = $this->CI->Booking_model->getBookingsForfeitedForCheckIn_PartiallyConsumed( $eventID, $showtimeID );
		
		$forfeited = array_merge( ($forfeit_noncon !== false) ? $forfeit_noncon : Array() , ($forfeit_partial !== false) ? $forfeit_partial : Array() );
		$bookingsProcessed = Array();
		
		foreach( $forfeited as $guestUUID => $guestObj )
		{
			log_message( 'DEBUG', 'Guest no-show: '.$guestUUID ); // error code 2203
			$eventSlot = $this->CI->Slot_model->getSlotAssignedToUser( $guestUUID );
			@$this->CI->Seat_model->markSeatAsAvailable( $eventSlot->EventID, $eventSlot->Showtime_ID, $eventSlot->Seat_x, $eventSlot->Seat_y );	
			@$this->CI->Slot_model->setSlotAsAvailable($eventSlot->UUID );
			if( isset($bookingsProcessed[ $guestObj->bookingNumber ] ) ){
				$bookingsProcessed[ $guestObj->bookingNumber ]++;
			}else{
				$bookingsProcessed[ $guestObj->bookingNumber ] = 1;
			}
			log_message( 'DEBUG', 'Guest slot freed: '.@$eventSlot->UUID ); // error code 2203
		}
		
		foreach( $bookingsProcessed as $key=>$val ) @$this->CI->Booking_model->markAsNoShowForfeited( $key );
		
	 }// forfeitSlotsOfNoShowGuests(..)
	
	function getBillingRelevantData( $bookingNumber )
	{
			/**
			*	@created 09MAR2012-1125. 
				
			*	@purpose Gets entries in table `purchase` which have connection with the booking number specified,
				computes the amount due and returns the array containing such data.	
			*	@returns Associative array, containing the data requested.
			*	@remarks If total existing payments exceeds the current amount to be paid, this returns
					zero for amount due since we don't have to refund.
			**/
			$unpaidPurchasesArray = $this->CI->Payment_model->getUnpaidPurchases( $bookingNumber );
			$paidPurchasesArray   = $this->CI->Payment_model->getPaidPurchases( $bookingNumber );
			$unpaidTotal 		  = $this->CI->Payment_model->sumTotalCharges( $unpaidPurchasesArray );
			$paidTotal 			  = $this->CI->Payment_model->sumTotalCharges( $paidPurchasesArray );
			$amountDue 			  = $unpaidTotal - $paidTotal;
			
			if( $amountDue < 0 ) $amountDue = FREE_AMOUNT;	// we don't have to refund ( signified by negative here )
			return Array(
				AKEY_UNPAID_PURCHASES_ARRAY => $unpaidPurchasesArray,
				AKEY_PAID_PURCHASES_ARRAY   => $paidPurchasesArray,
				AKEY_UNPAID_TOTAL		    => $unpaidTotal,
				AKEY_PAID_TOTAL 		    => $paidTotal,
				AKEY_AMOUNT_DUE             => $amountDue
			);
	}//getBillingRelevantData
	
	function getSendSeatInfoToViewData( $bookingNumber )
	{
		/**
		*	@created 07JUN2012-1807
		*	@description Assembles data related to seating for display in the forward pages.
		*	@returns Array,						
				<key: Guest UUID>
					-> <MATRIX_X>_<MATRIX_Y>
		**/
		$bookingObj = $this->CI->Booking_model->getBookingDetails( $bookingNumber );
		$guestObj   = $this->CI->Guest_model->getGuestDetails( $bookingNumber );
		$sendSeatInfoToView = Array();
		
		if( is_array( $guestObj ) and count( $guestObj ) > 0 )
		{
			foreach( $guestObj as $singleGuest )
			{
				$guestSlotObj = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$bookingObj->EventID, 
					$bookingObj->ShowingTimeUniqueID,
					$bookingObj->TicketClassGroupID, 
					$bookingObj->TicketClassUniqueID,
					$singleGuest->UUID
				);			
				$sendSeatInfoToView[ $singleGuest->UUID ] = $this->CI->Seat_model->getVisualRepresentation( 
					$guestSlotObj->Seat_x, 
					$guestSlotObj->Seat_y,
					$bookingObj->EventID,
					$bookingObj->ShowingTimeUniqueID
				);
			}
		}		
		return $sendSeatInfoToView;
		
	}// getSendSeatInfoToViewData(..)
	
	function getSlotRollbackDataOfPurchase( $unpaidPurchaseObj_or_transID )
	{
		/**
		*	@created 11MAR2012-1526
		*	@description Since when changing a booking, there's a transaction ID matching the purchase attached to that change,
				and in that transaction, we record the former booking details so we can revert if the deadline for payment
				for the change in booking lapses. Here is how it is retrieved.
		*	@remarks NO Error checking yet.
		*	@history Moved from confirmSlotsOfThisBooking(..).
		*	@history 18JUN2012-1330 Moved from EventCtrl
		*	@returns BOOLEAN FALSE if not roll back data got.
		**/
		$returnThis = Array(
			TRANSACTION_ID => NULL,
			OLD_SHOWTIME_ID => NULL,
			OLD_SHOWTIME_TC_GROUP_ID => NULL,
			OLD_SHOWTIME_TC_UNIQUE_ID => NULL
		);
		log_message("DEBUG"," bookingmaintenance\getSlotRollbackDataOfPurchase() accessed: param 1: ". $unpaidPurchaseObj_or_transID );
		if( is_numeric( $unpaidPurchaseObj_or_transID ) )
		{
			$transactionID = $unpaidPurchaseObj_or_transID;
		}else{
			$rollbackData   = strval( $unpaidPurchaseObj_or_transID->Comments);
			$transactionID  = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( 'transaction' , $rollbackData );
		}
		$transactionObj = $this->CI->TransactionList_model->getTransaction( $transactionID );
		if( $transactionObj === false ) return FALSE;
		$rollBackInfo   = $transactionObj->Data;
		if( is_null( $rollBackInfo ) ) return FALSE;
		$returnThis[ TRANSACTION_ID ] 			 = $transactionID;
		$returnThis[ OLD_SHOWTIME_ID ] 			 = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( 'oldShowingTime' , $rollBackInfo );
		$returnThis[ OLD_SHOWTIME_TC_GROUP_ID ]  = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_GROUP_ID , $rollBackInfo );
		$returnThis[ OLD_SHOWTIME_TC_UNIQUE_ID ] = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_UNIQUE_ID , $rollBackInfo );
		return $returnThis;
	}//getSlotRollbackDataOfPurchase(..)
	
	function pay_and_confirm( $bookingNumber, $from, $paymentChannel = NULL, $totalCharges, $paymentProcessingStage, $info, $agent = NULL )
	{
		/**
		*	@created 18JUN2012-1249
		*	@description The gateway to the settlement of a booking when called via book_step6
				and managebooking_finalize : payment and confirmation of slots are processed here.
				Needs _constants.inc included.
		*	@param $bookingNumber - obviously.
		*	@param $from - where this activity is from.
		*	@param $totalCharges - what is the total charges
		*	@param $paymentProcessingStage - what stage number should we mark upon accessing this function.
		*	@param $info ARRAY - Containing info regarding this. Structure is:
						"eventID" => INT, obviously
						"showtimeID" => INT, obviously. If manage booking, this is the new showtime ID.
						"ticketClassGroupID" => INT, obviously. If manage booking, this is the new TCG ID.
						"ticketClassUniqueID" => INT, obviously. If manage booking, this is the new TCU ID.
					* Manage booking specific:
						"transactionID" => string, OPTIONAL . rollback data if ever.
		*   @param $agent ARRAY - Present only if the caller of this is a payment agency (i.e. COD or PayPal ).	Structure:
					0 - paymentChannel UniqueID					
		*	@returns ARRAY. Structure:
				"boolean"	=> BOOLEAN. Whether transaction here is successful or not in general.
				"code"		=> INT. Code number of the error, if any. Values:
					*	0	- NO ERROR.
					*	1	- No error. Payment mode is determined to be "CASH-ON-DELIVERY" one. 
					*	2   - Online processor detected. Initially, PayPal.
					*	15	- error after calling confirmSlotsOfThisBooking()
					*   16   - error after calling processPayment()
					*	17	- Online payment processor detected. However, not supported as of the meantime.
					*  5110 - Error getting online payment processor data.
				"message"  =>  STRING. Explanation of the error, if any.
				"misc"     =>  ARRAY. Miscellaneous ifnfo, especially when code is 2.
				"redirect" =>  STRING. Only for online payment processors. Redirect here to be able to
					use the payment processor service.
				"redirect_access_code" => INT. Needs to be set to CI session's "activity_stage" to be able
					to access "redirect"
		**/
		
		$eventObj           = $this->CI->Event_model->getEventInfo( $info[ "eventID" ] );
		$guestObj 		    = $this->CI->Guest_model->getGuestDetails( $bookingNumber );
		$paymentChannel_obj = $this->CI->Payment_model->getSinglePaymentChannel( 
							$info[ "eventID" ], $info[ "showtimeID" ], $paymentChannel );
		$slots	    = count( $guestObj );
		$mb_trans_id = isset($info["transactionID"]) ? $info["transactionID"] : NULL ;
		$returnThis = Array(
			"boolean" => false,
			"code" => 0,
			"message" => "NULL"
		);
		log_message('DEBUG', 'bookingmaintenance\pay_and_confirm() accessed');
		if( !is_null($paymentChannel) ) $this->CI->Payment_model->setPaymentModeForPurchase( $bookingNumber, $paymentChannel, NULL );
		if( $totalCharges === FREE_AMOUNT or isset( $agent[0] ) ){
			$processPaymentResultArr = $this->processPayment(
				$bookingNumber, 
				"", 
				$mb_trans_id,
				isset( $agent[0] ) ? $agent[0] : NULL
			);
			if( $processPaymentResultArr['boolean'] ){
				if( $this->confirmSlotsOfThisBooking( $bookingNumber, $from, $mb_trans_id ) )
				{
					$returnThis[ "boolean" ] = TRUE;
				}else{
					$returnThis[ "message" ] = "confirmSlotsOfThisBooking() error. Please see CI log.";
					$returnThis[ "code" ] = 15;
				}
			}else{
				$returnThis[ "message" ] = "processPayment() error. Please see CI log.";
				$returnThis[ "code" ] = 16;
				$returnThis[ "misc" ] = $processPaymentResultArr;
			}
		}else{
			/* 
				Payment is needed or this is a user booking/making changes to booking.
				It seems marking this as pending bla is redundant?
			*/
			//if( $from == BOOK )
			//{	// 18JUN2012-1315: Is this really needed?
				$this->CI->Booking_model->markAsPendingPayment( $bookingNumber, ( $from == BOOK ) ? "NEW" : "MODIFY" );
				foreach( $guestObj as $eachGuest )
				{
					$slotAssignedObj = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
						$info[ "eventID" ],
						$info[ "showtimeID" ],
						$info[ "ticketClassGroupID" ],
						$info[ "ticketClassUniqueID" ],
						$eachGuest->UUID
					);
					$this->CI->Slot_model->setSlotAsPendingPayment( $slotAssignedObj->UUID );
				}
			//}
			if( $paymentChannel_obj->Type == "ONLINE" )
			{
				/* 
					Determine which online payment processor to use
				*/
				$paymentProcessor = $this->determineOnlinePaymentModeCode( $paymentChannel_obj );
				if( $paymentProcessor === false )
				{
					$returnThis[ "message" ] = "Error getting payment processor data. Please choose another payment method.";
					$returnThis[ "code" ] = 5110;	// EC 5110
				}else{
					switch( $paymentProcessor )
					{
						case PAYMODE_PAYPAL: 
							$paypalTotal = floatval($totalCharges * PAYPAL_FEE_PERCENTAGE) + PAYPAL_FEE_FIXED;
							$paypalTotal =  round( $paypalTotal , 2 );
							$paypalInternalData = $paymentChannel_obj->internal_data;
							$chargeDescriptor = $slots." Ticket(s) for ".$eventObj->Name." ordered via The UPLB Express Ticketing System";
							$this->CI->clientsidedata_model->setPaypalAccessible();
							$this->CI->clientsidedata_model->setDataForPaypal( $bookingNumber."|".$totalCharges."|".$paypalTotal."|".$chargeDescriptor."|".$paypalInternalData );
							$this->CI->clientsidedata_model->updateSessionActivityStage( $paymentProcessingStage );
							$returnThis[ "boolean" ] = TRUE;
							$returnThis[ "redirect" ] = "paypal/process";
							$returnThis[ "redirect_access_code" ] = STAGE_BOOK_6_PAYMENTPROCESSING;
							$returnThis[ "message" ] = "ONLINE|PAYPAL";
							$returnThis[ "code" ] = 2;
							break;
						default:
							/*
								The online payment processor we support now is only PayPal.
								Now let customers choose other payment modes first.
							*/
							$returnThis[ "message" ] = "ONLINE|404";
							$returnThis[ "code" ] = 17;
					}
				}
			}else{
				/*
					So far, other allowed values for $paymentChannel_obj->Type
					are { "COD" | "OTHER" }. No need to process those for now.
					"COD" if processed outside of these if() statements. "OTHER" - later.
				*/
				$returnThis[ "boolean" ] = TRUE;
				$returnThis[ "message" ] = "COD|OTHER";
				$returnThis[ "code" ] = 1;
			}
		}
		return $returnThis;
	}//pay_and_confirm()
	
	function processPayment( $bNumber, $customData = "", $purchaseComments=FALSE, $paymentChannel_sent = NULL )
	{
		/**
		*	@created 28FEB2012-1148
		*	@calledBy called by book_step6, confirm_step3
		*	@history Moved from confirm_step3,so this can be used in BookStep6 when
			there are no charges ( FREE ).
		**/
		$result = Array(
			//error code 5104
			'boolean' => FALSE,
			'status' => 'ERROR',
			'code'   => -1,
			'message' => 'Something went wrong.'
		);
		$userPermitted;
		$paymentModeObj;
		$bookingDetails  = $this->CI->Booking_model->getBookingDetails( $bNumber );
		$billingData	 = $this->getBillingRelevantData( $bNumber );				
				
		log_message('DEBUG', 'bookingmaintenance\processpayment() accessed');
		if( $billingData[ AKEY_UNPAID_PURCHASES_ARRAY ] === false )
		{			
			$result['status'] = "ERROR";
			$result['code'] = 1004;
			$result['message'] = "Already paid."; //1004
			return $result;
		}
		$paymentChannel  = is_null($paymentChannel_sent) ? $billingData[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID : $paymentChannel_sent;
		$paymentModeObj = $this->CI->Payment_model->getSinglePaymentChannel( 
			$bookingDetails->EventID, 
			$bookingDetails->ShowingTimeUniqueID, 
			$paymentChannel
		);
		if( $paymentModeObj === FALSE )
		{
			log_message('DEBUG', 'PAYMENT MODE '. $paymentChannel .' NOT ALLOWED FOR EVENT ' . $bookingDetails->EventID . " " . $bookingDetails->ShowingTimeUniqueID );
			$result['status'] = "ERROR";
			$result['code'] = 4008;
			$result['message'] = "This payment mode is not allowed to be used for this event."; //4008			
			return $result;
		}
		/*
			For online payment modes (i.e. PayPal ), you don't need to check whether a user
			is authorized payment agency.
		*/
		if( $paymentModeObj->Type != "ONLINE" ){
			$userPermitted = $this->CI->Account_model->isUserAuthorizedPaymentAgency(
					$this->CI->clientsidedata_model->getAccountNum(),
					$bookingDetails->EventID,
					$bookingDetails->ShowingTimeUniqueID,
					$paymentChannel
			);
			if( $userPermitted['value'] === false ){
				log_message('DEBUG', 'PAYMENT AGENT DENIED.');
				$result['status'] = "ERROR";
				$result['code'] = 4007;
				$result['message'] = "You do not have permission to confirm a reservation for this event.<br/><br/>*"; //4007
				$result['message'] .= $userPermitted['comment'];
				return $result;			
			}
		}else{
			log_message('DEBUG', 'Online payment mode detected ' . $paymentModeObj->Name );
		}
		$paymentID    = $this->CI->Payment_model->createPayment( 
			$bNumber,
			$billingData[ AKEY_AMOUNT_DUE ], 
			$billingData[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID,
			$this->CI->clientsidedata_model->getAccountNum(),
			$customData
		);
		if( $paymentID !== false )
		{
			log_message('DEBUG', 'Payment created for ' . $bNumber  . ' : ' . $paymentID );
			foreach( $billingData[ AKEY_UNPAID_PURCHASES_ARRAY ] as $singlePurchase)
			{
				$this->CI->Payment_model->setAsPaid( $bNumber, $singlePurchase->UniqueID, $paymentID );
				if( $purchaseComments !== FALSE )
				{
					/*
						Mainly for manage booking.
						Update the `Comments` field of `purchases` with the transaction ID.
						This enables us to undo the changes if payment time lapses.
					*/
					$this->CI->Payment_model->updatePurchaseComments(
						$bNumber, 
						$singlePurchase->UniqueID,
						$purchaseComments
					);
				}
			}
			
			$this->CI->Booking_model->markAsPaid( $bNumber );
			$result['boolean'] = TRUE;
			$result['code'] = 1003;
			$result['status'] = "OKAY";
			$result['message'] = "Succesfully proccessed payment."; //1003			
		}else{
			log_message('DEBUG', 'Unable to create payment entry.' );
			$result['boolean'] = FALSE;
			$result['code'] = 5102;
			$result['status'] = "ERROR";
			$result['message'] = "Unable to create payment entry when confirming payment. Please try again."; //5102
		}
		return $result;	
	}//processPayment(..)
	
	function react_on_pay_and_confirm( $response_pandc, $fallBackURI, $fallBackStage )
	{
		/**
		*	@created 18JUN2012-1434
		*	@description Handles where we should go after calling ($this->)pay_and_confirm()
				back in the controller where this is called.
		*	@param $response_pand ARRAY - returned by pay_and_confirm() back in calling controller
		*	@param $fallBackURI STRING - URI to go when needed.
		*	@param $fallBackStage INT - The number that needs to be set to CI session's 'acitvity_stage'
						to access $fallBackURI
		**/
		log_message('DEBUG', 'bookingmaintenance\react_on_pay_and_confirm() accessed');
		if( $response_pandc["boolean"] == FALSE )
		{
			switch( $response_pandc["code"] )
			{
				case 15: 
						$data = $this->assembleErrorOnSlotConfirmation(
							$fallBackURI
						);
						break;
				case 16:
						$data = $this->assembleErrorPaymentNotification(
							$fallBackURI,
							$response_pandc["misc"]['status']."|".$response_pandc["misc"]['message']
						);
						break;
				case 17: 
						$data =  $data = $this->bassembleOnlinePaymentProcessorNotSupported( $fallBackURI );
						break;
				case 5110:
						$data = $this->assembleOnlinePaymentProcessorErrorData(
							$fallBackURI
						);
						break;
			}
			$this->CI->load->view('errorNotice', $data );
			$this->CI->clientsidedata_model->updateSessionActivityStage( $fallBackStage );
			return FALSE;
		}else{
			// online payment mode detected, redirect to there.
			if($response_pandc["code"] == 2)
			{
				$this->CI->clientsidedata_model->updateSessionActivityStage( $response_pandc["redirect_access_code"] );
				log_message('DEBUG', 'react and confirm: ' .  $response_pandc["redirect"] );
				redirect( $response_pandc["redirect"] );
				return FALSE;
			}
			return TRUE;
		}
	}//react_on_pay_and_confirm(..)
	
	function freeSlotsBelongingToClasses_NDX( $selectedTicketClass = FALSE, $slot_uuid_db_entry )
	{
		/**
		*	@created 09JUN2012-1607
		*	@description Supersedes Slot_model->freeSlotsBelongingToClasses(.)	
						
		*	@param $selectedTicketClass - INT - The ticket class selected. 
										- BOOLEAN FALSE - If we just want to make the slot UUIDs available for booking.
		**/
		$unlock_slot_UUIDs = "";		
		if( $selectedTicketClass === FALSE )
		{
			/* The current cookie-on-server's SLOTS_UUID value is the slot UUIDs
				separated by underscores. We just need to explode it.
			*/
			$free_these_UUIDs = explode('_' ,$slot_uuid_db_entry);
		}else{
			/* The current cookie-on-server's SLOTS_UUID value is of type WIN5, with keys as 
				the  respective Ticket Class Unique IDs. We get it and remove the entry for
				the ticket class the user selects.
			*/
			$unlock_slot_UUIDs = $this->CI->UsefulFunctions_model->removeWIN5_Data( $selectedTicketClass, $slot_uuid_db_entry );
			/*
				We then make an associative array out of it, so that we can easily access the UUID
				values in the foreach() later.
			*/
			$free_these_UUIDs = $this->CI->UsefulFunctions_model->makeAssociativeArrayThisWIN5_DATA( $unlock_slot_UUIDs );
		}
				
		foreach( $free_these_UUIDs as $value )
		{								
			$explodedUUIDs = explode( '_', $value );		// explode via delimiter underscore
			foreach( $explodedUUIDs as $uuid ) 
			{				
				$this->CI->Slot_model->setSlotAsAvailable( $uuid );	// free slots
			}
		}
	}//freeSlotsBelongingToClasses_NDX

	function isComingFromTicketClass( $m_bookingInfo )
	{
		/**
		*	@created 16JUN2012-1351
		*	@description For Manage Booking. Determines whether we
				came from ticket class.
		*	@param $m_bookingInfo MYSQL_OBJ An entry in `_manage_booking_cookies`.		
		**/
		if( !isset( $m_bookingInfo->GO_TICKETCLASS ) ) return FALSE;
		return ( @$m_bookingInfo->GO_TICKETCLASS >= MB_STAGESTAT_PASSED );
	}
	
	function isShowtimeChanged( $m_bookingInfo )
	{
		/**
		*	@created 16JUN2012-1351
		*	@description For Manage Booking. Determines whether the showtime is changed as 
				a result of making changes to the booking.
		*	@param $m_bookingInfo MYSQL_OBJ An entry in `_manage_booking_cookies`.		
		**/
		if( !isset( $m_bookingInfo->GO_SHOWTIME ) ) return FALSE;
		return ( @$m_bookingInfo->GO_SHOWTIME == MB_STAGESTAT_CHANGED );
	}
	
	function isTicketClassChanged( $m_bookingInfo )
	{
		/**
		*	@created 16JUN2012-1351
		*	@description For Manage Booking. Determines whether the ticket class
				 is changed as  a result of making changes to the booking.
		*	@param $m_bookingInfo MYSQL_OBJ An entry in `_manage_booking_cookies`.		
		**/
		return ( @$m_bookingInfo->GO_TICKETCLASS == MB_STAGESTAT_CHANGED );
	}
	
	function getMBReasonOnSeatPage( $isShowtimeChanged, $isTicketClassChanged )
	{
		
	}
	
}//class
	