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
		
		include_once('_constants.inc');
		
		//EMAIL EXPERIMENTAL
		$this->load->library('email');
		$this->load->library('bookingmaintenance');
		$this->load->library('paypal_lib');
		$this->load->model('clientsidedata_model');
		$this->load->model('email_model');
		$this->load->model('guest_model');
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
		{   //ec 4100
			die( "You are not allowed to access this." );
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
		if( $this->UsefulFunctions_model->getRealIpAddr() == "127.0.0.1" )
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
		{	 //ec 4100
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
			
			All lines indented by another column than usual are for debugging only.
			Remove only when you are now so confident on how this works.
		*/
		$visitorIP = $this->UsefulFunctions_model->VisitorIP();
        $visitorHostName = gethostbyaddr( $visitorIP );
			log_message('DEBUG', 'IPN function accessed');
			log_message('DEBUG', 'IPN Connection from IP ' . $visitorIP );
			log_message('DEBUG', 'IPN Connection from URI ' . $visitorHostName );
			log_message('DEBUG', 'IPN Connection from Port ' . $_SERVER['REMOTE_PORT'] );
			log_message('DEBUG', 'IPN START POST DATA ');
			foreach( $_POST as $key => $value ) log_message('DEBUG', 'POST DATA '.$key.' => "'.$value.'"' );
			log_message('DEBUG', 'IPN END POST DATA ');				
 		$ipn_init = (isset($this->paypal_lib->ipn_data[ 'txn_id' ])) ? $this->paypal_lib->ipn_data[ 'txn_id' ] : "NULL";
			log_message('DEBUG', "IPN initial: ".$ipn_init);
		if ($this->paypal_lib->validate_ipn( $visitorHostName ) ) 
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
						$guestDetails;
						$totalCharges = floatval($this->clientsidedata_model->getPurchaseTotalCharge() );
						$paymentDescriptor = 'uxtcharge='.$totalCharges.';mc_fee='.$this->paypal_lib->ipn_data[ 'mc_fee' ].';';
						$paymentDescriptor .= 'payer_id='.$this->paypal_lib->ipn_data['payer_id'].';'.'txn_id='.$this->paypal_lib->ipn_data['txn_id'].';';
						$paymentDescriptor .= 'merchant_email='.$this->paypal_lib->ipn_data['business'].';';
						$paymentDescriptor .= 'processor=paypal;';
						$this->bookingmaintenance->processPayment( $bookingNumber, $paymentDescriptor );
												
						//EMAIL EXPERIMENTAL
						$guestDetails = $this->guest_model->getGuestDetails( $bookingNumber );
						if( $guestDetails !== false )
						{
							$this->emailMain( 1, $bookingNumber , $guestDetails[0]->Email );
							$guestCount = count(  $guestDetails );
							if( $guestCount  > 1 ) {
								for( $xy = 1; $xy < $guestCount; $xy++ )
								{
									 $this->emailMain( 2, $bookingNumber , $guestDetails[$xy]->Email );
								}
							}							
						}
						//END EMAIL
					}else{
						$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_5_FORWARD );	// set again to be able to access  payment modes page
						$data = $this->bookingmaintenance->assemblePaypalFishy( $isPaypalOKObj['details'] );
						$this->load->view('errorNotice', $data );
					}
			}
		}else{
			//ec 2150
			log_message('DEBUG', 'Invalid ipn');
			echo "INVALID_IPN";
		}
	}//ipn()
	
	private function emailMain( $mode = 1, $bookingNumber = "NULL", $destination )
	{		
		$this->email_model->initializeFromSales();
		
		$this->email->from('sales@uplbtickets.info', 'The UPLB Ticketing System');
		$this->email->to( $destination ); 						

		if( $mode === 1 )
		{
			$this->email->subject('Booking Receipt ' . $bookingNumber );
			$this->email->message('Thank you for your payment.');			
		}else{
			$this->email->subject('Meow meow Booking Receipt ' . $bookingNumber );
			$this->email->message('You have been booked by your friend. HAHAHA. Contact him/her.');			
		}
		$this->email->message('We are in the process of starting our email module so no more info provided ont this mail. HAHAHA.');	
		
		$this->email->send();
		log_message('DEBUG', 'email bug 1');
		log_message('DEBUG', var_dump( $this->email->print_debugger() ) );
		log_message('DEBUG', 'email bug 2');
	}
}
?>