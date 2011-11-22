<?php

class SessionCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
	}
	
	function index()
	{
		/*
		
			login-login
			
		*/
		$this->load->view('ohHome');
	
	}
	
	

}
?>