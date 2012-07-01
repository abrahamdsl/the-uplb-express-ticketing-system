<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Booking Model
* 	Created 10FEB2012-2240
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This deals with booking processes, mostly utilities relating to such.
*
**/


class Booking_model extends CI_Model {

	function __construct()
	{
		parent::__construct();		
	}
			
	function createBookingDetails( $bNumber, $eventID, $showingTimeUID, $ticketClassGID, $ticketClassUID, $accountNum)	
	{
		/*
			Created 11FEB2012-0007				
			
			14FEB2012-1312: Removed params : $dateDeadline, $timeDeadline
		*/
		$sql_command = "INSERT INTO `booking_details` (`bookingNumber`, `EventID`, `TicketClassGroupID`, ";
		$sql_command .= "`ShowingTimeUniqueID`, `TicketClassUniqueID`, `MadeBy` ) VALUES (?, ?, ?, ?, ?, ?)";		
		return $this->db->query( $sql_command, Array( 
			$bNumber, $eventID, $ticketClassGID, $showingTimeUID, $ticketClassUID, $accountNum )
		);
	}// createBookingDetails
	
	function deleteAllBookingInfo( $bNumber )
	{
		/*
			Created 14FEB2012-0956
		*/
		$errorIndicator;
	
		$sql_command = "DELETE FROM `booking_guests` WHERE `bookingNumber` = ? ";
		$errorIndicator = $this->db->query( $sql_command, Array( $bNumber ) );
		$sql_command = "DELETE FROM `booking_details` WHERE `bookingNumber` = ? ";
		$errorIndicator = ( $errorIndicator and $this->db->query( $sql_command, Array( $bNumber ) ) );		
		return $errorIndicator;
	}
	
	function doesBookingNumberExist( $bNumber )
	{
		/*
			Created 10FEB2012-2355
			
			22FEB2012-2152: Renamed from 'isBookingNumberInUse' to 'doesBookingNumberExist', so private tag removed
		*/
		$sql_command = "SELECT `EventID` from `booking_details` WHERE `bookingNumber` = ? ";
		$arr_result = $this->db->query( $sql_command, array( trim($bNumber) ) )->result();
		
		return ( count($arr_result) === 1 );
	}//doesBookingNumberExist
	
	function generateBookingNumber()
	{
		/*
			Created 10FEB2011-2230
			
			Combinatorics:
				* 7 characters.
				* For numbers, zero is not included to avoid confusion with letter 'O'.
				* Min letter: 3, Max letter: 5
				* Zero index, starting with index 2,if  element [index-2] is equal to element[index], so will be replaced
					with aforementioned restrictions
		*/
	
		$bNumber;
		$charCount = 7;						// how long is the "booking number", as of 10FEB2012-2319, db can accomodate up to 20
		$letter;							// how many letters there should be
		$numbers;							// how many numbers
		$positioning = Array();
		
		do{
			$bNumber = "";
			$letter = rand(3,5);				
			$numbers = $charCount - $letter;
			// compute for assignment
			for( $x = 0; $x < $charCount; $x++ )
			{
				if( rand(0,1) === 0 )		// letter
				{
					if( $letter == 0 )		// if all letters have been allocated already
					{	// then, complete the bNumber by appending numbers and break
						for( ;$x < $charCount; $x++ ){
							$bNumber .= ( (string) rand(1,9) );						
						}
						break;
					}else{
						$bNumber.=( (string) chr(rand(65,90)) );
						$letter--;
					}
				}else{						// number
					if( $numbers == 0 )		// if all numbers have been allocated already
					{	// then, complete the bNumber by appending characters and break
						for( ;$x < $charCount; $x++ ){
							$bNumber .= ( (string) chr(rand(65,90)) );
						}
						break;
					}else{
						$bNumber .= ( (string) rand(1,9) );
						$numbers--;
					}
				}
			}
			
			//now check for so much duplicity from a column apart
			for( $x = 2; $x < $charCount; $x++ )
			{
				$inQuestion = ord($bNumber[$x]) ;
				if( $inQuestion == ord($bNumber[$x-2]) )
				{					
					if( $inQuestion < '58' )			// number
					{
						$inQuestion++;
						if( $inQuestion > 57 ) 				// ASCII 58 and beyond, which are numbers letters anymore
						{						
							$inQuestion -= rand( 2, 9);						
						}
					}else{									// letter
						$inQuestion += 2;
						if( $inQuestion > 90 ) 				// ASCII 91 and beyond, which are not letters anymore
						{						
							$inQuestion -= rand( 3, 20);						
						}
					}
					$bNumber[$x] = $inQuestion;
				}			
			}
		}while( $this->doesBookingNumberExist( $bNumber ) );
				
		return $bNumber;
	}//generateBookingNumber

