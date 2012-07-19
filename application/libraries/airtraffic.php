<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
*	Air Traffic Control library
* 	Created 09JUL2012-1239
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Introducing ATC model!
*	Name's inspiration comes from aviation - you know, you need to have clearances and ACKs
		when flying. :D
*   And I don't think it won't be amiss since most Internet connections pass through air
		some time, some where. :D
*	This handles clearances etc, during AJAX/XMLHttpRequest calls, especially, for error
		handling.
*	Anyway, if $this->clearance(..) returns FALSE, any DB created by ::initialize()
		is not cleared - deletion of it is entrusted to a CRON job or eventctrl/preclean.
		This is because we use the state of the DB entry being deleted as sign of success
		in sessionctrl/contact_tower
**/
class airtraffic{
	var $CI;

	function __construct()
    {
		$this->CI = & get_instance();
		$this->CI->load->model('atc_model');
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('usefulfunctions_model');

		include_once( APPPATH.'constants/_constants.inc');
	}

	function clearance( $guid, $attempt, $loop_time )
	{
		/**
		*	@created 09JUL2012-1349
		*	@description The deciding one which determines if both server
				and client have already fulfilled the requirements of a particular 
				stage (back in the caller of this) and thus ready to proceed (i.e., 
					commit or rollback database changes.)
		*	@param $guid STRING GUID of the air traffic object.
		*	@param $attempt INT Number of loops to retry waiting for sessionctrl/contact_tower's actions
		*	@param $looptime INT Number of seconds to sleep before attempting to contact tower again, as dictated by $attempt.
		*	@remarks 11JUL2012-1500 If a sleep or delay of { X == sessctrl/contact_tower::{ $sleep_timeout * $atttempts } }
				placed before the for loop which waits for the action of sessctrl/contact_tower, this will
				clear (return TRUE) but then sessctrl/contact_tower will return an error to the client.
				In short - transaction committed to DB but in client, opposite was displayed and thus it will
				be a problem.
				SOLUTION: Make sure that this function's for loop will start first than sessctrl/contact_tower. So
					far, I can't think of a circumstance where the opposite will happen, unintentionally.
		*	@returns BOOLEAN
		**/
		log_message('DEBUG','airtraffic::clearance called ' . $guid);
		/*
			We have to enclose the updating of the server-side state and stage number here 
			because before this, there is a mysql transaction and the autocommit was set to zero, 
			so this seems an exception by committing it immediately.
		*/
		// set security guid, to be compared when client contacts tower
		$auth_guid = $this->CI->usefulfunctions_model->guid();
		$this->CI->clientsidedata_model->setAuthGUID( $auth_guid );
		$this->writeAirTrafficDataToXML( Array( $guid, STAT_SERVEROK, $auth_guid ) );
		/*
			Now terminate the AJAX call back in client and let them initiate the call
			to seatctrl/contact_tower
		*/
		/*
			This part thanks to http://php.net/manual/en/features.connection-handling.php#71172
			Partially changed.
		*/
		// and finally..
		/*
			THIS SHOULD BE ONLY USED WHEN OUTPUT BUFFERING IS ON IN PHP	( default is OFF, but check anyway)
		*/
		//ob_end_clean();
		$msg = $this->CI->makexml_model->XMLize_AJAX_Response( "okay", "success", "CONTACT_TOWER", 0, "sessctrl/contact_tower", "" );
		ob_start();
		header("Connection: close");
		header("Content-Length: " .strlen($msg));
		echo $msg;
		@ob_end_flush(); // Strange behaviour, will not work
		flush();         // Unless both are called !
		log_message('DEBUG','airtraffic::clearance '  . $guid . ' returned headers and output, requesting client to call tower.');
		log_message('DEBUG','airtraffic::clearance '  . $guid . ' auth id : '. $this->CI->clientsidedata_model->getAuthGUID() );
		//now here, let's see if the client has already contacted tower
		for( $x = 0; $x < $attempt; $x++ )
		{
			log_message('DEBUG','airtraffic::clearance '. $guid . ' for loop ' . $x . ' of ' . $attempt );
			$arrayized = $this->readAirTrafficDataFromXML( $guid );
			switch( intval($arrayized[ 'status' ]) ){
				case STAT_CLIENTOK: 
					log_message('DEBUG','airtraffic::clearance granted ' . $guid );
					return TRUE;
				case STAT_CLIENT_FAILED:
					log_message('DEBUG','airtraffic::clearance failed ' . $guid . ' due to contact tower fault' );
					return FALSE;
				default: break;
			}
			sleep( $loop_time );
		}
		log_message('DEBUG','airtraffic::clearance wait on tower expired' . $guid );
		$this->writeAirTrafficDataToXML( Array( $guid, STAT_SERVER_WAIT_ON_CLIENT_TIMEOUT, $auth_guid ) );
		return FALSE;
	}//clearance(..)

