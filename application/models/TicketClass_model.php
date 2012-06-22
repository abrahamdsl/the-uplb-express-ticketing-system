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
	}//createTicketClass(..)
	
	function getHoldingTime( $eventID, $groupID, $uniqueID )
	{
		/*
			Created 01MAR2012-1140
		*/
		$ticketClassObj = $this->getSingleTicketClass( $eventID, $groupID, $uniqueID );
		if( $ticketClassObj === false ) return false;
		/*
			For now, holding time is from 2 minutes to 59 minutes only so
			from the database column `HoldingTime` which is of the form 'HH:MM:SS'
			we explode it via ':' and only get the MM which is in the resulting array index 1;
			If there is a change in this limitation, then this algorithm in getting the holding
			time should be also changed.
		*/
		$holdingTimeSplitted = explode(':', $ticketClassObj->HoldingTime );		
		return intval( $holdingTimeSplitted[1] );
	}//getHoldingTime
	
	function getDefaultTicketClasses()
	{
		/*
			created 12DEC2011 1754
			
			Obvious purpose isn't it? :-)
			BTW, an EventID of 0 in table `ticket_class` signifies that such class is a default			
		*/		
		$sql = ("SELECT * FROM `ticket_class` where `eventID` = '0' ORDER BY `priority` ASC");
		
		$query_obj = $this->db->query( $sql );
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
	
	//getTicketClassesOrderByPrice( $eventID, $groupID )
	function getMostExpensiveTicketClass( $eventID, $groupID )
	{
		/* 
			CREATED 08MAR2012-0323
			
			Returns MYSQL_OBJ or BOOLEAN FALSE on error.
		*/
		$ticketClassByPriceArray = $this->getTicketClassesOrderByPrice( $eventID, $groupID, TRUE );
		if( $ticketClassByPriceArray === false or count($ticketClassByPriceArray) < 1 ) return false;
		return $ticketClassByPriceArray[0];
	}//getMostExpensiveTicketClass
	
	function getSingleTicketClass( $eventID, $groupID, $uniqueID ){
		/*
			Created 06FEB2012-1850
			
			Returns MYSQL_OBJ or BOOLEAN FALSE
		*/
		$commonTC = $this->getTicketClasses( $eventID, $groupID );
		if( $commonTC === false ) return false;
		foreach( $commonTC as $singleClass )
		{
			if( intval($singleClass->UniqueID) === intval( $uniqueID ) ) return $singleClass;
		}
		
		return false;
	}//getSingleTicketClass
	
	function getSingleTicketClassName( $eventID, $groupID, $uniqueID ){
		/*
			Created 06FEB2012-1850
			
			Created for EventCtrl/manageBooking.
			This is used instead of SQL joins or straight call from there 
			to $this->getTicketClass - kinda more expensive in terms of processing power
			and storage.			
			Returns MYSQL_OBJ or BOOLEAN FALSE
		*/
		$singleClassObj = $this->getSingleTicketClass( $eventID, $groupID, $uniqueID );
		if( $singleClassObj === false )
			return "UNDEFINED";
		else
			return $singleClassObj->Name;
	}//getSingleTicketClassName(..)
	
	function getTicketClasses( $eventID, $groupID, $namePreferred = false )
	{
		/*
			Created 14JAN2012-1440
			
			05FEB2012-2056: Added returning of boolean false if no entry found.
		*/
		$devFriendlyArray = Array();
		
		if( $eventID == null || $groupID == null  ) return false;
		
		$sql_command = "SELECT * FROM `ticket_class` WHERE `EventID` = ? AND `GroupID` = ? ";
		$query_obj = $this->db->query( $sql_command, array( $eventID, $groupID ) );
		$array_result = $query_obj->result();
		
		if( count( $array_result ) < 1 ) return false;
		foreach( $array_result as $singleClass )
		{
			$key = ( $namePreferred ) ?  $singleClass->Name: $singleClass->UniqueID ;
			$devFriendlyArray[ $key ] = $singleClass;
		}
		return $devFriendlyArray;
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
	
	function getTicketClassesOrderByPrice( $eventID, $groupID, $doNotAssociative = FALSE )
	{
		/*
			Created 08MAR2012-0319
		*/
		$sql_command = "SELECT * FROM `ticket_class` WHERE `EventID` = ? AND `GroupID` = ? ORDER BY `Price` DESC";
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $groupID ) )->result();
		$newArray = Array();
		if( count( $arr_result) == 0 )
			return false;
		else{
			if( $doNotAssociative )
			{
				return $arr_result;
			}else{
				foreach( $arr_result as $eachTC )
				{
					$newArray[ $eachTC->UniqueID ] = $eachTC;
				}
				return $newArray;
			}
		}
	}//  getTicketClassesOrderByPrice
	
	function isTicketClassGroupOnlyForThisShowtime( $eventID, $showtimeID, $groupID )
	{
		$sql_command = "SELECT * FROM `showing_time` WHERE `UniqueID` != ? AND `EventID` = ? AND `Ticket_Class_GroupID` = ?";
		$arr_result  = $this->db->query( $sql_command , Array( $showtimeID, $eventID, $groupID ) )->result();
		return( count( $arr_result ) === 0 );
	}
	
	function isThereFreeTicketClass( $eventID )
	{
		/*
			Created 28FEB2012-1103
			
			Selects showing times and ticket classes of a showing time being 
			configured, inner joins them and sees if there is at least
			one ticket class being offered for free.
		*/	
		$sql_command = "SELECT * FROM `showing_time` INNER JOIN `ticket_class` ON `showing_time`.`EventID` = `ticket_class`.`EventID`";
		$sql_command .= " AND `showing_time`.`Ticket_Class_GroupID` = `ticket_class`.`GroupID` WHERE";
		$sql_command .= " `showing_time`.`Status` = 'BEING_CONFIGURED' AND `ticket_class`.`EventID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $eventID ) )->result();
		
		if( count( $arr_result) < 1 ) return false;
		foreach( $arr_result as $singleJoinedRecord ) if( floatval($singleJoinedRecord->Price) === 0.0 ) return true;		
		return false;
	}//isThereFreeTicketClass(..)
	
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
	
	function updateTicketClass( 
		$eventID, $GroupID, $UniqueID, $price = 0, $slots = 0, 
		$privileges = NULL,  $restrictions = NULL, $priority = 0, $holdingTime = 20
	)
	{
		/*
			CREATED 12DEC2011-2132
			
			30DEC2011-1307, added $UniqueID param
		*/
						
		$data = Array(					
			'Price'		  => $price,  
			'Slots'       => $slots, 
			'Privileges'	=> $privileges,
			'Restrictions'	=> $restrictions,
			'priority'		=> $priority,
			'HoldingTime'	=> $holdingTime
		);		
		$where = "`EventID` = ".$eventID." AND `GroupID` = ".$GroupID." AND `UniqueID` = ".$UniqueID; 
		$sql_command = $this->db->update_string('ticket_class', $data, $where);
		return $this->db->query( $sql_command );
	}//updateTicketClass()
} //class
?>