<?php

class SessionCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		include_once( APPPATH.'constants/_constants.inc');
		$this->load->library('user_agent');		
		$this->load->model('Account_model');
		$this->load->model('BrowserSniff_model');
		$this->load->model('login_model');
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
	
	private function sorryNoticeHeader()
	{
		echo "The UPLB Express Ticketing System<hr/><br/><br/>";
	}
	
	private function strictlyNotAllowedStandard()
	{
		$this->sorryNoticeHeader();
		echo "We're sorry, we have blacklisted your browser from using this system. due to incompabilities attributable to the browser";
		echo " not following established web standards or your browser is just plain outdated.";
		echo " Please upgrade to a more recent one.";
	}
	
	private function notTestedAndDeny()
	{
		$this->sorryNoticeHeader();		
		echo "We still haven't tested your browser for compatibility with this system and decided to block this browser. Please use other recent browsers.";
	}
	private function agentDenied()
	{
		$this->sorryNoticeHeader();		
		echo "You need to access this system thru an updated web browser!";
	}

	private function heyRobot()
	{
		$this->load->view("for_robot");
	}
		
	function index()
	{
		if( $this->login_model->isUser_LoggedIn() )
		{
			$this->userHome();			
		}else{
			$data = Array();
			$_client_iPv4;
			$_client_user_agent;
			$client_browserShort_and_OS;			
			$uuid = $this->UsefulFunctions_model->guid();
			if( $this->session->userdata('JUST_LOGGED_OUT') != TRUE )
			{	
				$_client_iPv4 = $this->UsefulFunctions_model->VisitorIP();
				$_client_user_agent = $this->input->user_agent();
				$client_browserShort_and_OS = $this->determineUserAgent();
				$bsniff_mod_data = $this->BrowserSniff_model->browser_detection( 'full', '', '' ) ;
				$uuid = $this->UsefulFunctions_model->guid();
			
				log_message('DEBUG', 'NEW USER Logging in from ' . $_client_iPv4 );
				log_message('DEBUG', 'NEW USER Agent Raw : ' . $_client_user_agent );
				log_message('DEBUG', 'NEW USER Agent + Platform: ' . $client_browserShort_and_OS  );		
				log_message('DEBUG', 'NEW USER Session UUID ' . $uuid);
				// check client's browser if permitted
				$data['UA_CHECK'] = $this->BrowserSniff_model->decideActionOnUserAgent();				
				$data['uuid_new_ident'] = $this->UsefulFunctions_model->guid();
				$data['uuid'] = $uuid;
				$data['_client_user_agent'] = $_client_user_agent;
				$data['client_browserShort_and_OS'] = $client_browserShort_and_OS;
				$data['_client_iPv4'] = $_client_iPv4;
				switch( $data['UA_CHECK'] )
				{
					case ( BR_STRICTLY_NOT_ALLOWED ):
						$this->telemetry_model->add( BR_STRICTLY_NOT_ALLOWED, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
						$this->strictlyNotAllowedStandard();
						return false;
					case ( BR_NOT_TESTED_AND_DENY ) :
						$this->telemetry_model->add( BR_NOT_TESTED_AND_DENY, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
						$this->notTestedAndDeny();
						return false;
					case ( BR_AGENT_DENIED ) :
						$this->telemetry_model->add( BR_AGENT_DENIED, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
						$this->agentDenied();
						return false;
					case ( BR_BOT_SIMPLE ):
						$this->telemetry_model->add( BR_BOT_SIMPLE, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
						$this->heyRobot();
						return false;
					default:
						/*
							For now: BR_UNKNOWN_BUT_PERMIT_STILL and BR_NOT_TESTED_BUT_PERMIT_STILL
							will be taken care of the view page.
						*/
						break;
				}
				$this->telemetry_model->add(1, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
				$this->session->set_userdata('telemetry_uuid', $uuid);
			}else{
				$this->session->unset_userdata('JUST_LOGGED_OUT');
			}
			$this->load->view('loginPage', $data );			
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
				$this->Account_model->getAccountNumber(  $username ),
				$this->Account_model->getUser_Names( $username )
			);
			/*
				Redirect to EventCtrl's function that delete's this current user's expired bookings.
			*/
			log_message('DEBUG', "user '" .$username."' logged in" );
			redirect('EventCtrl/preclean');
		}else{	
			// ec 4003
			log_message('DEBUG', "user '" .$username."' ATTEMPTED logged in - invalid credentials" );
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
	
	function redirect_unknown()
	{
		echo print_r( $_COOKIE[ 'ci_session' ] );
		echo "The server do not know where to redirect you."; //3999
	}
	
}//class
?>