	function deleteXML( $guid )
	{
		/**
		*	@created 14JUL2012-1736
		*	@description Deletes XML file.
		*	@returns VOID.
		**/
		return unlink( $this->CI->makexml_model->getAirTrafficRelPath( $guid ) );
	}

	function initialize(
		$guid, $sessActivity, $after_sessname, $after_sesstage, $go_onsuccess, 
		$call_func_on_success, $attempt, $looptime 
	){
		/**
		*	@created 09JUL2012-1244
		*	@description Initializes air traffic activities. Well, what really happens is this creates a data holder
				in DB regarding air traffic. This too contains the URI to redirect on success of the calling function,
				as well as the activity name and stage needed to be set in the CI session cookie.
		*	@remarks Currently, caller should be a script that responds to an ajax call.		
		*	@returns BOOLEAN.
		**/
		log_message('DEBUG','airtraffic::initialize called ' . $guid);
		if( $this->CI->atc_model->create( 
				$guid, 
				$sessActivity[0],
				$sessActivity[1],
				$after_sessname,
				$after_sesstage, 
				$go_onsuccess,
				$call_func_on_success,
				$attempt,
				$looptime
			) and
			$this->writeAirTrafficDataToXML( Array( $guid, STAT_CREATED, '' ) )
		)
		{
			$this->CI->clientsidedata_model->set_ATC_Guid( $guid );
			if( $this->CI->clientsidedata_model->get_ATC_Guid() === FALSE )
			{
				log_message('DEBUG','airtraffic::initialize failed on_set_atc_guid');
				echo $this->CI->makexml_model->XMLize_AJAX_Response( "error", "error", "ATC_INIT_FAIL_2", 0, "", "" );
				return FALSE;
			}
			return TRUE;
		}else{
			log_message('DEBUG','airtraffic::initialize failed general');
			echo $this->CI->makexml_model->XMLize_AJAX_Response( "error", "error", "ATC_INIT_FAIL_1", 0, "", "");
			return FALSE;
		}
	}//initialize(..)
	
	function readAirTrafficDataFromXML( $guid, $wait_for_exist_timeout = 10 )
	{
		/**
		*	@created 09JUL2012-2103
		**/
		log_message('DEBUG','airtraffic::readAirTrafficDataFromXML accessed');
		$filename = $this->CI->makexml_model->getAirTrafficRelPath( $guid );
		for( $x = 0; $x<$wait_for_exist_timeout; $x++){
			if( file_exists ( $filename  ) ){
				log_message('DEBUG','airtraffic::readAirTrafficDataFromXML file exists : ' . $filename );
				return $this->CI->makexml_model->toArray_prep(
					$this->CI->makexml_model->readXML( $filename )
				);
			}
			sleep( 1 );
		}
		log_message('DEBUG','airtraffic::readAirTrafficDataFromXML wait for file existence timeout reached');
		return FALSE;
	}
	
	function terminateService( $guid, $callDelXML = FALSE )
	{
		/**
		*	@created 09JUL2012-1246
		*	@description Deletes all ATC transactions/DB entry/CI session data, as
				well as external file(s).
		*	@param $guid STRING GUID of the air traffic object.
		*	@param @callDelXML BOOLEAN Whether to call the function that deletes the XML file used.
		*	@returns VOID.
		**/
		log_message('DEBUG','airtraffic::terminate called ' . $guid);
		$call_del_xml_res = TRUE;
		if( $callDelXML ) $call_del_xml_res = $this->deleteXML( $guid );
		return ( $this->CI->atc_model->delete( $guid ) and $call_del_xml_res );
	}
	
	function writeAirTrafficDataToXML( $arrayized )
	{
		/**
		*	@created 09JUL2012-2103
		**/
		return $this->CI->makexml_model->XMLize_AirTrafficData( $arrayized );
	}
}