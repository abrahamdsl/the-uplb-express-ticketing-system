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
		$GroupID, $UniqueID, $eventID  = NULL, $class = NULL, $price = 0, $slots = 0, 
		$privileges = NULL,  $restrictions = NULL, $priority = 0, $holdingTime = 20
	)
	{
		/*
			CREATED 12DEC2011-2132
			
			30DEC2011-1307, added $UniqueID param
		*/
		
		$data = array(
			'GroupID' => $GroupID,
			'UniqueID' => $UniqueID,
			'EventID' => $eventID,
			'Name' => $class,
			'Price' => $price,
			'Slots' => $slots,
			'Privileges' => $privileges,
			'Restrictions' => $restrictions,
			'Priority' => $priority,
			'HoldingTime' => "00:".$holdingTime.":00"
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
		
	function getLastTicketClassGroupID( $eventID = null )
	{
		/*
			Created 30DEC2011-1248
			
			Created with regard to Create Event Step 6.
			Returns an integer, or if none found, 0, meaning no showing time
			yet for this event.
		*/
		
		if( $eventID == null ) return false;
		
		$sql = "SELECT `EventID`,`GroupID` FROM  `ticket_class` WHERE  `EventID` =  ? ORDER BY  `GroupID` DESC LIMIT 0 , 1000";
		$query_obj = $this->db->query( $sql, array( $eventID ) );
		$array_result = $query_obj->result();
		
		// now, what we want is found at the first element
		if( count( $array_result ) > 0 )
		{
			$lastInt = intval( $array_result[0]->GroupID );
			return $lastInt;
		}else return 0;		
	}//getLastTicketClassGroupID
	
	function getSingleTicketClass( $eventID, $groupID, $uniqueID ){
		/*
			Created 06FEB2012-1850
			
			Returns MYSQL_OBJ
		*/
		$commonTC = $this->getTicketClasses( $eventID, $groupID );
		if( $commonTC === false ) return false;
		foreach( $commonTC as $singleClass )
		{
			if( intval($singleClass->UniqueID) === intval( $uniqueID ) ) return $singleClass;
		}
		
		return false;
	}//getSingleTicketClass
	
	function getTicketClasses( $eventID, $groupID )
	{
		/*
			Created 14JAN2012-1440
			
			05FEB2012-2056: Added returning of boolean false if no entry found.
		*/
		if( $eventID == null || $groupID == null  ) return false;
		
		$sql_command = "SELECT * FROM `ticket_class` WHERE `EventID` = ? AND `GroupID` = ? ";
		$query_obj = $this->db->query( $sql_command, array( $eventID, $groupID ) );
		$array_result = $query_obj->result();
		
		if( count( $array_result ) < 1 ) return false;
		return $array_result;
	}//getTicketClasses
	
	function getTicketClassesExceptThisUniqueID( $eventID, $groupID, $uniqueID )
	{
		/*
			Created 06FEB2012-1857						
		*/
		if( $eventID == null || $groupID == null  ) return false;
		
		$sql_command = "SELECT * FROM `ticket_class` WHERE `EventID` = ? AND `GroupID` = ? AND `UniqueID` != ?";
		$query_obj = $this->db->query( $sql_command, array( $eventID, $groupID, $uniqueID ) );
		$array_result = $query_obj->result();
		
		if( count( $array_result ) < 1 ) return false;
		return $array_result;
	}//getTicketClassesExceptThisUniqueID
	
	function makeArray_NameAsKey( $ticketClasses )
	{
		/*
			Created 04FEB2012-1921
			
			Accepts one parameter, actually, the one being returned by $this->getTicketClasses(..),
			This is being called from EventCtrl controller.
		*/
		$theArray = Array();
		
		foreach( $ticketClasses as $x )
		{
			/*
				Though this may seem redundant, but our objective is
				to be able to easily access an array containing ticket classes.				
			*/
			$theArray[ $x->Name ] = $x;			
		}
		
		return $theArray;
	}//makeArray_NameAsKey(..)
} //class
?>