	function getBookingDetails( $bNumber )
	{
		/*
			Created 22FEB2012-2252
		*/
		$sql_command = "SELECT * FROM `booking_details` where `bookingNumber` = ?";
		$arr_result = $this->db->query( $sql_command, array( $bNumber ) )->result();
		if( count( $arr_result ) == 1 )
			return $arr_result[0];
		else
			return false;
	}//getBookingDetails(..)
	
	function getBookingsForfeitedForCheckIn_NonConsumed( $eventID, $showtimeID )
	{
		$sql_command = "SELECT * FROM `booking_guests` INNER JOIN `booking_details` ON `booking_guests`.`bookingNumber`";
		$sql_command .= " = `booking_details`.`bookingNumber` WHERE `booking_details`.`EventID` = ? AND";
		$sql_command .= "  `booking_details`.`ShowingTimeUniqueID` = ? AND `booking_details`.`Status` = 'PAID'";
		$arr_result = $this->db->query( $sql_command, array(  $eventID, $showtimeID ) )->result();
		if( count( $arr_result )> 0 ){
			$var_b = Array();
			foreach($arr_result as $x ) $var_b[$x->UUID] = $x;		
			return $var_b;
		}else
			return false; 
	}//getBookingsForfeitedForCheckIn_NonConsumed()
	
	function getBookingsForfeitedForCheckIn_PartiallyConsumed( $eventID, $showtimeID )
	{
		// WHATEVER HAPPENED TO MY SQL JOINS! RAWR.
		
		$sql_command = "SELECT * FROM `booking_guests` INNER JOIN `booking_details` ON `booking_guests`.`bookingNumber`";
		$sql_command .= " = `booking_details`.`bookingNumber` INNER JOIN `event_slot` ON `booking_guests`.`UUID` = `event_slot`.`Assigned_To_User` INNER JOIN `event_attendance_real` on `event_attendance_real`.`GuestUUID` = ";
		$sql_command .= " `booking_guests`.`UUID`  WHERE `booking_details`.`EventID` = ? AND";
		$sql_command .= "  `booking_details`.`ShowingTimeUniqueID` = ? AND `booking_details`.`Status` = 'CONSUMED'";
		$sql_command .= "  AND `booking_details`.`Status2` = 'PARTIAL'";
					
		$arr_result_entered = $this->db->query( $sql_command, array(  $eventID, $showtimeID ) )->result();
		$var_a = Array();
		if( count( $arr_result_entered ) < 1 ) return false;
		
		foreach($arr_result_entered as $x ) $var_a[$x->UUID] = $x;
		
		$sql_command = "SELECT * FROM `booking_guests` INNER JOIN `booking_details` ON `booking_guests`.`bookingNumber`";
		$sql_command .= " = `booking_details`.`bookingNumber` INNER JOIN `event_slot` ON `booking_guests`.`UUID` = `event_slot`.`Assigned_To_User` WHERE `booking_details`.`EventID` = ? AND";
		$sql_command .= "  `booking_details`.`ShowingTimeUniqueID` = ? AND `booking_details`.`Status` = 'CONSUMED'";
		$sql_command .= "  AND `booking_details`.`Status2` = 'PARTIAL'";
		
		$arr_result_all = $this->db->query( $sql_command, array(  $eventID, $showtimeID ) )->result();
		$var_b = Array();
		foreach($arr_result_all as $x ) $var_b[$x->UUID] = $x;
		
		foreach( $var_a as $key=>$val ) unset( $var_b[$key] );
		if( count( $var_b )> 0 )
			return $var_b;
		else
			return false; 
	}//getBookingsForfeitedForCheckIn_NonConsumed()
	
