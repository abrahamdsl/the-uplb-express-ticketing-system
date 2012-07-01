<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	New Data Access Model For MANAGE Booking Cookies
* 	Created late 14JUN2012-1201
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*   Sprouted from ndx_model
*
*/

class ndx_mb_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
		$this->load->model('ndx_model');
		include_once( APPPATH.'constants/_constants.inc' );
		include_once( APPPATH.'constants/ndx.php' );
	}
	
	private function updateUnified( $uuid, $entryArray )
	{
		/**
		*	@created 14JUN2012-1311
		*	@param $uuid The UUID which is the primary key of the DB entry.
		*   @param $entryArray Associative array, containing the columns to be updated. The key
					is the column name and the value is the new value to be written to the DB.
		*	@description Updates the specified column(s) of an entry depending
				on $entryArray's contents.
		*	@history Just copied from ndx_model::updateUnified, constant name for DB table changed
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/	
		$this->db->where( COL_UUID_MB, $uuid );		
		return $this->db->update( COL_DB_MB_TABLE_NAME, $entryArray );
	}//updateUnified(..)
	
	function create( $entries )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Well, obviously, creates an entry in the DB.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		* 	@history Patterned after ndx_model::create
		**/
		date_default_timezone_set('Asia/Manila');
		$expiry = strtotime( '+60 min ', strtotime( date( 'Y-m-d H:i:s') ) );
		$data = Array(
			COL_EXPIRE_DATE	   => date( 'Y-m-d', $expiry ),
			COL_EXPIRE_TIME	   => date('H:i:s', $expiry ),
			COL_UUID_MB        => $entries[ 0 ],		
			COL_GO_SHOWTIME    => $entries[ 1 ],
			COL_GO_TICKETCLASS => $entries[ 2 ],
			COL_GO_SEAT        => $entries[ 3 ],
			COL_GO_PAYMENT     => $entries[ 4 ],
			COL_CURRENT_UUID   => $entries[ 5 ],
			COL_NEW_UUID       => $entries[ 6 ]
		);
		return $this->db->insert( COL_DB_MB_TABLE_NAME , $data );
	}//create(..)
	
	function delete( $uuid )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Well, obviously, deletes.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		*	@history Patterned after ndx_model::delete
		**/
		$obj = $this->get( $uuid );
		if( $obj !== FALSE )
		{
			$this->ndx_model->delete( @$obj->CURRENT_UUID );
			$this->ndx_model->delete( @$obj->NEW_UUID );
			return $this->db->delete( COL_DB_MB_TABLE_NAME, Array( COL_UUID_MB => $uuid ) );
		}else
			return FALSE;
	}//delete(..)
	
	function deleteExpiredManageBookingCookiesOnServer()
	{
		/**
		*	@created 24JUN2012-1916
		*	@description Deletes any MANAGE booking cookies-on-server that is expired,
				and the booking cookies-on-server that is currently on-record by those
				MB c-o-s.
		**/
		$result = TRUE;
		$sql_command = "SELECT * FROM `" . COL_DB_MB_TABLE_NAME . "` WHERE CONCAT(`";
		$sql_command .= COL_EXPIRE_DATE . "`,' ',`".COL_EXPIRE_TIME . "`) <= ? ";
		$arr_result = $this->db->query( $sql_command, Array( date("Y-m-d H:i:s"),  ) )->result();
		
		if( count($arr_result) > 0 )
		{
			foreach( $arr_result as $record ) {
				$result = $result AND $this->delete( $record->UUID_MB );
			}
		}
		return $result;
	}//deleteExpiredManageBookingCookiesOnServer()
	
	function get( $uuid )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Well, obviously, gets a record/entry from DB.
		*	@returns 
				- BOOLEAN FALSE : If specified record not found
				- MYSQL OBJ     : Of the entry being requested if found.
		*	@history Patterned after ndx_model::get
		**/
		if( $uuid === false ) return FALSE;
		$obj_result = $this->db->get_where( COL_DB_MB_TABLE_NAME, Array( COL_UUID_MB => $uuid ), 100000, 0 );
		$arr_result = $obj_result->result();
		
		if( count( $arr_result ) !== 1 ) return FALSE;
		return $arr_result[0];
	}//get(..)

	function get_where_new( $uuid )
	{	
		/**
		*	@created 19JUN2012-1445
		*	@description Gets a record in DB whose NEW_UUID is equal to param $uuid
		*	@returns 
				- BOOLEAN FALSE : If specified record not found
				- MYSQL OBJ     : Of the entry being requested if found.
		**/
		$arr_result = $this->db->get_where( COL_DB_MB_TABLE_NAME, array( COL_NEW_UUID => $uuid) )->result();
		if( count ( $arr_result ) < 1 )
			return FALSE;
		else
			return $arr_result[0];
	}//get_where_new(..)
	
	function updateCurrentUUID( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the `CURRENT_UUID` column of an existing entry.
				This points to an entry in table defined by `COL_DB_TABLE_NAME`, for
				the cookie-on-server data that signifies the current condition of the
				booking.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_CURRENT_UUID => $newVal ) );
	}
	
	function updateExpiryDate( $uuid, $newVal )
	{
		/**
		*	@created 12JUN2012-1604
		*	@description Updates the expiry date of this server-on-cookie.
		*	@returns See updateUnified(..)
		*	@history Just copied from ndx_model
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_DATE => $newVal ) );
	}//updateExpiryDate()
	
	function updateExpiryTime( $uuid, $newVal )
	{
		/**
		*	@created 12JUN2012-1604
		*	@description Updates the expiry time of this server-on-cookie.
		*	@returns See updateUnified(..)
		*	@history Just copied from ndx_model
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_TIME => $newVal ) );
	}//updateExpiryTime()
	
	function updateGoPayment( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the go payment column of an existing entry.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_GO_PAYMENT => $newVal ) );
	}//updateGoPayment
	
	function updateGoSeat( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the go seat column of an existing entry.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_GO_SEAT => $newVal ) );
	}//updateGoSeat
	
	function updateGoShowtime( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the go showtime column of an existing entry.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_GO_SHOWTIME => $newVal ) );
	}//updateGoShowtime
	
	function updateGoTicketClass( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the go ticket class column of an existing entry.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_GO_TICKETCLASS => $newVal ) );
	}//updateGoTicketClass()
	
	function updateNewUUID( $uuid, $newVal )
	{
		/**
		*	@created 14JUN2012-1311
		*	@description Updates the `NEW_UUID` column of an existing entry.
				This points to an entry in table defined by `COL_DB_TABLE_NAME`, for
				the cookie-on-server data that signifies the NEW condition of the
				booking.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_NEW_UUID => $newVal ) );
	}//updateNewUUID(..)
	
}