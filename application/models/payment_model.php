<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Payment Model
* 	Created 14FEB2012-1146
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This deals with all low-level things regarding payment.
*
**/

class payment_model extends CI_Model {
	
	function __construct()
	{		
		// 12JUN2012-1234 Errr, I am getting error messages that these two constants
		// are already defined - I've searched all fiels in paypal controller, then the libraries
		// but I can't find it. So for now, we just put a define() statement as compromise.
		/*
			This is the default value of an entry's `Payment_UniqueID` column
			in `purchase` (upon creation) / FACTORY SETTING.
		*/
		if( !defined( 'PURCHASE_NOTPAIDYET_INDICATOR' ) ) define( 'PURCHASE_NOTPAIDYET_INDICATOR', 0 );
		/*
			This is the default value of an entry's `Payment_Channel_ID` column
			in `purchase` (upon creation) / FACTORY SETTING. It means
			no payment mode is still being selected.
		*/
		if( !defined( 'PURCHASE_INITIAL_PCHANNEL' ) ) define( 'PURCHASE_INITIAL_PCHANNEL', -1 );
		parent::__construct();
	}
	
	private function doesPaymentExist( $uniqueID )
	{		
		/**
		*	@created 23FEB2012-0026
		*	@description Checks if the integer in question is already used as a unique identifier 
				for a payment.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "SELECT * FROM `payments` WHERE `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $uniqueID ) )->result();
		
		return ( count($arr_result) > 0 ? true : false );
	}//doesPaymentExist(..)
	
	function addPaymentChannel_ToShowTime( $eventID, $showtimeID, $pChannelUID, $comment )
	{
		/**
		*	@created 15FEB2012-1459
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "INSERT INTO `payment_channel_availability` VALUES ( ?, ?, ?, ? );";
		return $this->db->query( $sql_command, Array(
				$eventID, $showtimeID, $pChannelUID, $comment
			)
		);
	}//addPaymentChannel_ToShowTime(..)
	
	function createPayment( $bookingNumber, $amount, $paymentMode, $userAccountNum, $customData = "" )
	{
		/**
		*	@created 23FEB2012-0016
		*	@params	 Obviously.
		*	@returns INTEGER - signifying payment unique ID/num if successfully created
		*			 BOOLEAN FALSE if not.
		**/
		
		date_default_timezone_set('Asia/Manila');
		$uniqueID = $this->generatePaymentUniqueID();
		$sql_command = "INSERT INTO `payments` VALUES (?, ?, ?, ?, ?, ?, ?, ? )";
		$dbResult = $this->db->query( $sql_command, Array(
			$uniqueID,
			$bookingNumber,
			$amount,
			$userAccountNum,
			$paymentMode,
			date("H:i:s"),
			date("Y-m-d"),
			$customData
		));	
		if( $dbResult ) return $uniqueID;
		else
			return false;
	}// createPayment(..)

