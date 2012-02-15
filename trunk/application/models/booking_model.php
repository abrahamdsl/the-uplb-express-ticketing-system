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
	
	private function isBookingNumberInUse( $bNumber )
	{
		/*Created 10FEB2012-2355*/
		$sql_command = "SELECT `EventID` from `booking_details` WHERE `bookingNumber` = ?";
		$arr_result = $this->db->query( $sql_command, array( $bNumber) )->result();
		
		return ( count($arr_result) === 1 );
	}//isBookingNumberInUse
	
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
		}while( $this->isBookingNumberInUse( $bNumber ) );
				
		return $bNumber;
	}//generateBookingNumber

	function markAsPendingPayment( $bNumber, $type )
	{
		/*
			Created 14FEB2012-2153
			
			Param def'n:
			$type - values { "NEW" , "MODIFY" }
		*/
		$sql_command = "UPDATE `booking_details` SET `Status`= 'PENDING-PAYMENT_".$type."' WHERE `bookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bNumber ) );
	}// markAsPendingPayment
} //modelhbhg