<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 14FEB2012-1146

This deals with all things regarding payment.

*/


class Payment_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function addPaymentChannel_ToShowTime( $eventID, $showtimeID, $pChannelUID, $comment )
	{
		/*
			Created 15FEB2012-1459
		*/
		$sql_command = "INSERT INTO `payment_channel_availability` VALUES ( ?, ?, ?, ? );";
		return $this->db->query( $sql_command, Array(
				$eventID, $showtimeID, $pChannelUID, $comment
			)
		);
	}//addPaymentChannel_ToShowTime(..)
	
	function createPayment( $bookingNumber, $amount, $paymentMode )
	{
		/*
			Created 23FEB2012-0016
		*/
		date_default_timezone_set('Asia/Manila');
		$uniqueID = $this->generatePaymentUniqueID();
		$sql_command = "INSERT INTO `payments` VALUES (?, ?, ?, ?, ?, ?, ? )";
		$dbResult = $this->db->query( $sql_command, Array(
			$uniqueID,
			$bookingNumber,
			$amount,
			$this->session->userdata( 'accountNum' ),
			$paymentMode,
			date("H:i:s"),
			date("Y-m-d"),			
		));
		if( $dbResult ) return $uniqueID;
		else
			return false;
	}// createPayment(..)
	
	function createPaymentChannelPermission( $accountNum, $eventID, $showtimeID, $pChannelID  )
	{
		/*
			Created 23FEB2012-0243
		*/
		$sql_command = "INSERT INTO `payment_channel_permission` VALUES (?,?,?,?,?,?);";
		return $this->db->query( $sql_command, Array(
			$accountNum, $eventID, $showtimeID, $pChannelID, 1, ''
		));
	}//createPaymentChannelPermission(..)
	function createPurchase( $bookingNumber, $chargeType, $chargeDesc,
		$quantity, $amount, $deadlineDate, $deadlineTime )
	{
		/*
			Created 14FEB2012-1315
		*/
		$sql_command = "INSERT INTO `purchase` ( `BookingNumber`, `Charge_type`, `Charge_type_Description`, ";
		$sql_command .= "`Quantity`, `Amount`, `Deadline_Date`, `Deadline_Time` ) VALUES ( ?, ?, ?, ?, ?, ?, ? ); ";
		
		return $this->db->query( $sql_command, Array( $bookingNumber, $chargeType, $chargeDesc,
				$quantity, $amount, $deadlineDate, $deadlineTime ) 
		);
	}//createPurchase(..)
	
	function deleteAllBookingPurchases( $bookingNumber )
	{
		/*
			Created 14FEB2012-1319
		*/
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bookingNumber ) );
	}// deleteAllBookingPurchases(..)
	
	function generatePaymentUniqueID()
	{
		$number;
		
		do{
				$number = rand( 555111, 999333 );	// just so random
		}while( $this->doesPaymentExist( $number ) );
		
		return $number;
	}
	
	function doesPaymentExist( $uniqueID )
	{
		/*
			Created 23FEB2012-0026
		*/
		$sql_command = "SELECT * FROM `payments` WHERE `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $uniqueID ) )->result();
		
		return ( count($arr_result) > 0 ? true : false );
	}
	
	function getUnpaidPurchases( $bookingNumber )
	{
		/*
			Created 14FEB2012-1331
		*/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` = ? AND `Payment_UniqueID` = '0'";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}//getUnpaidPurchases
	
	function getPaymentChannels()
	{
		/*
			Created 15FEB2012-1409 | Gets all entries in the `payment_channel` table.
			This use is mostly in Create Event Step 6.
		*/
		$sql_command = "SELECT * FROM `payment_channel` WHERE 1";
		$arr_result = $this->db->query( $sql_command )->result();
		if( count($arr_result) > 0 )
			return $arr_result;
		else
			return false;
	}//getPaymentChannels()
	
	function getPaymentChannelsForEvent( $eventID, $showtimeID )
	{
		$sql_command = "SELECT * FROM `payment_channel_availability` INNER JOIN `payment_channel` ON ";
		$sql_command .= "`payment_channel`.`UniqueID` = `payment_channel_availability`.`PaymentChannel_UniqueID` where ";
		$sql_command .= "`payment_channel_availability`.`EventID` = ? AND `payment_channel_availability`.`ShowtimeID` = ? ";
		$sql_command .= "ORDER BY `payment_channel_availability`.`PaymentChannel_UniqueID` ASC";
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID ) )->result();
		
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}//getPaymentChannelsForEvent
	
	function getSinglePaymentChannel( $eventID, $showtimeID, $uniqueID )
	{
		/*
			Created 14FEB2012-1850
		*/
		$arr_result = $this->getPaymentChannelsForEvent( $eventID, $showtimeID );
		foreach( $arr_result as $singleChannel )
		{
			if( intval($singleChannel->UniqueID) === $uniqueID  ) return $singleChannel;
		}
		return false;
	}// getSinglePaymentChannel(..)
	
	function getSumTotalOfUnpaid( $bookingNumber, $purchases = null )
	{
		/*
			Created 23FEB2012-0031
		*/
		$amount = 0;
		
		if( $purchases === null )
		{
			// get from database using $bookingNumber
		}
		foreach( $purchases as $singlePurchase )
		{
			$amount += floatval( $singlePurchase->Amount );
		}
		
		return $amount;
	}// getSumTotalOfUnpaid
	
	function setAsPaid( $bookingNumber, $uniqueID, $paymentUniqueID )
	{
		/*
			Created 23FEB2012-0037
		*/
		$sql_command = "UPDATE `purchase` SET `Payment_UniqueID` = ?, `Deadline_Date` = NULL, `Deadline_Time` = NULL";
		$sql_command .= " WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		
		return $this->db->query( $sql_command, Array( $paymentUniqueID, $bookingNumber, $uniqueID ) );
	}//setAsPaid
	
	function setPaymentModeForPurchase( $bNumber, $pChannel )
	{
		/*
			Created 22FEB2012-2351
		*/
		$sql_command = "UPDATE `purchase` SET `Payment_Channel_ID` = ? WHERE `Payment_UniqueID` = 0 AND `BookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $pChannel, $bNumber ) );
	}
}//class