	function createPaymentMode( &$data )
	{
		/**
		*	@created (can't remember)
		*	@params Obviously for the others, but for some:
				- 'internal_data' - as it's name implies, internal data use for that payment mode.
						Since contents differ per payment mode, they are stored as plain-text 
						with respective formatting on its own right and retrieved on their specific use.
				- 'internal_data_type' - either { "XML" | "WIN5" }
		*	@description Inserts into database the new payment mode specified by the parameters.
		*	@revised <Revision 43>
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		unset( $data[ 'mode' ] );
		$data[ 'UniqueID' ] = $this->getLastPaymentModeUniqueID() + 1 ;
		return $this->db->insert('payment_channel', $data );
	}
	
	function createPaymentChannelPermission( $accountNum, $eventID, $showtimeID, $pChannelID  )
	{
		/**
		*	@created 23FEB2012-0243
		*   @description Creates DB entry for permission/access of a certain showing time to
				the certain payment mode, and the permission applied is GRANT (1).
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		
		$sql_command = "INSERT INTO `payment_channel_permission` VALUES (?,?,?,?,?,?);";
		return $this->db->query( $sql_command, Array(
			$accountNum, $eventID, $showtimeID, $pChannelID, 1, ''
		));
	}//createPaymentChannelPermission(..)
	
	function createPurchase( $bookingNumber, $chargeType, $chargeDesc,
		$quantity, $amount, $deadlineDate, $deadlineTime, $comments = NULL )
	{
		/**
		*	@created 14FEB2012-1315
		*	@description Creates a purchase entry for a certain item, e.g. Ticket or rebooking fee.
				Default value of payment indicator should be zero (see the constant ).
				The NULL value signifies the column `UniqueID` - MySQL takes care of the appropriate
					value via AUTO_INCREMENT.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "INSERT INTO `purchase` VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ); ";
		
		return $this->db->query( $sql_command, Array(
				NULL, $bookingNumber, $chargeType, $chargeDesc, $quantity, 
				$amount, PURCHASE_NOTPAIDYET_INDICATOR, PURCHASE_INITIAL_PCHANNEL, 
				$deadlineDate, $deadlineTime, $comments ) 
		);
	}//createPurchase(..)
	
	function deleteAllBookingPurchases( $bookingNumber )
	{
		/**
		*	@created 14FEB2012-1319
		*	@description Obviously.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ?";
		return $this->db->query( $sql_command, Array( $bookingNumber ) );
	}// deleteAllBookingPurchases(..)
	
	function deletePaymentMode( $uniqueID )
	{
		/**
		*	@created (can't remember)
		*	@description Deletes a single payment mode.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "DELETE FROM `payment_channel` WHERE `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $uniqueID ) );
	}//deletePaymentMode(..)
	
	function deleteSinglePurchase( $bookingNumber, $uniqueID )
	{
		/**
		*	@created (can't remember)
		*	@description Deletes a single purchase item.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $bookingNumber, $uniqueID ) );
	}//deleteSinglePurchase(..)

    function deleteUnpaidPurchases( $bookingNumber )
	{
		/**
		*	@created (can't remember)
		*	@description Deletes all purchased items that are not paid.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		$sql_command = "DELETE FROM `purchase` WHERE `BookingNumber` = ? AND";
		$sql_command .= "  `Payment_UniqueID` = " . PURCHASE_NOTPAIDYET_INDICATOR;		
		return $this->db->query( $sql_command, Array( $bookingNumber ) );
	}//deleteUnpaidPurchases(..)
			
	function generatePaymentUniqueID()
	{
		/**
		*	@created <can't remember>
		*	@description Generates random numbers for use as a payment's unique ID.
		*	@returns INTEGER - The unique payment ID.
		**/
		$number;
		
		do{
				$number = rand( 555111, 999333 );	// just so random
		}while( $this->doesPaymentExist( $number ) );
		
		return $number;
	}//generatePaymentUniqueID(..)
	
	function getLastPaymentModeUniqueID()
	{	
		/**
		*	@created <can't remember>
		*	@description Gets the largest payment unique ID in the DB.
		*	@returns INTEGER - The unique payment ID.
		**/
		$sql_command = "SELECT * FROM  `payment_channel` ORDER BY  `UniqueID` DESC LIMIT 0 , 1000000";		
		$array_result = $this->db->query( $sql_command )->result();
		
		// now, what we want should be found at the first element
		if( count( $array_result ) > 0 )
		{			
			return intval( $array_result[0]->UniqueID );
		}else return 0;		
	}// getLastPaymentModeUniqueID(..)
	
