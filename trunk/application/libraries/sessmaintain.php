<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Session Maintenance Library
* 	Created late 14JUL2012-1708
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Well, mostly about echoing content back to client.
*/
class sessmaintain{
	var $CI;
	
	function __construct( $params = NULL )
    {
		$this->CI = & get_instance();
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('event_model');
		$this->CI->load->model('login_model');
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('seat_model');
		$this->CI->load->model('slot_model');
		$this->CI->load->model('ticketclass_model');
    }
	
	private function setThings_OnControllerAccessRitual(){
	/**
	*	@created 17JUL2012-1317
	*	@description Refactored function because of double use by $this->onControllerAccessRitual()
	*/
		$this->CI->clientsidedata_model->setRedirectionURLAfterAuth(
			$this->CI->input->server( 'REDIRECT_QUERY_STRING' )
		);
		redirect('sessionctrl/authenticationNeeded');
		return FALSE;
	}
	
	function assemble4009(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"invalid authentication GUID", 
			"IDENTITY_SPOOF_DETECTED",
			4009, 
			"Are you hacking the app?",
			""
		);
		return FALSE;
	}
	
	function assemble4404(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"IVA_ACCESS_DENIED", 
			"IVA_ACCESS_DENIED",
			4404, 
			"Invalid access detected. You should not be calling this function. A module should do its work first before calling this.",
			""
		);
		return FALSE;
	}
	
	function assemble4405(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"ATC_DATA_MISSING", 
			"ATC_DATA_MISSING",
			4405, 
			"At least one required input field is not found.",
			""
		);
		return FALSE;
	}
	
	function assemble4406(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"ATC_REQUEST_UNKNOWN", 
			"ATC_REQUEST_UNKNOWN",
			4406, 
			"",
			""
		);
		return FALSE;
	}
	
	function assemble5900(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"ATC_IO_ERR", 
			"ATC_IO_ERR",
			5900, 
			"A required file for air traffic control cannot be read/written to.",
			""
		);
		return FALSE;
	}
	
	function assemble5901(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"ATC_SCRIPT_NOT_CLEARED", 
			"ATC_SCRIPT_NOT_CLEARED",
			5901, 
			"The original script did not finish processing. The transaction should have been rolled back.",
			""
		);	
		return FALSE;
	}
	
	function assemble5902(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"ATC_SCRIPT_NOT_DONE", 
			"ATC_SCRIPT_NOT_DONE",
			5902, 
			"You are not yet supposed to call tower for now. Please resubmit the form.",
			""
		);	
		return FALSE;
	}
	
	function assemble5903(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"ATC_PREMATURELY_EXITED", 
			"ATC_PREMATURELY_EXITED",
			5903, 
			"The original script terminated immediately before tower was contacted. Please resubmit again. The transaction has been rolled back.",
			""
		);	
		return FALSE;
	}
	
	function assembleAccountNum404()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"username not found",
			"ACCOUNT_NUM_404",
			4020, 
			"The specified account number is not found in the system.",
			""
		);
		return FALSE;
	}
	
	function assembleATC_V2_ClearanceFail()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"clearance fail", 
			"ATC_V2_CLEARANCE_FAIL",
			5910, 
			"Unable to seek clearance to move to the next page. Most probably, the submission of a form was prematurely terminated or there was a database error. Please try resubmission.",
			""
		);	
		return FALSE;
	}
	
	function assembleAuthFail(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"authentication fail", 
			"AUTH_FAIL",
			4003, 
			"Invalid credentials: the username and password combination is incorrect.",
			""
		);	
		return FALSE;
	}

	function assembleConfirmStep3Error( $response_pandc ){
		$sendback = "";
		if( isset( $response_pandc["misc"] ) ){
			foreach( $response_pandc["misc"] as $val ) $sendback .= ( $val."_" ); 
			$response_pandc[ "message" ] .= ( '<br/><br/>' . $sendback );
		}
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"error", 
			"UNIDENTIFIED",
			$response_pandc[ "code" ], 
			$response_pandc[ "message" ],
			''
		);
		return FALSE;
	}	
	
	function assembleCustomFuncXML404(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"file missing", 
			"CUSTFUNC_XML_404",
			5905, 
			"An internal file in use by the server was missing when it should not be. The transaction should have been rolled back.",
			""
		);
		return FALSE;
	}
	
	function assembleExistingEmployeeNumWarning()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"already in use",
			"EMPNUM_ALREADY_TAKEN",
			4204, 
			"The specified employee number is already in use.",
			""
		);	
		return FALSE;
	}
	
	function assembleExistingStudentNumWarning()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"already in use",
			"STUDENTNUM_ALREADY_TAKEN",
			4203, 
			"The specified student number is already in use.",
			""
		);	
		return FALSE;
	}
	
	function assembleExistingUsernameWarning()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"already in use",
			"USERNAME_ALREADY_TAKEN",
			4202, 
			"The specified username is already in use.",
			""
		);	
		return FALSE;
	}

	function assembleFreePaymentModeNotRemovable()
	{
		$this->CI->load->view( 'errorNotice', Array(
				'error' => "CUSTOM",
				'defaultAction' => 'Payment Modes',
				'redirect' => 2,
				'redirectURI' => base_url().'useracctctrl/managepaymentmode',
				'theMessage' => "By this system's design, this payment mode is not designed to be removable. Edit my code if you want to. <br/><br/>:D"
			)
		);
		return FALSE;
	}

	function assembleGenericFormValidationFail(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"form validation fail", 
			"INVALID_VALUE",
			4006, 
			"The submitted data to the server is in the incorrect format or the client-side form inputs check has been circumvented.",
			""
		);	
		return FALSE;
	}
	
	function assembleGenericTransactionFail(){
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"transaction fail", 
			"GENERIC_TRANSACTION_FAIL",
			5999, 
			"Something went wrong while processing your request. The transaction should have been rolled back. Please try submitting again.",
			""
		);	
		return FALSE;
	}
	
	function assembleInvalidPassword()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"invalid password", 
			"INVALID_PASSWORD",
			4010, 
			"The specified password for the user is invalid.",
			""
		);	
		return FALSE;
	}

	function assembleInvalidPasswordCurrent()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"invalid password", 
			"INVALID_PASSWORD",
			4010, 
			"The specified current password is invalid.",
			""
		);	
		return FALSE;
	}
	
	function assembleIntentionalISE(){
		$this->CI->output->set_status_header( 500, 'INTENTIONAL ISE TEST' );
		header("Connection: close");
		ob_start();
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"error", 
			"intentional Internal server err",
			0, 
			"This is just an intentional internal server error test.",
			""
		);
		$size = ob_get_length();
		header("Content-Length: {$size}");
		@ob_end_flush(); // Strange behaviour, will not work
		flush();        // Unless both are called !
		return FALSE;
	}
	
	function assemblePaymentModeAlreadyExists()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"already exists", 
			"PAYMENT_MODE_EXISTS",
			1500, 
			"Payment mode name exists already.",
			""
		);
		return FALSE;
	}
	
	function assembleProceed( $redirURI, $isEcho = TRUE ){
		/**
		*	@created <revision 39>
		*	@revised 20JUL2012-1129
		**/
		log_message('DEBUG', 'sessmaintain:assembleProceed accessed');
		$msg = $this->CI->makexml_model->XMLize_AJAX_Response(
			"okay", 
			"success", 
			"PROCEED",
			0, 
			"",
			is_null($redirURI) ? "" : urlencode(base_url().$redirURI)
		);
		if( $isEcho ){
			echo $msg;
			return TRUE;
		}else{
			return $msg;
		}
	}//assembleProceed
	
	function assembleProceedSpecific( $redirURI, $message = "", $redirAfter = 1000, $isEcho = TRUE ){
		/**
		*	@created 28JUL2012-1358
		*/
		log_message('DEBUG', 'sessmaintain:assembleProceedSpecified accessed');
		$msg = $this->CI->makexml_model->XMLize_AJAX_Response(
			"okay", 
			"success", 
			"PROCEED",
			0, 
			$message."<br/>Redirecting in " . ( $redirAfter / 1000 ) . " second(s) ... ",
			is_null($redirURI) ? "" : urlencode(base_url().$redirURI),
			$redirAfter
		);
		if( $isEcho ){
			echo $msg;
			return TRUE;
		}else{
			return $msg;
		}
	}//assembleProceed
	
	function assembleSpecificFormValidationFail( $fieldFriendlyName )
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"invalid " . $fieldFriendlyName,
			"GEN_FORM_VALIDATION_FAIL",
			4015, 
			"The specified ". $fieldFriendlyName . " is not entered in the correct format. ( Did you circumvent the browser-based check? )",
			""
		);	
		return FALSE;
	}

	function assembleUsername404()
	{
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error",
			"username not found",
			"USERNAME_DOES-NOT-EXIST",
			4001, 
			"The specified username is not found in the system.",
			""
		);
		return FALSE;
	}
	
	function assembleManagePaymentModeDeleteFail()
	{
		$this->CI->load->view( 'errorNotice', Array(
				'error' => "CUSTOM",
				'defaultAction' => 'Payment Modes',
				'redirect' => 2,
				'redirectURI' => base_url().'useracctctrl/managepaymentmode',
				'theMessage' => "Something went wrong while processing the deletion of the payment mode. It may have been not deleted. <br/><br/>Please try again."
			)
		);
		return FALSE;
	}//assembleManagePaymentModeDeleteFail
	
	function assembleManagePaymentModeDeleteOK()
	{
		$this->CI->load->view( 'successNotice', Array(
				'error' => "CUSTOM",
				'defaultAction' => 'Payment Modes',
				'redirect' => 2,
				'redirectURI' => base_url().'useracctctrl/managepaymentmode',
				'theMessage' => "The payment mode has been successfully deleted."
			)
		);
		return TRUE;
	}//assembleManagePaymentModeDeleteOK

	function assembleManagePaymentModeDeletePrompt( $uniqueID )
	{
		$this->CI->load->view( 'confirmationNotice', Array(
				'title' => "Be careful on what you wish for",
				'redirect' => 2,
				'yesURI' => base_url().'useracctctrl/managepaymentmode_delete_process',
				'noURI' => base_url().'useracctctrl/managepaymentmode',
				'redirectURI' => base_url().'useracctctrl/managepaymentmode',
				'theMessage' => "Are you sure you want to delete this payment mode?",
				'formInputs' => Array( 'pChannel' => $uniqueID )
			)
		);
		return TRUE;
	}//assembleManagePaymentModeDeletePrompt
	
	function assembleManagePaymentModeEdit404()
	{
		$this->CI->load->view( 'errorNotice', Array(
				'error' => "CUSTOM",
				'defaultAction' => 'Payment Modes',
				'redirect' => 2,
				'redirectURI' => base_url().'useracctctrl/managepaymentmode_edit',
				'theMessage' => "The payment mode specified does not exist. Are you hacking the app?"
			)
		);
	}//assembleManagePaymentModeEdit404

	function assembleManagePaymentModeEditValidateFail()
	{
		$data = Array();
		$data['defaultAction'] = 'Payment Modes';
		$data['redirect'] = 2;
		$data['error'] = 'NO_DATA';
		$data['redirectURI'] = base_url().'useracctctrl/managepaymentmode';
		$this->CI->load->view('errorNotice', $data );
		return FALSE;
	}

	function assembleOnlyAJAXAllowed( $defaultAct = "Home", $defaultURI = NULL )
	{
		$this->CI->load->view( 'errorNotice', Array(
				'error' => "CUSTOM",
				'defaultAction' => $defaultAct,
				'redirect' => 2,
				'redirectURI' => is_null($defaultURI) ?  base_url() : base_url().'useracctctrl/managepaymentmode',
				'theMessage' => "Something went wrong while processing the deletion of the payment mode. It may have been not deleted. <br/><br/>Please try again."
			)
		);
		return FALSE;
	}//assembleOnlyAJAXAllowed
	
	function onControllerAccessRitual( $allowed_without_auth = FALSE )
	{
	/**
	*	@created 17JUL2012-1317
	*	@description Handles the checking of whether a user is logged in or not and then if not,
			sets in the cookies where to redirect after a user logs in and redirects to the 
			login page.
	**/
		if( !$this->CI->login_model->isUser_LoggedIn() )
		{
			if( $allowed_without_auth !== FALSE ){
				if( !in_array( $this->CI->input->server( 'REDIRECT_QUERY_STRING' ), $allowed_without_auth ) ){
					return $this->setThings_OnControllerAccessRitual();
				}
			}else{
				return $this->setThings_OnControllerAccessRitual();
			}
		}
		return TRUE;
	}//onControllerAccessRitual(..)
	
	function successPaymentAndConfirmed(){
		// might not be used.
		log_message('debug','sessmaintain::successPaymentAndConfirmed accessed');
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"okay", 
			"success", 
			"PAYMENT_PROCESS_OK",
			1003, 
			"Succesfully proccessed payment and booking confirmed.",
			''
		);
		return TRUE;
	}

}