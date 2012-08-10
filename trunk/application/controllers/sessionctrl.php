<?php
/**
*	Session Controller
* 	CREATED <November 2011>
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Handles the interaction with the user regarding the access of this application.
**/
class sessionctrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		include_once( APPPATH.'constants/_constants.inc');
		include_once( APPPATH.'constants/atc.inc');
		$this->load->library('airtraffic');
		$this->load->library('bookingmaintenance');
		$this->load->library('form_validation');
		$this->load->library('inputcheck');
		$this->load->library('user_agent');
		$this->load->library('sessmaintain');
		$this->load->model('account_model');
		$this->load->model('atc_model');
		$this->load->model('booking_model');
		$this->load->model('browsersniff_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('login_model');
		$this->load->model('makexml_model');
		$this->load->model('ndx_model');
		$this->load->model('ndx_mb_model');
		$this->load->model('sat_model');
		$this->load->model('permission_model');
		$this->load->model('telemetry_model');
		$this->load->model('usefulfunctions_model');
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

	private function preclean( $userAccountNum )
	{
		/**
		*	@created 22APR2012-1454
		*	@purpose The defaulted bookings clean-up tool will be run on each booking got from the DB. If current
				user is an admin, all bookings (whether belonging to him or not are got), if he is not, then
				only those belonging to him are got.
				Also, the expired (manage) booking cookies-on-server, Session Activity Tracking on DB data are also deleted.
		*	@history 20JUL2012-1121 Moved from eventctrl and was privatized.
		*/
		$bookings = false;
		
		if( $this->permission_model->isAdministrator( $userAccountNum ) ){
			$bookings = $this->booking_model->getAllBookings( false, false );
		}else{
			$bookings = $this->booking_model->getAllBookings( $userAccountNum, false );
		}
		$this->ndx_mb_model->deleteExpiredManageBookingCookiesOnServer();
		$this->ndx_model->deleteExpiredBookingCookiesOnServer();
		$this->airtraffic->deleteExpired();
		// delete session activity tracking entries
		$this->sat_model->deleteExpiredSAT();
		if( $bookings !== false )
		{
		  foreach( $bookings as $singleBooking )
		  {
			/*
			  This checks if there are bookings marked as PENDING-PAYMENT' and yet
			  not able to pay on the deadline - thus forfeited now.
		    */
			$this->bookingmaintenance->cleanDefaultedBookings( $singleBooking->EventID, $singleBooking->ShowingTimeUniqueID ); 
		  }
		}
	}//preclean
	
	private function sorryNoticeHeader()
	{
		echo "The UPLB Express Ticketing System<hr/><br/><br/>";
	}
	
	private function strictlyNotAllowedStandard()
	{
		$this->sorryNoticeHeader();
		echo "We're sorry, we have blacklisted your browser from using this system, due to incompabilities attributable to the browser";
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
			$uuid = $this->usefulfunctions_model->guid();
			if( $this->session->userdata('JUST_LOGGED_OUT') != TRUE )
			{	
				$_client_iPv4 = $this->usefulfunctions_model->VisitorIP();
				$_client_user_agent = $this->input->user_agent();
				$client_browserShort_and_OS = $this->determineUserAgent();
				$bsniff_mod_data = $this->browsersniff_model->browser_detection( 'full', '', '' ) ;
				$uuid = $this->usefulfunctions_model->guid();
			
				log_message('DEBUG', 'NEW USER Logging in from ' . $_client_iPv4 );
				log_message('DEBUG', 'NEW USER Agent Raw : ' . $_client_user_agent );
				log_message('DEBUG', 'NEW USER Agent + Platform: ' . $client_browserShort_and_OS  );
				log_message('DEBUG', 'NEW USER Session UUID ' . $uuid);
				// check client's browser if permitted
				$data['UA_CHECK'] = $this->browsersniff_model->decideActionOnUserAgent();
				$data['uuid_new_ident'] = $this->usefulfunctions_model->guid();
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
						if( substr($data['_client_user_agent'], 0, 13 ) == 'W3C_Validator' ){
							unset( $data['UA_CHECK'] );
						}else{
							$this->telemetry_model->add( BR_AGENT_DENIED, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS );
							$this->agentDenied();
							return false;
						}
						break;
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
		if( !$this->sessmaintain->onControllerAccessRitual() ) return FALSE;
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['permissions'] = $this->permission_model->getPermissionStraight( $this->session->userdata( 'accountNum' ) );
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		$this->load->view('homepage', $data);
	}//userHome
	
	function authenticationNeeded()
	{	
		// ec 4999
		//$data['LOGIN_WARNING'] = array( "You have to log-in first before you can access the feature requested. " ) ;
		//$this->session->set_userdata($data);
		redirect ('sessionctrl/index');
	}
	
	private function getPostLoginRedirection(){
		/**
		*	@created 21JUL2012-2002
		
		**/
		$redirect_to = $this->clientsidedata_model->getRedirectionURLAfterAuth();
		$this->clientsidedata_model->deleteRedirectionURLAfterAuth();
		return ( ( $redirect_to === FALSE ) ? "sessionctrl/userHome" : $redirect_to );
	}
	
	function login()
	{
		/*
			@created 26NOV2011-2014 at SM City Calamba Global Pinoy Center :-D
			@revised 21JUL2012-1140
		*/
		// preliminary checks
		if( !$this->input->is_ajax_request() ) redirect( '/' );
		// form-validation, though back in the client the form is checked via JS
		if( !$this->inputcheck->sessionctrl__login() )
	    {
			return $this->sessmaintain->assembleGenericFormValidationFail();
		}
		// var init
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		// processing
		if( $this->account_model->isUserExistent( $username, $password ) ) 
		{
			$accountNum = $this->account_model->getAccountNumber( $username );
			// custom function call
			$customFunc = Array(
				"\$this->login_model->setUserSession( ". $accountNum .
					" , " . var_export( $this->account_model->getUser_Names( $username ), TRUE ). " ); ",
				"\$this->session->set_userdata('logged_in', TRUE);"
			);
			// initialize air traffic - i.e., URI and session activity name and stage to be set on success
			if( !$this->airtraffic->initialize( 
					IDLE, -1, $this->getPostLoginRedirection(), NULL, 10, 1, $customFunc
				)
			){
				return FALSE;
			}
			$this->db->trans_begin();
			$this->preclean( $accountNum );
			// now, seek clearance and decide whether or not to commit or rollback
			if( $this->airtraffic->clearance() and $this->airtraffic->terminateService() ){
				$this->db->trans_commit();
				log_message('DEBUG', "user '" .$username."' logged in | cleared for take off GUID : " . $this->airtraffic->getGUID() );
			}else{
				$this->db->trans_rollback();
				$this->airtraffic->deleteCustomFunctionsXML();
				log_message('DEBUG','logging in clearance error ' . $this->airtraffic->getGUID() );
			}
			$this->airtraffic->deleteXML();
		}else{
			return $this->sessmaintain->assembleAuthFail();
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
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
		$this->login_model->logout();
		$this->login_model->deleteUserCookies();
		$data['JUST_LOGGED_OUT'] = TRUE;
		$this->session->set_userdata($data);
		$this->telemetry_model->add(4,$this->usefulfunctions_model->guid(),$this->usefulfunctions_model->VisitorIP(),'REF_'.$this->session->userdata('telemetry_uuid'),'' );
		$this->session->unset_userdata('telemetry_uuid');
		redirect('/');
	} //logout
	
	function redirect_unknown()
	{	
		echo print_r( $_COOKIE[ 'ci_session' ] );
		echo "The server do not know where to redirect you."; //3999
	}

	function contact_tower( ){
		/**
		*	@created 09JUL2012-1400
		*	@description This is called by AJAX requests. Basically, writes in an XML file indicator
				whether client received server's response in an earlier AJAX call, so back in the script
				the server is executing, they can know whether or not to rollback a transaction
				or not (i.e. for connection timeout/interrupted during transmission especially for 
					forms having many input fields).
		*	@remarks You might want to remove the log_message() debugs.
		**/
		$sleep_timeout;
		$attempts;
		$request = $this->input->post( 'request' );
		$guid    = $this->clientsidedata_model->get_ATC_Guid();
		$x;
		$custom_calls = NULL;
		
		log_message('debug','sessionctrl/contact_tower|accessed|'. $guid . '|' .$request);
		$obj = $this->atc_model->get( $guid );	// fetch ATC data from DB
		$sessActivity = $this->clientsidedata_model->getSessionActivity();
		/*
			Check if the session stage should be in here.
		*/
		if( $obj === FALSE or count( $sessActivity ) !== 2 or
			!( $obj->DETAIL1 == $sessActivity[0] and intval( $obj->DETAIL2 ) == $sessActivity[1] )
		){	// IVA_ACCESS_DENIED
			log_message('DEBUG', 'dump -2 ' . print_r( $_POST, TRUE ) );
			log_message('DEBUG', 'dump -1 ' . print_r( $_COOKIE[ 'ci_session'], TRUE ) );
			log_message('DEBUG', 'dump .. ' . print_r( $obj, TRUE ) );
			log_message('DEBUG', 'dump 2.. ' . print_r( $sessActivity, TRUE ) );
			//log_message('DEBUG', 'dump 3.. ' . intval( intval( $obj->DETAIL2 ) == $sessActivity[1] ) );
			return $this->sessmaintain->assemble4404();
		}
		if( $request === FALSE)
		{	// ATC_DATA_MISSING
			return $this->sessmaintain->assemble4405();
			return FALSE;
		}
		// read air traffic data from file
		$at_data = $this->airtraffic->readAirTrafficDataFromXML( 10, $guid );
		if( count( $at_data ) < 1 )
		{	// ATC_IO_ERR : data not gotten properly.
			return $this->sessmaintain->assemble5900();
		}
		if( $at_data['auth'] !== $this->clientsidedata_model->getAuthGUID() )
		{	// // IDENTITY_SPOOF_DETECTED
			$at_data[ 'status' ] = STAT_CLIENT_FAILED;
			$this->airtraffic->writeAirTrafficDataToXML( $at_data );
			log_message('DEBUG','sessionctrl/contact_tower|'.$guid.'|GUID_AUTH_FAIL|IDENTITY_SPOOF_DETECTED : ' . print_r( $_COOKIE['ci_session'], TRUE ) . '|' . print_r( $at_data, TRUE ) );
			return $this->sessmaintain->assemble4009();
		}
		// retrieve custom function calls, if any
		if( $obj->IS_THERE_CUSTOM ){
			log_message('debug', $guid . ' has custom function calls ' );
			$custom_calls = $this->airtraffic->readCustomCallsFromXML( $guid );
			if( $custom_calls === FALSE ){
				log_message('DEBUG', 'XML custom calls for '. $guid .' missing!!! ' );
				return $this->sessmaintain->assembleCustomFuncXML404();
			}
		}
		// return $this->sessmaintain->assembleIntentionalISE();
		$current_stat  = intval( $at_data['status'] );
		$sleep_timeout = intval($obj->LOOP_TIME);
		// this will ensure that the function the client called before this will have the last say.
		$attempts      = intval($obj->ATTEMPTS) +  ( ( $sleep_timeout < 60 ) ? 2: 1 );
		switch( intval($request) ){
			case 1: 
					/* 
						Signifies that the script before calling tower already finished processing and
						is now only waiting for our actions.
					*/
					if( $current_stat  === STAT_SERVEROK ){
						$at_data[ 'status' ] = STAT_CLIENTOK;						// signifies that clientside has contacted server for clearance
						$this->airtraffic->writeAirTrafficDataToXML( $at_data );	// write the updated stat so the script can read it.
						/**
							Back in the calling script, the ATC data is read and they do their thing. The most important to
							us is that, the ATC data in the database is deleted. Therefore, we get the ATC data again
							here and if function returns FALSE, server has deleted the ATC data on DB meaning it's now ok to proceed.
						**/
						for( $x = 0; $x<$attempts; $x++ ){
							log_message('debug','sessionctrl/contact_tower dnc loop ' . $x . ' ' . $guid);
							if( $this->atc_model->get( $guid ) === FALSE )
							{
								// cleared now - the script has deleted it.
								log_message('debug','sessionctrl/contact_tower done now'. $guid);
								$this->clientsidedata_model->deleteAuthGUID();
								$this->clientsidedata_model->delete_ATC_Guid();
								$this->clientsidedata_model->updateSessionActivityStage( $obj->DETAIL4, !( $obj->DETAIL3 == IDLE ) );
								// execute any custom function calls
								if( $obj->IS_THERE_CUSTOM ){
									foreach( $custom_calls->call as $singleCall ){
										log_message('debug', ' executing custom function ' . $singleCall );
										eval( $singleCall );
									}
								}
								// now, the return XML to be echoed
								if( is_null( $obj->CALL_ON_SUCCESS) ){
									return $this->sessmaintain->assembleProceed( $obj->DETAIL5 );
								}else{
									return $this->sessmaintain->{$obj->CALL_ON_SUCCESS}();
								}
							}
							sleep( rand(1, $sleep_timeout) );
						}
						log_message('debug','sessionctrl/contact_tower server ATC_SCRIPT_NOT_CLEARED' );
						// restore the original configuration
						$at_data[ 'status' ] = $current_stat;
						$this->airtraffic->writeAirTrafficDataToXML( $at_data );
						return $this->sessmaintain->assemble5901();				// ATC_SCRIPT_NOT_CLEARED
					}else
					if(  $current_stat  === STAT_SERVER_WAIT_ON_CLIENT_TIMEOUT )
					{	// ATC_PREMATURELY_EXITED
						$this->airtraffic->deleteXML( $guid );
						return $this->sessmaintain->assemble5903();
					}else{
						// ATC_SCRIPT_NOT_DONE
						log_message('debug','sessionctrl/contact_tower server not done yet' );
						return $this->sessmaintain->assemble5902();
					}
			default:
					log_message('debug','sessionctrl/contact_tower Requested service unknown: ' . $request );
					$at_data[ 'status' ] = STAT_CLIENT_FAILED;	
					$this->airtraffic->writeAirTrafficDataToXML( $at_data );
					return $this->sessmaintain->assemble4406();	// ATC_REQUEST_UNKNOWN
		}
	}//contact_tower()
	
	function test_trans(){
		/**
		*	@created 18JUL2012-2100
		*	@description This is just a test for database transaction support.
		*	@remarks REMOVE ON PRODUCTION! :D
		**/
		$this->db->insert( 
			'event',
			Array( 'EventID' => rand(262,665), 'Name' => 'nsammple', 'Location' => 'dump yah', 'Description' => 'wala lungs', 'FB_RSVP' => NULL, 'Temp' => 100, 'ByUser' => 582327 ) 
		);
		var_dump( $this->db->get( 'event' )->result() );
		echo 'trans_start<br/>';
		$this->db->trans_begin();	// database checkpoint
		for( $x=0, $y =10; $x<$y;$x++ ){
			$this->db->insert( 
				'event',
				Array( 'EventID' => rand(667,984), 'Name' => $this->usefulfunctions_model->guid(),
					 'Location' => 'dump yah', 'Description' => 'wala lungs', 'FB_RSVP' => NULL, 'Temp' => 100, 'ByUser' => 582327 ) 
			);
		}
		echo 'now checking<br/>';
		var_dump( $this->db->get( 'event' )->result() );
		echo 'now rolling back<br/>';
		$this->db->trans_rollback();
		var_dump( $this->db->get( 'event' )->result() );
	}
	
	function serverpushtest()
	{
		ob_start();
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		//generate random number for demonstration
		$new_data = rand(0, 1000);
		$sleep = rand( 0, 5);
		log_message('DEBUG','Sleep for seconds ' . $sleep );
		echo "retry: 1000\n\n";
		echo "data: Panggapsumnida: gad " .$new_data . date('Y-m-d H:i:s') ."\n\n";
		//sleep( $sleep );
		//echo the new number
		ob_end_flush();
	}
}//class
?>