	function getPaidPurchases( $bookingNumber )
	{		
		/**
		*	@created 04MAR2012-1655
		*	@description Gets a booking's paid purchases.
		*	@returns ARRAY of DB OBJECTS - of purchased items
					 BOOLEAN FALSE - If no item found.
		**/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` = ? AND";
		$sql_command .= " `Payment_UniqueID` != ".PURCHASE_NOTPAIDYET_INDICATOR;
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}//getPaidPurchases(..)
	
	function getPaymentModeByName( $name )
	{
		/**
		*	@returns The DB object of the payment channel if found, BOOLEAN FALSE if not.
		**/
		$sql_command = "SELECT * FROM `payment_channel` WHERE `Name` = ?";
		$arr_result = $this->db->query( $sql_command, Array(  $name ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}
	
	function getUnpaidPurchases( $bookingNumber )
	{
		/**
		*	@created 14FEB2012-1331
		*	@description Selects unpaid purchase items from `purchase` table. These are those entries
		*		whose `Payment_UniqueID` field's value is 0 - factory setting for not yet being paid.
		*	@returns if at least one entry is found, 
					- ARRAY containing the entries
				else
					- BOOLEAN FALSE
		**/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` = ? AND `Payment_UniqueID` = '0'";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}//getUnpaidPurchases
	
	function getPaymentChannels( $includeZero = FALSE )
	{
		/**
		*	@created 15FEB2012-1409				
		*	@description Gets all entries in the `payment_channel` table. This is used in Create Event Step 6.
		*	@parameters * $includeZero - BOOLEAN - The parameter is expected to be true when we are to include the payment Channel
				"FREE" because the ticket cost is zero.
		*	@returns if at least one entry is found, 
					- ARRAY containing the entries
				else
					- BOOLEAN FALSE
		**/
		$quantifier = ( $includeZero === TRUE ) ? " 1" : " `UniqueID` > 0 ";
		$sql_command = "SELECT * FROM `payment_channel` WHERE ".$quantifier;
		$arr_result = $this->db->query( $sql_command )->result();
		if( count($arr_result) > 0 )
			return $arr_result;
		else
			return false;
	}//getPaymentChannels()
	
	function getPaymentChannelsForEvent( $eventID, $showtimeID, $includeFree = FALSE, $exclude = FALSE )
	{
		/**		
		*	@description Gets payment channels for the specified $eventID and $showtimeID
		*	@parameters 
		        - $includeZero - BOOLEAN - The parameter is expected to be true when we are to include the payment Channel
				"FREE" because the ticket cost is zero.
				- $exclude - The UniqueID of a Payment Channel to be excluded from selection. If not supplied,
				default is BOOLEAN FALSE.
		*	@returns if at least one entry is found, 
					- ARRAY containing the entries
				else
					- BOOLEAN FALSE
		**/
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
				if( intval($arr_result[0]->UniqueID) === 0 ) unset( $arr_result[0] );		
			}
			if( $exclude !== FALSE )
			{
				for( $x=0, $y = count($arr_result); $x<$y; $x++ ){
					if( isset( $arr_result[$x] ) ){
						if( intval($arr_result[$x]->UniqueID) === intval($exclude) ) unset($arr_result[$x]);
					}
				}
			}
			return $arr_result;
	}//getPaymentChannelsForEvent
	
