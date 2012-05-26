<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 *  PayPal_Lib Library Class (Paypal IPN Class)
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
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * PayPal_Lib Controller Class (Paypal IPN Class)
 *
 * This CI library is based on the Paypal PHP class by Micah Carrick
 * See www.micahcarrick.com for the most recent version of this class
 * along with any applicable sample files and other documentaion.
 *
 * This file provides a neat and simple method to interface with paypal and
 * The paypal Instant Payment Notification (IPN) interface.  This file is
 * NOT intended to make the paypal integration "plug 'n' play". It still
 * requires the developer (that should be you) to understand the paypal
 * process and know the variables you want/need to pass to paypal to
 * achieve what you want.  
 *
 * This class handles the submission of an order to paypal as well as the
 * processing an Instant Payment Notification.
 * This class enables you to mark points and calculate the time difference
 * between them.  Memory consumption can also be displayed.
 *
 * The class requires the use of the PayPal_Lib config file.
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Commerce
 * @author      Ran Aroussi <ran@aroussi.com>
 * @copyright   Copyright (c) 2006, http://aroussi.com/ci/
 *
 */

// ------------------------------------------------------------------------

class Paypal_Lib {

	var $last_error;			// holds the last error encountered
	var $ipn_log;				// bool: log IPN results to text file?

	var $ipn_log_file;			// filename of the IPN log
	var $ipn_response;			// holds the IPN response from paypal	
	var $ipn_data = array();	// array contains the POST values for IPN
	var $fields = array();		// array holds the fields to submit to paypal

	var $submit_btn = '';		// Image/Form button
	var $button_path = '';		// The path of the buttons
	
	var $CI;
	
	function Paypal_Lib()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('url');
		$this->CI->load->helper('form');
		$this->CI->load->config('paypallib_config');
		$this->CI->load->model('payment_model');
		$this->CI->load->model('UsefulFunctions_model');
				
		define( 'PAYPAL_URL_TEST', 'https://www.sandbox.paypal.com/cgi-bin/webscr' );
		
		$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		$this->last_error   = '';
		$this->ipn_response = '';

		$this->ipn_log_file = $this->CI->config->item('paypal_lib_ipn_log_file');
		$this->ipn_log      = $this->CI->config->item('paypal_lib_ipn_log'); 
		
		$this->button_path = $this->CI->config->item('paypal_lib_button_path');
		
		// populate $fields array with a few default values.  See the paypal
		// documentation for a list of fields and their data types. These defaul
		// values can be overwritten by the calling script.
		$this->add_field('rm','2');			  // Return method = POST
		$this->add_field('cmd','_xclick');

