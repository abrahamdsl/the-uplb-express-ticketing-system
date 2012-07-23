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
			urlencode(base_url().$redirURI)
		);
		if( $isEcho ){
			echo $msg;
			return TRUE;
		}else{
			return $msg;
		}
	}//assembleProceed
	
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