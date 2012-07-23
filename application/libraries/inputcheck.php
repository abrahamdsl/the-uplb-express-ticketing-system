<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Input Check Library 
* Created 22JUL2012-1931
* Part of "The UPLB Express Ticketing System"
* Special Problem of Abraham Darius Llave / 2008-37120
* In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
* University of the Philippines Los Banos
* ------------------------------
*
* Contains form validation checks that cannot be handled by CI's form validation class.
* There is some complication in extending the form validation class os just made a new one though.
* It is assumed that the form validation library is already loaded in
	the controller where this is called.
 */

class Inputcheck
{
	var $CI;
	
	function __construct()
    {
        log_message('debug', '*** Hello from MY_Form_validation ***');
		$this->CI = & get_instance();
    }
	
	private function unifiedBasicStringCheck( $value_sent, $allowedChars, $minlength, $maxlength = FALSE )
	{
		// passing here, the field is required
		if( $value_sent === FALSE ){
			return FALSE;
		}
		$field = trim( strtolower( $value_sent ) );
		$y = strlen( $field );
		// min len
		if( $y < $minlength ){
			return FALSE;
		}
		// max len, if supplied
		if( $maxlength !== FALSE ) {
			if( $y > $maxlength ){
				return FALSE;
			}
		}
		// check char-by-char if allowed
		for( $x = 0 ; $x < $y ; $x++ ){
			if( strpos( $allowedChars, $field[$x] ) === FALSE ){
				return FALSE;
			}
		}
		return TRUE;
	}
	
	private function is_seatMapName_valid( $str )
	{
		return $this->unifiedBasicStringCheck( $str, " abcdefghijklmnopqrstuvwxyz1234567890_.-", 1 );
	}
	
	private function is_username_valid( $str )
	{
		return $this->unifiedBasicStringCheck( $str, "abcdefghijklmnopqrstuvwxyz1234567890_.", 5 );
	}//is_username_valid

	/* END OF PRIVATE FUNCTIONS */
	
	function seatctrl__create_step2()
	{
		return (
			$this->is_seatMapName_valid( $this->CI->input->post('name') )
				and
			// for the rows and cols, checking it via CI's form validation is much more simplistic
			// rules at application/config/form_validation.php
			$this->CI->form_validation->run()
		);
	}

	function sessionctrl__login()
	{
		return (
			$this->is_username_valid( $this->CI->input->post('username') )
				and
			// for password, checking it via CI's form validation is much more simplistic
			// rules at application/config/form_validation.php
			$this->CI->form_validation->run()
		);
	}

}