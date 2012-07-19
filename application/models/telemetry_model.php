<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  Telemetry model
 *  Created 21MAY2012-132
 *	Part of "The UPLB Express Ticketing System"
 *  Special Problem of Abraham Darius Llave / 2008-37120
 *	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
 *	University of the Philippines Los Banos
 *	------------------------------
 *
 *	Various functions relating monitoring of the use of the web app for
 *  improvement and debugging purposes.
 *
**/

class telemetry_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}
	
	private function getEvent( $ID )
	{
		switch( $ID )
		{
			case 1: return "LOGIN_PAGE";
			case 2: return "LOGGED_IN";
			case 3: return "LOGGED_OUT";
			case 4: return "LOGGED_OUT_PAGE"; break;
			/* 
				25MAY2012-1240: Tentatively, these browser events are the same as those found
				in _constants.inc
			*/
			case 0x81: return "BROWSER_STRICTLY_NOT_ALLOWED"; break;
			case 0x82: return "BROWSER_UNKNOWN_BUT_PERMIT_STILL"; break;
			case 0x83: return "BROWSER_UNKNOWN_AND_DENY"; break;
			case 0x84: return "BROWSER_NOT_TESTED_BUT_PERMIT_STILL"; break;
			case 0x85: return "BROWSER_NOT_TESTED_AND_DENY"; break;
			case 0x86: return "BROWSER_BOT_SIMPLE"; break;
			case 0x87: return "BROWSER_AGENT_DENIED"; break;
			default: "NULL";
		}
	}
	
	
	function add( $mode, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS, $date_sent = false, $time_sent = false )
	{
		log_message("DEBUG" , "telemetry add ". $uuid . " server time native : " . date("Y-m-d H:i:s") );
		date_default_timezone_set('Asia/Manila');
		log_message("DEBUG" , "telemetry add ". $uuid . " server time after setting to Manila: " . date("Y-m-d H:i:s") );
		$date_x = ($date_sent === false ) ? date("Y-m-d") : $date_sent;
		$time_x = ($time_sent === false ) ? date("H:i") : $time_sent;
		$data = Array(		
			'UUID' => $uuid,
			'RecDATE' => $date_x,
			'RecTIME' => $time_x,
			'IPV4_ADDRESS' => $_client_iPv4,
			'EVENT' => $this->getEvent( $mode ),
			'USER_AGENT' => $_client_user_agent,
			'BROWSERSHORT_PLUS_OS' => $client_browserShort_and_OS
		);
		$this->db->insert( '_telemetry_basic', $data);
	}

}