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
	
	function createPayment( $bookingNumber, $amount, $paymentMode, $customData = "" )
	{
		/*
			Created 23FEB2012-0016
		*/
		date_default_timezone_set('Asia/Manila');
		$uniqueID = $this->generatePaymentUniqueID();
		$sql_command = "INSERT INTO `payments` VALUES (?, ?, ?, ?, ?, ?, ?, ? )";
		$dbResult = $this->db->query( $sql_command, Array(
			$uniqueID,
			$bookingNumber,
			$amount,
			$this->session->userdata( 'accountNum' ),
			$paymentMode,
			date("H:i:s"),
			date("Y-m-d"),
			$customData
		));
		if( $dbResult ) return $uniqueID;
		else
			return false;
	}// createPayment(..)
	
	function createPaymentMode( $ptype, $name, $person, $location, $cellphone, $landline,
			$email, $comments, $internal_data_type, $internal_data
	)
	{
		$uniqueID = $this->getLastPaymentModeUniqueID() + 1 ;
		$data = Array(
			'UniqueID'			=>  $uniqueID,
			'Type'	   			 => $ptype,
			'Name'	   	         => $name,
			'Contact_Person' 	 => $person,
			'Location'		 	 => $location,
			'Cellphone'		 	 => $cellphone,
			'Landline'		 	 => $landline,
			'Email'		     	 => $email,
			'Comments'		 	 => $comments,
			'internal_data_type' => $internal_data_type,
			'internal_data'		 => $internal_data
			
		);				
		return $this->db->insert('payment_channel', $data );		
	}//createPaymentMode(..)
	
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
		$quantity, $amount, $deadlineDate, $deadlineTime, $comments = NULL )
	{
		/*
			Created 14FEB2012-1315
		*/
		$sql_command = "INSERT INTO `purchase` ( `BookingNumber`, `Charge_type`, `Charge_type_Description`, ";
		$sql_command .= "`Quantity`, `Amount`, `Deadline_Date`, `Deadline_Time`, `Comments` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? ); ";
		
		return $this->db->query( $sql_command, Array( $bookingNumber, $chargeType, $chargeDesc,
				$quantity, $amount, $deadlineDate, $deadlineTime, $comments ) 
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
	
	function deletePaymentMode( $uniqueID )
	{
		$sql_command = "DELETE FROM `payment_channel` WHERE `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $uniqueID ) );
	}
	
	function deleteSinglePurchase( $bookingNumber, $uniqueID )
	{
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $bookingNumber, $uniqueID ) );
	}//deleteSinglePurchase(..)

    function deleteUnpaidPurchases( $bookingNumber )
	{
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ? AND `Payment_UniqueID` = 0";
		return $this->db->query( $sql_command, Array( $bookingNumber ) );
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
	
	function generatePaymentUniqueID()
	{
		$number;
		
		do{
				$number = rand( 555111, 999333 );	// just so random
		}while( $this->doesPaymentExist( $number ) );
		
		return $number;
	}
	
	function getLastPaymentModeUniqueID()
	{						
		$sql_command = "SELECT * FROM  `payment_channel` ORDER BY  `UniqueID` DESC LIMIT 0 , 1000";		
		$array_result = $this->db->query( $sql_command )->result();
		
		// now, what we want is found at the first element
		if( count( $array_result ) > 0 )
		{			
			return intval( $array_result[0]->UniqueID );
		}else return 0;		
	}// getLastPaymentModeUniqueID
	
	function getPaidPurchases( $bookingNumber )
	{
		/*
			Created 04MAR2012-1655
		*/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` = ? AND `Payment_UniqueID` != '0'";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}//getPaidPurchases
	
	function getPaymentModeByName( $name )
	{
		$sql_command = "SELECT * FROM `payment_channel` WHERE `Name` = ?";
		$arr_result = $this->db->query( $sql_command, Array(  $name ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
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
	
	function getPaymentChannels( $includeZero = FALSE )
	{
		/*
			Created 15FEB2012-1409 | Gets all entries in the `payment_channel` table.
			This use is mostly in Create Event Step 6.
			
			Modified 28FEB2012-1052: Added $includeZero parameter. BOOLEAN.
				The parameter is expected to be true when we are to include the payment Channel
				"FREE" because the ticket cost is zero.
		*/
		$quantifier = ( $includeZero === TRUE ) ? " 1" : " `UniqueID` > 0 ";
		$sql_command = "SELECT * FROM `payment_channel` WHERE ".$quantifier;
		$arr_result = $this->db->query( $sql_command )->result();
		if( count($arr_result) > 0 )
			return $arr_result;
		else
			return false;
	}//getPaymentChannels()
	
	function getPaymentChannelsForEvent( $eventID, $showtimeID, $includeFree = FALSE )
	{
		$sql_command = "SELECT * FROM `payment_channel_availability` INNER JOIN `payment_channel` ON ";
		$sql_command .= "`payment_channel`.`UniqueID` = `payment_channel_availability`.`PaymentChannel_UniqueID` where ";
		$sql_command .= "`payment_channel_availability`.`EventID` = ? AND `payment_channel_availability`.`ShowtimeID` = ? ";
		$sql_command .= "ORDER BY `payment_channel_availability`.`PaymentChannel_UniqueID` ASC";
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID ) )->result();
		
		if( count( $arr_result ) < 1 )
			return false;
		else
			/*
				Factory default: If UniqueID of a payment channel is zero - it means, automatic confirmation/payment
				of a booking since there's no charge/fees to be paid.
				
				So here, if the calling function specified to not include the free payment channel,
				detect if the UniqueID of first payment channel gotten from the query earlier
				is zero, and if so, unset from the array to be returned
			*/
			if( !$includeFree ){
				if( intval($arr_result[0]->UniqueID) === 0 )
					unset( $arr_result[0] );		
			}
			return $arr_result;
	}//getPaymentChannelsForEvent
	
	function getSinglePaymentChannelByInternalDataMerchantEmail( $paymentProcessor = 'paypal', $email )
	{	
		/**
		*	@created 26MAY2012-1314
		*   @author  abe		
		*   @assumptions The internal_data is of type WIN5 for now. If in XML, won't be able to find for now.
		**/
		$sql_command = "SELECT * FROM `payment_channel` WHERE `internal_data` REGEXP 'merchant_email=".mysql_real_escape_string( $email )."'";
		$sql_command .= " AND `internal_data` REGEXP 'processor=".mysql_real_escape_string( $paymentProcessor )."'";
		log_message( 'DEBUG', 'Mysql query xyz ' . $sql_command );
		$arr_result = $this->db->query( $sql_command )->result();
		
		if( count ($arr_result) == 1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePaymentChannelByInternalDataMerchantEmail()
	
	function getSinglePaymentChannelByUniqueID( $uniqueID )
	{		
		$sql_command = "SELECT * FROM `payment_channel` WHERE `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $uniqueID ) )->result();
		
		if( count ($arr_result) == 1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePaymentChannelByUniqueID()
	
	function getSinglePaymentChannel( $eventID, $showtimeID, $uniqueID )
	{
		/*
			Created 14FEB2012-1850
		*/
		$arr_result = $this->getPaymentChannelsForEvent( $eventID, $showtimeID, TRUE );
		if( $arr_result === false ) return false;		
		foreach( $arr_result as $singleChannel )
		{
			if( intval($singleChannel->UniqueID) === intval($uniqueID)  ) return $singleChannel;
		}
		return false;
	}// getSinglePaymentChannel(..)
	
	function getSinglePurchase( $bookingNumber, $uniqueID )
	{
		/*
			Created 29FEB2012-1129
		*/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` =? AND `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber, $uniqueID ) )->result();
		
		if( count( $arr_result ) ==1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePurchase(..)
	
	function getSumTotalOfPaid( $bookingNumber, $purchases = Array() )
	{
		/*
			Created 23FEB2012-0031
		*/
	
		if( count( $purchases ) < 1 ){		
			$purchases = $this->getPaidPurchases( $bookingNumber ); // get from database using $bookingNumber		
		}
		
		return $this->sumTotalCharges( $purchases );
	}// getSumTotalOfUnpaid
	
	function getSumTotalOfUnpaid( $bookingNumber, $purchases = Array() )
	{
		/*
			Created 23FEB2012-0031
			
			31MAR2012-1847: Might be redundant
		*/
	
		if( count( $purchases ) < 1 ){		// 04MAR2012-1657 : Why is this formerly ' > 0 '???
			$purchases = $this->getUnpaidPurchases( $bookingNumber ); // get from database using $bookingNumber		
		}
		
		return $this->sumTotalCharges( $purchases );
	}// getSumTotalOfUnpaid

	function isPaypalPaymentOK( $_IPN_Array )
	{
		/*
			Created 22MAR2012-2321
			
			Refer to https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
			for validity.
			returns array.
		*/
		$returnThis = Array(
			'boolean' => false, 'details' => ""
		);
		if( !isset( $_IPN_Array['payment_status'] ) ){
			$returnThis['details'] = "PAYMENT STATUS NOT SET";
			return $returnThis;
		}
		switch( $_IPN_Array['payment_status']  )
		{ 
			case 'Completed': $returnThis['boolean'] = TRUE; return $returnThis;
			case 'Created':   $returnThis['boolean'] = TRUE; return $returnThis;
			case 'Processed': $returnThis['boolean'] = TRUE; return $returnThis;
			case 'Pending':  if( $_IPN_Array['pending_reason'] != 'other' ){
								$returnThis['boolean'] = TRUE; 
								return $returnThis;	
							 }else{								
								$returnThis['details'] = "Pending reason not specified by PayPal"; 
								return $returnThis;	
							 }
		}//switch
		$returnThis['details'] = "Unspecified reason."; 
		return $returnThis;	
	}//isPaypalPaymentOK(..)
	
	function setAsPaid( $bookingNumber, $uniqueID, $paymentUniqueID )
	{
		/*
			Created 23FEB2012-0037
		*/
		$sql_command = "UPDATE `purchase` SET `Payment_UniqueID` = ?, `Deadline_Date` = NULL, `Deadline_Time` = NULL";
		$sql_command .= " WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		
		return $this->db->query( $sql_command, Array( $paymentUniqueID, $bookingNumber, $uniqueID ) );
	}//setAsPaid
	
	function setPaymentModeForPurchase( $bNumber, $pChannel, $uniqueID = NULL )
	{
		/*
			Created 22FEB2012-2351
			10MAR2012-1507 : Added param $uniqueID
		*/
		$insertTheseValues = Array( $pChannel, $bNumber );
		$sql_command = "UPDATE `purchase` SET `Payment_Channel_ID` = ? WHERE `Payment_UniqueID` = 0 AND `BookingNumber` = ?";
		if( $uniqueID !== NULL ){
			$sql_command .= " AND `UniqueID` = ?";
			$insertTheseValues[] = $uniqueID;
		}
		return $this->db->query( $sql_command,  $insertTheseValues );
	}//setPaymentModeForPurchase(..)s
	
	function sumTotalCharges( $purchasesArray )
	{
		/*
			Created 28FEB2012-1034
			
			Basically, receives array of MYSQL_OBJs returned by $this->get{'Unp'|'P'}aidPurchases(..) and sums 
			the total. This computation is moved from bookStep5.php to here.						
		*/
		$totalCharges = 0.0;
		if( !is_array( $purchasesArray ) or count( $purchasesArray ) < 1 ) return false;
		
		foreach( $purchasesArray as $singlePurchase ) $totalCharges += floatval($singlePurchase->Amount);	
		
		return $totalCharges;
	}//sumTotalCharges(..)
	
	function updatePaymentMode( $uniqueID, $ptype, $name, $person, $location, $cellphone, $landline,
			$email, $comments, $internal_data_type, $internal_data
	)
	{
		$data = Array(			
			'Type'	   			 => $ptype,
			'Name'	   	         => $name,
			'Contact_Person' 	 => $person,
			'Location'		 	 => $location,
			'Cellphone'		 	 => $cellphone,
			'Landline'		 	 => $landline,
			'Email'		     	 => $email,
			'Comments'		 	 => $comments,
			'internal_data_type' => $internal_data_type,
			'internal_data'		 => $internal_data
			
		);		
		$where = "`UniqueID` = ".$uniqueID; 
		$sql_command = $this->db->update_string('payment_channel', $data, $where );
		return $this->db->query( $sql_command );
	}
	
	function updatePurchaseComments( $bookingNumber, $uniqueID, $comments )
	{
		/*
			Created 10MAR2012-1053
		*/
		$sql_command = "UPDATE `purchase` SET `Comments` = ? WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		$query_result = $this->db->query( $sql_command, Array($comments,$bookingNumber, $uniqueID ) );
		
		return $query_result;
	}//updatePurchaseComments
}//class