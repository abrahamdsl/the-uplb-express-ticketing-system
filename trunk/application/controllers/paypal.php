<?php
/**
 *  PayPal_Lib Controller Class (Paypal IPN Class)
 *  Third-party plugin used, under Lesser GPL (License)
 *	Part of "The UPLB Express Ticketing System"
 *  Special Problem of Abraham Darius Llave / 2008-37120
 *	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
 *	University of the Philippines Los Banos
 *	------------------------------
 *
 *	See more description below.
 *
*/
/**
 * PayPal_Lib Controller Class (Paypal IPN Class)
 *
 * Paypal controller that provides functionality to the creation for PayPal forms, 
 * submissions, success and cancel requests, as well as IPN responses.
 *
 * The class requires the use of the PayPal_Lib library and config files.
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Commerce
 * @author      Ran Aroussi <ran@aroussi.com>
 * @copyright   Copyright (c) 2006, http://aroussi.com/ci/
 *
 */

class Paypal extends CI_Controller {

	function __construct()
	{
		parent::__construct();
				
		/*
			If you are testing this within your PC, make sure your internet connection/infrastructure
			supports port forwarding in port 80 so that your app can be accessed over the Internet (thus
			you can do PayPal post back ).
			
			(i.e., if server address is 128.31.12.212, it should be accessible on the net via port 80
				if on HTTP or if by HTTPS, respective port, which is 443 by default, or simply on the 
				broswer's address bar, entering "http://128.31.12.212" will open your website in your PC served
				by your webserver.
			).
		*/
		define( 'NON_HOSTING_IPADDR', "112.207.14.170" );
		
		/*
			Our default paypal merchant address
		*/
		define( 'PAYPAL_MERCHANT_EMAIL', 'abraha_1332349997_biz@gmail.com' );
		/*
			Should we go to sandbox site or REAL Paypal site?
		*/
		define( 'ISPAYPAL_TEST_MODE', TRUE );
		
		include_once( APPPATH.'constants/_constants.inc');
		
		//EMAIL EXPERIMENTAL
		$this->load->library('email');
		$this->load->library('bookingmaintenance');
		$this->load->library('functionaccess');
		$this->load->library('paypal_lib');
		$this->load->model('clientsidedata_model');
		$this->load->model('email_model');
		$this->load->model('guest_model');
		$this->load->model('payment_model');
		$this->load->model('usefulfunctions_model');
	}
			
	private function serializeIPN( $IPN_str )
	{
		$returnThis = "";
		
		foreach ($IPN_str as $key => $value) $returnThis .= $key.'='.$value.';';
		return $returnThis;
	}
	
	function index()
	{		
		// This is not intended to give a user interface.
		redirect('eventctrl');
	}
	
	function process()
	{
		/*
			IMPORTANT! The current server location better support port forwarding/accessible on the net
			
			
			Cookie data for Paypal Separated by pipes:
			<BOOKING-NUMBER>|<BASE-CHARGE>|<PAYPAL-FEE-TOTAL>|<"CHARGE DESCRIPTION">|<INTERNAL_DATA>
			
			<INTERNAL_DATA> - WIN5 format containing merchant email
		*/
		$paypalData;
		$paypalData_tokenized;
		$prepURL;
		$merchantEmail;
		$testMode = false;
		$testMode_check;

		if( !$this->clientsidedata_model->isPaypalAccessible() )
		{   
			if( !$this->functionaccess->preBookStep6PR_OnlinePayment_Check( STAGE_BOOK_6_PAYMENTPROCESSING ) ) return false;			
		}
		$paypalData = $this->clientsidedata_model->getDataForPaypal();
		if( $paypalData === false )
		{	// ec 4150
			die( "Paypal data not found.");
		}
		$prepURL = base_url();
	
		/*
			Check for and act on the localhost controversy!			
		*/
		if( $this->usefulfunctions_model->getRealIpAddr() == "127.0.0.1" )
		{
			$prepURL = str_replace('localhost', NON_HOSTING_IPADDR, $prepURL );
			$prepURL = str_replace('127.0.0.1', NON_HOSTING_IPADDR, $prepURL );
		}
		$paypalData_tokenized = explode('|', $paypalData );	
				
		// Merchant email and testmode is found in index 4 of $paypalData_tokenized, we extract it via this function call
		$data = $this->paypal_lib->getPaypalCrucialDetails( 3, $paypalData_tokenized[4] );
		if( $data === false )
		{
			$this->clientsidedata_model->setPaypalCrucialDataErrorNoticeAccessible();
		    redirect( 'paypal/crucial_data_error' );
		}
		$this->paypal_lib->add_field('currency_code', 'PHP' );
		$this->paypal_lib->add_field('business'		, $data[ 'merchant_email' ] );
	    $this->paypal_lib->add_field('return'		, base_url().'paypal/success' );
	    $this->paypal_lib->add_field('cancel_return', base_url().'paypal/cancel' );
		
		// We only need to key in the prepped URL in notifyURL
	    $this->paypal_lib->add_field('notify_url'	, $prepURL.'paypal/ipn' ); 
		
	    $this->paypal_lib->add_field('custom'		, 'The item number is your booking number.' );
		$this->paypal_lib->add_field('item_name'	, $paypalData_tokenized[3] );
	    $this->paypal_lib->add_field('item_number'	, $paypalData_tokenized[0] );
	    $this->paypal_lib->add_field('amount'		, floatval($paypalData_tokenized[1]) +  floatval($paypalData_tokenized[2]) );
		log_message('DEBUG','pp lib 3 back in controller testmode is ' . intval( $data ['testmode'] ) );
	    $data['paypal_form'] = $this->paypal_lib->paypal_form( 'paypal_form', $data[ 'testmode' ] );	
		$this->load->view('payPalRedirect', $data);        
	}//process()

