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

class Telemetry_model extends CI_Model {

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
			default: "NULL";
		}
	}
	
	
	function add( $mode, $uuid, $_client_iPv4, $_client_user_agent, $client_browserShort_and_OS, $date_sent = false, $time_sent = false )
	{
		date_default_timezone_set('Asia/Manila');
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