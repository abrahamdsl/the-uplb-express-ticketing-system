<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Useful Functions Model
* 	Created15DEC2011-1546
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains a lot of utility functions.
**/


class UsefulFunctions_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function startsWith_mine( $findInThis, $findThis )
	{
		return true;
	}
	
	function startsWith($Haystack, $Needle)
	{
		// Recommended version, using strpos
		return strpos($Haystack, $Needle) === 0;
	}
	
	function getRealIpAddr()
	{
		/*
			Gets the CLIENT's IP address.
			From: http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
		*/
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	function getValueOfWIN5_Data( $needle, $haystack )
	{
		/*
			Created 10MAR2012-1100
					
			Returns the value requested if found, else BOOLEAN FALSE.
		*/
		$tokenizedHaystack;
		$muchTokenizedHaystack = Array();
		$muchTokenizedHaystack = $this->makeAssociativeArrayThisWIN5_DATA( $haystack );
		/*
			Now compare values.
		*/
		foreach( $muchTokenizedHaystack as $key => $value ) if( $key == $needle ) return $value;
		return false;
	}//getValueOfWIN5_Data
	
	function makeAssociativeArrayThisWIN5_DATA( $haystack )
	{
		/*
			Warning: the keys are always in string
		*/
		$haystackLength = strlen($haystack);
		if( $haystack[ $haystackLength - 1 ] === ';' ) 
			$haystack = substr( $haystack, 0, $haystackLength-1 );
		$tokenizedHaystack = explode(';', $haystack );		
		$muchTokenizedHaystack = Array();
		/*
			Separate via equals sign
		*/
		foreach( $tokenizedHaystack as $eachEntry )
		{
			$letsDivorce = explode('=', $eachEntry );
			$muchTokenizedHaystack[ strval($letsDivorce[0]) ] = $letsDivorce[1];
		}
		return $muchTokenizedHaystack;
	}
	
	function removeWIN5_Data( $needle, $haystack )
	{
		$tokenizedHaystack;
		$muchTokenizedHaystack = Array();		
		$exclude = NULL;
		$newdata = "";
		
		
		$muchTokenizedHaystack = $this->makeAssociativeArrayThisWIN5_DATA( $haystack );
		echo 'wah';
		echo var_dump( $muchTokenizedHaystack );
		/*
			Now compare values.
		*/
		foreach( $muchTokenizedHaystack as $key => $value ) if( $key == $needle ) $exclude = $key;
		
		// now, rewrite
		foreach( $muchTokenizedHaystack as $key => $value ) if( $key !== $exclude ) $newdata .= ( $key."=".$value.";" );
		return $newdata;
	}
	
	function guid( ){
		/*
		Retrieved 21MAY2012-1515
		http://php.net/manual/en/function.com-create-guid.php
		Comment by: Kristof_Polleunis at yahoo dot com 28-Apr-2005 08:16
		
		*/
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
					.substr($charid, 0, 8).$hyphen
					.substr($charid, 8, 4).$hyphen
					.substr($charid,12, 4).$hyphen
					.substr($charid,16, 4).$hyphen
					.substr($charid,20,12)
					.chr(125);// "}"
			return $uuid;			
		}
	}//guid()
	
	function isHourValid_24( $hour )
	{
		$thisHour = intval( $hour, 10 );		
		if( $thisHour > 23 ) return false;
		
		return true;
	}
	
	function isInternalDataTypeValid( $text )
	{
		$validTypes = Array( 'WIN5', 'XML' );
		return in_array( strtoupper( $text), $validTypes, true );
	}
	
	function isMinuteValid( $minute )
	{		
		$thisMinute = intval( $minute, 10 );			
		if( $thisMinute > 59 ) return false;
		
		return true;
	}

	function isPaymentModeTypeValid( $text )
	{
		$validTypes = Array( 'COD', 'ONLINE', 'OTHER' );
		return in_array( strtoupper( $text), $validTypes, true );
	}
	
	function isSecondValid( $seconds )
	{
		$thisSeconds = intval( $seconds, 10 );		
		if( $thisSeconds > 59 ) return false;
		
		return true;
	}
	
	function isTimeValid( $time1 )
	{
		// Adapted from JavaScript (generalChecks.js) created 27MAR2012
		/*
			only accepts time in HH:MM, or HH:MM:SS format in 24 hour format		
		*/
		$timeLength = strlen(time1);
		$splitted;	
		switch( $timeLength )
		{
			case 5:		
						$splitted = explode(':', time1);
						if( count( $splitted ) != 2 ) return false;
						if( !$this->isHourValid_24( $splitted[0] ) ) return false;
						if( !$this->isMinuteValid( $splitted[1] ) ) return false;
						return true;
						break;
			case 7:
			case 8:
						$splitted = explode(':', time1);
						if( count( $splitted ) != 3 ) return false;
						if( !$this->isHourValid_24( $splitted[0] ) ) return false;
						if( !$this->isMinuteValid( $splitted[1] ) ) return false;
						if( !$this->isSecondValid( $splitted[2] ) ) return false;
						return true;
						break;
			default:  	return false;
		}		
	}//isTimeValid
	
	function makeIPN_string_to_Array( $_IPN_in_string )
	{
		/*
			22MAR2012-2310 with dang: Param format:
			
			{ <key><equal><value><semicolon> }*
			
			example:
			charset=windows-1252;payment_status=PENDING;pending_status=multicurency;
		*/
		$returnThis = Array();
		$stringLength = strlen( $_IPN_in_string );
		// if string passed ends with semi-colon, then remove
		if( $_IPN_in_string[ $stringLength-1 ] == ';' )
		{
			$_IPN_in_string = substr( $_IPN_in_string, 0, $stringLength - 1 );	
		}		
		$firstExplode = explode(';', $_IPN_in_string);		
		foreach( $firstExplode as $val )
		{			
			$secondExplode = explode('=', $val );
			$returnThis[ $secondExplode[0] ] = $secondExplode[1];
		}
		return $returnThis;
	}// makeIPN_string_to_Array(..)
	
	function outputShowingTime_SimpleOneLine( $startDate, $startTime, $endDate, $endTime, $newLine = false )
	{
			//27MAR2012-0427
			$returnThis = "";
			
			$returnThis = date( 'Y-M-d l', strtotime($startDate));
			/*
				No need to show seconds if zero
			*/
			if( $newLine ) $returnThis .= '<br/>';
			else $returnThis .= '&nbsp;';
			$splitted = explode(':', $startTime);
			$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';
			if( $newLine ) $returnThis .= '<br/>';
			$returnThis .= date( $timeFormat." A", strtotime($startTime)); 
			if( $startDate != $endDate ){ $returnThis .= "<br/>"; }
			$returnThis .= "<br/>to &nbsp;";			
			if( $newLine ) $returnThis .= '<br/>';
			if( $startDate != $endDate ){ 			
				// if show ends past midnight (red eye), then display the next day's date.
				if( $newLine ) $returnThis .= '<br/>';
				$returnThis .= date( 'Y-M-d l', strtotime($endDate));
				if( $newLine ) $returnThis .= '<br/><br/>';				
			}
			/*
				No need to show seconds if zero
			*/
			$splitted = explode(':',  $endTime );
			$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
			$returnThis .= date( $timeFormat." A", strtotime( $endTime ));
						
			return $returnThis;			
	}//outputShowingTime_SimpleOneLine(..)
	
	 function VisitorIP()
    { 
		/*
			From http://wiki.jumba.com.au/wiki/PHP_Get_user_IP_Address
		*/
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
		else $TheIp=$_SERVER['REMOTE_ADDR'];
	 
		return trim($TheIp);
    }
	
}//class
?>