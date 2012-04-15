<?php

class SessionCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('Permission_model');
				
	}
	
	function index()
	{
		if( $this->login_model->isUser_LoggedIn() )
		{
			$this->userHome();			
		}else{						
			$this->load->view('loginPage');
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
	
		if(  $this->Account_model->isUserExistent( $username, $password )
		) //if something was submitted, this will return true
		{
			$this->login_model->setUserSession(
				$this->Account_model->getAccountNumber(  $this->input->post( 'username' ) ),
				$this->Account_model->getUser_Names( $this->input->post('username') )
			);
			//$this->userHome();			
			redirect('/');
		}else{			
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
		redirect('/');
	} //logout

}//class
?>