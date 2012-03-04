<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 10FEB012-2240

This deals with booking processes, mostly utilities relating to such.

*/


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
		$sql_command = "SELECT `EventID` from `booking_details` WHERE `bookingNumber` = ?";
		$arr_result = $this->db->query( $sql_command, array( $bNumber) )->result();
		
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
	
	function getPaymentPeriodExpiredBookings( $eventID, $showtimeID )
	{
		/*
			Created 25FEB2012-1247
		*/
		$sql_command = "SELECT * FROM `booking_details` INNER JOIN `purchase` ON `booking_details`.`bookingNumber`";
		$sql_command .=  " =`purchase`.`bookingNumber` WHERE `EventID` = ? AND `ShowingTimeUniqueID` = ? AND";
		$sql_command .= " `Status` = 'PENDING-PAYMENT' AND CURRENT_TIMESTAMP >=";
		$sql_command .= " CONCAT(`purchase`.`Deadline_Date`,' ',`purchase`.`Deadline_Time`);";
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}// getPaymentPeriodExpiredBookings
	
	function getPaidBookings( $userAccountNum )
	{
		/*
			Created 01MAR2012-2233
			
			Created for Manage Booking section. Gets details of
			paid bookings for management of guest like 
			changing of seats, upgrading to a higher ticket class and the likes.
		*/
		date_default_timezone_set('Asia/Manila');
		$sql_command = " SELECT * FROM `showing_time` INNER JOIN `booking_details` ON
			`showing_time`.`EventID` = `booking_details`.`EventID` INNER JOIN `event` ON 
			`showing_time`.`EventID` =  `event`.`EventID` WHERE `showing_time`.`Status` = 'CONFIGURED' AND
			`showing_time`.`UniqueID` = `booking_details`.`ShowingTimeUniqueID` AND
			CONCAT(`Selling_Start_Date`,' ',`Selling_Start_Time`) <= CURRENT_TIMESTAMP AND
			CONCAT(`Selling_End_Date`,' ',`Selling_End_Time`) >= CURRENT_TIMESTAMP AND 
			`booking_details`.`Status` = 'PAID' and `booking_details`.`MadeBy` = ?;";
		$arr_result = $this->db->query( $sql_command, Array ( $userAccountNum ) )->result();	
		if( count( $arr_result ) > 0 )
			return $arr_result;
		else
			return false;
	}//getPaidBookings(..)
	
	function isBookingUnderThisUser( $bookingNumber, $accountNum )
	{
		/*
			Created 01MAR2012-2351
		*/
		$bookingObj = $this->getBookingDetails( $bookingNumber );
		if( $bookingObj === false ) return false;
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
	
	
	
} //modelhbhg