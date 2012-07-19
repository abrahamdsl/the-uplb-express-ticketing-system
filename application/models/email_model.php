<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  Email model
 *  Created 21MAY2012-132
 *	Part of "The UPLB Express Ticketing System"
 *  Special Problem of Abraham Darius Llave / 2008-37120
 *	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
 *	University of the Philippines Los Banos
 *	------------------------------
 *
 *	Various functions relating to email functionality of the system.
 *
**/

class email_model extends CI_Model {
	var $senderEmailAddr = NULL;
	var $senderName = NULL;
	
	function __construct()
	{
		parent::__construct();		
		define( 'DEFAULT_SENDER_NAME', 'The UPLB Express Ticketing System' );
		define( 'DB_IDENT_EMAIL_CI_DEFAULT_PROTOCOL', 'EMAIL_CI_DEFAULT_PROTOCOL'  );
		define( 'DB_IDENT_EMAIL_SALES', 'EMAIL_SALES' );
		define( 'DB_IDENT_EMAIL_SALES_PASSWORD', 'EMAIL_SALES_PASSWORD' );
		define( 'DB_IDENT_EMAIL_SALES_SERVER_PATH', 'EMAIL_SALES_SERVER_PATH' );
		define( 'DB_IDENT_EMAIL_SMTP_HOST_ADDRESS', 'EMAIL_SMTP_HOST_ADDRESS' );
		define( 'DB_IDENT_EMAIL_SERVICE_STATUS', 'EMAIL_SERVICE_STATUS' );
		define( 'EM_USER_AGENT', 'CodeIgniter/2.1.0 SalesDepartment/http://uplbtickets.com' );
		$this->load->library('email');
	}
	
	private function getDefaultEmailDetailsUnified( $identifier )
	{
		/**
		*	@created 21MAY2012-1346
		*	@purpose Gets the email detail to be used in email transmission, as supported
						by CodeIgniter, as specified in $identifier parameter.
		             Such values should be changeable/set up by the system administrator.
		
		*   @returns STRING - if DB entry is found, and that value is returned
					 BOOLEAN FALSE - DB entry is not found
		*
		**/	
		$returnThis         	= NULL;
		$sql_command           	= "SELECT * FROM `system_settings` WHERE `Name` = '".$identifier."'";
		
		$arr_result  		   = $this->db->query( $sql_command )->result();
		return( (count ($arr_result) === 1 ) ? $arr_result[0]->Value : FALSE );
	}//getDefaultEmailUnified(..)
	
