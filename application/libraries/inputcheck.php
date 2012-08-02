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
		$this->CI = & get_instance();
		define( 'SPECIALS_NAME_AND_EMAIL', ".-_" );
    }
	
	private function checkSpecialSymAbnormality( $str )
	{
		/**
		*	@created 28JUL2012-1118
		*/
		// checks if the first or last char is not one of the special symbols
		if( strpos( SPECIALS_NAME_AND_EMAIL, $str[0]) !== FALSE ) return FALSE;
		$sp_len = strlen( SPECIALS_NAME_AND_EMAIL );
		if( strrpos( SPECIALS_NAME_AND_EMAIL, $str[strlen($str)-1]) !== FALSE ) return FALSE;
		$specials = SPECIALS_NAME_AND_EMAIL;
		for( $a = 0; $a < $sp_len; $a++ )
		{
			for( $i=0; $i < $sp_len; $i++ )
			{
				//	checks for two succeeding special symbols
				if( strpos( $str, $specials[$a].$specials[$i] ) !== FALSE ) return FALSE;
			}
		}
		return TRUE;
	}
		
	private function unifiedBasicIntegerCheck( $value_sent, $minlength = NULL, $maxlength = NULL )
	{
		/**
		*	@created 25JUL2012-1111
		*/
		$allowedChars = "0123456789";
		// passing here, the field is required
		if( $value_sent === FALSE ){
			return FALSE;
		}
		$field = trim( strtolower( $value_sent ) );
		$y = strlen( $field );
		// min len, if supplied
		if( !is_null( $minlength ) ){
			if( $y < $minlength ){
				return FALSE;
			}
		}
		// max len, if supplied
		if( !is_null( $maxlength) ) {
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
	
	private function unifiedBasicStringCheck( &$value_sent, $allowedChars, $minlength, $maxlength = FALSE )
	{
		if( $minlength == -1 AND $value_sent == "" ) return NULL;
		// passing here, the field is required
		if( $value_sent === FALSE ) return FALSE;
		$value_sent = trim( $value_sent );
		$field = strtolower( $value_sent );
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
	
	function is_email_valid( &$str )
	{
		/**
		*	@created 26JUL2012-1156
		*	@remarks JavaScript counterpart : assets\javascript\form-validation\usersignup.js::isEmail_valid
		*	@revised 26JUL2012-2238
		*/
		$allowedChars = "abcdefghijklmnopqrstuvwxyz0123456789@" . SPECIALS_NAME_AND_EMAIL ;
		$atPos;
		$stopPos;
		$ch;
		$checkAT = 0;
		$IsEmail;
		$check;
		$specials = ".-_";

		$this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_EMAIL_ALL_FORM_FAIL );
		$check 		= $this->unifiedBasicStringCheck( $str, $allowedChars, 6 );
		if( !$check ) return FALSE;
		$email_len  = strlen ( $str );
		$atPos   	= strpos( $str, '@');
		$stopPos 	= strpos( $str, '.');
		
		// email address empty string?
		if( $str == "" ) return FALSE;
		// checks for @ and .
		if( $atPos === FALSE OR $stopPos === FALSE ) return FALSE;
		// checks if @ is used first before .
		if( $atPos == 0 ) return FALSE;
		// checks if . does not follow @ immediately
		if( ($stopPos - $atPos) == 1 ) return FALSE;
		//Check for more than 1 '@' character
		if( $atPos != strrpos( $str, '@' ) ) return FALSE;
		if( !$this->checkSpecialSymAbnormality( $str ) ) return FALSE;
		$this->CI->clientsidedata_model->deleteLastInternalError();
		return TRUE;
	}
	
	function is_empNum_valid( $enum_sent )
	{
		/**
		*	@created 25JUL2012-1124
		*	@remarks NO DASHES ALLOWED. Anyway, we don't much care about UP Emp num. Hehehe. Sorry.
				Someone knowledgeable correct this in the future.
		*/
		$check = $this->unifiedBasicIntegerCheck( trim($enum_sent), 10, 10 );
		if( !$check ) $this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_EMPNUM_FORM_FAIL );
		return $check;
	}
	
	function is_internal_data_type_valid( $text )
	{
		$validTypes = Array( 'WIN5', 'XML' );
		return in_array( strtoupper( $text), $validTypes, true );
	}
	
	function is_name_valid( &$str, $minLength )
	{
		/**
		*	@created 25JUL2012-1440
		*	@remarks Make sure always at sync with 
				.\assets\javascript\form-validation\usersignup.js::isName_valid
		*/
		$check = $this->unifiedBasicStringCheck( $str, "abcdefghijklmnopqrstuvwxyz .-", $minLength );
		// it is more economical to set this and remove if $str passes checks
		$this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_NAME_ALL_FORM_FAIL );
		// check returns NULL if empty string is allowed
		if( !is_null( $check ) ){
			if( !$check ) return FALSE;
			//now check for overuse of dots, hypens and spaces respectively
			$y = strlen( $str );
			$dot = strpos( $str, '.' );
			$hypen = strpos( $str, '-' );
			$space = strpos( $str, ' ' );
			//now check for inappropriate positioning of dots and hypens
			if( !$this->checkSpecialSymAbnormality( $str ) ) return FALSE;
			if( $dot !== FALSE AND 
				( ($dot - 1) >= 0 ) AND
				!ctype_alpha( $str[$dot-1] )
			){
				return FALSE;				// a letter should precede a dot
			}
			if( $y !== FALSE AND 
					( ($hypen - 1) >= 0 ) AND
					!ctype_alpha( $str[$hypen-1] )
			){
				return FALSE;				// a hypen should be placed between two letters
			}
		}
		$this->CI->clientsidedata_model->deleteLastInternalError();
		return TRUE;
	}
	
	function is_seatMapName_valid( &$str )
	{
		$check  = $this->unifiedBasicStringCheck( $str, " abcdefghijklmnopqrstuvwxyz1234567890_.-", 1 );
		if( !$check ) $this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_SEATNAME_FORM_FAIL );
		return $check;
	}
	
	function is_studentNum_valid( &$snum_sent )
	{
		/**
		*	@created 25JUL2012-1122
		*/
		$this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_STUDENTNUM_FORM_FAIL );
		$snum = trim( $snum_sent );
		if( strlen( $snum ) < 9 ) return FALSE;
		// check if with dash or not
		if( $snum[4] == '-' )
		{
			if( strlen( $snum ) != 10 ) return FALSE;
			$firstHalfCheck = $this->unifiedBasicIntegerCheck( substr( $snum, 0, 3), NULL );
			$secondHalfCheck = $this->unifiedBasicIntegerCheck( substr( $snum, 5), NULL );
			$result = $firstHalfCheck AND $secondHalfCheck;
			if( $result ) $this->CI->clientsidedata_model->deleteLastInternalError();
			return $result;
		}else{
			if( strlen( $snum ) != 9 ) return FALSE;
			$wholeCheck = $this->unifiedBasicIntegerCheck( $snum, NULL );
			if( $wholeCheck ) $this->CI->clientsidedata_model->deleteLastInternalError();
			return $wholeCheck;
		}
	}

	function is_payment_mode_id_valid( &$str, $editMode = FALSE )
	{
		$check = $this->unifiedBasicIntegerCheck( $str, 1 );
		//die( var_dump( $check ) );
		if( $check AND !$editMode ) return ( intval( $str ) > 1 ) ;			// coz 0 is reserved for 'FREE'
		return $check;
	}
	
	function is_payment_mode_name_valid( &$str )
	{
		return $this->unifiedBasicStringCheck( $str, 'abcdefghijklmnopqrstuvwxyz1234567890. -()*', 1 );
	}
	
	function is_payment_mode_type_valid( $text )
	{
		$validTypes = Array( 'COD', 'ONLINE', 'OTHER' );
		return in_array( strtoupper( $text), $validTypes, true );
	}
	
	
	function is_password_valid( &$str )
	{
		$check = $this->unifiedBasicStringCheck( $str, "abcdefghijklmnopqrstuvwxyz1234567890_.~-=[]{}|\\:;'\"<>,.?/", 8 );
		if( !$check ) $this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_PASSWORD_FORM_FAIL );
		return $check;
	}

	function is_phone_valid( &$str, $whatPhone )
	{
		/**
		*	@created 26JUL2012-1138
		*	@remarks JavaScript counterpart : .\assets\javascript\form-validation\usersignup.js::isPhone_valid
		*/
		$minLength;
		$placeIDD;
		$x = 0;
		$y = strlen( $str );
		
		$this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_PHONE_ALL_FORM_FAIL );
		switch( $whatPhone )
		{
			case "CELL" :	 $minLength = 10; break;
			case "LANDLINE": $minLength = 7; break;
			default:
				return FALSE;
		}
		$placeIDD =  strpos( $str, "+" );
		if( $placeIDD !== FALSE )
		{
			$x = 1;
			if ( $placeIDD > 0 )
			{
				return FALSE;		// "The '+' sign is only allowed at the beginning";
			}else
			if( $placeIDD == 0 )
			{
				switch( $whatPhone )
				{
					// since IDD, and the least IDD Access code is '1' for USA/Canada
					case "CELL" :	 $minLength++;    break;
					// same reasoning as above.
					case "LANDLINE": $minLength += 3; break;
					default: 		 return FALSE;
				}
			}
		}
		$check = $this->unifiedBasicIntegerCheck( substr( $str, $x ), $minLength );
		if( !$check ) return FALSE;
		$this->CI->clientsidedata_model->deleteLastInternalError();
		return TRUE;
	}
	
	function is_username_valid( &$str )
	{
		$check = $this->unifiedBasicStringCheck( $str, "abcdefghijklmnopqrstuvwxyz1234567890_.", 5 );
		if( !$check ) $this->CI->clientsidedata_model->setLastInternalError( INTERNAL_ERR_USERNAME_FORM_FAIL );
		return $check;
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
				AND
			// for password, checking it via CI's form validation is much more simplistic
			// rules at application/config/form_validation.php
			$this->CI->form_validation->run()
		);
	}

	function useracctctrl__addpaymentmode_step2( &$data )
	{
		// shared with useracctctrl/managepaymentmode_save
		return (
			isset( $data['mode'] ) ? ( intval( $data['mode'] ) > -1 ) : TRUE
			AND 
			isset( $data['uniqueID'] ) ? $this->is_payment_mode_id_valid( $data['uniqueID'] ) : TRUE
			AND
			$this->is_payment_mode_name_valid( $data['Name'] )
			AND
			$this->is_name_valid( $data['Contact_Person'], 2 )
			AND
			$this->is_phone_valid( $data[ 'Cellphone' ] , "CELL" )
			AND
			( $data[ 'Landline' ] == '' ) ? TRUE : $this->is_phone_valid( $data[ 'Landline' ] , "LANDLINE" )
			AND
			$this->is_email_valid( $data[ 'Email' ] )
			AND
			$this->is_payment_mode_type_valid( $data['Type'] )
			AND
			$this->is_internal_data_type_valid( $data['internal_data_type'] )
		);
	}
	
	function useracctctrl__isUserExisting2( $identifier )
	{
		return( $this->is_username_valid( $identifier )
				OR
				// for account number
				$this->unifiedBasicIntegerCheck( $identifier, 6, 6 )
		);
	}
	
	function useracctctrl__manageAccountSave( &$details, &$details_uplb )
	{
		/**
		*	@created 25JUL2012-1224
		*/
		$uplb_cons = TRUE;
		if($details_uplb !== FALSE ){
			// if both are NULL (meaning, the field was left blank), then this is FALSE.
		//  Back in view page, user should just disable the checkbox if he's not going to enter either one.
			$uplb_cons = !( is_null( $details_uplb[ 'studentNumber' ] ) AND  is_null( $details_uplb[ 'employeeNumber' ] ) );
			if( $uplb_cons )
			{
				$uplb_cons = (
					// the nullity of these two is influenced by the earlier call to usefulfunctions_model::extractUPLBConstDetailsFromPOST
					// from the controller where this is called.
					( is_null( $details_uplb[ 'studentNumber' ] ) ? TRUE : $this->is_studentNum_valid( $details_uplb[ 'studentNumber' ] ) )
					AND
					( is_null( $details_uplb[ 'employeeNumber' ] ) ? TRUE : $this->is_empNum_valid( $details_uplb[ 'employeeNumber' ] ) )
				);
			}
		}
		return (
			$this->is_username_valid( $details[ 'username' ] )
			AND
			$this->is_name_valid( $details[ 'Fname' ] , 2 )
			AND
			$this->is_name_valid( $details[ 'Mname' ] , -1 )
			AND
			$this->is_name_valid( $details[ 'Lname' ] , 2 )
			AND
			$this->is_phone_valid( $details[ 'Cellphone' ] , "CELL" )
			AND
			(  $details[ 'Landline' ]  == "" ) ? TRUE : $this->is_phone_valid(  $details[ 'Landline' ] , "LANDLINE" )
			AND
			$this->is_email_valid( $details[ 'Email' ] )
			AND
			$uplb_cons
		);
	}
	
	function useracctctrl__manageuser_editrole_save( &$data )
	{
		$result = TRUE;
		foreach( $data as $key => $value )
		{
			$result = $result AND $this->unifiedBasicStringCheck( $value, "10", 1, 1 );
		}
		return $result;
	}
}