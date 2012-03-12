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
}//class
?>