	function getPaymentPeriodExpiredBookings( $eventID, $showtimeID )
	{
		/**
		*	@created 25FEB2012-1247
		*	@revised 23JUN2012-1433 Instead of using MySQL's CURRENT_TIMESTAMP constant, we substituted it for
				getting the time via PHP as this is much more fool-proof regarding hosting server's time differences.
		**/
		date_default_timezone_set('Asia/Manila');
		$sql_command = "SELECT * FROM `booking_details` INNER JOIN `purchase` ON `booking_details`.`bookingNumber`";
		$sql_command .=  " =`purchase`.`bookingNumber` WHERE `EventID` = ? AND `ShowingTimeUniqueID` = ? AND";
		$sql_command .= " `Status` = 'PENDING-PAYMENT' AND `purchase`.`Amount` > 0 AND ? >=";
		$sql_command .= " CONCAT(`purchase`.`Deadline_Date`,' ',`purchase`.`Deadline_Time`);";		
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID, date("Y-m-d H:i:s") ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}// getPaymentPeriodExpiredBookings
	
	function getAllBookings( $userAccountNum = false, $getOnlyPaidOnes = false )
	{	
		/*
			@created 09MAR2012-1406
			@purpose Gets all bookings of a guest.
			@revised 22JUN2012-1208 Corrected error in SQL command wherein selling date and time
				was included. Criteria for inclusion changed to status != ARCHIVED
		*/
		date_default_timezone_set('Asia/Manila');
		$statusFilter = ( $getOnlyPaidOnes ) ? "AND `booking_details`.`Status` = 'PAID'" : "";
		$sql_command = " SELECT * FROM `showing_time` INNER JOIN `booking_details` ON
			`showing_time`.`EventID` = `booking_details`.`EventID` INNER JOIN `event` ON 
			`showing_time`.`EventID` =  `event`.`EventID` WHERE `showing_time`.`Status` != 'ARCHIVED' AND
			`showing_time`.`UniqueID` = `booking_details`.`ShowingTimeUniqueID` "; 
		$sql_command .= $statusFilter;
		$sql_command .= ( $userAccountNum !== FALSE ) ? " AND `booking_details`.`MadeBy` = ?;" : "";
		$arr_result = $this->db->query( $sql_command, Array ( $userAccountNum ) )->result();	
		if( count( $arr_result ) > 0 )
			return $arr_result;
		else
			return false;
	}// getAllBookings
	
	function getPaidBookings( $userAccountNum )
	{
		/*
			Created 01MAR2012-2233
			
			Created for Manage Booking section. Gets details of
			paid bookings for management of guest like 
			changing of seats, upgrading to a higher ticket class and the likes.
		*/
		return $this->getAllBookings( $userAccountNum, true );
	}//getPaidBookings(..)
	
	function isBookingBeingBooked( $bookingNumberOrObj )
	{
		/**
		*	@created 07JUN2012-1449
		*	@purpose Detects if the current booking is being booked. 
		*	@param $bookingNumberOrObj STRING/MYSQL_OBJ The booking number or booking number object. If booking number is specified,
				the entries are retrieved from the database, else the object is directly accessed for `Status`.
		**/
		
		$bookingObj = null;
		if( is_string( $bookingNumberOrObj ) ) 
			$bookingObj = $this->getBookingDetails( $bookingNumberOrObj );
		else
			$bookingObj = $bookingNumberOrObj;
			
		if( $bookingObj === false ) return false;		
		return( $bookingObj->Status == 'BEING_BOOKED' );		
	}//isBookingBeingBooked(..)
	
