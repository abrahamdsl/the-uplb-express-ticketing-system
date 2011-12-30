<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 12DEC2011 1753
*/


class TicketClass_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function createTicketClass( 
		$UniqueID, $eventID  = NULL, $class = NULL, $price = 0, 
		$slots = 0, $privileges = NULL,  $restrictions = NULL, $priority = 0
	)
	{
		/*
			CREATED 12DEC2011-2132
			
			30DEC2011-1307, added $UniqueID param
		*/
		$data = array(
			'UniqueID' => $UniqueID,
			'EventID' => $eventID,
			'Name' => $class,
			'Price' => $price,
			'Slots' => $slots,
			'Privileges' => $privileges,
			'Restrictions' => $restrictions,
			'Priority' => $priority
		);
		
		return $this->db->insert( 'ticket_class', $data );
	}//createTicketClasses(..)
	
	function getDefaultTicketClasses()
	{
		/*
			created 12DEC2011 1754
			
			Obvious purpose isn't it? :-)
			BTW, an EventID of 0 in table `ticket_class` signifies that such class is a default			
		*/
		//$query_obj = $this->db->get_where( 'ticket_class' , array( 'eventID' => 0 ) );
		$sql = ("SELECT * FROM `ticket_class` where `eventID` = ? ORDER BY `priority` ASC");
		
		$query_obj = $this->db->query($sql, array( 'eventID' => 0 ) );
		return $query_obj->result( );
		
	}//getDefaultTicketClasses()
		
	function getLastTicketClassUniqueID( $eventID = null )
	{
		/*
			Created 30DEC2011-1248
			
			Created with regard to Create Event Step 6.
			Returns an integer, or if none found, 0, meaning no showing time
			yet for this event.
		*/
		
		if( $eventID == null ) return false;
		
		$sql = "SELECT `EventID`,`UniqueID` FROM  `ticket_class` WHERE  `EventID` =  ? ORDER BY  `UniqueID` DESC LIMIT 0 , 1000";
		$query_obj = $this->db->query( $sql, array( $eventID ) );
		$array_result = $query_obj->result();
		
		// now, what we want is found at the first element
		if( count( $array_result ) > 0 )
		{
			$lastInt = intval( $array_result[0]->UniqueID );
			return $lastInt;
		}else return 0;		
	}//getLastTicketClassUniqueID
		
} //class
?>