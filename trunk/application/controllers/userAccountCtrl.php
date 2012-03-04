<?php

class userAccountCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
		$this->load->library('session');
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('MakeXML_model');
		$this->load->model('Permission_model');
	}
	
	function index()
	{
		/*
		
			login-login
			
		*/
		if( $this->login_model->isUser_LoggedIn() )
		{
			$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
			$this->load->view('homepage', $data);
		}else{			
			$this->userSignup();
		}	
	}//index
	
	function getUserInfoForBooking()
	{
		/*
			Created 26FEB2012-2026
		*/
		$mainInfo;
		$username;
		$accountNum = false;
		
		if( $this->input->is_ajax_request() === false ) redirect('/');				
		
		$username = $this->input->post( 'username' );						
		if( $username === "DEFAULT" )
		{
			$accountNum = $this->session->userdata( 'accountNum' );
			$mainInfo = $this->Account_model->getUserInfoByAccountNum( $accountNum );
		}else{		
			$mainInfo = $this->Account_model->getUserInfoByUsername( $username );	
			if( $mainInfo !== false ) $accountNum = intval($mainInfo->AccountNum);
		}
		if( $mainInfo === FALSE )
		{	
			echo "ERROR_NO-USER-FOUND";
			return false;
		}
		if( $mainInfo->BookableByFriend == 0 or $mainInfo->BookableByFriend == false )
		{
			echo "ERROR_NO-PERMISSION-TO-BOOK-EXCEPT-HIMSELF";
			return false;
		}
		$uplbConstituencyInfo = $this->Account_model->getUserUPLBConstituencyData($accountNum );				
		echo $this->MakeXML_model->XMLize_UserInfoForBooking( $mainInfo, $uplbConstituencyInfo );		
		return true;	
	}//getUserInfoForBooking
	
	function newUserWelcome()
	{
		$step;

		$step = $this->session->userdata('userSignup_step');	// get where stage it is
		//$data['userSignup_step'] = 3;							// now next step
		//$this->session->set_userdata($data);
		//$isFunctionCallValid = isset($_POST["formValidityIndicator"]);		
		
		if( $step == 2  )
		{			
			$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
			$this->load->view('newUserWelcome', $data);
		}else{
			redirect("userAccountCtrl"); // redirect to homepage
		}
		
	}// newUserWelcome
	
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
	} //userSignup
	
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
			
			$this->Account_model->createAccount();	// perform insertion to database			
			
			//create default permission
			$this->Permission_model->createDefault( $this->Account_model->getAccountNumber(  $this->input->post( 'username' ) ) );	
		
		// set these data for use while navigating the site (i.e., the nav bar)			
			
			$this->login_model->setUserSession(
				$this->Account_model->getAccountNumber(  $this->input->post( 'username' ) ),
				$this->Account_model->getUser_Names( $this->input->post('username') )
			);
			
			$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
			$this->load->view('userSignup_part2', $data);
		}else{
			// user is trying to access part 2 without acccomplishing step1, so redirect to step1 first
			redirect("userAccountCtrl/userSignup");
		}
	} // userSignup_step2()
		
} // class
?>