	function isBookingExpired( $bookingNumberOrObj, $lookStatus2 = false, $oldOrNew = NULL )
	{
		/*
				
			@purpose Detects if the current booking is expired.
			@param $bookingNumberOrObj STRING/MYSQL_OBJ The booking number or booking number object. If booking number is specified,
				the entries are retrieved from the database, else the object is directly accessed for `Status`.
			@param $lookStatus2  BOOLEAN Default is FALSE. If true, checks `Status2`.
			@param $oldOrNew STRING Values { 'NOT-YET-NOTIFIED', 'FOR-DELETION' }. Checks if `Status2` is equal to those.			
		*/
		$bookingObj = null;
		if( is_string( $bookingNumberOrObj ) ) 
			$bookingObj = $this->getBookingDetails( $bookingNumberOrObj );
		else
			$bookingObj = $bookingNumberOrObj;
			
		if( $bookingObj === false ) return false;
		if( $lookStatus2 === false )
			return( $bookingObj->Status == 'EXPIRED' );
		else
			return( $bookingObj->Status == 'EXPIRED' and $bookingObj->Status2 == $oldOrNew );
	}//isBookingExpired(..)
	
	
	function isBookingUpForChange( $bookingNumberOrObj )
	{
		/*
			Created 10MAR2012-1535
			
			Detects if the current booking is an existing one waiting for changes to be completed,
			i.e. (1) Change ticket class and/or (2) Change showing time
			
			The parameter that can be passed can be either a the booking number or
			the MYSQL Object of that booking number.
		*/
		$bookingObj = null;
		if( is_string( $bookingNumberOrObj ) ) 
			$bookingObj = $this->getBookingDetails( $bookingNumberOrObj );
		else
			$bookingObj = $bookingNumberOrObj;
			
		if( $bookingObj === false ) return false;
		return( $bookingObj->Status == 'PENDING-PAYMENT' and
				$bookingObj->Status2 == 'MODIFY' 
		);
	}//isBookingUpForChange
		
	function isBookingUpForPayment( $bookingNumberOrObj )
	{
		/*
				
			Detects if the current booking is waiting for payment
		*/
		$bookingObj = null;
		if( is_string( $bookingNumberOrObj ) ) 
			$bookingObj = $this->getBookingDetails( $bookingNumberOrObj );
		else
			$bookingObj = $bookingNumberOrObj;
			
		if( $bookingObj === false ) return false;
		return( $bookingObj->Status == 'PENDING-PAYMENT' );
	}//isBookingUpForPayment
	
	function isBookingRolledBack( $bookingNumberOrObj )
	{
		/*
			Created 11MAR2012-1125
			
			Detects if the current booking was rolled back form some change
			as  a result of non-payment of dues before a deadline.
				
			The parameter that can be passed can be either a the booking number or
			the MYSQL Object of that booking number.
		*/
		$bookingObj = null;
		if( is_string( $bookingNumberOrObj ) ) 
			$bookingObj = $this->getBookingDetails( $bookingNumber );
		else
			$bookingObj = $bookingNumberOrObj;
			
		if( $bookingObj === false ) return false;
		return( $bookingObj->Status2 == 'ROLLED-BACK' );
	}//isBookingRolledBack
	
	function isBookingUnderThisUser( $bookingNumber, $accountNum )
	{
		/**
		*	@created 01MAR2012-2351
		*	@description Checks if the booking is under the specified user via the supplied account number.
		*	@returns NULL - If booking number is not found in the system, else
					 BOOLEAN
		*	@revised 25JUN2012-1405
		**/
		$bookingObj = $this->getBookingDetails( $bookingNumber );
		if( $bookingObj === false ) return NULL;
		return ( intval( $bookingObj->MadeBy ) === $accountNum ) ;
	}//isBookingUnderThisUser(..)
	
