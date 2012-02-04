<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 14DEC2011 13343
*/


class Slot_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();	
	}
	
	function createSlots( $quantity, $eventID, $showtimeID, 
		$ticketClass_GroupID, $ticketClass_UniqueID  )
	{
		/*
			Created 14JAN2012-1335
		
		*/
		$startingUniqueID;
		$sql_command;
		$x;
		$y;
		
		$startingUniqueID = $this->getSlotsLastGroupID( $eventID, $showtimeID );				
		$sql_command  = "INSERT INTO `event_slot` ( `UniqueID`, `UUID`, `EventID`, `Showtime_ID`, `Ticket_Class_GroupID`, `Ticket_Class_UniqueID` ) VALUES (?, UUID( ), ?, ?, ?, ?) ";
		// CODE MISSING: database checkpoint
		for( $x = 0, ++$startingUniqueID; $x < $quantity; $x++, $startingUniqueID++ )
		{			
			$query_obj = $this->db->query( $sql_command, array(
							$x + 1, //$startingUniqueID,
							$eventID,
							$showtimeID,
							$ticketClass_GroupID,
							$ticketClass_UniqueID
						) 
			);
			if( $query_obj === false )
			{
				// CODE MISSING:  database rollback
				die("Error during creation of slots.");
			}
		}
		// CODE MISSING:  database commit
		return true;
	}//createSlots
	
	function getSlotsLastGroupID( $eventID = null, $showtimeID = null )
	{
		/*
			Created 14JAN2012-1338
		
			Returns an integer, or if none found, 0, meaning no showing time
			yet for this event.
		*/
		
		if( $eventID == null or  $showtimeID ) return false;
		
		$sql = "SELECT `UniqueID`,`EventID`,`Showtime_ID` FROM  `event_slot` WHERE  `EventID` =  ? AND `Showtime_ID` = ? ORDER BY  `GroupID` DESC LIMIT 0 , 10000";
		$query_obj = $this->db->query( $sql, array( $eventID, $showtimeID ) );
		$array_result = $query_obj->result();
		
		// now, what we want is found at the first element
		if( count( $array_result ) > 0 )
		{
			$lastInt = intval( $array_result[0]->UniqueID );
			return $lastInt;
		}else return 0;		
	}//getSlotsLastGroupID
}//class
?>