	private function getDefaultEmailProtocol()
	{
		/**
		*	@created 21MAY2012-1346
		*	@purpose Gets the email protocol to be used in email transmission, as supported
						by CodeIgniter.
		             Such values should be changeable/set up by the system administrator.
		
		*   @returns STRING - if DB entry is found, and that value is returned
					 BOOLEAN FALSE - DB entry is not found
		*
		**/			
		return( $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_CI_DEFAULT_PROTOCOL ) );
	}//getDefaultEmailProtocol(..)
	
	private function getSalesEmailServerPath()
	{
		/**
		*	@created 21MAY2012-1346
		*	@purpose Gets the server path to be used in email transmission, as supported
						by CodeIgniter.
		             Such values should be changeable/set up by the system administrator.
		
		*   @returns STRING - if DB entry is found, and that value is returned
					 BOOLEAN FALSE - DB entry is not found
		*
		**/			
		return( $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_SALES_SERVER_PATH ) );
	}//getSalesEmailServerPath(..)
	
	private function getSMTPHost()
	{		
		/**
		*	@created 21MAY2012-1346
		*	@purpose Gets the server path to be used in email transmission, as supported
						by CodeIgniter.
		             Such values should be changeable/set up by the system administrator.
		
		*   @returns STRING - if DB entry is found, and that value is returned
					 BOOLEAN FALSE - DB entry is not found
		*
		**/			
		return( $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_SMTP_HOST_ADDRESS ) );
	}//getSMTPHost(..)
	
	function initializeFromSales( $overwriteGlobalEmail = false )
	{
		/**
		*	@created <i forgot, maybe late May 2012>
		*	@description Gets essential email details from database.
		*	@returns Array, structure:
				'boolean' => BOOLEAN Indicates whether transaction is successful or not in general.
				'code'	  => INT code if any for the error
				'message' => STRING message explaining the error.
		*	@remarks Indented than usual lines are for debugging purposes only.
		*	@revised 05JUL2012-1307
		**/
		$config    = Array();
		$emailCombiInfo = $this->getSalesEmailInfoFromDB( TRUE );

		$config['useragent'] = EM_USER_AGENT;
		$config['protocol']  = $this->getDefaultEmailProtocol();
		$config['mailpath']  = $this->getSalesEmailServerPath();
		$config['smtp_host'] = $this->getSMTPHost();
		$config['smtp_user'] = $emailCombiInfo['EMAIL'];
		$config['smtp_pass'] = $emailCombiInfo['PASSWORD'];
		/*
			If all necessary entries are found in the DB, there shouldn't be 
			any BOOLEAN FALSE values in the $config array.
		*/
		if( in_array( FALSE, $config, TRUE ) )
		{
			$errorMsg = 'One or more necessary email info assumed to be in the database is not found.';
				log_message( 'ERROR', 'email_model->initializeFromSales(): ERROR , '.$errorMsg );
				foreach( $config as $key => $eachEntry ) log_message('DEBUG', 'Info ' . $key . ' => '. $eachEntry);			
			return Array( 'boolean' => FALSE, 'code' => 4310, 'message' => $errorMsg ); // EC 4310
		}
		// initialize the email functionality of CodeIgniter given the settings
		$this->email->initialize($config);
		// if sender email address is the same as smtp_user
		if( $overwriteGlobalEmail ) $this->senderEmailAddr = $emailCombiInfo['EMAIL'];
		return Array( 'boolean' => TRUE, 'code' => 0, 'message' => NULL );
	}//initializeFromSales(..)
	
	function isEmailOn(){
		/**
		*	@created 16JUL2012-1228
		*	@description Checks if the email service of the app is turned on or off.
		*	@returns BOOLEAN
				FALSE if the system setting in the DB is not found or email service is set to zero (off)
				TRUE , obviously.
		**/
		$val  = $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_SERVICE_STATUS );
		if( $val === FALSE ) return FALSE;
		return( intval($val) === 1 );
	}
	
	function from( $emailAdd, $senderName )
	{
		$emailAdd_local   = ( $emailAdd === "DEFAULT" )   ? $this->senderEmailAddr     : $emailAdd;
		$senderName_local = ( $senderName === "DEFAULT" ) ?  DEFAULT_SENDER_NAME : $senderName;
		$this->email->from( $emailAdd_local, $senderName_local );
	}
	
	function getDefaultSenderName()
	{
		/**
		*	@created 25JUN2012-1306
		*	@description Gets the default sender name for use in our emails. 
		**/
		return DEFAULT_SENDER_NAME;
	}
	
	function getSalesEmailInfoFromDB( $includePass = false )
	{
		/**
		*	@created 21MAY2012-1335
		*	@purpose Gets the email address of the "Sales Email" from the Database.
		             Such values should be changeable/set up by the system administrator.
		
		*   @returns Array, associative.
					 Keys: "email", "password"
					 Values of "email" : * either the email address in string (as entered in the DB)
									     * or if not found, BOOLEAN false.
					 Values of "password" : * if $includePass is BOOLEAN false, NULL.
											* else
												* password in the DB, unencrypted.
												* or if not found, BOOLEAN false.
        *												
		**/
		$returnThis = Array( "EMAIL" => NULL, "EMAIL" => NULL );
		
		$returnThis[ "EMAIL" ]       = $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_SALES );
		if( $includePass === true ){
		   $returnThis[ "PASSWORD" ] = $this->getDefaultEmailDetailsUnified( DB_IDENT_EMAIL_SALES_PASSWORD );		
		}
		
		return $returnThis;
	}//getSalesEmailInfoFromDB(..)
	
	function message( $message )
	{
		if( is_array( $message ) )
		{
			foreach( $message as $singleMsg ) $this->email->message( $singleMsg );
		} else {
			$this->email->message( $message );
		}
	}
		
	function send()
	{		
		return $this->email->send();
	}
	
	function subject( $subject )
	{
		$this->email->subject( $subject );
	}
	
	function to( $destination )
	{
		$this->email->to( $destination );
	}
	
	
}//Class