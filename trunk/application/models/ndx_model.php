<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	New Data Access Model For Booking Cookies
* 	Created late 09JUN2012-1201
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Introducing cookie-on-server!
*	Default lifetime is 1 hour (60 minutes).
*	Made in response to cookie-setting bug I encountered when introducing
		the "Change Payment mode" feature.
*	All booking cookie data previously are now stored in DB. Anyway, servers today
		are that powerful now.
*/

class ndx_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');

		include_once( APPPATH.'constants/ndx.php' );
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
		return $this->db->update( COL_DB_TABLE_NAME, $entryArray );
	}//updateUnified(..)
	
	function create( $entries )
	{
		/**
		*	@created 09JUN2012-1243
		*	@description Well, obviously, creates an entry in the DB.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		date_default_timezone_set('Asia/Manila');
		$expiry = strtotime( '+60 min ', strtotime( date( 'Y-m-d H:i:s') ) );
		$data = Array(
			COL_EXPIRE_DATE		  => date( 'Y-m-d', $expiry ),
			COL_EXPIRE_TIME		  => date('H:i:s', $expiry ),
			COL_UUID              => $entries[ 0 ],		
			COL_BOOKING_NUMBER    => $entries[ 1 ],		
			COL_EVENT_ID 		  => $entries[ 2 ],		
			COL_SHOWTIME_ID 	  => $entries[ 3 ],		
			COL_TICKET_CLASS_GROUP_ID  => $entries[ 4 ],		
			COL_TICKET_CLASS_UNIQUE_ID => $entries[ 5 ],		
			COL_PURCHASE_IDS      => $entries[ 6 ],		
			COL_SLOTS_UUID        => $entries[ 7 ],		
			COL_SLOT_QUANTITY     => $entries[ 8 ],		
			COL_VISUALSEAT_DATA   => $entries[ 9 ],		
			COL_EVENT_NAME 	      => $entries[ 10 ],		
			COL_START_DATE 	  	  => $entries[ 11 ],		
			COL_START_TIME 	      => $entries[ 12 ],		
			COL_END_DATE	      => $entries[ 13 ],		
			COL_END_TIME 		  => $entries[ 14 ],		
			COL_EVENT_LOCATION => $entries[ 15 ]			
		);		
		return $this->db->insert( COL_DB_TABLE_NAME , $data );
	}//create(..)
	
	function delete( $uuid )
	{
		/**
		*	@created 09JUN2012-1244
		*	@description Well, obviously, deletes.
		*	@returns BOOLEAN - whether transaction was carried out successfully (TRUE) or not (FALSE)
		**/
		if( is_null( $uuid ) or $uuid === FALSE ) return TRUE;
		return $this->db->delete( COL_DB_TABLE_NAME, Array( COL_UUID => $uuid ) );
	}//delete(..)
	
	function deleteExpiredBookingCookiesOnServer(){
		/**
		*	@created 24JUN2012-1916
		*	@description Deletes any booking cookies-on-server that is expired.
		**/
		date_default_timezone_set('Asia/Manila');
		$sql_command = "DELETE FROM `" . COL_DB_TABLE_NAME . "` WHERE CONCAT(`";
		$sql_command .= COL_EXPIRE_DATE . "`,' ',`".COL_EXPIRE_TIME . "`) <= ? ";
		return $this->db->query( $sql_command, Array( date("Y-m-d H:i:s"),  ) );
	}//deleteExpiredBookingCookiesOnServer()
	
	function get( $uuid )
	{
		/**
		*	@created 09JUN2012-1246
		*	@description Well, obviously, gets a record/entry from DB.
		*	@returns 
				- BOOLEAN FALSE : If specified record not found
				- MYSQL OBJ     : Of the entry being requested if found.
		**/
		if( $uuid === false ) return FALSE;
		$obj_result = $this->db->get_where( COL_DB_TABLE_NAME, Array( COL_UUID => $uuid ), 100000, 0 );
		$arr_result = $obj_result->result();
		
		if( count( $arr_result ) !== 1 ) return FALSE;
		return $arr_result[0];
	}//get(..)
			
	function updateBookingNumber( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1248
		*	@description Updates the booking number of an existing entry.
		*	@returns See updateUnified(..)
		**/				
		return $this->updateUnified( $uuid, Array( COL_BOOKING_NUMBER => $newVal ) );
	}//updateBookingNumber()
	
	function updateEventID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Event ID of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EVENT_ID => $newVal ) );
	}//updateEventID()
	
	function updateEventName( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Event Name of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EVENT_NAME => $newVal ) );
	}//updateEventName()
	
	function updateEventDateEnd( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the date of the end of an event.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_END_DATE => $newVal ) );
	}//updateEventDateEnd()
	
	function updateEventDateStart( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the date of the start of an event.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_START_DATE => $newVal ) );
	}//updateEventDateStart()
	
	function updateEventLocation( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the event location  of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EVENT_LOCATION => $newVal ) );
	}//updateEventLocation()
	
	function updateEventTimeEnd( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the time of the end of an event.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_END_TIME => $newVal ) );
	}//updateEventTimeEnd()
	
	function updateEventTimeStart( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the time of the start of an event.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_START_TIME => $newVal ) );
	}//updateEventTimeStart()

	function updateExpiryDate( $uuid, $newVal )
	{
		/**
		*	@created 12JUN2012-1604
		*	@description Updates the expiry date of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_DATE => $newVal ) );
	}//updateExpiryDate()
	
	function updateExpiryTime( $uuid, $newVal )
	{
		/**
		*	@created 12JUN2012-1604
		*	@description Updates the expiry time of this server-on-cookie.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_EXPIRE_TIME => $newVal ) );
	}//updateExpiryTime()
	
	function updateShowtimeID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Showing time ID of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_SHOWTIME_ID => $newVal ) );
	}//updateShowtimeID()
	
	function updatePaymentDeadlineDate( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Payment Deadline Date of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_PAYMENT_DEADLINE_DATE => $newVal ) );
	}//updatePaymentDeadlineDate()
	
	function updatePaymentDeadlineTime( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Payment Deadline Time of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_PAYMENT_DEADLINE_TIME => $newVal ) );
	}//updatePaymentDeadlineTime()
	
	function updatePurchaseID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the PurchaseIDs of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_PURCHASE_IDS => $newVal ) );
	}//updatePurchaseID()
	
	function updateSlotQuantity( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the slot quantity of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_SLOT_QUANTITY => $newVal ) );
	}//updateSlotQuantity()
	
	function updateSlotsUUID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the list of the UUIDs of the slots of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_SLOTS_UUID => $newVal ) );
	}//updateSlotsUUID()
	
	function updateTicketClassGroupID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Ticket Class Group ID of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_TICKET_CLASS_GROUP_ID => $newVal ) );
	}//updateTicketClassGroupID()
	
	function updateTicketClassUniqueID( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Ticket Class Unique ID of an existing entry.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_TICKET_CLASS_UNIQUE_ID => $newVal ) );
	}//updateTicketClassUniqueID()
	
	function updateUPLBEmployeeNumData( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the UPLB Employee Num constituency data.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_UPLB_EMPNUM_DATA => $newVal ) );
	}//updateUPLBEmployeeNumData()
	
	function updateUPLBStudentNumData( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the UPLB Student Num constituency data.
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_UPLB_STUDENTNUM_DATA => $newVal ) );
	}//updateUPLBStudentNumData()
	
	function updateVisualSeatData( $uuid, $newVal )
	{
		/**
		*	@created 09JUN2012-1310
		*	@description Updates the Visual Seat Data of an existing entry( for use
				in the view pages to show the seat assignments of guest(s) ).
		*	@returns See updateUnified(..)
		**/		
		return $this->updateUnified( $uuid, Array( COL_VISUALSEAT_DATA => $newVal ) );
	}//updateVisualSeatData()
	
	
}//class