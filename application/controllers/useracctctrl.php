<?php
/**
*	User account controller
* 	CREATED <November 2011>
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains URIs regarding user account management,signing up and for the Administrator - managing other users and
		as well as payment modes.
*/
class useracctctrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		include_once( APPPATH.'constants/_constants.inc');
		$this->load->library('airtraffic_v2');
		$this->load->library('functionaccess');
		$this->load->library('inputcheck');
		$this->load->library('session');
		$this->load->library('sessmaintain');
		$this->load->model('login_model');
		$this->load->model('academic_model');
		$this->load->model('account_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('makexml_model');
		$this->load->model('payment_model');
		$this->load->model('permission_model');
		$this->load->model('usefulfunctions_model');
		
		if( !$this->sessmaintain->onControllerAccessRitual(  
				Array(
				'/useracctctrl/userSignup'
				)
			)
		) return FALSE;
	}

	function index()
	{
		redirect('sessionctrl');
	}//index

	private function manageuser_common( $checkPermissionOnly = false )
	{
		/**
		*	@created <March 2012>
		*	@description Checks if user is an administrator and outputs the view page if ever
		*	@revised 01AUG2012-1220
		*/
		if( !$this->permission_model->isAdministrator() )
		{   // ec 4101
			$data['error'] = "NO_PERMISSION";
			$this->load->view( 'errorNotice', $data );
			return FALSE;
		}
		return TRUE;
	}// manageuser_common(..)

	private function manageuser_common2()
	{
		/**
		*	@created 31JUL2012-1243
		*	@history Extracted from $this->manageuser_common2()
		*	@description Returns info (main info and uplb constituency) needed for the view page when managing users.
		*/
		$concernedUserAccountNum = $this->clientsidedata_model->getAdminManagesUser();
		if( $concernedUserAccountNum === FALSE ) return FALSE;
		return Array(
			'accountNum'  => $concernedUserAccountNum,
			'userMainInfo' => $this->account_model->getUserInfoByAccountNum( $concernedUserAccountNum ),
			'userUPLBInfo' => $this->account_model->getUserUPLBConstituencyData( $concernedUserAccountNum )
		);
	}

	function addpaymentmode()
	{
		/**
		*	@created <March 2012>
		*	@description Serve page of the add payment mode operation.
		*	@revised 01AUG2012-1340
		*/
		// is admin check
		if( !$this->manageuser_common( true ) ) return FALSE;
		// access check
		if( !$this->functionaccess->preUseracctctrl__addpaymentmode() ) return FALSE;
		$data['mode'] = 0;
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MPAY_ADD_1_FW );
		$this->load->view( 'managePaymentModes/managePaymentModes02', $data );
	}
	
	function addpaymentmode_step2() //see @todo
	{
		/**
		*	@created <March 2012>
		*	@description Save page for adding a new payment mode.
		*	@revised 01AUG2012-1323
		*	@todo 
			-DELETE and use managepaymentmode_save() later
			-HOT-SPOT FOR REFACTORING. VERY COMMON WITH $this->managepaymentmode_save
		*/
		// access check
		if( !$this->functionaccess->preUseracctctrl__addpaymentmode_step2() ) return FALSE;
		// harvest details
		$postData = $this->usefulfunctions_model->extractPaymentModeDetailsFromPOST();
		// server side form validation
		if( !$this->inputcheck->useracctctrl__addpaymentmode_step2( $postData ) ){
			echo var_dump( $_POST );
			return $this->sessmaintain->assembleGenericFormValidationFail(); //EC 4998
		}
		// is the name already taken
		if( $this->payment_model->getPaymentModeByName($postData['Name']) !== FALSE )
		{
			return $this->sessmaintain->assemblePaymentModeAlreadyExists();
		}
		$this->airtraffic_v2->initialize( STAGE_MPAY_ADD_2_PR, STAGE_MPAY_1_FW );
		// now do modify DB
		$this->payment_model->createPaymentMode( $postData );
		// now check whether to commit
		if( $this->airtraffic_v2->clearance() ){
			$this->airtraffic_v2->commit();
			log_message('DEBUG','addpaymentmode_step2() cleared for take off ' . $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleProceedSpecific(
				'useracctctrl/managepaymentmode',
				'The payment mode has been successfully added.',
				3000
			);
		}else{
			$this->airtraffic_v2->rollback();
			log_message('DEBUG','addpaymentmode_step2() clearance error '. $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleGenericTransactionFail(); // EC 5500 ?
		}
	}//addpaymentmode_step2()
	
	function cancel_manage_account()
	{
		/**
		*	@created 28JUL2012-1607
		*/
		if( $this->functionaccess->preUseracctctrl__myAccountFW() )
		{
			$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
			return $this->sessmaintain->assembleProceed( "sessionctrl" );
		}
		// might not be able to reach here because of internal mechanism in library
		// functionaccess, but just in case.
		$this->load->view('_stopcodes/4100.xml');
		return FALSE;
	}

	function changePassword_step1()
	{
		/**
		*	@created <Late March 2012>
		*	@revised 30JUL2012-1530
		*/
		if( !$this->functionaccess->preUseracctctrl__myAccountFW() ) return FALSE;
		$this->load->view('manageAccount/changePassword');
	}
	
	function changePassword_step2()
	{
		/**
		*	@created <Late March 2012>
		*	@revised 30JUL2012-1541
		*	@remarks Take note, we don't perform new password confirmation here, inasa na sa JavaScript! hahaha.
			( kasalanan na yan ng  naghack/trick dun sa page.javascript)
		*/
		$accountNum;
		$userObj;
		$oldPass 	= FALSE;
		$newPass;
		$isAdminResettingPassword;
		$whereNext;
		$whereNextURI = 'useracctctrl/myAccount';
		$responseDescriptor;
		$responseCaption;
		$oldPasswordValid = FALSE;
		$nextStage = -1;
		$nextName  = IDLE;
		
		$accountNum = $this->clientsidedata_model->getAdminResetsPasswordIndicator();
		$isAdminResettingPassword = (  $accountNum !== FALSE );
		$newPass = $this->input->post( "password" );

		if( $isAdminResettingPassword ){
			$oldPasswordValid = TRUE;
			$nextName = ADMIN_MANAGE_USER;
			$nextStage = STAGE_MU_2_FW;
		}else{
			if( !$this->functionaccess->preUseracctctrl__changePassword_step2() ) return FALSE;
			$accountNum = $this->clientsidedata_model->getAccountNum();
			$oldPass    = $this->input->post( "oldPassword" );
			// validate form input
			if( $this->inputcheck->is_password_valid( $oldPass ) )
			{
				$userObj 	= $this->account_model->getUserInfoByAccountNum( $accountNum, FALSE );
				if( !$this->account_model->authenticateUser( $userObj->username, $oldPass ) )
				{
					return $this->sessmaintain->assembleInvalidPasswordCurrent();
				}
				$oldPasswordValid = TRUE;
			}
		}
		// validate form input
		if( !$oldPasswordValid OR !$this->inputcheck->is_password_valid( $newPass ) ){
			$this->clientsidedata_model->deleteAllInternalErrors();
			return $this->sessmaintain->assembleGenericFormValidationFail();
		}
		$this->airtraffic_v2->initialize( STAGE_MACCT1_PR, $nextStage, $nextName );
		$result = $this->account_model->setPassword( $newPass, $accountNum );	// main activity
		if( $this->airtraffic_v2->clearance() ){
			$this->airtraffic_v2->commit();
			log_message('DEBUG','changePassword_step2 cleared for take off ' . $this->airtraffic_v2->getGUID() );
			if( $isAdminResettingPassword ){
				$responseDescriptor = "The user's password has been changed.";
				$this->clientsidedata_model->deleteAdminResetsPasswordIndicator();
			}else{
				$responseDescriptor = "Your password has been changed.";
			}
			return $this->sessmaintain->assembleProceedSpecific(
				'useracctctrl/' . ( ( $isAdminResettingPassword ) ? 'manageUser_step2' : "myAccount" ),
				$responseDescriptor,
				3000
			);
		}else{
			$this->airtraffic_v2->rollback();
			log_message('DEBUG','changePassword_step2 clearance error '. $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleGenericTransactionFail();
		}
	}//changePassword_step2

	function isUserExisting() // not yet
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
		/**
		*	@description Checks if a user is attached to an AccountNum or username being submitted.
		*	@assumption  Data submitted is deemed to be accountNum if it is an integer.
		*	@revised 31JUL2012-1140 Only now redirects to $this->manageUser_step2
		*/
		define( 'ACCOUNTNUM_I', "AccountNum" );
		define( 'USERNAME_I', "username" );
		$identifier_val;
		$identifier_type;
		$accountNum = 0;

		$identifier_val = $this->input->post( 'useridentifier' );
		// server side form validation
		if( !$this->inputcheck->useracctctrl__isUserExisting2( $identifier_val ) )
		{
			$this->clientsidedata_model->deleteAllInternalErrors();
			return $this->sessmaintain->assembleGenericFormValidationFail();
		}
		// 30JUL2012-2012 - I don't get the second IF condition.
		$identifier_type = ( is_numeric( $identifier_val) && !( intval( $identifier_val ) == 0 or intval( $identifier_val )== 1  ) ) 
								? ACCOUNTNUM_I : USERNAME_I;
		switch( $identifier_type )
		{
			case ACCOUNTNUM_I:  $userExists = ( $this->account_model->getUserInfoByAccountNum( $identifier_val, FALSE ) !== FALSE );
								$accountNum = $identifier_val;
								break;
			case USERNAME_I	: 	$userObj =  $this->account_model->getUserInfoByUsername( $identifier_val );
								$userExists = ( $userObj !== FALSE );
								if( $userExists ) $accountNum = $userObj->AccountNum;
								break;
		}//switch
		if( $userExists )
		{
			$this->airtraffic_v2->initialize( STAGE_CR_SEAT3_PR, STAGE_MU_2_FW, ADMIN_MANAGE_USER );
			// actually, no DB transaction to "protect" but only the session activity.
			if( $this->airtraffic_v2->clearance() ){
				$this->airtraffic_v2->commit();
				$this->clientsidedata_model->setAdminManagesUser( $accountNum );
				return $this->sessmaintain->assembleProceed( "useracctctrl/manageUser_step2" );
			}else{
				$this->airtraffic_v2->rollback();
				return $this->sessmaintain->assembleATC_V2_ClearanceFail();
			}
		}else{
			switch( $identifier_type )
			{
				case ACCOUNTNUM_I: return $this->sessmaintain->assembleAccountNum404();
				case USERNAME_I:    return $this->sessmaintain->assembleUsername404();
			}
		}
	}//isUserExisting2(..)
	
	function getUserInfoForBooking()//maybe?
	{
		/*
			Created 26FEB2012-2026
		*/
		$mainInfo;
		$username;
		$accountNum = false;
		
		if( $this->input->is_ajax_request() === false ){
			return $this->sessmaintain->assembleOnlyAJAXAllowed();
		}
		
		$username = $this->input->post( 'username' );
		if( $username === "DEFAULT" )
		{
			$accountNum = $this->clientsidedata_model->getAccountNum();
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
	
	function managepaymentmode()
	{
		/**
		*	@revised 01AUG2012-1425
		*/
		if( !$this->manageuser_common( true ) OR
			!$this->functionaccess->preUseracctctrl__managepaymentmode()
		) return FALSE;
		$data['paymentChannels'] = $this->payment_model->getPaymentChannels( true );
		$this->clientsidedata_model->setSessionActivity( ADMIN_MANAGE_PAYMENTMODE, STAGE_MPAY_1_FW );
		$this->load->view( 'managePaymentModes/managePaymentModes01', $data );
	}
	
	function managepaymentmode_delete( $uniqueID = FALSE )
	{
		/**
		*	@created <April 2012>
		*	@revised 02AGU2012-1418
		*	@todo <See $this::managepaymentmode_delete_process >
		*/
		if( !$this->manageuser_common( true ) ) return FALSE;
		if( $uniqueID === false){
			return $this->sessmaintain->assembleGenericFormValidationFail();
		}
		return $this->sessmaintain->assembleManagePaymentModeDeletePrompt( $uniqueID );
	}
	
	function managepaymentmode_delete_process()
	{
		/**
		*	@created <April 2012>
		*	@revised 02AGU2012-1418
		*	@todo 
				- Further revision to comply with session activity tracking in DB,
				airtraffic_v2 and ajaxifying this in $this->managepaymentmode() instead
				(like how deletion of an existing works in eventctrl/managebooking )
				I have left it out for now since this is a rarely used functionality.
				- Check first if this payment mode is currently in use by existing bookings,
				or at least warn them about the consequences. For now, I'm so lazy to include
				it here.
		*/
		$this->manageuser_common( true );
		$uniqueID = $this->input->post( 'pChannel' );
		if( $uniqueID === false){
			return $this->sessmaintain->assembleSpecificFormValidationFail( 'payment mode' );
		}
		if( intval($uniqueID) === 0 ){
			/*
				Automatic confirmation since free is not removable
				EC 2515
			*/
			return $this->sessmaintain->assembleFreePaymentModeNotRemovable();
		}
		$this->db->trans_begin();
		$this->payment_model->deletePaymentMode( $uniqueID );
		if( $this->db->trans_status() )
		{   
			$this->db->trans_commit();
			return $this->sessmaintain->assembleManagePaymentModeDeleteOK();
		}else{
			//ec 5505
			$this->db->trans_rollback();
			return $this->sessmaintain->assembleManagePaymentModeDeleteFail();
		}
	}
	
	function managepaymentmode_edit( $uniqueID = FALSE )
	{
		/**
		*	@revised 01AUG2012-1520
		*/
		if( !$this->manageuser_common( true ) OR
		  !$this->functionaccess->preUseracctctrl__managepaymentmode_edit()
		){ 
			die('wahhh');
			return FALSE;
		}
		if( !$this->inputcheck->is_payment_mode_id_valid( $uniqueID, TRUE ) ){
			return $this->sessmaintain->assembleManagePaymentModeEditValidateFail();
		}
		$data['singleChannel'] = $this->payment_model->getSinglePaymentChannelByUniqueID(
			mysql_real_escape_string( $uniqueID )
		);
		if( $data['singleChannel'] === FALSE ){
			return $this->sessmaintain->assembleManagePaymentModeEdit404();
		}
		$data['mode'] = 1;
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_MPAY_EDIT_1_FW );
		$this->load->view( 'managePaymentModes/managePaymentModes02', $data );
	}

	function managepaymentmode_save()
	{
		/**
		*	@created <April 2012>
		*	@revised 01AUG2012-1410
		*	@remarks 
				- Some code for merging of $this->addpaymentmode_step2() is already present.
				- Definition of post data 'mode' : 0 - NEW ENTRY ; 1 - EDITING AN ENTRY
		*	@todo Remove $this->addpaymentmode_step2() and use only this. Might be complicated
				since you have to modify library functionaccess:preBookCheckAJAXUnified
		*/
		// access check
		if( !$this->functionaccess->preUseracctctrl__managepaymentmode_save() ) return FALSE;
		// harvest details
		$postData = $this->usefulfunctions_model->extractPaymentModeDetailsFromPOST();
		// server side form validation
		if( !$this->inputcheck->useracctctrl__addpaymentmode_step2( $postData ) ){
			return $this->sessmaintain->assembleGenericFormValidationFail(); //EC 4998
		}
		$activity = intval($postData['mode']);
		if( $activity  == 0 )
		{
			if( $this->payment_model->getPaymentModeByName($postData['Name']) !== FALSE )
			{
				return $this->sessmaintain->assemblePaymentModeAlreadyExists();
			}
		}
		$this->airtraffic_v2->initialize( STAGE_MPAY_EDIT_2_PR, STAGE_MPAY_1_FW );
		//now do activity
		if( $activity  == 0 )
		{
			$this->payment_model->createPaymentMode( $postData );
		}else{
			$this->payment_model->updatePaymentMode( $postData );
		}
		// now check whether to commit
		if( $this->airtraffic_v2->clearance() ){
			$this->airtraffic_v2->commit();
			log_message('DEBUG','managepaymentmode_save() cleared for take off ' . $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleProceedSpecific(
				'useracctctrl/managepaymentmode',
				'The payment mode has been successfully ' . ( ( $activity  == 0 ) ? 'added.' : 'edited.' ),
				3000
			);
		}else{
			$this->airtraffic_v2->rollback();
			log_message('DEBUG','managepaymentmode_save() clearance error '. $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleGenericTransactionFail(); // EC 5500/10 ?
		}
	}//managepaymentmode_save()

	function manageuser()
	{
		/**
		*	@created <March 2012>
		*	@revised 01AUG2012-1417
		*/
		//for admin only
		if( !$this->manageuser_common( TRUE ) OR
		    !$this->functionaccess->preUseracctctrl__manageuser()
		) return FALSE;
		$this->clientsidedata_model->setSessionActivity( ADMIN_MANAGE_USER, STAGE_MU_1_FW );
		$this->load->view( 'manageUser/manageUser01' );
	}// manageuser(..)

	function manageUser_step2()
	{
		/**
		*	@created <March 2012>
		*	@description Landing page for the selection of operations for a user being managed by
				the Admin.
		*	@revised 31JUL2012-1246
		*/
		if( !$this->functionaccess->preUseracctctrl__manageUser_step2() ) return FALSE;
		$this->load->view( 'manageUser/manageUser02', $this->manageuser_common2() );
	}//manageUser_step2

	function manageuser_editroles()
	{
		/**
		*	@created <March 2012>
		*	@description Landing page for the editing of roles of a user.
		*	@revised 31JUL2012-1935
		*/
		if( !$this->functionaccess->preUseracctctrl__manageUser_step2() ) return FALSE;
		$data = $this->manageuser_common2();
		$data['permissionObj'] = $this->permission_model->getPermissionStraight( $data['accountNum']  );
		$this->load->view( 'manageUser/manageUser03_editRoles.php', $data);
	}//manageuser_editroles(..)
	
	function manageuser_editrole_save()
	{
		/**
		*	@created <March 2012>
		*	@description Save URI for the editing of roles of a user.
		*	@revised 31JUL2012-1937
		*/
		if( !$this->functionaccess->preUseracctctrl__manageuser_editrole_save() ) return FALSE;

		// As per program design, the customer and admin role cannot be removed thus not harvested here
		$permissionsSent = Array(
			'eventmanager'  => $this->input->post('eventmanager'),
			'receptionist'  => $this->input->post('receptionist'),
			'facultymember' => $this->input->post('facultymember')
		);
		// server-side form validation
		if( !$this->inputcheck->useracctctrl__manageuser_editrole_save( $permissionsSent ) )
		{
			$this->clientsidedata_model->deleteAllInternalErrors();
			return $this->sessmaintain->assembleGenericFormValidationFail();
		}
		$data = $this->manageuser_common2();
		$this->airtraffic_v2->initialize( STAGE_MACCT1_PR, STAGE_MU_2_FW, ADMIN_MANAGE_USER );
		// now, transact with DB
		$this->account_model->setPermissions(
			$data['accountNum'],
			1,
			$permissionsSent[ 'eventmanager' ],
			$permissionsSent[ 'receptionist' ],
			NULL,
			$permissionsSent[ 'facultymember' ]
		);
		// now check whether to commit
		if( $this->airtraffic_v2->clearance() ){
			$this->airtraffic_v2->commit();
			log_message('DEBUG','manageuser_editroles_save cleared for take off ' . $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleProceedSpecific( "useracctctrl/manageUser_step2", "The changes in roles have been saved.", 2000 );
		}else{
			$this->airtraffic_v2->rollback();
			log_message('DEBUG','manageuser_editroles_save clearance error '. $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleGenericTransactionFail();
		}
	}//manageuser_editroles_save()

	function manageuser_resetpassword()
	{
		/**
		*	@created <March 2012>
		*	@description Page for keying in the new password for the user.
		*	@revised 31JUL2012-1806
		*/
		if( !$this->functionaccess->preUseracctctrl__manageUser_step2() ) return FALSE;
		$data = $this->manageuser_common2();
		$this->clientsidedata_model->setAdminResetsPasswordIndicator( $data['accountNum'] );
		$this->load->view( 'manageUser/resetPassword', $data);
	}

	function newUserWelcome()//notyet
	{
		$step = $this->session->userdata('userSignup_step');	// get where stage it is

		if( $step == 2  )
		{
			$data['userData'] = $this->login_model->getUserInfo_for_Panel();
			$this->load->view('newUserWelcome', $data);
		}else{
			redirect("sessionctrl"); // redirect to homepage
		}
		
	}// newUserWelcome
	
	function userSignup()//notyet
	{
		/*
			function for user sign up
			Part1 - basic info
		*/		
		if( $this->login_model->isUser_LoggedIn() ) redirect('sessionctrl');
		// set some session data to indicate that user is still signing up - first part
		$data['userSignup_step'] = 1;
		$this->session->set_userdata($data);
		$this->load->view('userSignup');
	} //userSignup
	
	function userSignup_step2()//notyet
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
		/**
		*	@created <Early March 2012? >
		*	@remarks Here and in the functions this calls, POST data is directly accessed.
				Might spell disaster when changing POST names in the view page.
		*	@revised 25JUL2012-1215
		*/
		// ajax only and check access
		if( !$this->functionaccess->preUseracctctrl__manageAccountSave() ) return FALSE;
		
		$accountNum   = $this->clientsidedata_model->getAccountNum();
		$details      = $this->usefulfunctions_model->extractUserAccountDetailsFromPOST();
		$details_uplb = $this->usefulfunctions_model->extractUPLBConstDetailsFromPOST();

		// server-side form validation first
		if( !$this->inputcheck->useracctctrl__manageAccountSave( $details, $details_uplb ) )
		{
			$fieldFriendlyName = "";
			switch( $this->clientsidedata_model->getLastInternalError() )
			{
				case INTERNAL_ERR_USERNAME_FORM_FAIL:   $fieldFriendlyName = "username"; break;
				case INTERNAL_ERR_PASSWORD_FORM_FAIL:   $fieldFriendlyName = "password"; break;
				case INTERNAL_ERR_NAME_ALL_FORM_FAIL:   $fieldFriendlyName = "name"; break;
				case INTERNAL_ERR_EMAIL_ALL_FORM_FAIL:  $fieldFriendlyName = "email address"; break;
				case INTERNAL_ERR_PHONE_ALL_FORM_FAIL:  $fieldFriendlyName = "phone"; break;
				case INTERNAL_ERR_STUDENTNUM_FORM_FAIL: $fieldFriendlyName = "student number"; break;
				case INTERNAL_ERR_EMPNUM_FORM_FAIL:     $fieldFriendlyName = "employee number"; break;
			}
			if( $fieldFriendlyName == "" )
			{
				return $this->sessmaintain->assembleGenericFormValidationFail();
			}else{
				return $this->sessmaintain->assembleSpecificFormValidationFail( $fieldFriendlyName );
			}
		}
		// check for these are already taken by others
		if( $this->account_model->getUserInfoByUsername( $details[ 'username' ], FALSE , $accountNum ) )
		{
			return $this->sessmaintain->assembleExistingUsernameWarning();
		}
		if( $details_uplb !== FALSE ){
			if( $this->account_model->isStudentNumberExisting( $details_uplb[ 'studentNumber' ], FALSE ) )
			{	
				return $this->sessmaintain->assembleExistingStudentNumWarning();
			}
			if( $this->account_model->isEmployeeNumberExisting( $details_uplb[ 'employeeNumber' ], FALSE ) )
			{
				return $this->sessmaintain->assembleExistingEmployeeNumWarning();
			}
		}
		// now, transact with DB
		$this->airtraffic_v2->initialize( STAGE_MACCT1_PR, -1, IDLE );
		$this->account_model->updateMainAccountDetails( $accountNum, $details );
		$this->account_model->updateUPLBConstituencyDetails( $accountNum, $details_uplb );
		if( $this->airtraffic_v2->clearance() ){
			$this->airtraffic_v2->commit();
			log_message('DEBUG','manageAccountSave cleared for take off ' . $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleProceedSpecific( "sessionctrl", "The changes in your account have been saved.", 3000 );
			//return $this->sessmaintain->assembleProceedSpecific( "useracctctrl/myaccount_forward#", "The changes in your account have been saved.", 3000 );
		}else{
			$this->airtraffic_v2->rollback();
			log_message('DEBUG','manageAccountSave clearance error '. $this->airtraffic_v2->getGUID() );
			return $this->sessmaintain->assembleGenericTransactionFail();
		}
	}//manageAccountSave(..)

	function manageuser_disciplineuser()
	{
		/**
		*	@created 31JUL2012-1543
		*/
		die('Feature not yet available.');
	}
	
	function manageuser_viewdetails( $acctnum )
	{
		/**
		*	@created 31JUL2012-1325
		*/
		die('Feature not yet available.');
	}
	
	function myAccount()
	{
		/**
		*	@created <March 2012>
		*	@revised 29JUL2012-1505
		*/
		if( !$this->functionaccess->preUseracctctrl__myAccountPR() ) return FALSE;
		$this->clientsidedata_model->setSessionActivity( MANAGE_ACCOUNT, STAGE_MACCT0_HOME );
		redirect('useracctctrl/myaccount_forward');
	}
	
	function myaccount_forward()
	{
		/**
		*	@created 29JUL2012-1511
		*/
		if( !$this->functionaccess->preUseracctctrl__myAccountFW() ) return FALSE;
		$accountNum 		 =  $this->clientsidedata_model->getAccountNum();
		$userObj 			 = $this->account_model->getUserInfoByAccountNum( $accountNum );
		$uplbConstituencyObj =  $this->account_model->getUserUPLBConstituencyData( $accountNum );
		$permissionsObj 	 = $this->permission_model-> getPermissionStraight( $accountNum );

		$data['userObj'] 			 = $userObj;
		$data['uplbConstituencyObj'] = $uplbConstituencyObj;
		$data['permissionsObj'] 	 = $permissionsObj;
		
		
		$this->load->view( 'manageAccount/accountHome', $data );
	}
} // class
?>