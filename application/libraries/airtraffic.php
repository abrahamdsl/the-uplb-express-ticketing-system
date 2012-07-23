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
	var $x_guid;
	var $x_sessAct;
	var $x_attempts;
	var $x_looptime;
	var $x_is_customfunc_set = FALSE;

	function __construct()
    {
		$this->CI = & get_instance();
		$this->CI->load->model('atc_model');
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('usefulfunctions_model');

		include_once( APPPATH.'constants/_constants.inc');
	}

	function clearance()
	{
		/**
		*	@created 09JUL2012-1349
		*	@description The deciding one which determines if both server
				and client have already fulfilled the requirements of a particular 
				stage (back in the caller of this) and thus ready to proceed (i.e., 
					commit or rollback database changes.)
		*	@remarks 11JUL2012-1500 If a sleep or delay of { X == sessctrl/contact_tower::{ $sleep_timeout * $atttempts } }
				placed before the for loop which waits for the action of sessctrl/contact_tower, this will
				clear (return TRUE) but then sessctrl/contact_tower will return an error to the client.
				In short - transaction committed to DB but in client, opposite was displayed and thus it will
				be a problem.
				SOLUTION: Make sure that this function's for loop will start first than sessctrl/contact_tower. So
					far, I can't think of a circumstance where the opposite will happen, unintentionally.
		*	@remarks $this->initialize() should have been called by the caller of this before this!
		*	@returns BOOLEAN
		**/
		log_message('DEBUG','airtraffic::clearance called ' . $this->x_guid );
		/*
			We have to enclose the updating of the server-side state and stage number here 
			because before this, there is a mysql transaction and the autocommit was set to zero, 
			so this seems an exception by committing it immediately.
		*/
		// set security guid, to be compared when client contacts tower
		$auth_guid = $this->CI->usefulfunctions_model->guid();
		$this->CI->clientsidedata_model->setAuthGUID( $auth_guid );
		$this->writeAirTrafficDataToXML( Array( $this->x_guid, STAT_SERVEROK, $auth_guid ) );
		/*
			Now terminate the AJAX call back in client and let them initiate the call
			to seatctrl/contact_tower
		*/
		$this->CI->usefulfunctions_model->returnPrematurely(
			$this->CI->makexml_model->XMLize_AJAX_Response( "okay", "success", "CONTACT_TOWER", 0, "sessctrl/contact_tower", "" )
		);
		log_message('DEBUG','airtraffic::clearance '  . $this->x_guid . ' returned headers and output, requesting client to call tower.');
		log_message('DEBUG','airtraffic::clearance '  . $this->x_guid . ' auth id : '. $this->CI->clientsidedata_model->getAuthGUID() );
		//now here, let's see if the client has already contacted tower
		for( $x = 0; $x < $this->x_attempts; $x++ )
		{
			log_message('DEBUG','airtraffic::clearance '. $this->x_guid . ' for loop ' . $x . ' of ' . $this->x_attempts );
			$arrayized = $this->readAirTrafficDataFromXML( 10, $this->x_guid );
			switch( intval($arrayized[ 'status' ]) ){
				case STAT_CLIENTOK: 
					log_message('DEBUG','airtraffic::clearance granted ' . $this->x_guid );
					return TRUE;
				case STAT_CLIENT_FAILED:
					log_message('DEBUG','airtraffic::clearance failed ' . $this->x_guid . ' due to contact tower fault' );
					return FALSE;
				default: break;
			}
			sleep( $this->x_looptime );
		}
		log_message('DEBUG','airtraffic::clearance wait on tower expired' . $this->x_guid );
		$this->writeAirTrafficDataToXML( Array( $this->x_guid, STAT_SERVER_WAIT_ON_CLIENT_TIMEOUT, $auth_guid ) );
		return FALSE;
	}//clearance(..)

	function deleteExpired( $checkExistence = TRUE )
	{
		/**
		*	@created 19JUL2012-1856
		*	@description Deletes expired ATC data in DB and the associated XML files.
		*	@returns VOID
		**/
		// DELETE XML files
		$obj = $this->CI->atc_model->getExpired_UUIDs();
		// DELETE DB ENTRIES
		$this->CI->atc_model->deleteExpired();
		if( $obj !== FALSE ){
			foreach( $obj as $singleObj ){
				$this->deleteXML( $singleObj->UUID, $checkExistence );
				$this->deleteCustomFunctionsXML( $singleObj->UUID, $checkExistence );
			}
		}
	}//deleteExpired
	
	function deleteCustomFunctionsXML( $guid_sent = FALSE, $checkExistence = FALSE )
	{
		/**
		*	@created 20JUL2012-1907
		*	@description Deletes XML file.
		*	@param $guid_sent STRING Default (no param when called ) is BOOLEAN FALSE. If so,
						the XML to be deleted is the one with the GUID declared in this class.
		*	@returns VOID.
		**/
		$filename = $this->CI->makexml_model->getAirTrafficCustomFuncRelPath(
			( $guid_sent === FALSE ) ? $this->x_guid : $guid_sent
		);
		log_message('DEBUG','airtraffic::deleteCustomFunctionsXML accessed, to delete : ' . $filename );
		if( $checkExistence ) if( !file_exists( $filename ) ) return FALSE;
		return unlink( $filename );
	}//deleteCustomFunctionsXML

	function deleteXML( $guid_sent = FALSE, $checkExistence = FALSE )
	{
		/**
		*	@created 14JUL2012-1736
		*	@description Deletes XML file.
		*	@param $guid_sent STRING Default (no param when called ) is BOOLEAN FALSE. If so,
						the XML to be deleted is the one with the GUID declared in this class.
		*	@returns VOID.
		**/
		$filename = $this->CI->makexml_model->getAirTrafficRelPath( 
				( $guid_sent === FALSE ) ? $this->x_guid : $guid_sent
			);
		if( $checkExistence ) if( !file_exists( $filename ) ) return FALSE;
		return unlink( $filename );
	}//deleteXML

	function getGUID()
	{
		/**
		*	@created 20JUL2012-1235
		*	@description Gets the GUID of the airtraffic session.
		*	@remarks $this->initialize() should have been called by the caller of this before this!
		**/
		return $this->x_guid;
	}//getGUID()
	
	function initialize(
		$after_sessname, $after_sesstage, $go_onsuccess, 
		$call_func_on_success, $attempt, $looptime, $additional_calls = FALSE
	){
		/**
		*	@created 09JUL2012-1244
		*	@description Initializes air traffic activities. Well, what really happens is this creates a data holder
				in DB regarding air traffic. This too contains the URI to redirect on success of the calling function,
				as well as the activity name and stage needed to be set in the CI session cookie.
		*	@param $additional_calls 
				-	BOOLEAN FALSE , if not specified
				-   ARRAY OF STRINGS - STRINGS that are executable by PHP's eval() function.
		*	@remarks Currently, caller should be a script that responds to an ajax call.
		*	@returns BOOLEAN. But if FALSE, also echoes <ajaxresponse>.
		**/
		$this->x_guid     = $this->CI->usefulfunctions_model->guid();
		$this->x_sessAct  = $this->CI->clientsidedata_model->getSessionActivity();
		$this->x_attempts = $attempt;
		$this->x_looptime = $looptime;
		
		log_message('DEBUG','airtraffic::initialize called ' . $this->x_guid );
		if(     ($additional_calls === FALSE) ? TRUE : $this->writeCustomCallsToXML( $this->x_guid, $additional_calls )
			and
				$this->CI->atc_model->create(
					$this->x_guid, 
					$this->x_sessAct[0],
					$this->x_sessAct[1],
					is_null($after_sessname) ? $this->x_sessAct[0] : $after_sessname,
					$after_sesstage,
					$go_onsuccess,
					$call_func_on_success,
					$attempt,
					$looptime,
					$this->x_is_customfunc_set
				)
			and
				$this->writeAirTrafficDataToXML( Array( $this->x_guid, STAT_CREATED, '' ) )
		)
		{
			$this->CI->clientsidedata_model->set_ATC_Guid( $this->x_guid );
			if( $this->CI->clientsidedata_model->get_ATC_Guid() === FALSE )
			{	// actually, the chance of this happening is very miniscule.
				log_message('DEBUG','airtraffic::initialize failed on_set_atc_guid');
				$this->terminateService( TRUE );
				echo $this->CI->makexml_model->XMLize_AJAX_Response( 
					"error", "error", "ATC_INIT_FAIL_2", 0, "Failed to set authentication GUID", "" 
				);
				return FALSE;
			}
			return TRUE;
		}else{
			log_message('DEBUG','airtraffic::initialize failed general');
			$this->terminateService( TRUE, TRUE );
			echo $this->CI->makexml_model->XMLize_AJAX_Response( 
				"error", "error", "ATC_INIT_FAIL_1", 0, "Failed to create air traffic data.", ""
			);
			return FALSE;
		}
	}//initialize(..)
	
	function readAirTrafficDataFromXML( $wait_for_exist_timeout = 10, $guid_sent = FALSE )
	{
		/**
		*	@created 09JUL2012-2103
		*	@param $guid_sent STRING Default is BOOLEAN FALSE. If so,
						the XML to be deleted is the one with the GUID declared in this class.
		**/
		log_message('DEBUG','airtraffic::readAirTrafficDataFromXML accessed');
		$filename = $this->CI->makexml_model->getAirTrafficRelPath(
						( $guid_sent === FALSE ) ? $this->x_guid : $guid_sent
					);
		// wait for file to be written since we have to consider delays
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
	
	function readCustomCallsFromXML( $guid )
	{
		/**
		*	@created 09JUL2012-1952
		**/
		$filename = $this->CI->makexml_model->getAirTrafficCustomFuncRelPath( $guid );
		if( !file_exists( $filename ) ) return FALSE;
		return ( new SimpleXMLElement( $this->CI->makexml_model->readXML( $filename ) ) );
	}
	
	function terminateService( $callDelXML = FALSE, $checkXMLExistence = FALSE )
	{
		/**
		*	@created 09JUL2012-1246
		*	@description Deletes all ATC transactions/DB entry/CI session data, as
				well as external file(s).
		*	@remarks $this->initialize() should have been called by the caller of this before this!
		*	@param @callDelXML BOOLEAN Whether to call the function that deletes the XML file used.
		*	@returns BOOLEAN.
		**/
		log_message('DEBUG','airtraffic::terminate called ' .  $this->x_guid );
		$call_del_xml_res = TRUE;
		if( $callDelXML ){
			$call_del_xml_res = $this->deleteXML( $this->x_guid, $checkXMLExistence );
		}
		log_message('DEBUG','airtraffic::deleteCustomFunctionsXML ' . ( $this->x_is_customfunc_set ? '' : 'does not' )  .' need to be called' );
		if( $this->x_is_customfunc_set ){
			$call_del_xml_res =  $call_del_xml_res and 
				$this->deleteCustomFunctionsXML( $this->x_guid, $checkXMLExistence );
		}
		return ( $this->CI->atc_model->delete( $this->x_guid ) and $call_del_xml_res );
	}
	
	function writeAirTrafficDataToXML( $arrayized )
	{
		/**
		*	@created 09JUL2012-2103
		*	@returns STRING
		**/
		return $this->CI->makexml_model->XMLize_AirTrafficData( $arrayized );
	}
	
	function writeCustomCallsToXML( $guid, $data )
	{
		/**
		*	@created 20JUL2012-1400
		*	@description Writes custom statements to be called by sessctrl/contact_tower during Air Traffic Control transactions.
		**/
		$result = $this->CI->makexml_model->XMLize_AirTrafficCustomFuncData( $guid, $data );
		if( $result ) $this->x_is_customfunc_set = TRUE;
		return $result;
	}

}