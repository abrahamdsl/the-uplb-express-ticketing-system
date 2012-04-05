<?php
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
			supports port forwarding so that your app can be accessed over the Internet (thus
			you can do PayPal post back ).
			
			(i.e., if server address is 128.31.12.212, it should be accessible on the net via port 80
				if on HTTP or if by HTTPS, respective port, which is 443 by default, or simply on the 
				broswer's address bar, entering "http://128.31.12.212" will open your website in your PC served
				by your webserver.
			).
		*/
		define( 'NON_HOSTING_IPADDR', "112.207.14.170" );
		
		/*
			Our paypal merchant address
		*/
		define( 'PAYPAL_MERCHANT_EMAIL', 'abraha_1332349997_biz@gmail.com' );
		/*
			Should we go to sandbox site or REAL Paypal site?
		*/
		define( 'ISPAYPAL_TEST_MODE', TRUE );
		/* 
			Please check EventCtrl on whether these are the same. The definitions there precede this, so
			change accordingly if different.
		*/
		define( 'AKEY_UNPAID_PURCHASES_ARRAY', 'unpaidPurchasesArray' );		
		define( 'AKEY_PAID_PURCHASES_ARRAY', 'paidPurchasesArray' );		
		define( 'AKEY_UNPAID_TOTAL', 'unpaidTotal' );		
		define( 'AKEY_PAID_TOTAL', 'paidTotal' );		
		define( 'AKEY_AMOUNT_DUE', 'amountDue' );		
		define( 'STAGE_BOOK_1_PROCESS', 0 );
		define( 'STAGE_BOOK_1_FORWARD', 1 );
		define( 'STAGE_BOOK_2_PROCESS', 2 );
		define( 'STAGE_BOOK_2_FORWARD', 3 );
		define( 'STAGE_BOOK_3_PROCESS', 4 );
		define( 'STAGE_BOOK_3_FORWARD', 5 );		
		define( 'STAGE_BOOK_4_PROCESS', 6 );
		define( 'STAGE_BOOK_4_CLASS_1_FORWARD', 7 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_CLASS_2_FORWARD', 8 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_FORWARD', 9 );
		define( 'STAGE_BOOK_5_PROCESS', 10 );
		define( 'STAGE_BOOK_5_FORWARD', 11 );
		define( 'STAGE_BOOK_6_PROCESS', 12 );
		define( 'STAGE_BOOK_6_PAYMENTPROCESSING', 13 );
		define( 'STAGE_BOOK_6_FORWARD', 14 );
		
		$this->load->library('bookingmaintenance');
		$this->load->library('paypal_lib');
		$this->load->model('clientsidedata_model');
		$this->load->model('Payment_model');
		$this->load->model('UsefulFunctions_model');
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
		redirect('EventCtrl');
	}
	
	function process()
	{
		/*
			IMPORTANT! The current server location should support port forwarding/accessible on the net
			
			
			Cookie data for Paypal Separated by pipes:
			<BOOKING-NUMBER>|<BASE-CHARGE>|<PAYPAL-FEE-TOTAL>|"CHARGE DESCRIPTION">
		*/
		$paypalData;
		$paypalData_tokenized;
		$prepURL;
						
		if( !$this->clientsidedata_model->isPaypalAccessible() )
		{
			die( "You are not allowed to access this." );
		}
		$paypalData = $this->clientsidedata_model->getDataForPaypal();
		if( $paypalData === false )
		{
			die( "Paypal data not found.");
		}
		$prepURL = base_url();
	
		/*
			Check for and act on the localhost controversy!			
		*/
		if( $this->UsefulFunctions_model->getRealIpAddr() == "127.0.0.1" )
		{
			$prepURL = str_replace('localhost', NON_HOSTING_IPADDR, $prepURL );
			$prepURL = str_replace('127.0.0.1', NON_HOSTING_IPADDR, $prepURL );
		}
		$paypalData_tokenized = explode('|', $paypalData );		
		$this->paypal_lib->add_field('currency_code', 'PHP' );
		$this->paypal_lib->add_field('business'		, PAYPAL_MERCHANT_EMAIL );
	    $this->paypal_lib->add_field('return'		, base_url().'paypal/success' );
	    $this->paypal_lib->add_field('cancel_return', base_url().'paypal/cancel' );
		
		// We only need to key in the prepped URL in notifyURL
	    $this->paypal_lib->add_field('notify_url'	, $prepURL.'paypal/ipn' ); 
		
	    $this->paypal_lib->add_field('custom'		, 'The item number is your booking number.' );
		$this->paypal_lib->add_field('item_name'	, $paypalData_tokenized[3] );
	    $this->paypal_lib->add_field('item_number'	, $paypalData_tokenized[0] );
	    $this->paypal_lib->add_field('amount'		, floatval($paypalData_tokenized[1]) +  floatval($paypalData_tokenized[2]) );
		
	    $data['paypal_form'] = $this->paypal_lib->paypal_form();	
		$this->load->view('payPalRedirect', $data);        
	}//process()
		
	function cancel()
	{		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );	// set again to be able to access  payment modes page
		$data = $this->bookingmaintenance->assemblePaypalPaymentUserCancelledNotification();
		$this->load->view('errorNotice', $data );		
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
		$bookingNumber;
		
		if( !$this->clientsidedata_model->isPaypalAccessible() )
		{
			die( "You are not allowed to access this." );
		}		
		$bookingNumber = $this->clientsidedata_model->getBookingNumber();		
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_6_FORWARD ); 
		$this->clientsidedata_model->setBookingProgressIndicator( 6 );
		redirect('EventCtrl/book_step6_forward');		
	}// success()
	
	function ipn()
	{
		/*
			01APR2012-2239 : Processing for refunds, reversal and others are pending here.
		*/
 		$ipn_init = $this->paypal_lib->ipn_data[ 'txn_id' ];
		log_message('DEBUG', "IPN initial: ".$ipn_init);
		if ($this->paypal_lib->validate_ipn()) 
		{
			$bookingNumber = $this->paypal_lib->ipn_data[ 'item_number' ];
			log_message('DEBUG', "IPN received for booking number: ".$bookingNumber);
						
			switch( $this->paypal_lib->ipn_data[ 'payment_status' ] )
			{
				case "Refunded":				
						break;
				case "Reversal":
						break;
				default: 
					$isPaypalOKObj = $this->Payment_model->isPaypalPaymentOK($this->paypal_lib->ipn_data );
					if( $isPaypalOKObj['boolean'] )
					{
						$totalCharges = floatval($this->clientsidedata_model->getPurchaseTotalCharge() );
						$paymentDescriptor = 'uxtcharge='.$totalCharges.';mc_fee='.$this->paypal_lib->ipn_data[ 'mc_fee' ].';';
						$paymentDescriptor .= 'payer_id='.$this->paypal_lib->ipn_data['payer_id'].';'.'txn_id='.$this->paypal_lib->ipn_data['txn_id'].';';
						$this->bookingmaintenance->processPayment( $bookingNumber, $paymentDescriptor );
					}else{
						$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );	// set again to be able to access  payment modes page
						$data = $this->bookingmaintenance->assemblePaypalFishy( $isPaypalOKObj['details'] );
						$this->load->view('errorNotice', $data );
					}
			}
		}else{
			log_message('DEBUG', 'Invalid ipn');
			echo "INVALID_IPN";
		}
	}//ipn()
}
?>