		$this->add_field('currency_code', $this->CI->config->item('paypal_lib_currency_code'));
	    $this->add_field('quantity', '1');		
	}//Paypal_Lib()
	
	function add_field($field, $value) 
	{
		// adds a key=>value pair to the fields array, which is what will be 
		// sent to paypal as POST variables.  If the value is already in the 
		// array, it will be overwritten.
		$this->fields[$field] = $value;
	}
	
	function dump() 
	{
		// Used for debugging, this function will output all the field/value pairs
		// that are currently defined in the instance of the class using the
		// add_field() function.

		ksort($this->fields);
		echo '<h2>ppal->dump() Output:</h2>' . "\n";
		echo '<code style="font: 12px Monaco, \'Courier New\', Verdana, Sans-serif;  background: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; display: block; margin: 14px 0; padding: 12px 10px;">' . "\n";
		foreach ($this->fields as $key => $value) echo '<strong>'. $key .'</strong>:	'. urldecode($value) .'<br/>';
		echo "</code>\n";
	}
	
	function log_ipn_results($success) 
	{
		if (!$this->ipn_log) return NULL;  // is logging turned off?

		// Timestamp
		$text = '['.date('m/d/Y g:i A').'] - '; 

		// Success or failure being logged?
		if ($success) $text .= "SUCCESS!\n";
		else $text .= 'FAIL: '.$this->last_error."\n";

		// Log the POST variables
		$text .= "IPN POST Vars from Paypal:\n";
		foreach ($this->ipn_data as $key=>$value)
			$text .= "$key=$value, ";

		// Log the response from the paypal server
		$text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;

		// Write to log
		$fp=fopen($this->ipn_log_file,'a');
		fwrite($fp, $text . "\n\n"); 

		fclose($fp);  // close file
	}//log_ipn_results()
	
	function paypal_form($form_name='paypal_form', $testMode ) 
	{
		$str = '';
		$formURL;
		
		log_message('DEBUG','paypal form function testmode '.intval( $testMode ) );
		$formURL = ( $testMode === true ) ? PAYPAL_URL_TEST : $this->paypal_url;		
		log_message('DEBUG','Paypal url computed '.$formURL );		
		$str .= '<form method="post" action="'.$formURL.'" name="'.$form_name.'"/>' . "\n";
				
		foreach ($this->fields as $name => $value)		
			$str .= form_hidden($name, $value) . "\n";		
		$str .= form_close() . "\n";

		return $str;
	}
		
	function getPaypalCrucialDetails( $identType = 1, $identData = 2 )
	{
		/**
		* @created 26MAY2012-1307
		* @author Abe
		* @description 						
			The index 4 is checked for 'merchant_email' entry. If not found,returns BOOLEAN FALSE 
			to the caller (controller).
			In determining whether the PayPal account is in test mode ( developer.paypal.com ), the index 4
			is searched for 'testmode' entry. If not found or has value of "false", the account is not in test mode 
			(as in real-world payments and transactions are happening on the account ), if "true" or invalid value
			( not either "false" or "true" it is in test mode.		
		* @params
		 	$identType values:
				1 - $identData is the payment mode's `UniqueID`
				2 - $identData is the merchant's email
				3 - $identData is the payment mode's `internal_data` entry
		   $identData - obviously
		**/
		$paymentModeObj = NULL;
		$internalData;
		$testMode_check;
		$data = Array( 'merchant_email' => NULL, 'testmode' => false );
		
		switch( $identType )
		{
			case 1: $paymentModeObj = $this->CI->payment_model->getSinglePaymentChannelByUniqueID( $identData );
					if( $paymentModeObj === false ) return false;					
					break;
			case 2: $paymentModeObj = $this->CI->payment_model->getSinglePaymentChannelByInternalDataMerchantEmail( 'paypal', $identData );
					if( $paymentModeObj === false ) return false;
					break;
			case 3: break;
			default: return false;
		}
		if( is_null( $paymentModeObj ) )
		{
			// meaning, $identType is not 1 or 2
			$internalData = $identData;
		}else{
			$internalData = $paymentModeObj->internal_data;
		}
		$data[ 'merchant_email' ] = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data('merchant_email', $internalData );		
		if( $data[ 'merchant_email' ] === false ){
			return false;
		}
		$testMode_check = $this->CI->UsefulFunctions_model->getValueOfWIN5_Data('testmode', $internalData );			
		log_message( 'DEBUG', 'pplib 1 '. strval( $testMode_check) );
		log_message( 'DEBUG', 'pplib 2 is bool ' . intval( is_bool( $testMode_check) ) );
		if( $testMode_check === false ) 
			$data[ 'testmode' ] = false;
		else{
			switch( strval( $testMode_check ) )
			{
				case "false" : //default, no need to assign;
								break;
				case "true"  :  $data[ 'testmode' ] = true; 
								break;
				default		 :  $data[ 'testmode' ] = true;
			}
		}
		return $data;
	}//getPaypalCrucialDetails(..)
	
	function validate_ipn( $visitorHostName = NULL )
	{		
		// parse the paypal URL
		$url_parsed = parse_url($this->paypal_url);		  
				
		// generate the post string from the _POST vars aswell as load the
		// _POST vars into an arry so we can play with them from the calling
		// script.
		$post_string = '';	 
		if ( isset( $_POST ) and count( $_POST ) > 0  )
		{
			foreach ($_POST as $field => $value)
			{ 				
				// put line feeds back to CR+LF as that's how PayPal sends them out
				// otherwise multi-line data will be rejected as INVALID
                $value = str_replace("\n", "\r\n", $value);
                $this->ipn_data[$field] = $value;
                $post_string .= $field.'='.urlencode(stripslashes($value)).'&';
			}
		}else{
			return false;
		}
		
		// check first if the merchant email belongs to us.
		$merchantEmail = mysql_real_escape_string( $_POST[ 'receiver_email' ] );
		$data = $this->getPaypalCrucialDetails( 2, $merchantEmail );		
		if( $data === false ){
			log_message( 'ERROR', 'Merchant email delivered to IPN processing is not in our list of payment modes! Are we being hacked?' );
			return false;
		}else{
			log_message( 'DEBUG', 'Merchant email is in record' );
			if( $data[ 'testmode' ] ) $url_parsed = parse_url( PAYPAL_URL_TEST );
		}
		$post_string.="cmd=_notify-validate"; // append ipn check command

		// open the connection to paypal
		$fp = fsockopen('ssl://'.$url_parsed['host'], 443, $err_num,$err_str, 30);
		if( !$fp )
		{
			// could not open the connection.  If logging is on, the error message
			// will be in the log.
			log_message('DEBUG', 'Cannot open connection to PayPal for verification of IPN!');	// 5149
			$this->last_error = "fsockopen error no. $errnum: $errstr";
			$this->log_ipn_results(false);
			if( $visitorHostName !== NULL )
			{
				// try alternate validation
				if( $this->validate_ipn_fallback( $visitorHostName ) )
				{
					log_message('DEBUG', 'IPN ALTERNATE Validation SUCCESS.' ); //1151
					$this->log_ipn_results(true);
					return true;		 
				}else
					return false;
			}
			return false;
		}else{ 			
			// Post the data back to paypal
			fputs($fp, "POST ".$url_parsed['path'].$post_string." HTTP/1.1\r\n"); 
			fputs($fp, "Host: $url_parsed[host]\r\n"); 
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
			fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 
			fputs($fp, "Connection: close\r\n\r\n"); 
			fputs($fp, $post_string . "\r\n\r\n"); 
			log_message('DEBUG', 'IPN url ' . $url_parsed['host'].$url_parsed['path'].$post_string );
			// loop through the response from the server and append to variable
			while(!feof($fp))
				$this->ipn_response .= fgets($fp, 1024); 

			fclose($fp); // close connection
		}		
		
		if (strpos($this->ipn_response,"VERIFIED") !== FALSE )
		{
			// Valid IPN transaction.
			log_message('DEBUG', 'IPN Validation SUCCESS.' ); // 1150
			$this->log_ipn_results(true);
			return true;		 
		}else{
			// Invalid IPN transaction.  Check the log for details.
			log_message('DEBUG', 'IPN Validation Failed.' ); // 5150
			log_message('DEBUG', 'IPN PayPal\'s response : ' . $this->ipn_response);
			$this->last_error = 'IPN Validation Failed.';
			$this->log_ipn_results(false);	
			return false;
		}
	}//validate_ipn(..)

	function validate_ipn_fallback( $visitorHostName = "" )
	{
		/*
			@created 23APR2012-0147
			@purpose If for some reason we cannot use the POST BACK to PayPal (i.e. fsockopen() is disabled
			 on server, we will just check if the sender of the POST data is paypal.com.
			 
			 WARNING: If hackers can manipulate the data during transmission (i.e., we are not using SSL/HTTPS),
			 then we're dead  - payment can be completed. As much as possible do not use this ( use POST BACK
			  / fsockopen(..) bla bla ).
			)
			@demo 
			 Supposed $visitorHostName == "ipn.paypal.com"
			 After explode: $arr_vhname  = array( "ipn", "paypal", "com" );			
			 So last two elements of the array is checked if it belongs to PayPal's domain.
		*/
		$arr_vhname = explode('.', $visitorHostName );
		$arrlen = count( $arr_vhname );
		if( $arrlen < 1 ) return false;
		for( $x = 0; $x< $arrlen; $x++ ) $arr_vhname[ $x ] = strtolower( $arr_vhname[ $x]  );
		
		return ( $arr_vhname[ $arrlen-2 ] == "paypal" and $arr_vhname[ $arrlen-1 ] == "com" );
	}//validate_ipn_fallback
	
}

?>