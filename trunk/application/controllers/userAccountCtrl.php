<?php

class userAccountCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
		$this->load->library('session');
	}
	
	function index()
	{
		/*
		
			login-login
			
		*/
		$this->load->view('ohHome');
	
	}
	
	function userSignup()
	{
		/*
			function for user sign up
			Part1 - basic info
		*/		
		// set some session data to indicate that user is still signing up - first part
		$data['userSignup_step'] = 1;		
		
		$this->session->set_userdata($data);
		
		$this->load->view('userSignup');		// now load the webpage
	}
	
	function userSignup_step2()
	{
		$step = $this->session->userdata('userSignup_step');
		$status = $this->session->userdata('userSignup_status');
		
		/* this is a correct function call since we are receiving posted data with "studentNumber"
		   however, this can be a hazard if some arbitrary HTML made with a form having such "studentnumber"
		   submits to our url/function call
			
		*/
		$isFunctionCallValid = isset($_POST["studentNumber"]);		
		
		
		if(  $step == 1 &&  $isFunctionCallValid )
		{					
			$data['userSignup_step'] = 2;			// now, new step
			
			$this->session->set_userdata($data);
			$this->load->view('userSignup_part2');
		}else{
			// user is trying to access part 2 without acccomplishing step1, so redirect to step1 first
			redirect("userAccountCtrl/userSignup");
		}
	} // userSignup_step2()
	
	function newUserWelcome()
	{
		$isFunctionCallValid = isset($_POST["formValidityIndicator"]);		
		
		if( $isFunctionCallValid )
		{
			die("Welcome.... xoxo");
		}else{
			redirect("userAccountCtrl"); // redirect to homepage
		}
		
	}// newUserWelcome
	
	function test()
	{
		$this->load->view('test');
	}

}
?>