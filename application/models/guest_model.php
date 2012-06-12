<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Guest Model
* 	Created 10FEB012-2017
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This deals with table `booking_guests` or basically anything that we can do for our guests.
**/

class Guest_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();		
	}
	
	function assignSeatToGuest( $guestUUID, $matrix_x, $matrix_y , $eventID = NULL, $showtimeID = NULL,
		$ticketClassGroupID = NULL, $ticketClassUniqueID= NULL
	)
	{
		/*
			Created 14FEB2012-0906
			11MAR2012-1623: Added params $eventID and $showtimeID
			12MAR2012-1713: Added params $ticketClassGroupID  and $ticketClassUniqueID
		*/
		$paramArrays =  Array( $matrix_x, $matrix_y, $guestUUID);
		$sql_command = "UPDATE `event_slot` SET `Seat_x` = ?, `Seat_y` = ? WHERE `Assigned_To_User` = ? ";
		if( $eventID != NULL and $showtimeID != NULL )
		{
			$sql_command .= "AND `EventID` = ? AND `Showtime_ID` = ?";
			$paramArrays[] = $eventID;
			$paramArrays[] = $showtimeID;
		}
		if( $ticketClassGroupID != NULL and $ticketClassUniqueID != NULL )
		{
			$sql_command .= "AND `Ticket_Class_GroupID` = ? AND `Ticket_Class_UniqueID` = ?";
			$paramArrays[] = $ticketClassGroupID;
			$paramArrays[] = $ticketClassUniqueID;
		}
		return $this->db->query( $sql_command, $paramArrays );
	}//assignSeatToGuest
	//09161733236 aletheia grace del rosario
	
	function getGuestsAlreadyEnteredEvent( $bookingNumber )
	{
		// create 17mar2012-1109
		$sql_command = "SELECT * FROM  `booking_guests` INNER JOIN `event_attendance_real` ON `booking_guests`.`UUID` = `event_attendance_real`.`GuestUUID`  WHERE  `booking_guests`.`bookingNumber` =  ?";
		$arr_result = $this->db->query( $sql_command, $bookingNumber )->result();
		$arr_result2;
		
		if( count($arr_result) > 0 ){
			$arr_result2 = Array();
			foreach( $arr_result as $val )
			{
				$arr_result2[ $val->GuestUUID ] = $val;
			}
			return $arr_result2;
		}else
			return false;
	}//getGuestsAlreadyEnteredEvent(..)
	
	function getGuestsAlreadyExitedEvent( $bookingNumber )
	{
		// create 17mar2012-1109
		$sql_command = "SELECT * FROM  `booking_guests` INNER JOIN `event_attendance_real` ON `booking_guests`.`UUID` = `event_attendance_real`.`GuestUUID` ";
		$sql_command .= "WHERE  `booking_guests`.`bookingNumber` = ? AND `event_attendance_real`.`ExitDate` IS NOT NULL AND `event_attendance_real`.`ExitTime` IS NOT NULL ";
		$arr_result = $this->db->query( $sql_command, $bookingNumber )->result();
		$arr_result2;
		
		if( count($arr_result) > 0 ){
			$arr_result2 = Array();
			foreach( $arr_result as $val )
			{
				$arr_result2[ $val->GuestUUID ] = $val;
			}
			return $arr_result2;
		}else
			return false;
	}//getGuestsAlreadyExitedEvent(..)
	
	function getAttendanceRecord( $guestUUID )
	{
		// DEPRECATED 02APR2012-2008
		$this->db->where('GuestUUID', $guestUUID  );		
		$sql_command = "SELECT * FROM `event_attendance_real` INNER JOIN `booking_guests` ON `event_attendance_real`.`GuestUUID`";
		$sql_command .= " = `booking_guests`.`UUID` WHERE `event_attendance_real`.`GuestUUID` = ? ";
		$arr_result = $this->db->query( $sql_command, Array( $guestUUID ) )->result();
		
		if( count( $arr_result ) > 0 ) return $arr_result[0];
		else
			return false;
	}
		
	function getGuestDetails( $bookingNumber )
	{
		/*
			Created 12FEB2012-1927
		*/
		$sql_command = "SELECT * FROM `booking_guests` WHERE `bookingNumber` = ? ORDER BY `Sequence` ASC";
		$arr_result = $this->db->query( $sql_command, array( $bookingNumber )  )->result();
		if( count( $arr_result ) < 1 ) return false;
		else
			return $arr_result;
	}// getGuestDetails	
	
	function getGuestDetails_UUID_AsKey( $bookingNumber )
	{ 
		/*
			Created 11MAR2012-2253.
			
			An extension of $this->getGuestDetails( $bookingNumber );
			I need to get guest details and since an array will be returned,
			with the index as the UUID key.
		*/
		$guestObj = $this->getGuestDetails( $bookingNumber );
		$returnThis = Array();
		if( $guestObj === false ) return $returnThis;
		foreach( $guestObj as $eachGuest ) $returnThis[ $eachGuest->UUID ] = $eachGuest;
		return $returnThis;
	}// getGuestDetails_UUID_AsKey
	
	function getGuestDetails_ForCheckIn( $bookingNumber )
	{
		/*
			Created 14MAR2012-1138
		*/
		$sql_command = "SELECT * FROM  `booking_guests` INNER JOIN  `event_slot` ON  `booking_guests`.`UUID` = ";
		$sql_command .= "`event_slot`.`Assigned_To_User` INNER JOIN `seats_actual` ON `event_slot`.`Seat_x` = `seats_actual`.`Matrix_x` AND";
		$sql_command .= " `event_slot`.`Seat_y` = `seats_actual`.`Matrix_y` AND `event_slot`.`EventID` = `seats_actual`.`EventID` AND ";
		$sql_command .= " `event_slot`.`Showtime_ID` = `seats_actual`.`Showing_Time_ID`";
		$sql_command .= " WHERE  `booking_guests`.`bookingNumber` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		
		if( count( $arr_result ) > 0 )
			return $arr_result;
		else
			return false;			
	}//getGuestDetails_ForCheckIn
	
	function getSingleGuest( $UUID )
	{
		/*
			Created 14FEB2012-1105
		*/
		$sql_command = "SELECT * FROM `booking_guests` WHERE `UUID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $UUID ) )->result();
		
		if( count( $arr_result ) != 1 )
			return false;
		else
			return $arr_result[0];
	}//getSingleGuest( .. )
	
	function getSingleGuestExtended( $UUID )
	{
		/*
			Created 14FEB2012-1105
		*/
		$sql_command = "SELECT * FROM `booking_guests` INNER JOIN `booking_details` ON `booking_details`.`bookingNumber`=";
		$sql_command .= "`booking_guests`.`bookingNumber` WHERE `booking_guests`.`UUID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $UUID ) )->result();
		
		if( count( $arr_result ) != 1 )
			return false;
		else
			return $arr_result[0];
	}//getSingleGuestExtended( .. )
	
	
	function insertGuestDetails( $bookingNumber, $guestNum, $accountNum, $Fname,  $Mname,  $Lname, 
		$gender, $cellphone, $landline, $email, $studentNum = NULL, $employeeNum = NULL
	)
	{
		// 13MAR2012-1004 | Added $studentNum and $employeeNum
		$sql_command = "INSERT INTO `booking_guests` VALUES ( UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$data = Array(
			'bookingNumber' => $bookingNumber,
			'Sequence' => $guestNum,
			'AccountNum' => intval($accountNum),
			'Fname' => strtoupper( $Fname ),
			'Mname' => strtoupper( $Mname ),
			'Lname' => strtoupper( $Lname ),
			'Gender' => $gender,
			'Cellphone' => $cellphone,
			'landline' => $landline,
			'Email' => strtolower( $email ),
			'studentNumber' => $studentNum,
			'employeeNumber' => $employeeNum
		);
		return $this->db->query( $sql_command, $data);
	}//insertGuestDetails
	
	function removeSeatFromGuest( $guestUUID )
	{
		/*
			Review | 10MAR2012-1131 | Isn't this already being done by slot_model->setSlotAsAvailable( $uuid )?
			Created 14FEB2012-1104
			
			-Shouldn't this be in Slot_model?
			
			This only removes the seat from the `event_slot` table.
			Back in the controller where this was called, you should call 
				* $this->model->markSeatAsAvailable(..);
				after this function.
		*/				
		$sql_command = "UPDATE `event_slot` SET `Seat_x` = NULL, `Seat_y` = NULL WHERE `Assigned_To_User` = ?";
		return $this->db->query( $sql_command, Array( $guestUUID ) );
	}
		
}//model