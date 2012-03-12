<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 10FEB012-2017

This deals with table `booking_guests` or basically anything that we can do for our guests.

*/


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
	
	function getGuestDetails( $bookingNumber )
	{
		/*
			Created 12FEB2012-1927
		*/
		$sql_command = "SELECT * FROM `booking_guests` WHERE `bookingNumber` = ?";
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
	
	
	function insertGuestDetails( $bookingNumber, $accountNum, $Fname,
		 $Mname,  $Lname, $gender, $cellphone, $landline, $email	)
	{
		$sql_command = "INSERT INTO `booking_guests` VALUES ( UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$data = Array(
			'bookingNumber' => $bookingNumber,
			'AccountNum' => intval($accountNum),
			'Fname' => $Fname,
			'Mname' => $Mname,
			'Lname' => $Lname,
			'Gender' => $gender,
			'Cellphone' => $cellphone,
			'landline' => $landline,
			'Email' => $email
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