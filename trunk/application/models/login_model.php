<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function isUser_LoggedIn()
	{	
		$result = $this->session->userdata('logged_in');
		
		return $result;
	}
	
	function check_and_Act_On_Login($redirectTo = NULL, $loadThisView = NULL, $data = NULL)
	{
		/*	22 NOV 2011 | Taken from Redbana Internship Project
			--------------------
			made | abe | 02JUN2011_1340
		    - this is a function intended to be called on by controllers, 
			wherein a user is checked if logged in, and if not acts accordingly
			based on the parameters passed by the calling controllers
			If a user is logged in, function ends.
			
			ASSUMPTION:
			Either $redirectTo or $loadThisView can be used.
			Cannot be both null.
			If both specified, $redirectTo is prioritized.
			PARAMS:						
			$redirectTo   :
			$loadThisView :
			$data         : error messages
			
			RETURNS:
			nothing
		*/
		
		if ( $this->isUser_LoggedIn() == FALSE )
		{
			if($redirectTo == NULL and $loadThisView == NULL)
			{
				die("check_and_Act_On_Login: Specify where to go!");
			}else{
				if($redirectTo != NULL){
				    $this->session->set_userdata('LOGIN_WARNING', $data); 
					redirect($redirectTo);			// as of 02JUN2011, the current problem is how to pass data by redirecting
				}
				if($loadThisView != NULL) $this->load->view($loadThisView, $data);				
			}	
		}
	} // check_and_Act_On_Login

}


/* End of file login_model.php */
/* Location: ./application/models/login_model.php */