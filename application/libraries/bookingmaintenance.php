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
*/

class BookingMaintenance{
	var $bookingNumberGlobal;
	var $CI;
	
	public function __construct( $params = NULL )
    {		
	
		$this->CI = & get_instance();
		$this->CI->load->model('Account_model');
		$this->CI->load->model('Booking_model');
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('Guest_model');
		$this->CI->load->model('Payment_model');
		$this->CI->load->model('Seat_model');		
		$this->CI->load->model('Slot_model');		
		$this->CI->load->model('TicketClass_model');		
		$this->CI->load->model('TransactionList_model');		
		$this->CI->load->model('UsefulFunctions_model');
        $bookingNumberGlobal = (is_array($params) )?  $params[0] : $this->CI->clientsidedata_model->getBookingNumber();		
    }
	
	public function assembleNoMoreSlotSameTicketClassNotification( $eventID, $showtimeID, $slots, $bookingNumber )
	{
		// Error code 2050
		$data = Array();
		$data['title'] = 'Oops, some technicality';
		$data['theMessage'] = 'There are no more slots available for the ticket class in your current booking.<br/><br/>';
		$data['theMessage'] .= 'Would you like to continue by selecting any other ticket class?';
		$data['theMessage'] .= ' Please note that price differences if any will be charged.';
		$data['yesURI'] = base_url().'EventCtrl/manageBooking_changeShowingTime_process';
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
	
	public function assembleOnlinePaymentProcessorNotSupported( $otherMsgs = "" )
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
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';		
		return $data;
	}//assembleOnlinePaymentProcessorNotSupported
	
	public function assembleErrorPaymentNotification( $otherMsgs = "" )
	{
		// Error code 5104
		$data = Array();
		$data['error'] = 'CUSTOM';		
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = 'Something went wrong while processing your payment. Please choose another payment mode.<br/><br/>WARNING: Do not refresh the page.';		//5104
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';		
		return $data;
	}//assembleErrorPaymentNotification()
	
	public function assemblePaypalPaymentMissingCrucial( $otherMsgs = "" )
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
	
	public function assemblePaypalPaymentUserCancelledNotification( $otherMsgs = "" )
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
	
	public function assemblePaypalFishy( $otherMsgs = "" )
	{
		// Error code  5103
		$data = Array();
		$data['error'] = 'CUSTOM';
		$data['title'] = 'Payment Processing Error';
		$data['theMessage'] = "We have received your payment through PayPal but it did not pass our standards. (i.e., it was held by PayPal";
		$data['theMessage'] .= "pending review for fraud). ";
		$data['theMessage'] .= '<br/><br/>Please choose another payment mode or try again.<br/><br/>Please contact us to refund the amount ';
		$data['theMessage'] .= 'that may have been charged.<br/><br/>';
		$data['theMessage'] .= $otherMsgs;
		$data['defaultAction'] = 'Payment page';
		$data['redirect']	= 2;
		$data['redirectURI'] = base_url().'EventCtrl/book_step5_forward';		
		return $data;
	}//assemblePaypalFishy(..)
	
	public function cancelPendingChanges( $bookingNumberOrObj, $reason = 1 )
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
	
	public function cleanDefaultedBookings( $eventID, $showtimeID )
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
	
	public function cleanDefaultedSlots( $eventID, $showtimeID, $ticketClassesObjSENT = NULL )
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
	
	
	public function deleteBookingTotally_andCleanup( $bookingNumber, $expiryCleanup = NULL )
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
	
	private function properDetermineOnlinePaymentCode( $paymentModeObj )
	{		
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
	
	public function forfeitSlotsOfNoShowGuests( $eventID, $showtimeID )
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
	
	public function getBillingRelevantData( $bookingNumber )
	{
			/*
				@created 09MAR2012-1125. 
				
				@purpose Gets entries in table `purchase` which have connection with the booking number specified,
				computes the amount due and returns the array containing such data.				
			*/
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
		
	function processPayment( $bNumber, $customData = "" )
	{	
		/*
			@created 28FEB2012-1148
			@calledBy called by book_step6 & its forward, confirm_step3
			@purpose Moved from confirm_step3,so this can be used in BookStep6 when
			there are no charges (FREE ).
		*/		
		$result = Array(
			//error code 5104
			'boolean' => FALSE,
			'status' => 'ERROR',
			'message' => 'Something went wrong.'
		);
		$userPermitted;
		$bookingDetails  = $this->CI->Booking_model->getBookingDetails( $bNumber );
		$billingData	 = $this->getBillingRelevantData( $bNumber );		
				
		if( $billingData[ AKEY_UNPAID_PURCHASES_ARRAY ] === false )
		{			
			$result['status'] = "ERROR";
			$result['message'] = "Already paid."; //1004
			return $result;
		}
		$userPermitted = $this->CI->Account_model->isUserAuthorizedPaymentAgency(
				$this->CI->clientsidedata_model->getAccountNum(),
				$bookingDetails->EventID,
				$bookingDetails->ShowingTimeUniqueID,
				$billingData[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID
		);
		if( $userPermitted['value'] === false ){
			$result['status'] = "ERROR";
			$result['message'] = "You do not have permission to confirm a reservation for this event.<br/><br/>*"; //4007
			$result['message'] .= $userPermitted['comment'];
			return $result;			
		}
		$totalCharges = floatval( $billingData[ AKEY_AMOUNT_DUE ] );
		$paymentID    = $this->CI->Payment_model->createPayment( 
			$bNumber, 
			$totalCharges, 
			$billingData[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID,
			$customData
		);
		if( $paymentID !== false )
		{
			foreach( $billingData[ AKEY_UNPAID_PURCHASES_ARRAY ] as $singlePurchase)
			{
				$this->CI->Payment_model->setAsPaid( $bNumber, $singlePurchase->UniqueID, $paymentID );
			}
			$this->CI->Booking_model->markAsPaid( $bNumber );
			$result['boolean'] = TRUE;
			$result['status'] = "OKAY";
			$result['message'] = "Succesfully proccessed payment."; //1003
			return $result;	
		}else{			
			$result['boolean'] = FALSE;
			$result['status'] = "ERROR";
			$result['message'] = "Unknown error occurred when confirming payment. Please try again."; //5102
			return $result;
		}
	}//processPayment(..)
}//class
	