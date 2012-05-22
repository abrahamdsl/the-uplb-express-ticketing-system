<?php
/**
*	Booking Maintenance Controller
* 	CREATED 28 NOV 2011 2035
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains most functionalities regarding an event.

	At current, checks using functionacces library, infinite redirection could happen
	if data is submitted (book proper functions), then suddenly terminated midway
	its processing (before setting that we can now go to the view function). I don't
	know if CodeIgniter has safeguards against this.
	
	At current, user needs to be logged in to be able to use the features of this.
*/
class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		
		include_once('_constants.inc');
		
		$this->load->helper('cookie');
		$this->load->model('login_model');
		$this->load->model('Academic_model');
		$this->load->model('Account_model');
		$this->load->model('Booking_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('CoordinateSecurity_model');
		$this->load->model('email_model');
		$this->load->model('Event_model');
		$this->load->model('Guest_model');
		$this->load->model('MakeXML_model');
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
	{	/* 
			Created 12MAR2012-1738 
						
			Stores details of the manage booking activity.
		*/
		$appendThisSessionActivityDataEntry  = PAYMENT_MODE.'='.$paymentMode.';'.OLD_SHOWTIME_ID.'='. $oldShowtimeID.';';
		$appendThisSessionActivityDataEntry .= OLD_SHOWTIME_TC_GROUP_ID.'='.$oldTicketClassGroupID.';'.OLD_SHOWTIME_TC_UNIQUE_ID.'=';
		$appendThisSessionActivityDataEntry .= $oldTicketClassUniqueID.';';		
		$this->clientsidedata_model->appendSessionActivityDataEntryLong( $appendThisSessionActivityDataEntry );	
	}// assembleDataForManageBookingPending_ViewDetais
		
	private function assembleRelevantDataOfNewSeats( $eventID, $newShowingTimeID, $terminateOnNone = true )
	{
		/*
			Created 08MAR2012-1719
			
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
			CREATED 11MAR2012-2350 Actually matagal na nga din, refactored lang
			
			@purpose When managing booking and some seats are available, so this outputs a table
						in HTML showing the relevant info.
						
						The class "center_purest" of element <table> is dependent on 'body_all.css'
		*/
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

	
	
	private function confirmSlotsOfThisBooking( $bookingNumber )
	{
		/*
			Created 02MAR2012-2200 
			
			@purpose Created so as to separate writing to database so as to confirm slots from
			just getting visual infos of seats ( so, this was taken from $this->getSeatVisual_Guests() ).
		*/		
		// CODE-MISSING: DATABASE CHECKPOINT
		$guest_arr  = $this->Guest_model->getGuestDetails( $bookingNumber );
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		
		if( $this->Booking_model->isBookingUpForChange( $bookingNumber ) )
		{						
			$unpaidPurchasesArray = $this->Payment_model->getUnpaidPurchases( $bookingNumber );		
			// it is impossible that #unpaidPurchasesArray is not of what we expect here, ceteris paribus.
			$rollbackInfo 		  = $this->getSlotRollbackDataOfPurchase($unpaidPurchasesArray[0] );			
			foreach( $guest_arr as $eachGuest )
			{				
				// Get the old slot assigned to user and mark the old seat as available
				$supposedlyOldSlot = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$bookingObj->EventID,
					$rollbackInfo['oldShowtimeID'],
					$rollbackInfo['oldTicketClassGroupID'],
					$rollbackInfo['oldTicketClassUniqueID'],
					$eachGuest->UUID					
				);
				$this->Seat_model->markSeatAsAvailable(
					$bookingObj->EventID,
					$rollbackInfo['oldShowtimeID'],
					$supposedlyOldSlot->Seat_x,
					$supposedlyOldSlot->Seat_y,
					"BOOKING_CHANGE_SUCCESS_FREED"
				);
				$this->Slot_model->setSlotAsAvailable( $supposedlyOldSlot->UUID );
				/*
					Get the new slot assigned to guest, and via extracting the assigned seat,
					update the new seat whose status is currently -4 to 1 (assigned) by calling 
					the respective function					
				*/
				$newSlot = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
					$bookingObj->EventID,
					$bookingObj->ShowingTimeUniqueID,
					$bookingObj->TicketClassGroupID,
					$bookingObj->TicketClassUniqueID,
					$eachGuest->UUID					
				);
				$this->Seat_model->markSeatAsAssigned(
					$bookingObj->EventID,
					$bookingObj->ShowingTimeUniqueID,
					$newSlot->Seat_x,
					$newSlot->Seat_y, 
					""
				);				
			}
			$this->TransactionList_model->createNewTransaction(
				$this->session->userdata('accountNum'),
				'BOOKING_CHANGE_CONFIRM',
				'BY_AUTHORIZED_AGENT',
				$bookingNumber,
				'Secret!',
				'WIN5',
				NULL				
			);
			return true;
		}else{
			// This is an entirely new booking.			
			foreach( $guest_arr as $eachGuest )
			{
				$eSlotObject = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
				$this->Slot_model->setSlotAsBooked( $eSlotObject->UUID );
			}
			return true;
		}
		return false;
		// CODE-MISSING: DATABASE COMMIT
	}//confirmSlotsOfThisBooking(..)
					
	private function decodeSeatVisualCookie( $bookInstanceEncryptionKey )
	{
		$array_result = Array(
			'boolean' => false,
			'textStatus' => 'ERROR',
			'textStatusFriendly' => NULL
		);		
		$decodedSeatVisualCookie = explode('|',$this->encrypt->decode( 
				$this->clientsidedata_model->getVisualSeatInfo()
			) 
		);
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
			//$seatVisuals[ $mainSub[0] ] = $mainSub[1];
			$seatVisuals[ $mainSub[0] ] = isset($mainSub[1]) ? $mainSub[1] : "";
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
					$this->clientsidedata_model->getPurchaseIDs()
				)
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
		if( $purchaseCount === 0 ) return true;
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
				$this->clientsidedata_model->getEventID(),
				$this->clientsidedata_model->getShowtimeID()
			);
			$guestSeats[ $eachGuest->UUID ] = ( $eSeatObject->Visual_row."-".$eSeatObject->Visual_col );
		}
		
		return $guestSeats;
	}//getSeatVisual_Guests( $guest_arr )
	
	
	private function getSlotRollbackDataOfPurchase( $unpaidPurchaseObj )
	{
		/*
			Created 11MAR2012-1526 | NO Error checking yet.
			Moved from confirmSlotsOfThisBooking(..).
			Since when changing a booking, there's a transaction ID matching the purchase attached to that change,
			and in that transaction, we record the former booking details so we can revert if the deadline for payment
			for the change in booking lapses. Here is how it is retrieved.
		*/
		$returnThis = Array(
			TRANSACTION_ID => NULL,
			OLD_SHOWTIME_ID => NULL,
			OLD_SHOWTIME_TC_GROUP_ID => NULL,
			OLD_SHOWTIME_TC_UNIQUE_ID => NULL
		);
		$rollbackData   = strval( $unpaidPurchaseObj->Comments);
		$transactionID  = $this->UsefulFunctions_model->getValueOfWIN5_Data( 'transaction' , $rollbackData );
		$transactionObj = $this->TransactionList_model->getTransaction( $transactionID );
		$rollBackInfo   = $transactionObj->Data;
		$returnThis[ TRANSACTION_ID ] 			 = $transactionID;
		$returnThis[ OLD_SHOWTIME_ID ] 			 = $this->UsefulFunctions_model->getValueOfWIN5_Data( 'oldShowingTime' , $rollBackInfo );
		$returnThis[ OLD_SHOWTIME_TC_GROUP_ID ]  = $this->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_GROUP_ID , $rollBackInfo );
		$returnThis[ OLD_SHOWTIME_TC_UNIQUE_ID ] = $this->UsefulFunctions_model->getValueOfWIN5_Data( OLD_SHOWTIME_TC_UNIQUE_ID , $rollBackInfo );
		return $returnThis;
	}//getSlotRollbackDataOfPurchase(..)
	
	
	
	private function getSeatRepresentationsOfGuests( $eventID, $showtimeID, $guest_arr,
		$ticketClassGroupID = NULL, $ticketClassUniqueID = NULL
	)
	{
		/*
			Created 03MAR2012-1147
			11MAR2012-1441 Added params $ticketClassGroupID, $ticketClassUniqueID 
		*/
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
					'Matrix_x' => (isset ($slotObj->Seat_x) ) ? $slotObj->Seat_x : "",
					'Matrix_y' => (isset ($slotObj->Seat_y) ) ? $slotObj->Seat_y : ""
				);
			}else{
				$seatMatrixRepObj = $this->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );								
			}
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
	
	private function immediatelyAssignSlotsAndSeats_MidManageBooking( 
		$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID, 
		$oldTicketClassUniqueID, $newShowtimeID, $newTicketClassUniqueID 
	)
	{
		/*
			Created 11MAR2012-2307 - Actually matagal na, nilabas lang mula sa isang function. Hahaha.
			
			DOES NOT REMOVE OLD SEAT if any.
		*/
		// Now, these are the UUIDs of the slots of the ticket class selected. We are going to extract them.
		$eventUUIDs = $this->input->cookie( $newTicketClassUniqueID ."_slot_UUIDs" );
		$eventUUIDs_tokenized = explode('_', $eventUUIDs );
		$seatInfoArray = Array();
		$x = 0;			
		foreach( $guestObj as $eachGuest )
		{
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
				$eventUUIDs_tokenized[ $x++ ],
				$eachGuest->UUID
			);
			// Assign the same seat to the new slot as with the old slot
			$this->Guest_model->assignSeatToGuest( 
				$eachGuest->UUID, 
				$oldSlot->Seat_x,
				$oldSlot->Seat_y,
				$eventID,
				$newShowtimeID
			);			
			// mark the seat in the new showing time as assigned
			$this->Seat_model->markSeatAsAssigned( 
				$eventID,
				$newShowtimeID,
				$oldSlot->Seat_x,
				$oldSlot->Seat_y
			);				
			$seatInfoArray[ $eachGuest->UUID ] = Array(
				'Matrix_x' => $oldSlot->Seat_x,
				'Matrix_y' => $oldSlot->Seat_y
			);
		}//foreach guest												
		$this->assembleForWritingDataOfNewSeats( $seatInfoArray );
	}//immediatelyAssignSlotsAndSeats_MidManageBooking(..)
	
	private function isBookingSeatsAvailableNewShowtime( $bookingNumber, $showtimeID )
	{
		/*
			Created 06MAR2012-1617. 
			
			Gets slots of a booking and check if the seats in the new showtime corresponding to the
			current booking are available.
			
			Returns an array.
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
		*/
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
		if( count($returnThis['guest_no_seat']) === 0 )
			return $returnThis;
		else{
			$returnThis['boolean'] = false;
			return $returnThis;
		}
	}//isBookingSeatsAvailableNewShowtime(..)
		
	
	private function setBookingCookiesOuter( $eventID, $showtimeID, $slots, $bookingNumber = 'XXXXX' )	
	{
		/*
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
	
	function book()
	{
		/*
			@purpose, just sets the booking progress indicator then redirects to
			forward page.
		*/
		$this->clientsidedata_model->setBookingProgressIndicator( 1 );
		redirect('EventCtrl/book_forward');
	}//book(..)
	
	function book_forward()
	{
		/*
			Created 29DEC2011-2048
			
			Hotspot for refactoring (i.e., in Event_model, create a single function
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
		redirect('/');
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
		  it means, user went straight to change ticket class (without changing showing time).
		  It is an Array, with index 0,1,2 corresponding to `EventID`, `showingTimes` and `slot`
		
      */
	  $bookInstanceEncryptionKey;
	  $sessionActivity 				=  $this->clientsidedata_model->getSessionActivity();     
      $eventID 						= $this->input->post( 'events' );
      $showtimeID 					= $this->input->post( 'showingTimes');
      $slots 						= $this->input->post( 'slot' );
      $eventInfo;
      $cookie_names;
      $cookie_values;
      $expiryCleanup = Array();	  
	  $isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
	  $isThereSlotInSameTicketClass = true;
	  	 	
	  if( $ticketClassSelectionEssentials === null )
	  {
		/*
			This means the activity is purchasing tickets or user switched
			to another showing time and is choosing a new ticket class.
		*/		
		$eventID 	= $this->input->post( 'events' );
		$showtimeID = $this->input->post( 'showingTimes');
		$slots 		= $this->input->post( 'slot' );		
	  }else{
		/*
			User went straight to change ticket class functionality - for the
			same showing time.
		*/
		if( count( $ticketClassSelectionEssentials ) != 3 ) 
		{
			die("ERROR_Invalid data passed to ticket class selection."); //5050
		}
		$eventID 	= $ticketClassSelectionEssentials[0];
		$showtimeID = $ticketClassSelectionEssentials[1];
		$slots 		= $ticketClassSelectionEssentials[2];		
	  }	  		
      
	  //  Validate if form submitted has the correct data. 	  
      $this->functionaccess->preBookStep2Check( $eventID, $showtimeID, $slots, STAGE_BOOK_1_FORWARD );	  	  
	  $this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_2_PROCESS );
	  //This sets the initial cookies we need throughout the process.	  
      if( $this->setBookingCookiesOuter( $eventID, $showtimeID, $slots, null ) === false ) return false; 
	  	 
	  /*
		 This checks if there are bookings marked as PENDING-PAYMENT' and yet
		 not able to pay on the deadline - thus forfeited now.
	  */
	  $this->bookingmaintenance->cleanDefaultedBookings( $eventID, $showtimeID ); 
	  /*
		This sets encryption key whom we match with other cookies we are encrypting,
		to avoid cookie injection.
	  */
	  $this->clientsidedata_model->setBookInstanceEncryptionKey();
		
	  // now ticket classes proper
	  $showtimeObj   = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 
	  $ticketClasses = $this->TicketClass_model->getTicketClassesOrderByPrice( $eventID, $showtimeObj->Ticket_Class_GroupID );
	  
	  /*
		Check if there are event_slots (i.e., records in `event_slot` ) that the status
		is 'BEING_BOOKED' but lapsed already based on the ticket class' holding time.
	  */	  
	  $this->bookingmaintenance->cleanDefaultedSlots( $eventID, $showtimeID, $ticketClasses );
	 
	 
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
		 $this->clientsidedata_model->setTicketClassSlotUUIDsCookie( $singleClass->UniqueID, $serializedClass_Slot_UUID );
      }//foreach
	  
	  if( $isActivityManageBooking )
	  {	  		
		if(!$this->clientsidedata_model->changeSessionActivityDataEntry( 'ticketclass', 1 )){
			die('INTERNAL-SERVER-ERROR: Activity data cookie manipulated. Please start over by refreshing Manage Booking page.');
		}
		$this->clientsidedata_model->setBookingNumber( $bookingNumber );
		$this->clientsidedata_model->setAvailabilityOfSlotInSameTicketClass( intval( $isThereSlotInSameTicketClass ));
	  }
	  $this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_2_FORWARD );		// our activity tracker	
	  $this->clientsidedata_model->setBookingProgressIndicator( 2 );
      redirect( 'EventCtrl/book_step2_forward' );
   }//book_step2(..) 
	
	function book_step2_forward()
	{
		$eventID;
		$showtimeID;
		$showtimeObj;		
								
		$eventID   = $this->clientsidedata_model->getEventID();
		$showtimeID = $this->clientsidedata_model->getShowtimeID();		
		
		//  Validate if form submitted has the correct data. 	  
		$this->functionaccess->preBookStep2FWCheck( $eventID, $showtimeID, STAGE_BOOK_2_FORWARD );
		
		$showtimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID ); 	// counter check against spoofing
		if( $showtimeObj === false )
		{			
			 $data['error'] = "CUSTOM";
			 $data['theMessage'] = "Internal Server Error.<br/>Showing time specified not found. Are you trying to hack the app?";
			 $this->load->view( 'errorNotice', $data );
			 return false;
		}
		if( $this->functionaccess->isActivityManageBooking() )
		{									
			/*
				Load additional data that are required by manage booking feature.
			*/
			$bookingNumber = $this->clientsidedata_model->getBookingNumber();
			if( !$this->functionaccess->preBookCheckUnified( Array( $bookingNumber), STAGE_BOOK_2_FORWARD ) ) die();
			$data['bookingObj'] = $this->Booking_model->getBookingDetails( $bookingNumber );
			if( $data['bookingObj'] === false ) die('BOOKING_NUMBER_404');
			$data['existingTCName'] = $this->TicketClass_model->getSingleTicketClassName( 
				$eventID, 
				$data['bookingObj']->TicketClassGroupID, 
				$data['bookingObj']->TicketClassUniqueID 
			);
			$data['existingPayments'] = $this->Payment_model->getSumTotalOfPaid( $bookingNumber , NULL );
		}
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
		
		$eventID 			 = $this->clientsidedata_model->getEventID();
		$showtimeID			 = $this->clientsidedata_model->getShowtimeID();
		$ticketClassGroupID  = $this->clientsidedata_model->getTicketClassGroupID();
		$ticketClassUniqueID = $this->input->post('selectThisClass');
		//echo var_dump( $this->clientsidedata_model->getSessionActivity() );		
		//die();
		//	Check if this page can be accessed already.				
		$this->functionaccess->preBookStep3PRCheck( $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID, STAGE_BOOK_2_FORWARD );
	    $this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_3_PROCESS );
		$selectedTicketClass = $this->TicketClass_model->getSingleTicketClass( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		$allOtherClasses     = $this->TicketClass_model->getTicketClassesExceptThisUniqueID( $eventID, $ticketClassGroupID, $ticketClassUniqueID );
		if( $selectedTicketClass === false /*or $allOtherClasses === false*/ ) // 08FEB2012-2145, turned into comment condition somewhat ambiguous
		{					
			 $this->cancelBookingProcess();
			 $data['error'] = "CUSTOM";         
			 $data['theMessage'] = "INTERNAL SERVER ERROR<br/><br/>Cannot find DB records for the selected ticket class. Please contact the system administrator";
			 $this->load->view( 'errorNotice', $data );
			 return false;
		}
		$this->Slot_model->freeSlotsBelongingToClasses( $allOtherClasses );		// since we now don't care about these, free so.
		/* 
			Now set the uniqueID of the ticketclass.
			Change expiry time later to how long is ticket class holding time.
		 */
		$this->clientsidedata_model->setTicketClassUniqueID($ticketClassUniqueID, 3600);
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_3_FORWARD );
		$this->clientsidedata_model->setBookingProgressIndicator( 3 );
		if( $this->functionaccess->isActivityBookingTickets() )
			redirect( 'EventCtrl/book_step3_forward' );
		else
		if( $this->functionaccess->isActivityManageBooking() ){
			//$this->clientsidedata_model->changeSessionActivityDataEntry( 'ticketclass', 1 );
			$bookingNumber = $this->clientsidedata_model->getBookingNumber();			
			$bookingObj    = $this->Booking_model->getBookingDetails( $bookingNumber );
			$isTicketClassChanged = ( intval($bookingObj->TicketClassUniqueID) !== intval($ticketClassUniqueID) ) ? 1 : 0;
			$isShowtimeChanged    = ( intval($bookingObj->ShowingTimeUniqueID) !== intval($showtimeID) ) ? 1 : 0;			
			$appendThisActivityDataEntry = 'isTicketClassChanged='.$isTicketClassChanged.';isShowtimeChanged='.$isShowtimeChanged.';';	
			$this->clientsidedata_model->appendSessionActivityDataEntryLong(  $appendThisActivityDataEntry );
			
			redirect( 'EventCtrl/manageBooking_changeShowingTime_process2' );
		}else{
			die( "INVALID SESSION ACTIVITY: " );
		}
	}//book_step3()
			
	function book_step3_forward(){				
		$this->functionaccess->preBookStep3FWCheck( STAGE_BOOK_3_FORWARD );
		$this->load->view( 'book/bookStep3' );
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
				
		// get essential data
		$eventID              = $this->clientsidedata_model->getEventID();
		$showtimeID           = $this->clientsidedata_model->getShowtimeID();
		$ticketClassGroupID   = $this->clientsidedata_model->getTicketClassGroupID();
		$ticketClassUID       = $this->clientsidedata_model->getTicketClassUniqueID();		
		$slots                = intval( $this->clientsidedata_model->getSlotsBeingBooked() );		
		$guestUUIDs           = $this->clientsidedata_model->getTicketClassSlotUUIDsCookie( $ticketClassUID );	
		
		// check if accessible already.
		$this->functionaccess->preBookStep4PRCheck( $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUID, $slots, $guestUUIDs,  STAGE_BOOK_3_FORWARD );
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
					intval( $this->input->post( $identifier."accountNum" ) ),
					$this->input->post( $identifier."firstName" ),					
					$this->input->post( $identifier."middleName" ),					
					$this->input->post( $identifier."lastName" ),					
					$this->input->post( $identifier."gender" ),					
					$this->input->post( $identifier."cellphone" ),					
					$this->input->post( $identifier."landline" ),					
					$this->input->post( $identifier."email_01" ),
					( strlen( $uplb_tc_studNum ) > 8 ) ? $uplb_tc_studNum: NULL,
					( strlen( $uplb_tc_empNum ) > 8 ) ? $uplb_tc_empNum : NULL					
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
				/*$uplbConstituent[ $eachGuest->UUID ] = Array(
					'studentNumber'  => $eachGuest->studentNumber ,
					'employeeNumber' => $eachGuest->employeeNumber
				);*/
				$guest_StudentNumPair .= ($eachGuest->studentNumber.'_' );
				$guest_EmpNumPair .= ($eachGuest->employeeNumber.'_' );
			}
			
		}	
		
		// remove trailing underscores
		$guest_StudentNumPair = substr( $guest_StudentNumPair, 0, strlen($guest_StudentNumPair)-1 );	
		$guest_EmpNumPair  = substr( $guest_EmpNumPair, 0, strlen($guest_EmpNumPair)-1 );
		
		// now set the bookingNumber for cookie access		
		$this->clientsidedata_model->setBookingNumber( $bookingNumber );
		
		// Set payment deadline date and time 
		$this->clientsidedata_model->setPaymentDeadlineDate( $bookingPaymentDeadline["date"] );
		$this->clientsidedata_model->setPaymentDeadlineTime( $bookingPaymentDeadline["time"] );
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD );		// our activity tracker
		$this->clientsidedata_model->setBookingProgressIndicator( 4 );
		/*
			Now decide where to go next.
			If any of the strings that collect specified student/employee number aren't blank, then
			go to associateclasstobooking.
		*/
		//echo var_dump($guest_StudentNumPair);
		//echo var_dump( $guest_EmpNumPair );
		//die();
		if( strlen( $guest_StudentNumPair ) > 0  or strlen( $guest_EmpNumPair ) > 0 ) 
		{			
			$this->clientsidedata_model->setUPLBConsStudentNumPair( $guest_StudentNumPair );
			$this->clientsidedata_model->setUPLBConsEmpNumPair( $guest_EmpNumPair );
			
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_CLASS_1_FORWARD );		// our activity tracker
			redirect( 'AcademicCtrl/associateClassToBooking' );
		}else{
			redirect( 'EventCtrl/book_step4_forward' );
		}
	}//book_step4
			
	function book_step4_forward(){
			
		$eventID              = $this->clientsidedata_model->getEventID();
		$showtimeID           = $this->clientsidedata_model->getShowtimeID();		
		$bookingNumber        = $this->clientsidedata_model->getBookingNumber();		
		$isShowtimeChanged 	  = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isShowtimeChanged' ));
		$isTicketClassChanged = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isTicketClassChanged' ));
		$isComingFromTicketClass;
						
		$this->functionaccess->preBookStep4FWCheck( STAGE_BOOK_4_FORWARD );		// Access validity indicator		
		$this->seatmaintenance->cleanDefaultedSeats( $eventID, $showtimeID );
		
		$data['guests'] 				  = $this->Guest_model->getGuestDetails( $bookingNumber );
		$data['manageBooking_chooseSeat'] = $this->functionaccess->isActivityManageBookingAndChooseSeat();
		
		if( $data['manageBooking_chooseSeat'] )
		{
			/*
				This means user already booked and why he is on this page is that he is changing
				his booking - changing seats.
			*/						
			$data['guestSeatDetails'] = $this->seatmaintenance->getExistingSeatData_ForManageBooking(
				$data['guests'],
				$eventID,
				$showtimeID,
				$isTicketClassChanged
			);
		}
		$data['isTicketClassChanged'] = $isTicketClassChanged;
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
		$billingInfo     		   = NULL;		
		$eventID 		           = $this->clientsidedata_model->getEventID();
		$showtimeID 	           = $this->clientsidedata_model->getShowtimeID();
		$slots 			           = intval( $this->clientsidedata_model->getSlotsBeingBooked() );
		$bookingNumber 	 		   = $this->clientsidedata_model->getBookingNumber();
		$bookInstanceEncryptionKey = $this->clientsidedata_model->getBookInstanceEncryptionKey();
		
		// access validity check
		$this->functionaccess->preBookStep5PRCheck( $eventID, $showtimeID, $slots, $bookingNumber, STAGE_BOOK_4_FORWARD );		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_PROCESS );
						
		/*
			For each seat submitted (chosen by the user), get its visual representation
			and mark it as assigned
		*/
		for( $x = 0; $x < $slots; $x++ )
		{
			$seatMatrix 		  = $this->input->post( "g".($x+1)."_seatMatrix" );
			$seatMatrix_tokenized = explode( '_', $seatMatrix );
			$guestUUID 			  = $this->input->post(  "g".($x+1)."_uuid" );
			$sendSeatInfoToView[ $guestUUID ] = $this->Seat_model->getVisualRepresentation( 
				$seatMatrix_tokenized[0], 
				$seatMatrix_tokenized[1],
				$eventID,
				$showtimeID
			);			
			$this->Seat_model->markSeatAsAssigned(
				$eventID,
				$showtimeID,
				$seatMatrix_tokenized[0], 
				$seatMatrix_tokenized[1]
			);
			$this->Guest_model->assignSeatToGuest( 
				$guestUUID, $seatMatrix_tokenized[0], $seatMatrix_tokenized[1] 
			);			
		}
		$billingInfo  = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$purchases    = $billingInfo[ AKEY_UNPAID_PURCHASES_ARRAY ];
		$totalCharges = $billingInfo[ AKEY_AMOUNT_DUE ];
		$purchaseCount = count( $purchases );
		$this->encryptPurchaseCookies( $bookInstanceEncryptionKey, $bookingNumber, $purchases );
		$this->encryptSeatVisualCookie( $bookInstanceEncryptionKey, $sendSeatInfoToView );
		
		$this->clientsidedata_model->setPurchaseCount( $purchaseCount );
		$this->clientsidedata_model->setPurchaseTotalCharge( $totalCharges );
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );		
		if( $totalCharges === FREE_AMOUNT )		
			redirect( 'EventCtrl/book_step6' );
		else			
			$this->clientsidedata_model->setBookingProgressIndicator( 5 );
			redirect( 'EventCtrl/book_step5_forward' );
	}//book_step5(..)
	
	function book_step5_forward()
	{
		$bookInstanceEncryptionKey;		
		$bookingNumber             = $this->clientsidedata_model->getBookingNumber();
		$eventID                   = $this->clientsidedata_model->getEventID();
		$showtimeID                = $this->clientsidedata_model->getShowtimeID();
		$slots 			           = intval( $this->clientsidedata_model->getSlotsBeingBooked() );
		$totalCharges 			   = floatval( $this->clientsidedata_model->getPurchaseTotalCharge() );
		$bookInstanceEncryptionKey = $this->clientsidedata_model->getBookInstanceEncryptionKey();
		
		//access validity check
		if( !$this->functionaccess->preBookStep5FWCheck( $eventID, $showtimeID, $slots, $bookingNumber, $totalCharges, STAGE_BOOK_5_FORWARD ) ) return false;		
				 																		
		//	Decoding purchase identifiers		
		$decodingPurchaseCookieObj = $this->decodePurchaseCookie_And_GetObjs( $bookInstanceEncryptionKey );		
		if( $decodingPurchaseCookieObj['boolean'] )
		{
			$data['purchases'] = $decodingPurchaseCookieObj['textStatusFriendly'];
		}else{
			//$this->cancelBookingProcess();
			echo "Booking process *should BE* cancelled.<br/><br/>";
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
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent( $eventID, $showtimeID, FALSE );
		$data['guests'] 		 = $this->Guest_model->getGuestDetails( $bookingNumber );		
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
		
		$bookingNumber             = $this->clientsidedata_model->getBookingNumber();
		$eventID                   = $this->clientsidedata_model->getEventID();
		$showtimeID                = $this->clientsidedata_model->getShowtimeID();		
		$bookInstanceEncryptionKey = $this->clientsidedata_model->getBookInstanceEncryptionKey();
		$slots 			           = $this->clientsidedata_model->getSlotsBeingBooked();
		$totalCharges 			   = $this->clientsidedata_model->getPurchaseTotalCharge();
		$ticketClassUniqueID	   = $this->clientsidedata_model->getTicketClassUniqueID();
		
		/*
			If total charges is zero, then payment mode unique_id is 0 (factory default - auto
			confirmation since free), else, the one sent by post.
						
		*/
		$paymentChannel = ( is_float( $totalCharges ) and $totalCharges === FREE_AMOUNT ) ? FACTORY_AUTOCONFIRMFREE_UNIQUEID : intval($this->input->post( 'paymentChannel' ) ) ;		
		
		// access validity check				
		$this->functionaccess->preBookStep6PRCheck( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots, STAGE_BOOK_5_FORWARD );
		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_PROCESS );		
		// serialize guest UUIDs
		$clientUUIDs = explode( "_" , $this->input->post( $this->clientsidedata_model->getTicketClassSlotUUIDsCookie( $ticketClassUniqueID ) ) );
		// to be accessed in forward page
		$this->clientsidedata_model->setPaymentChannel( $paymentChannel );						   
		$this->Payment_model->setPaymentModeForPurchase( $bookingNumber, $paymentChannel, NULL );		
		$paymentChannel_obj      =  $this->Payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );
		$eventObj                = $this->Event_model->getEventInfo( $eventID );
		$data['guests']          = $this->Guest_model->getGuestDetails( $bookingNumber );
		$data['singleChannel']   = $paymentChannel_obj;
		$data['paymentChannels'] = $this->Payment_model->getPaymentChannelsForEvent( 
			$eventID, 
			$showtimeID,
			( $totalCharges === FREE_AMOUNT )
		);
		
		//	Decoding seat visual cookie		
		$decodingSeatVisualsCookieObj = $this->decodeSeatVisualCookie( $bookInstanceEncryptionKey );
		if($decodingSeatVisualsCookieObj['boolean'] )
		{
			$data['seatVisuals'] = $decodingSeatVisualsCookieObj['textStatusFriendly'];
		}else{
			die( var_dump( $decodingSeatVisualsCookieObj ) );
		}		
						
		if( $totalCharges === FREE_AMOUNT )
		{
			$processPaymentResultArr = $this->bookingmaintenance->processPayment( $bookingNumber );
			if( $processPaymentResultArr['boolean'] )
				$this->confirmSlotsOfThisBooking( $bookingNumber );
			else{				
				// LOL! Free amount nga eh error pa! HAHAHA. But just in case.				
				$data = $this->bookingmaintenance->assembleErrorPaymentNotification( $processPaymentResultArr['status']."|".$processPaymentResultArr['message'] );
				$this->load->view('errorNotice', $data );
				// set again to be able to access  payment modes page
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );
				return false;
			}
		}else{
			/* 
				Payment is needed 
				It seems marking this as pending bla is redundant?
			*/
			$this->Booking_model->markAsPendingPayment( $bookingNumber, "NEW" );
			foreach( $data['guests'] as $eachGuest )
			{
				$slotAssignedObj = $this->Slot_model->getSlotAssignedToUser( $eachGuest->UUID );
				$this->Slot_model->setSlotAsPendingPayment( $slotAssignedObj->UUID );
			}
			/*  Try PayPal payment: 
				Factory Setting: UniqueID is 2
				Redirect to PayPal processing too.
			*/
			if( $paymentChannel_obj->UniqueID == FACTORY_PAYPALPAYMENT_UNIQUEID )
			{
				/*
					Cookie data for Paypal Separated by pipes:
					<BOOKING-NUMBER>|<BASE-CHARGE>|<PAYPAL-FEE-TOTAL>|<"CHARGE DESCRIPTION">|<merchantemail>|<testmode>
				*/									
				$paypalTotal = floatval($totalCharges * PAYPAL_FEE_PERCENTAGE) + PAYPAL_FEE_FIXED;
				$paypalTotal =  round( $paypalTotal , 2 );
				$chargeDescriptor = $slots." Ticket(s) for ".$eventObj->Name." ordered via The UPLB Express Ticketing System";
				$this->clientsidedata_model->setPaypalAccessible();
				$this->clientsidedata_model->setDataForPaypal( $bookingNumber."|".$totalCharges."|".$paypalTotal."|".$chargeDescriptor );
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_PAYMENTPROCESSING );
				redirect( 'paypal/process' );
			}
		}
		/*
			Try to send email.
		*/
		foreach( $data['guests'] as $singleGuest)
		{
			$msgBody = "";
			$this->email_model->initializeFromSales( TRUE );
			
			$this->email_model->from( 'DEFAULT', 'DEFAULT');
			$this->email_model->to( $singleGuest->Email ); 						

			$this->email_model->subject('Show Itinerary Receipt ' . $bookingNumber );
			$msgBody = 'Your booking is pending.';
			$msgBody .= 'Kang song dae guk.<br/>';
			$msgBody .= 'Kim jong il\r\n';
			$msgBody .= 'Kim jong un\n';
			$msgBody .= 'We are in the process of starting our email module so no more info provided ont this mail. HAHAHA.';	
			$this->email_model->message( $msgBody );
			
			$this->email_model->send();			
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_FORWARD ); // our activity tracker
			$this->clientsidedata_model->setBookingProgressIndicator( 6 );
		}
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
		
		$bookingNumber             = $this->clientsidedata_model->getBookingNumber();
		$eventID                   = $this->clientsidedata_model->getEventID();
		$showtimeID                = $this->clientsidedata_model->getShowtimeID();		
		$bookInstanceEncryptionKey = $this->clientsidedata_model->getBookInstanceEncryptionKey();
		$totalCharges 			   = $this->clientsidedata_model->getPurchaseTotalCharge();
		$paymentChannel 		   = ( $totalCharges === FREE_AMOUNT ) ? FACTORY_AUTOCONFIRMFREE_UNIQUEID : $this->clientsidedata_model->getPaymentChannel();
		$slots 			           = $this->clientsidedata_model->getSlotsBeingBooked();
		
		// access validity check				
		$this->functionaccess->preBookStep6FWCheck( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots, STAGE_BOOK_6_FORWARD );
		
		$paymentChannel_obj    = $this->Payment_model->getSinglePaymentChannel( $eventID, $showtimeID, $paymentChannel );		
		$data['singleChannel'] = $paymentChannel_obj;
		$data['guests']        = $this->Guest_model->getGuestDetails( $bookingNumber );
		
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
		}
		//	Decoding seat visual cookie		
		$decodingSeatVisualsCookieObj = $this->decodeSeatVisualCookie( $bookInstanceEncryptionKey );
		if($decodingSeatVisualsCookieObj['boolean'] )
			$data['seatVisuals'] = $decodingSeatVisualsCookieObj['textStatusFriendly'];
		else{
			echo "<h1>Notice</h1>";
			echo "<p>You have seen this most probably because you have clicked the<br/>";
			echo "refresh button on your browser. This is not supported. Please go to the";
			echo "\"Manage Booking\" section in the homepage to view the details of your booking";
			echo "</p>";
			die();
		}
		if( $paymentChannel_obj->Type == "COD" )
		{			
			$this->load->view( 'book/bookStep6_COD', $data);
		}else
		if( $paymentChannel_obj->Type == "ONLINE" )
		{										
			/*
				If online payment worked, then booking should not have any more charges as evidenced
				by return of BOOLEAN FALSE of getUnpaidPurchases
			*/				
			if( $this->Payment_model->getUnpaidPurchases( $bookingNumber ) === FALSE )
			{
				$this->load->view( 'confirmReservation/confirmReservation02-free', $data );						
			}else{
				// set again to be able to access  payment modes page
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );
				echo "<h1>Notice</h1>";
				echo "<p>There is something fishy with your online payment.<br/><br/> Please use other payment methods.(Please don't refresh page else error).</p>";
				echo "<br/>";
				echo '<a href="'.base_url().'EventCtrl/book_step5_forward">Go back to Payment modes</a>';
			}			
		}else{
			$this->clientsidedata_model->updateSessionActivityStage( -1 );			
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
		
		$accountNum    = $this->clientsidedata_model->getAccountNum();
		$bookingNumber = $this->input->post( 'bookingNumber' );
		if( !$this->Booking_model->isBookingUnderThisUser( $bookingNumber , $accountNum ) )
		{
			echo "ERROR_NO-PERMISSION_This booking is not under you.";
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
				
		$sessionActivity    = $this->clientsidedata_model->getSessionActivity();
		$eventID            = $this->clientsidedata_model->getEventID();
		$ticketClassGroupID = $this->clientsidedata_model->getTicketClassGroupID();
		$bookingNumber      = $this->clientsidedata_model->getBookingNumber();
		
		//access validity check
		//$this->functionaccess->preCancelBookingProcesss( $eventID, $ticketClassGroupID, STAGE_BOOK_2_PROCESS );	
		$bookingStage = $this->clientsidedata_model->getSessionActivityStage();
		if( $bookingStage < STAGE_BOOK_3_PROCESS )
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
		}else{			
			/*  Review 23APR2012-0314 - Why is this here?
				if( $this->functionaccess->isActivityManageBooking() )
			*/ 
			$this->bookingmaintenance->deleteBookingTotally_andCleanup( $bookingNumber, NULL );
		}// end if( $bookingStage < STAGE_BOOK_3_FORWARD )
		$this->clientsidedata_model->deleteBookingCookies();		
		if( !$this->input->is_ajax_request() ) redirect('/');
		else		
			return true;
	}//cancelBookingProcess
			
	function confirm()
	{
		/*
			Created 22FEB2012-2157
		*/
		$this->clientsidedata_model->setSessionActivity( CONFIRM_RESERVATION, STAGE_CONFIRM_1_FORWARD );
		$this->load->view( 'confirmReservation/confirmReservation01' );
	}
	
	function confirm_step2()
	{
		/*
			Created 22FEB2012-2157
		*/
		if( !$this->input->is_ajax_request() ) redirect('/');
		
		$bNumber         = $this->input->post( 'bookingNumber' );		
		// access validity function
		$preCheck = $this->functionaccess->preBookCheckAJAXUnified( Array( $bNumber ), false, STAGE_CONFIRM_1_FORWARD );		
		if( strpos( $preCheck, "ERROR" ) === 0 )
		{   /* 
				This outputs errors like this is not accessible to non-event mgr users.. bla bla.		
			*/
			$breakThem = explode('_', $preCheck );
			echo $this->MakeXML_model->XMLize_AJAX_Response( 
					"error", "error", "GENERIC_ERROR", 0, $breakThem[1], ""  //???
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
			if( $this->Booking_model->isBookingExpired( $bNumber ) )
			{
				echo $this->MakeXML_model->XMLize_AJAX_Response( 
					"error", "deadline lapsed", "BOOKING_DEADLINE_LAPSED", 0, "The deadline of payment/confirmation for the specified booking has passed and as such slots and seats are now forfeited.", ""  //1005
				);
				return false;
			}
			$guestDetails   = $this->Guest_model->getGuestDetails( $bNumber );
			$bookingDetails = $this->Booking_model->getBookingDetails( $bNumber );
			$this->setBookingCookiesOuter( 
				$bookingDetails->EventID, 
				$bookingDetails->ShowingTimeUniqueID,
				count( $guestDetails ), 
				$bNumber 
			);
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_CONFIRM_2_FORWARD );
			echo $this->MakeXML_model->XMLize_AJAX_Response( 
					"okay", "proceed", "BOOKING_CONFIRM_CLEARED", 0, "The booking is cleared to undergo confirmation.", ""  //1006
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
		$bNumber 		  = $this->clientsidedata_model->getBookingNumber();
		$data			  = Array();
		$paymentInstances = Array();
		
		
		//access validity check
		$this->functionaccess->preConfirmStep2FWCheck( $bNumber, STAGE_CONFIRM_2_FORWARD );
		$this->clientsidedata_model->setBookingNumber( $bNumber );
		$bookingDetails   = $this->Booking_model->getBookingDetails( $bNumber );		
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bNumber );		
		foreach( $billingInfoArray as $key => $value ) $data[ $key ] = $value;
		
		$data[ 'guests' ]             = $this->Guest_model->getGuestDetails( $bNumber  );
		$data[ 'seatVisuals' ] 		  = $this->getSeatRepresentationsOfGuests( 
			$bookingDetails->EventID,
			$bookingDetails->ShowingTimeUniqueID,
			$data['guests'],
			$bookingDetails->TicketClassGroupID,
			$bookingDetails->TicketClassUniqueID
		);
		if( $data[ AKEY_UNPAID_PURCHASES_ARRAY ] === false ){			
			$data['theMessage'] = "There are no pending payments for this booking/It has been confirmed already."; //1004
			$data['redirect'] = FALSE;
			$data['noButton'] = TRUE;
			$this->load->view( 'successNotice', $data );
			return true;
		}		
		$data['singleChannel']  =  $this->Payment_model->getSinglePaymentChannel(
			$this->clientsidedata_model->getEventID(),
			$this->clientsidedata_model->getShowtimeID(),
			intval($data['unpaidPurchasesArray'][0]->Payment_Channel_ID )
		);
		/*
			For all the currenly unpaid purchases, the earliest among the deadlines are found
			in the first entry/index.
		*/
		$this->clientsidedata_model->setPaymentDeadlineDate( $data['unpaidPurchasesArray'][0]->Deadline_Date );
		$this->clientsidedata_model->setPaymentDeadlineTime( $data['unpaidPurchasesArray'][0]->Deadline_Time );
		
		$this->load->view( 'confirmReservation/confirmReservation02', $data );
	}//confirm_step2_forward()
	
	function confirm_step3()
	{		
		if( !$this->input->is_ajax_request() ) redirect( '/' );				
		$bNumber    = $this->clientsidedata_model->getBookingNumber();
		$accountNum = $this->clientsidedata_model->getAccountNum();
		
		//access validity check		
		if(!$this->functionaccess->preConfirmStep3PRCheck( $bNumber, $accountNum, STAGE_CONFIRM_2_FORWARD ) ) die();
						
		if( $this->confirmSlotsOfThisBooking( $bNumber ) )
		{
			$result = $this->bookingmaintenance->processPayment( $bNumber );
			echo $result['status']."_".$result['message'];		
			if( $result['boolean'] )
			{
				$this->TransactionList_model->createNewTransaction(
					$accountNum,
					'PAYMENT_RECEIPT',
					'BOOKING_CONFIRMATION',
					$bNumber,
					'No comment!',
					'WIN5',			
					""
				);
			}
		}else{
			echo "ERROR_UNKNOWN";			
			$this->TransactionList_model->createNewTransaction( 
				$accountNum,
				'PAYMENT_RECEIPT',
				'UNKNOWN_FAILURE',
				$bNumber,
				'Secret!',
				'WIN5',			
				""
			);		
		}
		$this->clientsidedata_model->updateSessionActivityStage( -1 );
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
	
	function modify_select()
	{		
		/* DEPRECATED
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
		/*
			Created 30DEC2011-1053
		*/
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
	}//getForCheckInShowingTimes
	
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
		$okayBookings = $this->Booking_model->getAllBookings( $this->session->userdata('accountNum') );
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
		$this->clientsidedata_model->setSessionActivity( "MANAGE_BOOKING", 0, "showingtime=0;ticketclass=0;seat=0;newcost=0;" );
		$this->load->view( 'manageBooking/manageBooking01', $data );
	}//manageBooking(..)
	
	function manageBooking_changeSeat()
	{
		/*
			Created 02MAR2012-1257
		*/
		$bookingNumber;
		$bookingObj;
		$guestCount;
		$ticketClassUniqueID =  $this->clientsidedata_model->getTicketClassUniqueID();
		
		$this->functionaccess->__reinit();
		$isComingFromTicketClass = ( intval($this->clientsidedata_model->getSessionActivityDataEntry( 'ticketclass' ))===1 );
		log( 'DEBUG', 'Reached  manageBooking_changeSeat(). Is coming from ticket class? '.intval($isComingFromTicketClass) );
		if(!$this->clientsidedata_model->changeSessionActivityDataEntry( 'seat', 1 )){
			die('INTERNAL-SERVER-ERROR: Activity data cookie manipulated. Please start over by refreshing Manage Booking page.');
		}
		/*
			If this function is called/redirect to but came from ticket class, get booking  number from cookies, else
			this was directly called from the manageBooking main page, so get the booking number via POST data.
		*/
		$bookingNumber = ($isComingFromTicketClass) ? $this->clientsidedata_model->getBookingNumber() : $this->input->post( 'bookingNumber' );
		if( $bookingNumber === false )
		{
			die( 'ERROR_DATA-NEEDED: BOOKING NUMBER NOT SUPPLIED.' );
		}
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false )
		{
			die( 'Booking does not exist' );
		}		
		if( $isComingFromTicketClass === false )
		{	
			$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, STAGE_BOOK_4_FORWARD );
			$guestObj = $this->Guest_model->getGuestDetails( $bookingNumber ); 
			$guestCount = count( $guestObj );
			$this->setBookingCookiesOuter( 
				$bookingObj->EventID, 
				$bookingObj->ShowingTimeUniqueID, 
				$guestCount, 
				$bookingNumber 
			);		
			/*
				Ticket class uniqueID is still -1 when setBookingCookiesOuter(..) was called
				so now fill it with correct value
			*/					
			$this->clientsidedata_model->setTicketClassUniqueID( $bookingObj->TicketClassUniqueID, 3600 );
			$this->clientsidedata_model->setSlotsBeingBooked( count($guestObj), 3600 );
		}else{
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD ); 
		}		
		$this->clientsidedata_model->changeSessionActivityDataEntry( 'seat', 1 );
		$this->session->set_userdata( '_mbook_fromtc' , intval($isComingFromTicketClass) );		
		$this->clientsidedata_model->setBookingProgressIndicator( 4 );
		redirect( 'EventCtrl/book_step4_forward' );
	}//manageBooking_changeSeat
	
	function manageBooking_changeSeat_process()
	{
		$guestCount = $this->clientsidedata_model->getSlotsBeingBooked();
		$eventID = $this->clientsidedata_model->getEventID();
		$showtimeID = $this->clientsidedata_model->getShowtimeID();
		$finishImmediatelySessData = $this->session->userdata( 'manageBooking_finishImmediately ' );
		$isComingFromTicketClass = ( intval($this->clientsidedata_model->getSessionActivityDataEntry( 'ticketclass' ))===1 );
		$isShowtimeChanged 	  = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isShowtimeChanged' ));
		$isTicketClassChanged = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isTicketClassChanged' ));
		$processedSeats = 0;
		$newSeatsUUID = "";
		$newSeatsMatrixInfo = "";
		
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
		// CODE-MISSING: DATABASE CHECKPOINT
		$seatInfoArray = Array();
		
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
			$userHasOldSeat = (is_array( $seatMatrix_old_tokenized) and count( $seatMatrix_old_tokenized ) == 2 );
			$userDoesNotHaveOldSeat = (!$userHasOldSeat and (intval($seat_old) == 0) );
			if( (!is_array( $seatMatrix_old_tokenized) or 
				count( $seatMatrix_old_tokenized ) != 2 ) and 
				!$userDoesNotHaveOldSeat
			)
			{
				// CODE-MISSING: DATABASE ROLLBACK
				die( 'Invalid seat data' );
			}
			/*
				If old is not the same as the intended input for the seat chosen, then
				do manipulation.
			*/
			if( is_array($seatMatrix_tokenized) and count( $seatMatrix_tokenized ) == 2 )
			{
				if( $seat_old != $seat )
				{
					if( !$isComingFromTicketClass )
					{	
						/*
							User just went straight to change seat feature - no changing of ticket class involved.
							So no checking for charges, just shoot away.
						*/
						$this->Seat_model->markSeatAsAssigned(	// new seat
							$eventID,
							$showtimeID,
							$seatMatrix_tokenized[0], 
							$seatMatrix_tokenized[1]
						);
						if( $userHasOldSeat ) $this->Seat_model->markSeatAsAvailable(	//old seat
							$eventID,
							$showtimeID,
							$seatMatrix_old_tokenized[0], 
							$seatMatrix_old_tokenized[1]
						);
						// change seat in guest's record in `event_slot`
						$this->Guest_model->assignSeatToGuest( 
							$guestUUID, 
							$seatMatrix_tokenized[0], 
							$seatMatrix_tokenized[1],
							$eventID,
							$showtimeID
						);
						$processedSeats++;
					}else{
						$bookingNumber = $this->clientsidedata_model->getBookingNumber();
						$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
						/* 12MAR2012-2138, 
							Singled-out when testing UPG-TC > NOT-FREE > CHANGE_SEAT > YES
								and found out that app frees the seat of the current slot reserved
								when in fact it should not
								
						if( $userHasOldSeat )$this->Seat_model->markSeatAsAvailable(	//old seat
							$eventID,
							//$bookingObj->ShowingTimeUniqueID,
							$showtimeID,
							$seatMatrix_old_tokenized[0], 
							$seatMatrix_old_tokenized[1]
						);*/
						$this->Seat_model->markSeatAsPendingPayment(
							$eventID, 
							$showtimeID,
							$seatMatrix_tokenized[0], 
							$seatMatrix_tokenized[1],
							"2012-03-10 10:00:00"		// adjust date later!!!
						);
						/*
							Write the info of the guestUUIDs and corresponding new seats, for use
							in managebooking_confirm later
						*/						
						
					}
					$seatInfoArray[ $guestUUID ] = Array(
						'Matrix_x' => $seatMatrix_tokenized[0],
						'Matrix_y' => $seatMatrix_tokenized[1]
					);
				}//if
			}
		}//for
		// write in cookies, which guest UUIDs have their seats changed, and the new seat matrix indicators		
		$this->clientsidedata_model->updateSessionActivityStage( 5 );
		$this->assembleForWritingDataOfNewSeats( $seatInfoArray );
		if( $isComingFromTicketClass )
		{																		
			log_message('FINISHED manageBooking_changeSeat_process: Redirecting to "managebooking_confirm"');
			redirect('EventCtrl/managebooking_confirm');
		}
		
		// CODE-MISSING: DATABASE COMMIT		
		//die( var_dump( $_POST ) );
		$data[ 'theMessage' ] = ($processedSeats == 0) ? "No changes to seats have been made." : "The seats have been changed.";
		$data[ 'redirect' ] = FALSE;
		$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
		$data[ 'defaultAction' ] = 'Manage Booking';
		$this->postManageBookingCleanup();
		$this->load->view( 'successNotice', $data );				
	}//manageBooking_changeSeat_process
		
	function manageBooking_changeShowingTime()
	{
		/*
			Created 03MAR2012-1613
		*/
		//die( var_dump( $_POST ) );
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$bookingObj;
		$guestCount;
		$eventObj;
		$configuredEventsInfo = Array();
		
		if( $bookingNumber === false )
		{
			die( 'ERROR_DATA-NEEDED' );
		}
		$this->clientsidedata_model->changeSessionActivityDataEntry( 'showingtime', 1 );
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 		
		if( $bookingObj === false )
		{
			die( 'Booking does not exist' );
		}
		$eventObj = $this->Event_model->getEventInfo( $bookingObj->EventID );
		$configuredEventsInfo[] = $eventObj;
		$data['configuredEventsInfo'] = $configuredEventsInfo;		
		$data['existingShowtimeID'] = $bookingObj->ShowingTimeUniqueID;
		$data['guestCount'] = count( $this->Guest_model->getGuestDetails( $bookingNumber ) );
		$data['bookingNumber'] = $bookingNumber;
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
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_1_FORWARD );
			$this->clientsidedata_model->setBookingProgressIndicator( 1 );
			$data['currentShowingTime'] = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
			$this->load->view( 'manageBooking/manageBooking02_selectShowingTime.php', $data );
		}		
	}//manageBooking_changeShowingTime
			
	function manageBooking_changeShowingTime_process()
	{
		/*
			Created 04MAR2012-1339
		*/	
		$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
		$eventID = $this->input->post( 'events' );
		$showtimeID = $this->input->post( 'showingTimes' );
		$slots = $this->input->post( 'slot' );
		$bookingNumber = $this->input->post( 'bookingNumber' );
		//die( var_dump( $_POST ) );
		// ACCESS validity check
		if( ( $sessionActivity[0] == "MANAGE_BOOKING" and $sessionActivity[1] === 1) === FALSE or
			$eventID === false or $showtimeID === false or $slots === false or  $bookingNumber === false 
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
			die( 'Cannot locate the booking number specified.' );
			return false;
		}
		$this->book_step2( $bookingNumber );
	}//manageBooking_changeShowingTime_processs(..)
	
	function manageBooking_changeShowingTime_process2()
	{
		/*
			Created 04MAR2012-1455
		*/			
		$bookingNumber;
		$sessionActivity 			= $this->clientsidedata_model->getSessionActivity();
		$guestUUIDs_SeatUnavailable = Array();
		
		$isShowtimeChanged;
		$eventID;
		$oldShowtimeID;
		$oldTicketClassGroupID;
		$oldTicketClassUniqueID;
		$newShowtimeID 				= $this->clientsidedata_model->getShowtimeID();		
		$newTicketClassGroupID  	= $this->clientsidedata_model->getTicketClassGroupID();
		$newTicketClassUniqueID 	= $this->clientsidedata_model->getTicketClassUniqueID();
		$oldTicketClassObj;
		$newTicketClassObj;
		$guestCount;
		$guestObj;
		// to be used in storing entries in purchase table
		$oldShowtimeObj;
		$newShowtimeObj;
		$oldShowtimeChargeDescriptor = "";
		$newShowtimeChargeDescriptor = "";			
		if( ( $sessionActivity[0] == "MANAGE_BOOKING"  and  $sessionActivity[1] == STAGE_BOOK_3_FORWARD ) === FALSE )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
						
		$bookingNumber = $this->clientsidedata_model->getBookingNumber();				
		/*
			Get booking details: compare current ticket class. If just equal, redirect to
			the proper next page immediately. 
			This algorithm might be problematic in case of different ticket classes for
			different showing times.
		*/
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber ); 				
		$eventID = $bookingObj->EventID;
		$oldShowtimeID = $bookingObj->ShowingTimeUniqueID;
		$oldTicketClassGroupID = $bookingObj->TicketClassGroupID;
		$oldTicketClassUniqueID = $bookingObj->TicketClassUniqueID;
		
		$isShowtimeChanged 	  = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isShowtimeChanged' ));
		$isTicketClassChanged = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isTicketClassChanged' ));
		if( !$isShowtimeChanged and !$isTicketClassChanged )
		{			
			$data[ 'theMessage' ] = "No changes in ticket class or showing time detected. Your booking was not modified.";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl/manageBooking';
			$data[ 'defaultAction' ] = 'Manage Booking';
			$this->clientsidedata_model->setSessionActivity( MANAGE_BOOKING, 0 );
			$this->clientsidedata_model->deleteBookingCookies();			
			$this->load->view( 'successNotice', $data );
			return true;
		}	
		/*
			Now see if we have to have other charges.
		*/		
		$slots = $this->clientsidedata_model->getSlotsBeingBooked();			// get ticket class object first. It contains the prices.
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
		$newShowtimeID 			= $this->clientsidedata_model->getShowtimeID();	
		$bookingPaymentDeadline = $this->Event_model->getShowingTimePaymentDeadline( 
			$eventID,
			$newShowtimeID
		);
		
		if( $isTicketClassChanged )
		{
			/*
			As of 12MAR2012-00001 | Asking of question with regard to this is on-hold.
			Kasi naman di ba, dapat alam nila na bagong ticket class siyempre ibang seats yun.
			
			$changeSeatCaption = 1;
			$theMessage = "Since you have chosen a new ticket class which has a different seat allocation,";
			$theMessage .= " you have to choose new seat(s).";*/
			if( $isShowtimeChanged )
			{
				$this->Payment_model->createPurchase(
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					$slots,
					$slots * floatval( $newTicketClassObj->Price),
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
			}else{			
			}		
			$this->Payment_model->createPurchase(
				$bookingNumber,
				"UPGRADE",
				"To '".$newTicketClassObj->Name."' From '".$oldTicketClassObj->Name."' Class",
				$slots,
				$slots * floatval( $newTicketClassObj->Price),
				$bookingPaymentDeadline["date"],
				$bookingPaymentDeadline["time"]
			);			
			redirect( 'EventCtrl/manageBooking_changeSeat' );
		}else{
			//  $isTicketClassChanged is false.
			//  Arriving here, $isShowtimeChanged is true
			
			// check if seats with the same coordinates in the new showing time are still available
			$seatAvailabilityCheck = $this->isBookingSeatsAvailableNewShowtime( $bookingNumber, $newShowtimeID );
			if( $seatAvailabilityCheck['boolean'] === true )
			{									
				$guestObj = $this->Guest_model->getGuestDetails( $bookingNumber );			
				$this->immediatelyAssignSlotsAndSeats_MidManageBooking( 
					$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID, 
					$oldTicketClassUniqueID, $newShowtimeID, $newTicketClassUniqueID 
				);
				$this->Payment_model->createPurchase(
					$bookingNumber,
					"SHOWTIME_CHANGE",
					"To ".$newShowtimeChargeDescriptor." FROM ".$oldShowtimeChargeDescriptor,
					$slots,
					0,
					$bookingPaymentDeadline["date"],
					$bookingPaymentDeadline["time"]
				);
				$data['theMessage'] =  "Would you still like to change your seats in this new showing time? ";
				$data['theMessage'] .= "This can be done later though.";
				$data['yesURI'] = base_url().'EventCtrl/manageBooking_changeSeat';
				$data['noURI'] = base_url().'EventCtrl/manageBooking_confirm';
				$data['formInputs'] = Array( 
					'option_change_seat_notified' => '1'
				);
				$this->clientsidedata_model->updateSessionActivityStage( 3 );
				$this->load->view( 'confirmationNotice', $data );
				return true;
			}else{								
				$guestObj = $this->Guest_model->getGuestDetails_UUID_AsKey( $bookingNumber );
				/* Remove the guests whose seats are not available in the new slot since we 
					can't assign the their seats in the new showing time. 
				*/
				foreach(  $seatAvailabilityCheck['guest_no_seat'] as $eachSlot2 ) 
				{					
					if( in_array( $eachSlot2->UUID, $guestObj ) ) unset( $guestObj[$eachSlot2->UUID ] );
				}
				// Immediately assign the new available seats to the guests whose seats are still available.
				$this->immediatelyAssignSlotsAndSeats_MidManageBooking( 
					$guestObj, $eventID, $oldShowtimeID, $oldTicketClassGroupID, 
					$oldTicketClassUniqueID, $newShowtimeID, $newTicketClassUniqueID 
				);
				
				$changeSeatCaption = 2;
				$theMessage = "The seats of the following guests are not available in the new showing time";
				$theMessage .= " you have selected.<br/>";
				$theMessage2 = "<br/>Do you want to continue and select other seat(s) for these guests? Selecting No will cancel this process and rollback all changes.";			
				$tableProper = $this->assembleUnavailableSeatTableForManageBooking( $seatAvailabilityCheck['guest_no_seat'] );
				
				//	Assemble data for sending to view page.				
				$data['title'] = 'Oops, some technicality';
				$data['theMessage'] = $theMessage.$tableProper.$theMessage2;
				$data['yesURI'] = base_url().'EventCtrl/manageBooking_changeSeat';
				$data['noURI'] = base_url().'EventCtrl/manageBooking_cancel';
				$data['formInputs'] = Array( 
					'noMoreSeatSameTicketClassNotified' => '1',				
				);
				/*
					Some cookies needed for displaying output and monitoring progress.
				*/
				$this->clientsidedata_model->updateSessionActivityStage( 3 );
				$this->clientsidedata_model->appendSessionActivityDataEntry( 'changeSeatCaption', $changeSeatCaption );
				$this->load->view( 'confirmationNotice', $data );
				return false;
			}
		}// $isTicketClassChanged is false.
		
	}//manageBooking_changeShowingTime_processs2(..)
		
	function managebooking_cancel()
	{
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
	
	function sample1()
	{
		$data['customTitle'] = "Redirection";			
		$data['theMessage'] = "You are being redirected to Paypal<br/></br>Please wait...";
		$data['redirect'] = FALSE;
		$data['noButton'] = TRUE;
		$this->load->view( 'successNotice', $data );
	}
	
	function managebooking_confirm()
	{
		/*
			Created 08MAR2012-0936
		*/
			
		$bookingNumber = $this->clientsidedata_model->getBookingNumber();		
		$newSeatsUUID = $this->clientsidedata_model->getManageBookingNewSeatUUIDs();
		$newSeatsMatrix = $this->clientsidedata_model->getManageBookingNewSeatMatrix();
		$newTicketClassGroupID   = $this->clientsidedata_model->getTicketClassGroupID();
		$newShowtimeID 		 = $this->clientsidedata_model->getShowtimeID();
		$newTicketClassUniqueID  = $this->clientsidedata_model->getTicketClassUniqueID();
		
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		$oldShowtimeID = $bookingObj->ShowingTimeUniqueID;
		$oldTicketClassGroupID = $bookingObj->TicketClassGroupID;
		$oldTicketClassUniqueID = $bookingObj->TicketClassUniqueID;
		$bookingGuests = $this->Guest_model->getGuestDetails( $bookingNumber );
		$oldGuestSeatVisualRep = $this->getSeatRepresentationsOfGuests( 
			 $bookingObj->EventID, 
			 $bookingObj->ShowingTimeUniqueID,
			 $bookingGuests
		);
		$eventObj = $this->Event_model->getEventInfo( $bookingObj->EventID );
		$oldShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
		$newShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $newShowtimeID );

		$isShowtimeChanged 	  = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isShowtimeChanged' ));
		$isTicketClassChanged = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isTicketClassChanged' ));
		/*
			Now calculate purchases
		*/
		$paymentChannels = NULL;		
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );	
		if( $billingInfoArray['amountDue'] > 0 )
		{
			$paymentChannels = $this->Payment_model->getPaymentChannelsForEvent(
				$bookingObj->EventID, 
				$bookingObj->ShowingTimeUniqueID,
				FALSE
			);
		}
		/*
			Process the newSeats cookies.
		*/
		$newSeatsInfoArray = $this->assembleRelevantDataOfNewSeats( $bookingObj->EventID, $newShowtimeID, false );				
		$data['bookingNumber'] = $bookingObj->bookingNumber;
		$data['oldSeatVisuals'] = $oldGuestSeatVisualRep;
		$data['singleEvent'] = $eventObj;
		$data['guestCount'] = count( $bookingGuests );
		$data['guests'] = $bookingGuests;
		$data['currentShowingTime'] = $oldShowingTimeObj ;
		$data['newShowingTime'] = $newShowingTimeObj;
		$data['newSeatData'] = $newSeatsInfoArray;
		$data['unpaidPurchasesArray'] = $billingInfoArray['unpaidPurchasesArray'];
		$data['paidPurchasesArray'] = $billingInfoArray['paidPurchasesArray'];
		$data['unpaidTotal'] = $billingInfoArray['unpaidTotal'];
		$data['paidTotal'] = $billingInfoArray['paidTotal'];
		$data['amountDue'] = $billingInfoArray['amountDue'];
		$data['paymentChannels'] = $paymentChannels;
		$data['oldTicketClassName'] = $this->TicketClass_model->getSingleTicketClassName(
			$bookingObj->EventID, 
			$bookingObj->TicketClassGroupID, 
			$bookingObj->TicketClassUniqueID 
		);
		$data['newTicketClassName'] =$this->TicketClass_model->getSingleTicketClassName(
			$bookingObj->EventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID 
		);		
		$data['isShowtimeChanged'] = $isShowtimeChanged;
		$data['isTicketClassTheSame'] = !$isTicketClassChanged;
		$this->session->set_userdata( 'book_step' , 5 );	// for use in progress indicator
		$this->load->view( 'manageBooking/manageBookingConfirm', $data );		
	}//managebooking_confirm()
	
	function managebooking_finalize()
	{
		/*
			Created 08MAR2012-0952
		*/
		
		$bookingNumber 			 = $this->clientsidedata_model->getBookingNumber();
		$eventID				 =  $this->clientsidedata_model->getEventID();
		$oldShowingTimeID;
		$oldTicketClassGroupID;
		$oldTicketClassUniqueID;
		$newTicketClassGroupID   = $this->clientsidedata_model->getTicketClassGroupID();
		$newShowingTimeID 		 = $this->clientsidedata_model->getShowtimeID();
		$newTicketClassUniqueID  = $this->clientsidedata_model->getTicketClassUniqueID();
		$isComingFromTicketClass = ( intval($this->clientsidedata_model->getSessionActivityDataEntry( 'ticketclass' ))===1 );
		$isTicketClassChanged;
		$noPendingPayment;
		$paymentMode;
		
		//tokenize the slot UUIDs
		$newSlotsUUIDs 			= explode('_', $this->input->cookie( $newTicketClassUniqueID.'_slot_UUIDs' ) );
		
		$paymentMode 			= $this->input->post( 'paymentChannel' );
		$bookingObj 			= $this->Booking_model->getBookingDetails( $bookingNumber );
		$oldShowingTimeID		= $bookingObj->ShowingTimeUniqueID;
		$oldTicketClassGroupID 	= $bookingObj->TicketClassGroupID;
		$oldTicketClassUniqueID = $bookingObj->TicketClassUniqueID;
		$bookingGuests 			= $this->Guest_model->getGuestDetails( $bookingNumber );	
		$oldGuestSeatVisualRep 	= $this->getSeatRepresentationsOfGuests( $eventID, $oldShowingTimeID, $bookingGuests );	
		
		$eventObj = $this->Event_model->getEventInfo( $bookingObj->EventID );
		$oldShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $bookingObj->ShowingTimeUniqueID );
		$newShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $newShowingTimeID );
		$newSeatsInfoArray = $this->assembleRelevantDataOfNewSeats( $bookingObj->EventID, $newShowingTimeID, false );
		$isShowtimeChanged 	  = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isShowtimeChanged' ));
		$isTicketClassChanged = intval(  $this->clientsidedata_model->getSessionActivityDataEntry( 'isTicketClassChanged' ));
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$noPendingPayment = ( floatval($billingInfoArray['amountDue']) === 0.0 );
		
		if( $newSeatsInfoArray !== false )
		{
			foreach( $newSeatsInfoArray as $key => $value )
			{
				log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : '.$key." : ".$value['Matrix_x']."-".$value['Matrix_y']." ".$value['visual_rep'] );
			}
		}else{
			log_message('DEBUG', 'User did not choose a new seat at all.' );
		}
		log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : Ended log of new seats info' );				
		log_message( 'DEBUG', 'MANAGE_BOOKING|FINALIZE  : User does not have to pay for this change? '.intval( $noPendingPayment  ) );		
		/*
			End of log area.
		*/
		
		$x = 0;						
		foreach( $bookingGuests as $eachGuest )
		{	
								
			/*	
				Assign the new slot for the new showing time and/or ticket class
				(We still don't remove the old slot)
			*/				
			$this->Slot_model->assignSlotToGuest(
					$eventID,
					$newShowingTimeID,
					$newSlotsUUIDs[ $x ],
					$eachGuest->UUID
			);
			$this->Slot_model->setSlotAsBooked( $newSlotsUUIDs[ $x++ ] );
			$thisUserFormerSlotObj = $this->Slot_model->getSlotAssignedToUser_MoreFilter( 
				$eventID,
				$oldShowingTimeID,
				$oldTicketClassGroupID,
				$oldTicketClassUniqueID,
				$eachGuest->UUID 
			);
			if( $noPendingPayment )
			{				
				//	Create payment entry				
				$paymentID = $this->Payment_model->createPayment( 
					$bookingNumber,
					floatval($billingInfoArray['amountDue']),
					0
				);
				/*
					Set the unpaid purchases as paid.
				*/
				foreach($billingInfoArray['unpaidPurchasesArray'] as $unpaidPurchase )
				{
					$this->Payment_model->setAsPaid(
						$bookingNumber,
						$unpaidPurchase->UniqueID,
						$paymentID
					);
				}							
				/* 
					Since no pending payment, there's no way to fallback if not paid at the right time. 
					We now mark the former slot as available
				*/							
				$this->Slot_model->setSlotAsAvailable( $thisUserFormerSlotObj->UUID );							
				log_message('DEBUG ', 'OLD_SEAT_MARK_AS_AVAILABLE: '.$bookingObj->EventID.'_'.$bookingObj->ShowingTimeUniqueID );
				log_message('DEBUG ', 'OLD_SEAT_MARK_AS_AVAILABLE: '.$oldGuestSeatVisualRep[ $eachGuest->UUID ][ 'matrix_x' ].'-'.$oldGuestSeatVisualRep[ $eachGuest->UUID ][ 'matrix_y' ]);
				$this->Seat_model->markSeatAsAvailable( 
					$eventID, 
					$oldShowingTimeID,
					$oldGuestSeatVisualRep[ $eachGuest->UUID ][ 'matrix_x' ],
					$oldGuestSeatVisualRep[ $eachGuest->UUID ][ 'matrix_y' ],					
					""	
				);			
				if( isset( $newSeatsInfoArray[ $eachGuest->UUID ] ) )
				{
					// Meaning, a new seat was selected.
					log_message('DEBUG', 'NEW_SEAT_MARK_AS_ASSIGNED: '.$eachGuest->UUID." ".$newShowingTimeID." ".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ]."-".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ] );
					$this->Seat_model->markSeatAsAssigned( 
						$bookingObj->EventID, 
						$newShowingTimeID,
						$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ],
						$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ]					
					);
				}				
				$this->Booking_model->markAsPaid(  $bookingNumber );
			}else{
				/*
					Payment is pending.
				*/
				$this->Booking_model->markAsPendingPayment( $bookingNumber, "MODIFY" );
				// set the payment mode for the purchases
				foreach($billingInfoArray['unpaidPurchasesArray'] as $unpaidPurchase )
				{
					$this->Payment_model->setPaymentModeForPurchase(
						$bookingNumber,
						$paymentMode,
						$unpaidPurchase->UniqueID
					);
				}	
				/*
					Same with no payment pending - but here, we only mark seat as pending payment
					if the new seat data is set.
				*/
				if( isset( $newSeatsInfoArray[ $eachGuest->UUID ] ) )
				{
					$bookingPaymentDeadline = $this->Event_model->getShowingTimePaymentDeadline( 
						$eventID,
						$newShowingTimeID
					);
					log_message('DEBUG', 'NEW_SEAT_MARK_AS_PENDING_PAYMENT: '.$eachGuest->UUID." ".$newShowingTimeID." ".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ]."-".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ] );
					$this->Seat_model->markSeatAsPendingPayment(
						$eventID,
						$newShowingTimeID,
						$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ],
						$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ],
						$bookingPaymentDeadline['date']." ".$bookingPaymentDeadline['time']
					);
				}
			}
			/* 
				Come what may she believes and that faith is.. oh wait, we have to assign the new seat to the new slot
				If payment pending and payment period lapsed, some record of queries will just have to undo this.
				
				We have to perform isset(..) on the matrix info of the seats - aside from checking the non-existence of new seats,
				it is done to avoid overwriting the pre-assigned seats on the new showing time when a user changed showing time but
				same ticket class (in that case, the system automatically assigns the equal of the seat in the former showing time with respect to
				the new showing time if ever the seat in the showing time is still available ).
			*/						
			if( isset($newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ]) and isset( $newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ]) )
			{
				log_message('DEBUG', 'ASSIGNING_SEAT_TO_GUEST: '.$eachGuest->UUID." ".$newShowingTimeID." ".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ]."-".$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ] );
				$this->Guest_model->assignSeatToGuest(
					$eachGuest->UUID, 
					$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_x' ],
					$newSeatsInfoArray[ $eachGuest->UUID ][ 'Matrix_y' ],
					$eventID,
					$newShowingTimeID,
					$newTicketClassGroupID,
					$newTicketClassUniqueID
				);
			}
		}//foreach guest	
		
		$this->Booking_model->updateBookingDetails(
				$bookingNumber,
				$eventID,
				$newShowingTimeID,
				$newTicketClassGroupID,
				$newTicketClassUniqueID
		);
		$transactionID = $this->TransactionList_model->createNewTransaction(
			$this->session->userdata('accountNum'),
			'TICKET_CLASS_UPGRADE',
			'UPDATED_BOOKING_DETAILS',
			$bookingNumber,
			'Secret!',
			'WIN5',			
			Array(
				'oldShowingTime'		 => $oldShowingTimeID,
				'oldTicketClassGroupID'  => $oldTicketClassGroupID,
				'oldTicketClassUniqueID' => $oldTicketClassUniqueID,
				'newShowingTime' 		 => $newShowingTimeID,
				'newTicketClassGroupID'  => $newTicketClassGroupID,
				'newTicketClassUniqueID' => $newTicketClassUniqueID
			)
		);
		/*
			Update the `Comments` field of `purchases` with the transaction ID.
			This enables us to undo the changes if payment time lapses.
		*/
		foreach( $billingInfoArray['unpaidPurchasesArray'] as $unpaidPurchase )
		{
			$this->Payment_model->updatePurchaseComments(
				$bookingNumber, 
				$unpaidPurchase->UniqueID,
				'transaction='.strval( $transactionID )
			);
		}
		
		$this->clientsidedata_model->updateSessionActivityStage( 0 );				// activity indicator		
		$this->assembleDataForManageBookingPending_ViewDetais(						// Info Needed for forward page
			$paymentMode, $bookingObj->ShowingTimeUniqueID, $bookingObj->TicketClassGroupID,  $bookingObj->TicketClassUniqueID
		);	
		if( $noPendingPayment )
			echo 'Finalization completed.<br/><br/><a href="'.base_url().'EventCtrl/manageBooking">Back to Manage Booking</a>';
		else
			if( $paymentMode == FACTORY_PAYPALPAYMENT_UNIQUEID )
			{
				/*
					Cookie data for Paypal Separated by pipes:
					<BOOKING-NUMBER>|<BASE-CHARGE>|<PAYPAL-FEE-TOTAL>|"CHARGE DESCRIPTION">
				*/									
				$paypalTotal = floatval($billingInfoArray['amountDue'] * PAYPAL_FEE_PERCENTAGE) + PAYPAL_FEE_FIXED;				
				$chargeDescriptor = $slots." Ticket Class Upgrade ".$eventObj->Name." ordered via The UPLB Express Ticketing System";
				$this->clientsidedata_model->setPaypalAccessible();
				$this->clientsidedata_model->setDataForPaypal( $bookingNumber."|".$billingInfoArray['amountDue']."|".$paypalTotal."|".$chargeDescriptor );
				$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_PAYMENTPROCESSING );
				redirect( 'paypal/process' );
			}
			redirect( 'EventCtrl/managebooking_finalize_forward');
	}//managebooking_finalize
	
	function managebooking_finalize_forward()
	{		
		$bookingNumber 			 = $this->clientsidedata_model->getBookingNumber();
		$newTicketClassGroupID   = $this->clientsidedata_model->getTicketClassGroupID();
		$newShowingTimeID 		 = $this->clientsidedata_model->getShowtimeID();
		$newTicketClassUniqueID  = $this->clientsidedata_model->getTicketClassUniqueID();
		$oldTicketClassGroupID   ;
		$oldShowingTimeID 		 ;
		$oldTicketClassUniqueID  ;
		$paymentMode;
		
		if( $bookingNumber === false )
		{
			die("You have reached the maximum number of refreshes allowed for this page. Please go to Manage Booking instead. ");
		}
		
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		$eventObj = $this->Event_model->getEventInfo( $bookingObj->EventID );
		
		$bookingGuests = $this->Guest_model->getGuestDetails( $bookingNumber );	
		$eventID =  $bookingObj->EventID;
		$oldShowingTimeID = $this->clientsidedata_model->getSessionActivityDataEntry( 'oldShowtimeID' );
		$oldTicketClassGroupID 	= $this->clientsidedata_model->getSessionActivityDataEntry( 'oldTicketClassGroupID' );
		$oldTicketClassUniqueID = $this->clientsidedata_model->getSessionActivityDataEntry( 'oldTicketClassUniqueID' );
		
		$oldShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $oldShowingTimeID );
		$newShowingTimeObj = $this->Event_model->getSingleShowingTime( $bookingObj->EventID, $newShowingTimeID );		
		$paymentMode = $this->clientsidedata_model->getSessionActivityDataEntry( 'paymentMode' );
		$oldGuestSeatVisualRep = $this->getSeatRepresentationsOfGuests( 
			 $eventID, 
			 $oldShowingTimeID,
			 $bookingGuests,
			 $oldTicketClassGroupID,
			 $oldTicketClassUniqueID
		);
		
		$newSeatsInfoArray = $this->assembleRelevantDataOfNewSeats( $eventID, $newShowingTimeID, false );
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$noPendingPayment = (floatval( $billingInfoArray['amountDue'])  === 0.0 );
								
		$data['bookingNumber'] = $bookingNumber;
		$data['oldSeatVisuals'] = $oldGuestSeatVisualRep;
		$data['singleEvent'] = $eventObj;
		$data['guestCount'] = count( $bookingGuests );
		$data['guests'] = $bookingGuests;
		$data['currentShowingTime'] = $oldShowingTimeObj ;
		$data['newShowingTime'] = $newShowingTimeObj;
		$data['newSeatData'] = $newSeatsInfoArray;
		$data['unpaidPurchasesArray'] = $billingInfoArray['unpaidPurchasesArray'];
		$data['paidPurchasesArray'] = $billingInfoArray['paidPurchasesArray'];
		$data['unpaidTotal'] = $billingInfoArray['unpaidTotal'];
		$data['paidTotal'] = $billingInfoArray['paidTotal'];
		$data['amountDue'] = $billingInfoArray['amountDue'];				
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
			$bookingObj->EventID, 
			$oldTicketClassGroupID, 
			$oldTicketClassUniqueID 
		);
		$data['newTicketClassName'] =$this->TicketClass_model->getSingleTicketClassName(
			$bookingObj->EventID, 
			$newTicketClassGroupID, 
			$newTicketClassUniqueID 
		);			
		if( !$noPendingPayment ){
			$this->load->view( 'manageBooking/manageBookingFinalize_COD', $data );
		}else{
			$data['defaultAction'] = 'Manage Booking';		
			$data['redirect'] = TRUE;		
			$data['redirectURI'] = base_url().'EventCtrl/manageBooking';
			$data['theMessage'] = "The changes to your booking has been successfully made.";				
			$this->load->view( 'successNotice', $data );
		}
		$this->postManageBookingCleanup();		
	}
	
	function managebooking_pendingchange_viewdetails()
	{
		$bookingNumber = $this->input->post( 'bookingNumber' );
		if( $bookingNumber === false ) die( 'Booking number needed. ');		
		
		$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
		$rollbackData = $this->getSlotRollbackDataOfPurchase( $billingInfoArray['unpaidPurchasesArray'][0] );		
		$this->assembleDataForManageBookingPending_ViewDetais(	
			$billingInfoArray['unpaidPurchasesArray'][0]->Payment_Channel_ID, 
			$rollbackData[ 'oldShowtimeID' ],			
			$rollbackData[ 'oldTicketClassGroupID' ],			
			$rollbackData[ 'oldTicketClassUniqueID' ]			
		);
		$bookingObj = $this->Booking_model->getBookingDetails( $bookingNumber );
		$this->clientsidedata_model->setBookingNumber( $bookingNumber );
		$this->clientsidedata_model->setShowtimeID( $bookingObj->ShowingTimeUniqueID );
		$this->clientsidedata_model->setTicketClassGroupID( $bookingObj->TicketClassGroupID );
		$this->clientsidedata_model->setTicketClassUniqueID( $bookingObj->TicketClassUniqueID );
		redirect('EventCtrl/managebooking_finalize_forward');
	}//managebooking_pendingchange_viewdetails()
	
	
	function managebooking_upgradeticketclass()
	{	
		/*
			Created 08MAR2012-0021
			Direct handler of upgrading ticket class when clicked in manage booking section.
			First attempt too on all lowercase in a function name.
		*/
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$sessionActivity =  $this->clientsidedata_model->getSessionActivity();		
		if( ( $sessionActivity[0] == "MANAGE_BOOKING" and $sessionActivity[1] === 0 ) === false or
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
			$this->clientsidedata_model->setSessionActivity( "MANAGE_BOOKING", 0, "showingtime=0;ticketclass=0;seat=0;newcost=0;" );
			$this->clientsidedata_model->deleteBookingCookies();			
			$this->load->view( 'errorNotice', $data );
			return true;
		}
		/*
			Call function book_step2 instead of usual redirect. It's okay
			since we have set manage-booking specific data already.
		*/
		$this->clientsidedata_model->setSessionActivity( "MANAGE_BOOKING", STAGE_BOOK_2_FORWARD );
		$this->book_step2( $bookingNumber, Array( 
				$bookingObj->EventID,
				$bookingObj->ShowingTimeUniqueID,
				count( $this->Guest_model->getGuestDetails( $bookingNumber ) )
			) 
		);
	}//managebooking_upgradeticketclass
		
	function postManageBookingCleanup( $redirect = false )
	{
		$this->clientsidedata_model->setSessionActivity('MANAGE_BOOKING', 0 );
		$this->clientsidedata_model->deleteBookingCookies();	
	}
	
	function postBookingCleanup( $doNotRedirect = false )
	{
		/*
			Created 19FEB2012-1751
			
			Does anything that should be done after a user successfully books,
			like clearing cookies
		*/
		// deal first with the "X_slot_UUIDs" cookie since it's not included in the pre-determined cookies
		$redirectTo = $this->session->userdata ('redirect_to');		
		$ticketClassUniqueID = $this->clientsidedata_model->getTicketClassUniqueID();
		delete_cookie( $ticketClassUniqueID.'_slot_UUIDs'  );		
		delete_cookie( 'uplbConstituentStudentNumPair' );
		delete_cookie( 'uplbConstituentEmpNumPair' );				
		delete_cookie( 'mbooknsu' );				
		delete_cookie( 'mbooknsm' );				
		$this->clientsidedata_model->deleteBookingCookies();
		$this->session->unset_userdata( 'bookInstanceEncryptionKey' );
		$this->clientsidedata_model->deleteBookingProgressIndicator( );
		if( $doNotRedirect ) return true;
		if( $this->input->is_ajax_request() === FALSE ) {
			if( $redirectTo !== FALSE )
			{
				$this->session->unset_userdata( 'redirect_to' );				
				redirect( $redirectTo );
			}else
				redirect( '/' );
		}
	}	
} //class
?>