	function getSinglePaymentChannelByInternalDataMerchantEmail( $paymentProcessor = 'paypal', $email )
	{	
		/**
		*	@created 26MAY2012-1314		
		*   @assumptions The internal_data is of type WIN5 for now. If in XML, won't be able to find for now.
		*	@returns The DB object of the payment channel if found, BOOLEAN FALSE if not.
		**/
		$sql_command = "SELECT * FROM `payment_channel` WHERE `internal_data` REGEXP 'merchant_email=".mysql_real_escape_string( $email )."'";
		$sql_command .= " AND `internal_data` REGEXP 'processor=".mysql_real_escape_string( $paymentProcessor )."'";
		$arr_result = $this->db->query( $sql_command )->result();
		
		if( count ($arr_result) == 1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePaymentChannelByInternalDataMerchantEmail()
	
	function getSinglePaymentChannelByUniqueID( $uniqueID )
	{	
		/**
		*	@returns The DB object of the payment channel if found, BOOLEAN FALSE if not.
		**/
		$sql_command = "SELECT * FROM `payment_channel` WHERE `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $uniqueID ) )->result();
		
		if( count ($arr_result) == 1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePaymentChannelByUniqueID()
	
	function getSinglePaymentChannel( $eventID, $showtimeID, $uniqueID )
	{		
		/**
		*	@created 14FEB2012-1850	
		*   @assumptions Gets the DB entry for the specified payment channel for the specified event.
		*	@returns The DB object of the payment channel if found, BOOLEAN FALSE if not.
		**/
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
		/**
		*	@created 29FEB2012-1129
		*   @assumptions Gets the DB entry for the purchase item specified by the Booking Number and UniqueID
		*	@returns The DB object of the payment channel if found, BOOLEAN FALSE if not.
		**/
		$sql_command = "SELECT * FROM `purchase` WHERE `BookingNumber` = ? AND `UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber, $uniqueID ) )->result();
		
		if( count( $arr_result ) == 1 )
			return $arr_result[0];
		else
			return false;
	}// getSinglePurchase(..)
	
	function getSumTotalOfPaid( $bookingNumber, $purchases = Array() )
	{		
		/**
		*	@created 23FEB2012-0031
		*   @description A consolidating function that sums total worth of paid items for the 
				specified booking number.
		*	@returns See sumTotalCharges()
		**/
		if( count( $purchases ) < 1 ){		
			$purchases = $this->getPaidPurchases( $bookingNumber ); // get from database using $bookingNumber		
		}
		
		return $this->sumTotalCharges( $purchases );
	}// getSumTotalOfPaid(..)
	
	function getSumTotalOfUnpaid( $bookingNumber, $purchases = Array() )
	{		
		/**
		*	@created 23FEB2012-0031
		*   @assumptions A consolidating function that sums total worth of unpaid items for the 
				specified booking number.
		*	@returns See sumTotalCharges()
		*	@remarks 31MAR2012-1847: Might be redundant
		**/
		if( count( $purchases ) < 1 ){		// 04MAR2012-1657 : Why is this formerly ' > 0 '???
			$purchases = $this->getUnpaidPurchases( $bookingNumber ); // get from database using $bookingNumber		
		}
		
		return $this->sumTotalCharges( $purchases );
	}// getSumTotalOfUnpaid(..)

	function isPaypalPaymentOK( $_IPN_Array )
	{
		/**
		*	@created 22MAR2012-2321
			@description Refer to https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
				for validity.
			@returns array.
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
		$returnThis['details'] = $_IPN_Array['payment_status']." : Unspecified/Unknown reason."; 
		return $returnThis;	
	}//isPaypalPaymentOK(..)
	
	function setAsPaid( $bookingNumber, $uniqueID, $paymentUniqueID )
	{
		/**
		*	@created 23FEB2012-0037
		*	@description Sets the purchased item as paid.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		*/
		$sql_command = "UPDATE `purchase` SET `Payment_UniqueID` = ?, `Deadline_Date` = NULL, `Deadline_Time` = NULL";
		$sql_command .= " WHERE `BookingNumber` = ? AND `UniqueID` = ?";

		return $this->db->query( $sql_command, Array( $paymentUniqueID, $bookingNumber, $uniqueID ) );
	}//setAsPaid
	
	function setPaymentModeForPurchase( $bNumber, $pChannel, $uniqueID = NULL )
	{
		/**
		*	@created 22FEB2012-2351
		*	@description Sets the payment mode/channel of the unpaid purchase items of the specified
				booking number ( and may be depending on $uniqueID, where supplied ).
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		*/
		$insertTheseValues = Array( $pChannel, $bNumber );
		$sql_command = "UPDATE `purchase` SET `Payment_Channel_ID` = ? WHERE";
		$sql_command .= " `Payment_UniqueID` = " . PURCHASE_NOTPAIDYET_INDICATOR;
		$sql_command .= " AND `BookingNumber` = ?";
		
		if( $uniqueID !== NULL ){
			$sql_command .= " AND `UniqueID` = ?";
			$insertTheseValues[] = $uniqueID;
		}
		return $this->db->query( $sql_command,  $insertTheseValues );
	}//setPaymentModeForPurchase(..)s
	
	function sumTotalCharges( $purchasesArray )
	{
		/**
		*	@created 28FEB2012-1034
		*   @description Basically, receives array of MYSQL_OBJs returned by $this->get{'Unp'|'P'}aidPurchases(..) and sums 
				the total. 
		* 	@returns BOOLEAN FALSE - If the parameter is not an array.
					 FLOAT		   - The sum of the each element's Amount in the array.
		**/
		$totalCharges = 0.0;
		
		if( !is_array( $purchasesArray ) or count( $purchasesArray ) < 1 ) return false;		
		foreach( $purchasesArray as $singlePurchase ) $totalCharges += floatval($singlePurchase->Amount);
		
		return $totalCharges;
	}//sumTotalCharges(..)

	function updatePaymentMode( &$data )
	{
		/**
		*	@revised 01AUG2012-1406
		*	@description Simply updates an entry in the `payment_channel` table
		*/
		$uniqueID = $data['uniqueID'];
		// unset because these aren't DB columns to be updated
		unset( $data['mode'] );
		unset( $data['uniqueID'] );
		$this->db->where( 'UniqueID', $uniqueID );
		return $this->db->update('payment_channel', $data );
	}
	
	function updatePurchaseComments( $bookingNumber, $uniqueID, $comments )
	{
		/**
		*	@created 10MAR2012-1053
		*	@description Simply updates the comments.
		**/
		$sql_command = "UPDATE `purchase` SET `Comments` = ? WHERE `BookingNumber` = ? AND `UniqueID` = ?";		
		
		return $this->db->query( $sql_command, Array($comments, $bookingNumber, $uniqueID ) );
	}//updatePurchaseComments
}// class