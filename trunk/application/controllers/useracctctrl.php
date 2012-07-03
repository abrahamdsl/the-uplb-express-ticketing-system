<?php

class useracctctrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
		include_once( APPPATH.'constants/_constants.inc');		
		$this->load->library('session');
		$this->load->model('login_model');
		$this->load->model('academic_model');
		$this->load->model('account_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('makexml_model');
		$this->load->model('payment_model');
		$this->load->model('permission_model');
		$this->load->model('usefulfunctions_model');
		
		if( !$this->login_model->isUser_LoggedIn() )
		{	// error code 4999
			$allowed_without_auth = Array(
				'/useracctctrl/userSignup'				
			);
			if( !in_array( $_SERVER[ 'REDIRECT_QUERY_STRING' ], $allowed_without_auth ) ) {
				$this->clientsidedata_model->setRedirectionURLAfterAuth( $_SERVER[ 'REDIRECT_QUERY_STRING' ] );
				redirect('sessionctrl/authenticationNeeded');
			}
		}	
	}
	
	function index()
	{
		redirect('sessionctrl');
	}//index
	
	function addpaymentmode()
	{
		$this->manageuser_common( true );
		$data['mode'] = 0;
		$this->load->view( 'managePaymentModes/managePaymentModes02', $data );
	}
	
	function addpaymentmode_step2(){
		//HOT-SPOT FOR REFACTORING. VERY COMMON WITH $this->managepaymentmode_save
				
		$ptype 		= $this->input->post( 'ptype' ); 
		$name	 	= $this->input->post( 'name' ); 
		$person 	= $this->input->post( 'person' ); 
		$location 	= $this->input->post( 'location' ); 
		$cellphone  = $this->input->post( 'cellphone' ); 
		$landline   = $this->input->post( 'landline' ); 
		$email 		= $this->input->post( 'email' ); 
		$comments 	= $this->input->post( 'comments' ); 
		$internal_data_type = $this->input->post( 'internal_data_type' ); 
		$internal_data 		= $this->input->post( 'internal_data' ); 
		if( $name === false ){
			$data['error'] = "NO_DATA";			
			$data['redirectURI'] = base_url().'useracctctrl/managepaymentmode';
			$data['defaultAction'] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		if( strlen($name) < 1 or
			!$this->usefulfunctions_model->isPaymentModeTypeValid( $ptype ) or
			!$this->usefulfunctions_model->isInternalDataTypeValid( $internal_data_type ) )
		{			
			echo( 'Invalid entries specified!<br/><br/>' ); // EC 4998
			echo('<a href="javascript: window.history.back();" >Go back</a>' );
			return false;
		}
		if( $this->payment_model->getPaymentModeByName( $name) !== FALSE )
		{
			echo( 'Payment mode exists already!<br/><br/>' ); // EC 1500
			echo('<a href="javascript: window.history.back();" >Go back</a>' );
			return false;
		}
		$result = $this->payment_model->createPaymentMode( $ptype, $name, $person, $location, $cellphone, $landline,
			$email, $comments, $internal_data_type, $internal_data
		);
		if( $result )
		{
			$data[ 'theMessage' ] = "The payment mode has been successfully added."; //error code 2500
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "Something went wrong while adding the payment mode. It may have been not saved."; // 5500
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}
	
	function changePassword_step1()
	{
		$this->load->view('manageAccount/changePassword');
	}
	
	function changePassword_step2(){
		/*
			Take note, we don't perform new password confirmation here, inasa na sa JavaScript! hahaha.
			( kasalanan na yan ng  naghack/trick dun sa page.javascript)
		*/
		if( !$this->input->is_ajax_request() ) die("NOT-ALLOWED"); // EC 4103
		
		$accountNum;
		$userObj;
		$oldPass 	= FALSE;
		$newPass;
		$isAdminResettingPassword;
		$whereNext;
		$whereNextURI = 'useracctctrl/myAccount';
		$responseDescriptor;
		$responseCaption;
		
		$accountNum = $this->clientsidedata_model->getAdminResetsPasswordIndicator();
		$isAdminResettingPassword = (  $accountNum !== FALSE );
		$newPass = $this->input->post( "password" );
		
		if( !$isAdminResettingPassword ){
			$accountNum = $this->clientsidedata_model->getAccountNum();
			$oldPass    = $this->input->post( "oldPassword" );
			$userObj 	= $this->account_model->getUserInfoByAccountNum( $accountNum );		
					
			if( !$this->account_model->authenticateUser( $userObj->username, $oldPass ) )
			{			
				echo $this->makexml_model->XMLize_AJAX_Response( 
					// ec 4003
					"error", "authentication failure", "AUTH_FAIL", 0, "Invalid current password. Please try again.", "" 
				);
				return false;
			}
		}
				
		$result = $this->account_model->setPassword( $newPass, $accountNum );
		
		if( $isAdminResettingPassword ){
			// EC 1002
			$whereNext = 'manageUser_step2';
			$responseDescriptor = "The user's password has been changed.";
			$responseCaption = "Manage User";
			$this->clientsidedata_model->deleteAdminResetsPasswordIndicator();
		}else{
		    // EC 1002
			$whereNext = 'myAccount';
			$responseDescriptor = "Your password has been changed.";
			$responseCaption = "My Account";
		}		
		$whereNext = '<br/><br/><a href="'.base_url().$whereNextURI.'" >Back to '.$responseCaption.'</a>';		
		echo $this->makexml_model->XMLize_AJAX_Response( "okay", "success", "PASSWORD_CHANGE-SUCCESS", 0, $responseDescriptor.$whereNext );
		return true;
	}//changePassword_step2
			
	function isUserExisting()
	{
		/*
			Created 13MAR2012-0118. WTF! Bakit ngayon lang.
			During sign-up proces, checks if the user's identity is already being used by someone.
		*/
		if( !$this->input->is_ajax_request() ) redirect( '/' );
		$identityTheftMsg = "If this is really you, please contact the administrator to find to verify your identity.";
		$username = $this->input->post( 'username' );
		$fName = $this->input->post( 'fName' );
		$mName = $this->input->post( 'mName' );
		$lName = $this->input->post( 'lName' );
		$studentNum = $this->input->post( 'studentNum' );
		$empNum = $this->input->post( 'empNum' );
		if( strtolower( $username ) === 'default' ) 
		{
			// EC 4201
			echo "ERROR_-1_The word 'default' cannot be used as a username.";
			return false;
		}
		if( $username === false or $fName === false or $lName === false )
		{
			// EC 4000
			echo "ERROR_0_Info needed";
			return false;
		}
		if( $this->account_model->getUserInfoByUsername( $username ) !== false )
		{   // EC 4202
			echo "ERROR_1_Username is already in use.";
			return false;
		}
		if( $this->account_model->isThisNameExistent( $fName, $mName, $lName ) !== false )
		{   // EC 4200
			echo "ERROR_2_The name '".$lName.", ".$fName." ".$mName."' is already being used.";
			return false;
		}
		if(  $studentNum != "disabled" and  $this->account_model->isStudentNumberExisting( $studentNum ) !== false )
		{   // EC 4203
			echo "ERROR_3_The student number ".$studentNum." is already being used by someone. ".$identityTheftMsg;
			return false;
		}
		if( $empNum != "disabled" and $this->account_model->isEmployeeNumberExisting( $empNum ) !== false )
		{   // ec 4204
			echo "ERROR_4_The employee number ".$studentNum." is already being used by someone. ".$identityTheftMsg;
			return false;
		}
		echo "OKAY"; // EC 2000
		return true;
	}//isUserExisting()
	
	function isUserExisting2()
	{
		/*
			@purpose Checks if a user is attached to an AccountNum or username being submitted.
			@assumption  Data submitted is deemed to be accountNum if it is an integer.
		*/
		define( 'ACCOUNTNUM_I', "AccountNum" );
		define( 'USERNAME_I', "username" );
		$identifier_val;
		$identifier_type;		
		$accountNum = 0;
		
		$identifier_val = $this->input->post( 'useridentifier' );				
		if( $identifier_val === false or strlen($identifier_val) < 1 ){ 
			echo $this->makexml_model->XMLize_AJAX_Response( 
			    // EC 4000
				"error", "information needed", "INFO_NEEDED", 0, "i need your info please! field: useridentifier", "" 
			);
			return false;
		}
		$identifier_type = ( is_numeric( $identifier_val) && !( intval( $identifier_val ) == 0 or intval( $identifier_val )== 1  ) ) 
								? ACCOUNTNUM_I : USERNAME_I;
			
		switch( $identifier_type )
		{
			case ACCOUNTNUM_I:  $userExists = ( $this->account_model->getUserInfoByAccountNum( $identifier_val ) !== FALSE );
								$accountNum = $identifier_val;
								break;									
			case USERNAME_I	: 	$userObj =  $this->account_model->getUserInfoByUsername( $identifier_val );
								$userExists = ( $userObj !== FALSE );
								if( $userExists ) $accountNum = $userObj->AccountNum;
								break;								
		}//switch
		if( $userExists )
		{
			echo $this->makexml_model->XMLize_AJAX_Response( 
			   // EC 4202
				"okay", "user exists", "USERNAME_EXISTS", 0, "user is existing.", "" 
			);
			$this->clientsidedata_model->setSessionActivity( ADMIN_MANAGE_USER, 2, 'accountNum='.$accountNum.";" );
			return true;
		}else{
			// EC 4001
			echo $this->makexml_model->XMLize_AJAX_Response( 
				"error", "not found", "USERNAME_DOES-NOT-EXIST", 0, "username is not existing.", "" 
			);
			return false;
		}
	}//isUserExisting2(..)
	
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
			$mainInfo = $this->account_model->getUserInfoByAccountNum( $accountNum );
		}else{		
			$mainInfo = $this->account_model->getUserInfoByUsername( $username );	
			if( $mainInfo !== false ) $accountNum = intval($mainInfo->AccountNum);
		}
		if( $mainInfo === FALSE )
		{	// EC 4001
			echo "ERROR_NO-USER-FOUND";
			return false;
		}
		if( $mainInfo->BookableByFriend == 0 or $mainInfo->BookableByFriend == false )
		{	// EC 4005
			echo "ERROR_NO-PERMISSION-TO-BOOK-EXCEPT-HIMSELF";
			return false;
		}
		$uplbConstituencyInfo = $this->account_model->getUserUPLBConstituencyData($accountNum );				
		echo $this->makexml_model->XMLize_UserInfoForBooking( $mainInfo, $uplbConstituencyInfo );		
		return true;	
	}//getUserInfoForBooking
	
	function manageuser()
	{
		//for admin only		
		$this->manageuser_common( true );
		$this->load->view( 'manageUser/manageUser01' );
	}// manageUser(..)
	
	function managepaymentmode()
	{
		$this->manageuser_common( true );
		$data['paymentChannels'] = $this->payment_model->getPaymentChannels( true );
		$this->load->view( 'managePaymentModes/managePaymentModes01', $data );	
	}
	
	function managepaymentmode_delete()
	{
		$this->manageuser_common( true );
		$uniqueID = $this->input->post( 'pChannel' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );
		$data['title'] =  "Be careful on what you wish for";
		$data['theMessage'] =  "Are you sure you want to delete this payment mode?";
		$data['yesURI'] = base_url().'useracctctrl/managepaymentmode_delete_process';
		$data['noURI'] = base_url().'useracctctrl/managepaymentmode';
		$data['formInputs'] = Array( 
			'pChannel' => $uniqueID
		);		
		$this->load->view( 'confirmationNotice', $data );
	}
	
	function managepaymentmode_delete_process()
	{
		$this->manageuser_common( true );
		$uniqueID = $this->input->post( 'pChannel' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );
		if( intval($uniqueID) === 0 ){
			/*
				Automatic confirmation since free is not removable
				EC 2515
			*/
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "By this system's design, this payment mode is not designed to be removable. Edit my code if you want to. <br/><br/>:D";			
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$result = $this->payment_model->deletePaymentMode( $uniqueID );
		if( $result )
		{   // ec 1501
			$data[ 'theMessage' ] = "The payment mode has been successfully deleted.";			
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			//ec 5505
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "Something went wrong while processing the deletion of the payment mode. It may have been not deleted. <br/><br/>Please try again.";
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}
	
	function managepaymentmode_edit()
	{
		$this->manageuser_common( true );
		$uniqueID = $this->input->post( 'pChannel' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' ); //EC 4000
		$data['singleChannel'] = $this->payment_model->getSinglePaymentChannelByUniqueID( $uniqueID );		
		$data['mode'] = 1;
		$this->load->view( 'managePaymentModes/managePaymentModes02', $data );
	}
		
	function managepaymentmode_save()
	{
		//form-validation skipped here. let javascript take care of that.
		//die( var_dump($_POST ) );
		
		/*
			$mode Def'n : 0 - NEW ENTRY ; 1 - EDITING AN ENTRY
		*/
		$mode       = intval( $this->input->post( 'mode' ) );
		$uniqueID 	= $this->input->post( 'uniqueID' ); 
		$ptype 		= $this->input->post( 'ptype' ); 
		$name	 	= $this->input->post( 'name' ); 
		$person 	= $this->input->post( 'person' ); 
		$location 	= $this->input->post( 'location' ); 
		$cellphone  = $this->input->post( 'cellphone' ); 
		$landline   = $this->input->post( 'landline' ); 
		$email 		= $this->input->post( 'email' ); 
		$comments 	= $this->input->post( 'comments' ); 
		$internal_data_type = $this->input->post( 'internal_data_type' ); 
		$internal_data 		= $this->input->post( 'internal_data' ); 
		if( $uniqueID === false or $name === false ){
			$data['error'] = "NO_DATA";			
			$data['redirectURI'] = base_url().'useracctctrl/managepaymentmode';
			$data['defaultAction'] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		if( strlen($uniqueID) < 1 or strlen($name) < 1 or
			!$this->usefulfunctions_model->isPaymentModeTypeValid( $ptype ) or
			!$this->usefulfunctions_model->isInternalDataTypeValid( $internal_data_type ) )
		{			
			echo( 'Invalid entries specified!<br/><br/>' ); //ec 4998
			echo('<a href="javascript: window.history.back();" >Go back</a>' );
			return false;
		}
		if( $mode == 0 )
		{
			if( $this->payment_model->getPaymentModeByName( $name) !== FALSE )
			{
				echo( 'Payment mode exists already!<br/><br/>' ); //ec 1500
				echo('<a href="javascript: window.history.back();" >Go back</a>' );
				return false;
			}
		}
		$result = $this->payment_model->updatePaymentMode(
			$uniqueID, $ptype, $name, $person, $location, $cellphone, $landline,
			$email, $comments, $internal_data_type, $internal_data
		);
		if( $result )
		{  // ec 1502
			$data[ 'theMessage' ] = "The payment mode has been successfully edited.";			
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
		  // EC 5510
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "Something went wrong while processing the modification of the payment mode. Your changes may have been not saved.";
			$data[ 'redirectURI' ] = base_url().'useracctctrl/managepaymentmode';
			$data[ 'defaultAction' ] = 'Payment modes';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}//managepaymentmode_save()

	private function manageuser_common( $checkPermissionOnly = false )
	{
		/*
			Gets main info and uplb constituency info.
		*/
		if( !$this->permission_model->isAdministrator() )
		{   // ec 4101
			$data['error'] = "NO_PERMISSION";					
			$this->load->view( 'errorNotice', $data );			
			return false;
		}
		if( $checkPermissionOnly ) return true;
		$concernedUserAccountNum = $this->clientsidedata_model->getSessionActivityDataEntry( 'accountNum' );		
		$data['accountNum']   = $concernedUserAccountNum;
		$data['userMainInfo'] = $this->account_model->getUserInfoByAccountNum( $concernedUserAccountNum );
		$data['userUPLBInfo'] = $this->account_model->getUserUPLBConstituencyData( $concernedUserAccountNum );
		
		return $data;
	}// manageuser_common(..)

	private function manageuser_precheck( $stage = 2 )
	{
		$sessionActivity = $this->clientsidedata_model->getSessionActivity( );				
		if( $sessionActivity[0] != ADMIN_MANAGE_USER and $sessionActivity[1] < $stage )
		{
			$data['error'] = "NO_DATA";	// ec 4000
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}//manageuser_precheck(..)

	function manageUser_step2()
	{		
		$this->manageuser_precheck( 2 );		
		$data = $this->manageuser_common();
		$this->clientsidedata_model->updateSessionActivityStage( 3 );	
		$this->load->view( 'manageUser/manageUser02', $data);		
	}//manageUser_step2

	function manageuser_editroles()
	{
		$this->manageuser_precheck( 3 );		
		$data = $this->manageuser_common();
		$data['permissionObj'] = $this->permission_model->getPermissionStraight( $data['accountNum']  );
		$this->load->view( 'manageUser/manageUser03_editRoles.php', $data);
	}//manageuser_editroles(..)
	
	function manageuser_editrole_save()
	{
		// customer and admin are not included, as they are not removable
		
		$this->manageuser_precheck( 2 );
		$data = $this->manageuser_common();
		$transResult;
		
		$permissionsSent = Array(			
			'eventmanager'  => $this->input->post('eventmanager'),
			'receptionist'  => $this->input->post('receptionist'),			
			'facultymember' => $this->input->post('facultymember')
		);
		foreach( $permissionsSent as $key => $value )
		{	// check for validity
			if( !( $value==="1" or $value==="0") )
			{	//ec 4006
				$data['error'] = "CUSTOM"; 
				$data['theMessage'] = "The submitted data to the server is in the incorrect format.";
				$data['redirectURI'] = base_url()."useracctctrl/manageuser_editroles";
				$data['defaultAction'] = "Edit Roles";
				$this->load->view( 'errorNotice', $data );			
				return false;
			}
		}
		$transResult =  $this->account_model->setPermissions( 
			$data['accountNum'],
			1,
			$permissionsSent[ 'eventmanager' ],
			$permissionsSent[ 'receptionist' ],
			NULL,
			$permissionsSent[ 'facultymember' ]
		);
		if( $transResult ){
			// ec 1600
			$data[ 'theMessage' ] = "The roles have been edited.";
			$data[ 'redirect' ] = true;
			$data[ 'redirectURI' ] = base_url().'useracctctrl/manageUser_step2';
			$data[ 'defaultAction' ] = 'Manage User';			
			$this->load->view( 'successNotice', $data );				
		}else{
			//ec 5600
			$data['error'] = "CUSTOM";
			$data['theMessage'] = "Something went wrong while updating permissions. Your changes might not be saved.";
			$data[ 'redirect' ] = true;
			$data['redirectURI'] = base_url()."useracctctrl/manageuser_editroles";
			$data['defaultAction'] = "Edit Roles";
			$this->load->view( 'errorNotice', $data );						
		}
	}//manageuser_editroles_save()

	function manageuser_resetpassword()
	{
		$this->manageuser_precheck( 2 );		
		$data = $this->manageuser_common();		
		$this->clientsidedata_model->setAdminResetsPasswordIndicator( $data['accountNum'] );
		$this->clientsidedata_model->updateSessionActivityStage( 3 );	
		$this->load->view( 'manageUser/resetPassword', $data);
	}

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
			redirect("useracctctrl"); // redirect to homepage
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
		
		/*
			13MAR2012-0114 - Removed the requirement for having a student number during sign-up
			what the f**k - why did I placed that restriction months ago?
		*/
		
		if(  $step == 1 )
		{					
			$data['userSignup_step'] = 2;			// now, new step
			$this->session->set_userdata($data);
			
			$this->account_model->createAccount();	// perform insertion to database			
			
			//create default permission
			$this->permission_model->createDefault( $this->account_model->getAccountNumber(  $this->input->post( 'username' ) ) );	
		
		// set these data for use while navigating the site (i.e., the nav bar)			
			
			$this->login_model->setUserSession(
				$this->account_model->getAccountNumber(  $this->input->post( 'username' ) ),
				$this->account_model->getUser_Names( $this->input->post('username') )
			);
			
			$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
			$this->load->view('userSignup_part2', $data);
		}else{
			// user is trying to access part 2 without acccomplishing step1, so redirect to step1 first
			redirect("useracctctrl/userSignup");
		}
	} // userSignup_step2()
		
	function manageAccountSave()
	{		
		$studentNumber = $this->input->post( 'studentNumber' );
		$employeeNumber = $this->input->post( 'employeeNumber' );		
		$_UPLB_Unique_violated = " you have chosen is already being used. No changes have been done to your account.";
		
		if( $this->account_model->isStudentNumberExisting( $studentNumber, FALSE ) )
		{	// EC 4203
			$data['error'] = "CUSTOM";
			$data[ 'theMessage' ] = "The new student number".$_UPLB_Unique_violated;
			$data[ 'redirect' ] = true;
			$data[ 'redirectURI' ] = base_url().'useracctctrl/myAccount';
			$data[ 'defaultAction' ] = 'Manage Account';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		if( $this->account_model->isEmployeeNumberExisting( $employeeNumber, FALSE ) )
		{	// EC 4204
			$data['error'] = "CUSTOM";
			$data[ 'theMessage' ] = "The new employee number".$_UPLB_Unique_violated;
			$data[ 'redirect' ] = true;
			$data[ 'redirectURI' ] = base_url().'useracctctrl/myAccount';
			$data[ 'defaultAction' ] = 'Manage Account';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		
		$transResult =  $this->account_model->updateMainAccountDetails();	
		$transResult2 =  $this->account_model->updateUPLBConstituencyDetails();		
		if( $transResult and $transResult2 ){
			//EC 2700
			$data[ 'theMessage' ] = "The changes in your account have been saved..";
			$data[ 'redirect' ] = true;
			$data[ 'redirectURI' ] = base_url().'useracctctrl/myAccount';
			$data[ 'defaultAction' ] = 'Manage Account';
			$this->load->view( 'successNotice', $data );				
		}else{
			$data['error'] = "CUSTOM";
			$data['theMessage'] = "Something went wrong while saving changes to your account. Your changes might not be saved.";
			$data[ 'redirect' ] = true;
			$data['redirectURI'] = base_url()."useracctctrl/myAccount";
			$data['defaultAction'] = 'Manage Account';
			$this->load->view( 'errorNotice', $data );						
		}
	}//manageAccountSave(..)
				
	function myAccount()
	{
		$accountNum =  $this->session->userdata( 'accountNum' );
		$userObj = $this->account_model->getUserInfoByAccountNum( $accountNum );
		$uplbConstituencyObj =  $this->account_model->getUserUPLBConstituencyData( $accountNum );
		$permissionsObj = $this->permission_model-> getPermissionStraight( $accountNum );
		
		$data['userObj'] 			 = $userObj;
		$data['uplbConstituencyObj'] = $uplbConstituencyObj;		
		$data['permissionsObj'] 	 = $permissionsObj;		
				
		$this->load->view( 'manageAccount/accountHome', $data );
	}
} // class
?>