	function crucial_data_error()
	{
		if( $this->clientsidedata_model->getPaypalCrucialDataErrorNoticeAccessible() !== FALSE )
		{
			$this->load->view( 'errorNotice', $this->bookingmaintenance->assemblePaypalPaymentMissingCrucial() );
			$this->clientsidedata_model->deletePaypalCrucialDataErrorNoticeAccessible();
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD ); 
			$this->clientsidedata_model->setBookingProgressIndicator( 5 );
		}else{
			redirect( 'paypal' );
		}
	}
		
	function cancel()
	{	
		$isMB =  $this->functionaccess->isActivityManageBooking();
		// set again to be able to access  payment modes page
		$this->clientsidedata_model->updateSessionActivityStage( $isMB ? STAGE_MB4_CONFIRM_FW : STAGE_BOOK_5_FORWARD );	
		$this->load->view('errorNotice', $this->bookingmaintenance->assemblePaypalPaymentUserCancelledNotification( $isMB ) );		
	}
	
	function success()
	{
		// This is where you would probably want to thank the user for their order
		// or what have you.  The order information at this point is in POST 
		// variables.  However, you don't want to "process" the order until you
		// get validation from the IPN.  That's where you would have the code to
		// email an admin, update the database with payment status, activate a
		// membership, etc.
	
		// You could also simply re-direct them to another page, or your own 
		// order status page which presents the user with the status of their
		// order based on a database (which can be modified with the IPN code 
		// below).	
		$isActivityManageBooking;
		$forwardStage;
		$forwardURL = "eventctrl/";
		
		if( !$this->clientsidedata_model->isPaypalAccessible() )
		{	 //ec 4100
			die( "You are not allowed to access this." );
		}
		$isActivityManageBooking = $this->functionaccess->isActivityManageBooking();
		$forwardStage = $isActivityManageBooking ? STAGE_MB9_FINAL_FW : STAGE_BOOK_6_FORWARD;
		$forwardURL .= $isActivityManageBooking ? 'managebooking_finalize_forward'  : 'book_step6_forward';
		$this->clientsidedata_model->updateSessionActivityStage( $forwardStage ); 
		$this->clientsidedata_model->setBookingProgressIndicator( 6 );
		redirect( $forwardURL );		
	}// success()
	
	function ipn()
	{
		/*
			01APR2012-2239 : Processing for refunds, reversal and others are pending here.
			
			All lines indented by another column than usual are for debugging only.
			Remove only when you are now so confident on how this works.
		*/
		$visitorIP = $this->usefulfunctions_model->VisitorIP();
        $visitorHostName = gethostbyaddr( $visitorIP );
		
		/* <area id="paypal_ipn_postdata_debug" > */ {
			log_message('DEBUG', 'IPN function accessed');
			log_message('DEBUG', 'IPN Connection from IP ' . $visitorIP );
			log_message('DEBUG', 'IPN Connection from URI ' . $visitorHostName );
			log_message('DEBUG', 'IPN Connection from Port ' . $_SERVER['REMOTE_PORT'] );
			log_message('DEBUG', 'IPN START POST DATA ');
			foreach( $_POST as $key => $value ) log_message('DEBUG', 'POST DATA '.$key.' => "'.$value.'"' );			
			log_message('DEBUG', 'IPN END POST DATA ');
			foreach( $_COOKIE as $key => $value ) log_message('DEBUG', 'COOKIE DATA '.$key.' => "'.$value.'"' );
			$ipn_init = (isset($this->paypal_lib->ipn_data[ 'txn_id' ])) ? $this->paypal_lib->ipn_data[ 'txn_id' ] : "NULL";
			log_message('DEBUG', "IPN initial: ".$ipn_init);
		}
		// </area> 
		if ($this->paypal_lib->validate_ipn( $visitorHostName ) ) 
		{
			$bookingNumber = $this->paypal_lib->ipn_data[ 'item_number' ];
				log_message('DEBUG', "IPN received for booking number: ".$bookingNumber);
						
			switch( $this->paypal_lib->ipn_data[ 'payment_status' ] )
			{
				case "Refunded":
						log_message('DEBUG','IPN received for '. $bookingNumber . ' is REFUND ');
						break;
				case "Reversal":
						log_message('DEBUG','IPN received for '. $bookingNumber . ' is REVERSAL ');
						break;
				default: 
					$isPaypalOKObj = $this->payment_model->isPaypalPaymentOK($this->paypal_lib->ipn_data );
					if( $isPaypalOKObj['boolean'] )
					{ 
						$billingInfoArray = $this->bookingmaintenance->getBillingRelevantData( $bookingNumber );
						$bookingObj       = $this->booking_model->getBookingDetails( $bookingNumber ); 
						$infoArray = Array(
							"eventID" => $bookingObj->EventID,
							"showtimeID" => $bookingObj->ShowingTimeUniqueID,
							"ticketClassGroupID" => $bookingObj->TicketClassGroupID,
							"ticketClassUniqueID" => $bookingObj->TicketClassUniqueID
						);
						// manage booking centric or not? act if ever.
						$isThisForManageBooking = $this->booking_model->isBookingUpForChange( $bookingNumber );
						if( $isThisForManageBooking ){
							$infoArray[ "transactionID" ] = $this->usefulfunctions_model->getValueOfWIN5_Data( 
								'transaction', 
								$billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Comments
							);
						}
						//payment descriptor mess
						$totalCharges = $billingInfoArray[ AKEY_AMOUNT_DUE ];
 						$paymentDescriptor = 'uxtcharge='.$totalCharges.';';
						$paymentDescriptor .= 'payer_id='.$this->paypal_lib->ipn_data['payer_id'].';'.'txn_id='.$this->paypal_lib->ipn_data['txn_id'].';';
						$paymentDescriptor .= 'merchant_email='.$this->paypal_lib->ipn_data['business'].';';
						$paymentDescriptor .= 'processor=paypal;';
						// now call payment gateway
						$response_pandc = $this->bookingmaintenance->pay_and_confirm(
							$bookingNumber,
							$isThisForManageBooking ? MANAGE_BOOKING :BOOK, 
							$paymentDescriptor,
							$totalCharges,
							STAGE_CONFIRM_2_FORWARD,
							$infoArray,
							Array(
								intval( @$billingInfoArray[ AKEY_UNPAID_PURCHASES_ARRAY ][0]->Payment_Channel_ID )
							)
						);
						if( $response_pandc[ "boolean" ] )
						{
							// payment creation and slot confirmation successful
							$guestDetails = $this->guest_model->getGuestDetails( $bookingNumber );
							if( $guestDetails !== false )
							{
								$this->bookingmaintenance->sendEmailOnBookSuccess(
									$bookingNumber,
									$guestDetails,
									$isThisForManageBooking ? 4 : 2
								);
							}
						}else{
							// HOW ABOUT HERE????
							$sendback = "ERROR|". $response_pandc[ "code" ]."|". $response_pandc[ "message" ];
							if( isset( $response_pandc["misc"] ) ) foreach( $response_pandc["misc"] as $val ) $sendback .= ( $val."|" ); 
							log_message("DEBUG", "paypal::ipn PAYMENT GATEWAY Error : " . $sendback);
						}
					}else{
						/* Email/notify user back in app that there's something wrong with paypal payment. 
						   Can opt to refund later but choose another payment method for now.
						*/
						log_message('DEBUG','PAYPAL Payment Questionable');
						echo "QUESTIONABLE";
					}
			}
		}else{
			//ec 2150
			log_message('DEBUG', 'Invalid ipn');
			echo "INVALID_IPN";
		}
	}//ipn()
}
?>