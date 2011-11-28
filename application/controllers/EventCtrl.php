<?php
/*
CREATED 28 NOV 2011 2035
*/

class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('Permission_model');
		
		if( !$this->login_model->isUser_LoggedIn() ) redirect('/SessionCtrl');
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	function create()
	{
		$data['createEvent_step'] = 1;			// now step 1 first
		$this->session->set_userdata($data);
	
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		$this->load->view('createEvent/createEvent_001b', $data);
	}//create
	
} //class
?>