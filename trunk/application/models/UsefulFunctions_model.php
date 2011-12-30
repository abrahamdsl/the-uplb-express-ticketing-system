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
}//class
?>