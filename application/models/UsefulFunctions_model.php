<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 15DEC2011-1546
*/


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
		$haystackLength = strlen($haystack);
		if( $haystack[ $haystackLength - 1 ] === ';' ) 
			$haystack = substr( $haystack, 0, $haystackLength-1 );
			
		$tokenizedHaystack = explode(';', $haystack );		
		/*
			Separate via equals sign
		*/
		foreach( $tokenizedHaystack as $eachEntry )
		{
			$letsDivorce = explode('=', $eachEntry );
			$muchTokenizedHaystack[ $letsDivorce[0] ] = $letsDivorce[1];
		}
		/*
			Now compare values.
		*/
		foreach( $muchTokenizedHaystack as $key => $value ) if( $key == $needle ) return $value;
		return false;
	}//getValueOfWIN5_Data
		
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