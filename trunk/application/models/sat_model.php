<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Session Activity Tracking Model
* 	Created late 09JUN2012-1201
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Tasked with modifying DB records regarding an activity, i.e., 
*   if the stage is not yet passed really. This is necessary because 
*	there when session activity CI session data is updated, in server, transmission back
*   to client might be interrupted thus the cookie will be not updated. And client upon
*   resubmitting, oh, will just perform the same. Now, we need an entry in the DB regarding that.
*	We will be banking on the assumption/hope that in case returning of headers that contains
* 	CI session cookie to the client failed, oru fallback DB is reliable.
*/

class sat_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
		date_default_timezone_set('Asia/Manila');
		include_once( APPPATH.'constants/sat.inc' );
	}
	
	private function updateUnified( $uuid, $entryArray )
	{
		/**
		*	@created 09JUN2012-1248
		*	@param $uuid The UUID which is the primary key of the DB entry.
		*   @param $entryArray Associative array, containing the columns to be updated. The key
					is the column name and the value is the new value to be written to the DB.
		*	@description Updates the specified column(s) of an entry depending
				on $entryArray's contents.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/	
		$this->db->where( COL_UUID, $uuid );
		return $this->db->update( COL_DB_TABLE_NAME_SAT, $entryArray );
	}//updateUnified(..)
	
	function create( $uuid, $name, $stage, $expiry_str = '+30 min ' )
	{
		/**
		*	@created 13JUL2012-1706
		*	@description Well, obviously, creates an entry in the DB.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/		
		$expiry = strtotime( $expiry_str, strtotime( date( 'Y-m-d H:i:s') ) );
		$data = Array(			
			COL_UUID              => $uuid,
			COL_START_DATE		  => date( 'Y-m-d' ),
			COL_START_TIME		  => date('H:i:s'),
			COL_CONTACT_DATE      => date( 'Y-m-d' ),
			COL_CONTACT_TIME	  => date('H:i:s'),
			COL_ACT_NAME          => $name,
			COL_ACT_STAGE	      => $stage,
			COL_EXPIRE_DATE		  => date( 'Y-m-d', $expiry ),
			COL_EXPIRE_TIME		  => date('H:i:s', $expiry )	
		);		
		return $this->db->insert( COL_DB_TABLE_NAME_SAT , $data );
	}//create(..)
	
	function delete( $uuid )
	{
		/**
		*	@created 13JUl2012-1713
		*	@description Well, obviously, deletes.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		if( is_null( $uuid ) or $uuid === FALSE ) return TRUE;
		return $this->db->delete( COL_DB_TABLE_NAME_SAT, Array( COL_UUID => $uuid ) );
	}//delete(..)
	
	function deleteExpiredSAT(){
		/**
		*	@created 13JUl2012-1713
		*	@description Deletes any booking cookies-on-server that is expired.
		**/
		
		$sql_command = "DELETE FROM `" . COL_DB_TABLE_NAME_SAT . "` WHERE CONCAT(`";
		$sql_command .= COL_EXPIRE_DATE . "`,' ',`".COL_EXPIRE_TIME . "`) <= ? ";
		return $this->db->query( $sql_command, Array( date("Y-m-d H:i:s"),  ) );
	}//deleteExpiredSAT()
	
	function get( $uuid )
	{
		/**
		*	@created 13JUl2012-1713
		*	@description Well, obviously, gets a record/entry from DB.
		*	@returns 
				- BOOLEAN FALSE : If specified record not found
				- MYSQL OBJ     : Of the entry being requested if found.
		**/
		if( $uuid === false ) return FALSE;
		$obj_result = $this->db->get_where( COL_DB_TABLE_NAME_SAT, Array( COL_UUID => $uuid ), 100000, 0 );
		$arr_result = $obj_result->result();
		
		if( count( $arr_result ) !== 1 ) return FALSE;
		return $arr_result[0];
	}//get(..)
	
	function update( $uuid, $actStage = NULL, $actName_x = NULL )
	{
		/**
		*	@created 13JUL2012-1716
		*	@description Updates the expiry date of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/
		$col_arr = Array();
		if( $actName_x !== NULL ){
			$col_arr[ COL_ACT_NAME ] = $actName_x;
		}
		if( $actStage !== NULL ){
			$col_arr[ COL_ACT_STAGE ] = $actStage;
		}		
		$col_arr[ COL_CONTACT_DATE ] = date("Y-m-d");
		$col_arr[ COL_CONTACT_TIME ] = date("H:i:s");
		return $this->updateUnified( $uuid, $col_arr );
	}//update()
	
	function updateExpiryDateAndTime( $uuid, $newDate_x = NULL, $newTime_x = NULL )
	{
		/**
		*	@created 13JUL2012-1716
		*	@description Updates the expiry date of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/
		$newDate = $newDate_x === NULL ? date("Y-m-d") :$newDate_x ;
		$newTime = $newTime_x === NULL ? date("H:i:s") :$newTime_x ;
		return $this->updateUnified(
			$uuid, 
			Array( COL_EXPIRE_DATE => $newDate, COL_EXPIRE_TIME => $newTime ) 
		);
	}//updateExpiryDate()
	
	function isOnDB_RecordAdvanced( $guid, $sessionActivityStage )
	{
		/**
		*	@created 13JUL2012-1700
		*	@description Checks if the client session activity stage in the DB is greater than
				the indicator found in CI session cookie.
		**/
		$obj = $this->get( $guid );
		if( $obj === FALSE ){
			// 13JUL2012-1733: Is this likely to happen?
			return Array ( FALSE, -1, "No object found" );
		}
		return Array(
			( intval( $obj->ACTIVITY_STAGE ) > $sessionActivityStage ),
			0,
			"success",
			$obj->ACTIVITY_NAME,
			intval( $obj->ACTIVITY_STAGE )
		);
	}
}