	function isTherePaymentPeriodExpiredBooking( $eventID, $showtimeID )
	{
		/*
			Created 25FEB2012-1258
		*/
		$callBack = $this->getPaymentPeriodExpiredBookings( $eventID, $showtimeID );
		if( $callBack === false ) return false;
		else
			return ( count( $callBack ) > 0 );
	}//isTherePaymentPeriodExpiredBooking

	function markAsConsumed_Full( $bNumber )
	{
		/*
			Created 17FEB2012-1058
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'CONSUMED',`Status2` = 'FULL' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsConsumed_Full
	
	function markAsConsumed_Partial( $bNumber )
	{
		/*
			Created 17FEB2012-1058
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'CONSUMED',`Status2` = 'PARTIAL' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsConsumed_Partial
	
	function markAsExpired_ForDeletion( $bNumber )
	{
		/*
			Created 25FEB2012-1303
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'EXPIRED',`Status2` = 'FOR-DELETION' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsExpired
	
	function markAsExpired_New( $bNumber )
	{
		/*
			Created 25FEB2012-1300
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'EXPIRED',`Status2` = 'NOT-YET-NOTIFIED' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsExpired
	
	function markAsNoShowForfeited( $bNumber )
	{
		/*
			Created 25FEB2012-1300
		*/
		$sql_command = "UPDATE `booking_details` SET `Status2` = 'NO-SHOW-FORFEITED' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsNoShowForfeited
	
	function markAsHoldingTimeLapsed_ForDeletion( $bNumber )
	{
		/*
			Created 01MAR2012-1303
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'LAPSED-HOLDING_TIME',`Status2` = 'FOR-DELETION' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsHoldingTimeLapsed_ForDeletion
	
	function markAsHoldingTimeLapsed_New( $bNumber )
	{
		/*
			Created 01MAR2012-1300
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'LAPSED-HOLDING_TIME',`Status2` = 'NOT-YET-NOTIFIED' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}// markAsHoldingTimeLapsed_New
	
	function markAsPaid( $bNumber )
	{
		/*
			Created 23FEB2012-0048
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'PAID',`Status2` = NULL WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}//markAsPaid(..)
	
	function markAsPendingPayment( $bNumber, $type )
	{
		/*
			Created 14FEB2012-2153
			
			Param def'n:
			$type - values { "NEW" , "MODIFY" }
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'PENDING-PAYMENT', `Status2` = '".$type."' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}// markAsPendingPayment
	
	function markAsRolledBack( $bNumber )
	{
		/*
			Created 11FEB2012-1119
			
			Updates a booking's `Status2` entry to `ROLLED-BACK` - happens if changes to a booking
			was not confirmed before the deadline. We do this so that there is an indicator so that
			we can notify the client when seeing the booking details.
		*/
		$sql_command = "UPDATE `booking_details` SET `Status2`= 'ROLLED-BACK' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}// markAsRolledBack
	
	function updateBookingDetails( $bNumber, $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID )
	{
		/*
			Created 04MAR2012-2314
		*/
		$sql_command = "UPDATE `booking_details` SET `EventID` = ?, `ShowingTimeUniqueID` = ?,";
		$sql_command .= " `TicketClassGroupID` = ?, `TicketClassUniqueID` = ? WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array(
				$eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID, $bNumber
			)
		);
	}// updateBookingDetails(..)
	
	function updateBooking2ndStatus( $bNumber, $newVal )
	{
		/**
		*	@created 07JUN2012-1502
		*	@description Updates the `Status2` column of entries in `booking_details`. Mainly for use
				when a booking is being booked and we need to track the progress for resume capability if ever
				the user accidentally closes the window/tab he/she is making his booking and cannot bring it back
				( maybe there are times that Shift + T  key combination won't work ).
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "UPDATE `booking_details` SET `Status2` = ? WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $newVal, $bNumber ) );
	}// updateBooking2ndStatus(..)
} //model