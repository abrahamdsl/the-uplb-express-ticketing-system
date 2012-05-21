<?php

class SessionCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->library('user_agent');
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('Permission_model');
		$this->load->model('telemetry_model');					
		$this->load->model('UsefulFunctions_model');					
	}
	
	private function determineUserAgent()
	{
		if ($this->agent->is_browser())
		{
			$agent = $this->agent->browser().' '.$this->agent->version();
		}
		elseif ($this->agent->is_robot())
		{
			$agent = $this->agent->robot();
		}
		elseif ($this->agent->is_mobile())
		{
			$agent = $this->agent->mobile();
		}
		else
		{
			$agent = 'Unidentified User Agent';
		}
		
		return $agent."|".$this->agent->platform();
	}
	
	function index()
	{
		if( $this->login_model->isUser_LoggedIn() )
		{
			$this->userHome();			
		}else{						
			$this->load->view('loginPage');			
			if( $this->session->userdata('JUST_LOGGED_OUT') != TRUE )
			{
				$_client_iPv4 = $this->UsefulFunctions_model->VisitorIP();
				$_client_user_agent = $this->input->user_agent();
				$client_browserShort_and_OS = $this->determineUserAgent();
				$uuid = $this->UsefulFunctions_model->guid();
				log_message('DEBUG', 'NEW USER Logging in from ' . $_client_iPv4 );
				log_message('DEBUG', 'NEW USER Agent Raw : ' . $_client_user_agent );
				log_message('DEBUG', 'NEW USER Agent + Platform: ' . $client_browserShort_and_OS  );		
				log_message('DEBUG', 'NEW USER Session UUID ' . $uuid);
				$this->telemetry_model->add(1, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );							
				$this->session->set_userdata('telemetry_uuid', $uuid);
			}else{
				$this->session->unset_userdata('JUST_LOGGED_OUT');
			}
		}
	} // index
	
	function userHome()
	{
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		$data['permissions'] = $this->Permission_model->getPermissionStraight( $this->session->userdata( 'accountNum' ) );
		$this->load->view('homepage', $data);		
	}//userHome
	
	function authenticationNeeded()
	{	
		// ec 4999
		$data['LOGIN_WARNING'] = array( " You have to log-in first before you can access the feature requested. " ) ;
		$this->session->set_userdata($data);
		$this->index();
	
	}
	
	function login( )
	{
		/*
			made | 26NOV2011 2014 at SM City Calamba Global Pinoy Center :-D
			
		*/
		$username = $this->input->post('username');
		$password = $this->input->post('password');
	
		if( $this->Account_model->isUserExistent( $username, $password ) ) 
		{   //if something was submitted, this will return true
			$this->login_model->setUserSession(
				$this->Account_model->getAccountNumber(  $this->input->post( 'username' ) ),
				$this->Account_model->getUser_Names( $this->input->post('username') )
			);
			/*
				Redirect to EventCtrl's function that delete's this current user's expired bookings.
			*/
			redirect('EventCtrl/preclean');
		}else{	
			// ec 4003
			$data['LOGIN_WARNING'] = array( " Invalid credentials. Please try again. " ) ;
			$this->session->set_userdata($data);
			$this->index();
		}
	}//login
	
	function logout()
	{
		/*
			As of 24 NOV 2011 1156, this only consists of a call to the logout function
			of the login model. and that function there is only of one line, which can be actually
			be substituted for the online here. But considering sound software engineering principles,
			it is better to separate it and there.
		*/		
		$this->login_model->logout();
		$this->login_model->deleteUserCookies();
		$data['JUST_LOGGED_OUT'] = TRUE;
		$this->session->set_userdata($data);
		$this->telemetry_model->add(4,$this->UsefulFunctions_model->guid(),$this->UsefulFunctions_model->VisitorIP(),'REF_'.$this->session->userdata('telemetry_uuid'),'' );
		$this->session->unset_userdata('telemetry_uuid');
		redirect('/');
	} //logout
	
	function plus12()
	{
		/* INTERNAL DEBUGGING FUNCTION ONLY | DELETE WHEN DEEMED NOT TO BE USED IN THE FUTURE
		
		$whatdahell = $this->db->query( "SELECT * FROM `_telemetry_basic` WHERE 1")->result();
		//echo var_dump( $whatdahell );
		foreach( $whatdahell as $singleRecord )
		{
			$oldTime = date( $singleRecord->RecDATE ." ". $singleRecord->RecTIME);
			$newTime = strtotime( '+12 hour', strtotime( $oldTime ) );
			echo "Old timestamp : ".$singleRecord->RecDATE ." ". $singleRecord->RecTIME."<br/>";
			echo "new timestamp : ".date('Y-m-d',$newTime)." ".date('H:i:s',$newTime)."<br/>-----------<br/>";
			$meow = "UPDATE `_telemetry_basic` SET `RecDATE` = '".date('Y-m-d',$newTime)."', `RecTIME` = '".date('H:i:s',$newTime)."' WHERE `UUID` = '".$singleRecord->UUID."'";
			$this->db->query( $meow );
		}
		*/
	}
}//class
?>