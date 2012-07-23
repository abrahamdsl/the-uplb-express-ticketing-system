<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Air Traffic Controller (ATC) Model
* 	Created late 09JUN2012-1201
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Introducing ATC model!
*	Name's inspiration comes from aviation - you know, you need to have clearances and ACKs
		when flying. :D
*   And I don't think it won't be amiss since most Internet connections traverse the air
		some time, some where. :D
*	This handles low-level things regarding our ATC architecture.
*/

class atc_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
		$this->load->model('usefulfunctions_model');

		include_once( APPPATH.'constants/atc.inc' );
		date_default_timezone_set('Asia/Manila');
	}
	
	private function updateUnified( $uuid, $entryArray )
	{
		/**
		*	@created 09JUL2012-1155
		*	@param $uuid The UUID which is the primary key of the DB entry.
		*   @param $entryArray Associative array, containing the columns to be updated. The key
					is the column name and the value is the new value to be written to the DB.
		*	@description Updates the specified column(s) of an entry depending
				on $entryArray's contents.
			@remarks Copied from ndx_model::updateUnified
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/	
		$this->db->where( COL_UUID, $uuid );
		return $this->db->update( COL_DB_TABLE_NAME_ATC, $entryArray );
	}//updateUnified(..)
	
	function create( 
		$uuid, $detail1, $detail2 = NULL, $detail3 = NULL, $detail4 = NULL, $detail5 = "", 
		$onSuccess = NULL, $attempt = 3, $looptime = 10, $is_there_custom_func = FALSE, $expire_plus_x = NULL )
	{
		/**
		*	@created 09JUl2012-1204
		*	@description Well, obviously, creates an entry in the DB.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/		
		$expire_plus = is_null( $expire_plus_x ) ? 5 : $expire_plus_x;
		$secs_to_expire = ($attempt * $looptime) + $expire_plus;
		
		$curr_time = date( 'Y-m-d H:i:s');
		$expiry = strtotime( '+'.$secs_to_expire.'sec ', strtotime( $curr_time ) );
		$data = Array(
			COL_CDATE => date( 'Y-m-d' ),
			COL_CTIME => date( 'H:i:s' ),
			COL_UUID  => $uuid,
			COL_D1    => $detail1,
			COL_D2    => $detail2,
			COL_D3    => $detail3,
			COL_D4    => $detail4,
			COL_D5    => $detail5,
			COL_AT    => $attempt,
			COL_LT    => $looptime,
			COL_IS_CUSTOM => ($is_there_custom_func) ? 1 : 0,
			COL_ON_SUCCESS    	  => $onSuccess,
			COL_EXPIRE_DATE		  => date( 'Y-m-d', $expiry ),
			COL_EXPIRE_TIME		  => date('H:i:s', $expiry )
		);
		return $this->db->insert( COL_DB_TABLE_NAME_ATC , $data );
	}//create(..)
	
	function delete( $uuid )
	{
		/**
		*	@created 09JUL2012-1204
		*	@description Well, obviously, deletes.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		if( is_null( $uuid ) or $uuid === FALSE ) return TRUE;
		return $this->db->delete( COL_DB_TABLE_NAME_ATC, Array( COL_UUID => $uuid ) );
	}//delete(..)

	function deleteExpired(){
		/**
		*	@created 09JUL2012-1205
		*	@description Deletes any entry that is expired.
		**/
		return $this->db->query( 
			"DELETE FROM `" . COL_DB_TABLE_NAME_ATC . "`" . $this->usefulfunctions_model->getGeneralWhereExpiredClause()
		);
	}//deleteExpired
	
	function getExpired_UUIDs(){
		/**
		*	@created 19JUL2012-1855
		*	@description Gets the UUIDs of the expired ATC entries.
		**/
		$sql_command = "SELECT `UUID` FROM `" . COL_DB_TABLE_NAME_ATC . "`" . $this->usefulfunctions_model->getGeneralWhereExpiredClause();
		$arr_result = $this->db->query( $sql_command )->result();
		if( count( $arr_result ) < 1 ) return FALSE;
		return $arr_result;
	}
	
	function get( $uuid )
	{
	
		/**
		*	@created 09JUL2012-1206
		*	@description Well, obviously, gets a record/entry from DB.
		*	@returns 
				- BOOLEAN FALSE : If specified record not found
				- MYSQL OBJ     : Of the entry being requested if found.
		**/
		if( $uuid === false or strlen( $uuid ) < 1 ) return FALSE;
		$obj_result = $this->db->get_where( COL_DB_TABLE_NAME_ATC, Array( COL_UUID => $uuid ), 100000, 0 );
		$arr_result = $obj_result->result();
		
		if( count( $arr_result ) !== 1 ) return FALSE;
		return $arr_result[0];
	}//get(..)

	function updateCallOnSuccessFunction( $uuid, $newVal )
	{
		/**
		*	@created 16JUL2012-1339
		*	@description Updates the Detail1 column.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_ON_SUCCESS => $newVal ) );
	}
	
	function updateDetail1( $uuid, $newVal )
	{
		/**
		*	@created 09JUL2012-1208
		*	@description Updates the Detail1 column.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_D1 => $newVal ) );
	}
	
	function updateDetail2( $uuid, $newVal )
	{
		/**
		*	@created 09JUL2012-1208
		*	@description Updates the Detail2 column.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_D2 => $newVal ) );
	}
	
	function updateDetail3( $uuid, $newVal )
	{
		/**
		*	@created 09JUL2012-1208
		*	@description Updates the Detail3 column.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_D3 => $newVal ) );
	}
	
	function updateExpiryDate( $uuid, $newVal )
	{
		/**
		*	@created 09JUL2012-1211
		*	@description Updates the expiry date of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_DATE => $newVal ) );
	}//updateExpiryDate()
	
	function updateExpiryTime( $uuid, $newVal )
	{
		/**
		*	@created 09JUL2012-1211
		*	@description Updates the expiry time of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_TIME => $newVal ) );
	}//updateExpiryTime()

}