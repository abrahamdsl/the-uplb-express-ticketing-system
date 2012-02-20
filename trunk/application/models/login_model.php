<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
	}
	
	function deleteUserCookies()
	{
		/*
			Created 19FEB2012-1357
		*/
		delete_cookie( 'firstName' );
		delete_cookie(  "middleName" );
		delete_cookie( "lastName" );
		delete_cookie( "accountNum" );
	}
	
	function getUserInfo_for_Panel()
	{
		/*  24NOV2011 1144 | Taken from Redbana Internship Project, then edited
			made | abe | 15JUN2011_2343			
			
			* Deprecated as of 19FEB2012-1349: Retained here until all calls to this from other files have been deleted. Delete on finish. 
		*/
		
		return array(
						   'accountNum' => 0,
						   'lastName' => 0,
						   'firstName' => 0,
						   'middleName' => 0,
		);	
	}
	
	function isUser_LoggedIn()
	{	
		$result = $this->session->userdata('logged_in');
		
		return $result;
	}
	
	function logout()
	{
		$data['logged_in'] = FALSE;
		$this->session->set_userdata($data);
		
		$this->session->sess_destroy();			
	}
	
	function setUserSession( $accountNum, $name  )
	{
		/* made 24 NOV 2011 1130
			sets the relevant UserSession Data for use throughout the app
			(i.e. for the name and account number of the customer)
			
			$name - string array having "first", "middle" and "last" as indices
			$accountNum - int var
			
			ASSUMPTION: all data passed here are already 'escaped'
			
			* Changed 19FEB2012-1348: We now only set accountNum in the session cookie. The names have their own cookies.				
		*/
		$cookieValidity = 3600 * 24;				// this means the cookie is valid for 24 hours.		
		$data['accountNum'] = $accountNum;
		$data['logged_in'] = TRUE;
		
		$this->session->set_userdata($data);
		
		// now set cookies, mainly for user bar
		$this->input->set_cookie( "firstName", $name['first'] , $cookieValidity );
		$this->input->set_cookie( "middleName", $name['middle'] , $cookieValidity );
		$this->input->set_cookie( "lastName", $name['last'] , $cookieValidity );
		$this->input->set_cookie( "accountNum", $accountNum , $cookieValidity );
		return true;
	}
	
	

}


/* End of file login_model.php */
/* Location: ./application/models/login_model.php */