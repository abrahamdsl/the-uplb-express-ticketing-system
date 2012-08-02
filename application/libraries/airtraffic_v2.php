<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
*	Air Traffic Control Version 2 library
* 	Created 23JUL2012-1855
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	All functions that calls this should respond to an AJAX request (and handled
     by our respective JavaScript file.
*/

class Airtraffic_v2{
	var $CI;
	var $activityGuid;
	var $currentSession;
	var $nextSession = Array( NULL, NULL );
	
	function __construct()
    {
		$this->CI = & get_instance();
		$this->CI->load->model('atc_model');
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('sat_model');
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('usefulfunctions_model');

		include_once( APPPATH.'constants/_constants.inc');
	}
	
	function getGUID()
	{
		return $this->activityGuid;
	}
	
	function initialize( $stagePR, $nextStage, $nextName = NULL )
	{
		/**
		*	@created 23JUL2012-1913
		*	@description initializes transaction. sets PR session activity and
				the target session activity if the calling function is a success, 
				and makes database checkpoint.
		*/
		/*
			Get session activity name and stage, as well as activity GUID.
		*/
		$this->currentSession = $this->CI->clientsidedata_model->getSessionActivity();
		$this->activityGuid   = $this->CI->clientsidedata_model->getActivityGUID();
		// Update the session tracker in the database with the -PR stage of the caller.
		$this->CI->sat_model->update( $this->activityGuid, $stagePR, $nextName );
		// if next activity name is not null, assign
		if( !is_null( $nextName ) ) $this->nextSession[0] = $nextName;
		// assign next activity stage
		$this->nextSession[1] = $nextStage;
		// database checkpoint
		$this->CI->db->trans_begin();
	}
	
	function rollback()
	{
		/**
		*	@created 23JUL2012-1913
		*	@description rolls nack DB transaction and session activity data in CI session cookie.
		*/
		// if former activity name is not defined, just rollback session tracking activity stage
		log_message('debug', 'library airtraffic_v2::rollback accessed');
		// database rollback first, since we have to rollback data in table `_sess_act`
		$this->CI->db->trans_rollback();
		if( is_null( $this->nextSession[0] ) ){
			$this->CI->sat_model->update( $this->activityGuid, $this->currentSession[1] );
		}else{
		// else, include the name
			$this->CI->sat_model->update( $this->activityGuid, $this->currentSession[1], $this->currentSession[0] );
		}
	}
	
	function commit()
	{
		/**
		*	@created 23JUL2012-1913
		*	@description commits transaction
		*/
		/*  
			Is next session activity name not defined? Then only update the activity stage
			in CI session cookie and session tracking in DB ( yah, it's there in 
			clientsidedata_model::updateSessionActivityStage )
		*/
		if( is_null( $this->nextSession[0] ) ){
			$this->CI->clientsidedata_model->updateSessionActivityStage( $this->nextSession[1] );
		}else{
		// else, set a new session activity
			$this->CI->clientsidedata_model->setSessionActivity( $this->nextSession[0], $this->nextSession[1] );
		}
		// database commit
		$this->CI->db->trans_commit();
	}
	
	function clearance()
	{
		/**
		*	@created 23JUL2012-1913
		*	@description returns clearance
		*/
		return ( (connection_status () == CONNECTION_NORMAL ) AND $this->CI->db->trans_status() );
	}
}