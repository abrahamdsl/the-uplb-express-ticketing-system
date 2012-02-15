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
	
	function assignSeatToGuest( $guestUUID, $matrix_x, $matrix_y  )
	{
		/*
			Created 14FEB2012-0906
		*/
		$sql_command = "UPDATE `event_slot` SET `Seat_x` = ?, `Seat_y` = ? WHERE `Assigned_To_User` = ? ";
		return $this->db->query( $sql_command, Array( $matrix_x, $matrix_y, $guestUUID) );
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
	}// getGuestNames(
	
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