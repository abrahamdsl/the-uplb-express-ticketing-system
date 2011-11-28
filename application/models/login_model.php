<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}

	// DEPRECATED 28 Nov 2011 1645
	//function check_and_Act_On_Login($redirectTo = NULL, $loadThisView = NULL, $data = NULL)
	
	function getUserInfo_for_Panel()
	{
		/*  24NOV2011 1144 | Taken from Redbana Internship Project, then edited
			made | abe | 15JUN2011_2343			
		*/
		
		return array(
						   'accountNum' => $this->session->userData('accountNum'),
						   'lastName' => $this->session->userData('lastName'), 
						   'firstName' => $this->session->userData('firstName'),
						   'middleName' => $this->session->userData('middleName')
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
		*/
		$data['firstName'] = $name['first'];
		$data['middleName'] = $name['middle'];
		$data['lastName'] = $name['last'];
		$data['accountNum'] = $accountNum;
		$data['logged_in'] = TRUE;
		
		$this->session->set_userdata($data);
		
		return true;
	}
	
	

}


/* End of file login_model.php */
/* Location: ./application/models/login_model.php */