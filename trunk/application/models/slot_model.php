<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 14DEC2011 13343
*/


class slot_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();	
	}
	
	function assignSlotToGuest( $eventID, $showtimeID, $uuid_Slot, $uuid_Guest )
	{
		/*
			Created 14FEB2012-0847
		*/	
		$sql_command = "UPDATE `event_slot` SET `Assigned_To_User` = ? WHERE `EventID` = ? AND `Showtime_ID` = ? AND `UUID` = ?";
		return $this->db->query( $sql_command, Array( $uuid_Guest, $eventID, $showtimeID, $uuid_Slot ) );
	}//assignSlotToUser

	
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
							$x + 1, //$startingUniqueID,U
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
	
	function freeSlotsBelongingToClasses( $ticketClasses )
	{
		/**
		*	@DEPRECATED 23JUN2012-1317
		*	@created 06FEB2012-1855
		*	@description Only to be called during booking proceedings since the cookies
			specified here are only made/avaialble during such.
			
			Parameter definition:
			$ticketClasses			- an array of MYSQL_OBJs
		**/
		if( is_array( $ticketClasses ) === false or count( $ticketClasses ) < 1 ) return false; 
		foreach( $ticketClasses as $singleClass )
		{
			$explodedUUIDs;
			$slotUUIDs_str;
			
			// get the cookie that contains the slot UUIDs
			$slotUUIDs_str = $this->input->cookie( $singleClass->UniqueID."_slot_UUIDs" );	
			if( $slotUUIDs_str === false ) continue;
			delete_cookie( $singleClass->UniqueID."_slot_UUIDs" );			
			$explodedUUIDs = explode( '_', $slotUUIDs_str );		// explode via delimiter underscore
			foreach( $explodedUUIDs as $uuid )
			{				
				$this->setSlotAsAvailable( $uuid );					// free slots
			}
		}
	}//freeSlotsBelongingToClasses
	
	function getBeingBookedSlots( $eventID, $showtimeID )
	{
		/*
			Created 01MAR2012-1154
		*/
		$sql_command = "SELECT * FROM `event_slot` WHERE `EventID` = ? AND `Showtime_ID` = ?";
		$sql_command .= " AND `Status` = 'BEING_BOOKED' ";
		$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID  ) )->result(); 
		if( count( $arr_result ) > 0 )
			return $arr_result;
		else
			return false;
	}//getBeingBookedSlots
	
	function getSeatAssignedToUser( $UUID )
	{
		/*
			Created 02MAR2012-2110
			
			Returns only the MATRIX REPRESENTATION (not the visual representation).
		*/
		$slotObj = $this->getSlotAssignedToUser( $UUID );
		if( $slotObj === false ) return false;
		return Array(
			'Matrix_x' => $slotObj->Seat_x,
			'Matrix_y' => $slotObj->Seat_y
		);
	}//getSeatAssignedToUser( ..)
	
	function getSingleSlot( $UUID )
	{
		/*
			Created 14FEB2012-1010
			
			An easy way to get the slots, so no hassle to deal with eventID, showtimeID and others.
			Mostly useful for resetting availability, etc.
		*/
		$sql_command = "SELECT * FROM `event_slot` WHERE `UUID` = ? ";
		return $this->db->query( $sql_command, Array( $UUID ) )->result(); 
	}//getSingleSlot
	
	
	function getSlotAssignedToUser( $UUID )
	{
		/*
			!!! FOR DEPRECATION! Use getSlotAssignedToUser_MoreFilter() instead!
			Created 19FEB2012-1735
			
			Obviously...
			
			Returns MYSQL Obj on okay, BOOLEAN FALSE on fail.
		*/
		$sql_command = "SELECT * FROM `event_slot` WHERE `Assigned_To_User` = ? ";
		$arr_result = $this->db->query( $sql_command, Array( $UUID ) )->result(); 
		
		if( count( $arr_result ) > 0 )
			return $arr_result[0];
		else
			return false;
	}//getSingleSlot
	
	function getSlotAssignedToUser_MoreFilter( 
		$eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID, $guestUUID 
	)
	{
		/*
			Created 10MAR2012-1119
			
			Arose during creation of feature that will rollback changes made
				to an existing booking.
			It was deemed $this->getSlotAssignedToUser( $UUID ) is insufficient
				because in the time when a booking was changed and payment is pending,
				two records in `event_slot` are assigned to a single guest in `booking_guests`.
			So this was created to narrow down the search.
			I won't erase that function by now, since it is being used widely, and this
			function is only needed during the feature I specified above.
			
			Ammendment 12MAR2012-1905 - Omo omo, it seems if there's time,
			we should deprecate getSlotAssignedToUser(). We need this current function
			more.
		*/
		$sql_command = "SELECT * FROM `event_slot` WHERE `EventID` = ? AND `Showtime_ID` = ? AND";
		$sql_command .= " `Ticket_Class_GroupID` = ? AND `Ticket_Class_UniqueID` = ? AND ";
		$sql_command .= " `Assigned_To_User` = ? ";
		$arr_result = $this->db->query( $sql_command, Array(
				$eventID, 
				$showtimeID, 
				$ticketClassGroupID, 
				$ticketClassUniqueID,
				$guestUUID
			)
		)->result();
		if( count( $arr_result ) > 0 )
			return $arr_result[0];
		else
			return false;
	}//getSlotAssignedToUser_MoreFilter
	
	function getSlotsForBooking( $quantity, $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID )
	{
		/*
			Created 05FEB2012-2104
			
			Created for Book Step2
		*/
		$x;
		$slotsChosen = Array();
		
		// loop $quantity times to get desired slots
		for( $x = 0; $x < $quantity; $x++ )
		{
			/*
				We only need one slot at a time so that we if we get it, we can set it as 'BEING_BOOKED' immediately, minimizing
				race-conditions side effect: i.e. you have selected then some other client's session might select that too.
			*/
			$sql_command = "SELECT * FROM `event_slot` WHERE `EventID` = ? AND `Showtime_ID` = ? AND `Ticket_Class_GroupID`";
			$sql_command .= " = ? AND `Ticket_Class_UniqueID` = ? AND `Status` = 'AVAILABLE' LIMIT 1 ";
			$result_arr = $this->db->query( $sql_command, array( $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID ) )->result();	
			if( count( $result_arr ) != 1 )
			{							
				if( $x > 0 ) foreach( $slotsChosen as $sc ) $this->setSlotAsAvailable( $sc->UUID );
				$slotsChosen = null;		// nullify and return false since no more X slots available
				return false;
			}else{
				$this->setSlotAsBeingBooked( $result_arr[0]->UUID );
				$slotsChosen[ $x ] = $result_arr[0];					// assign to be returned
			}
		}
		
		return $slotsChosen;
	}//getSlotsForBooking
	
	
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
	
	function getSlotsUnderThisBooking( $bookingNumber )
	{
		/*
			Created 04MAR2012-1723
		*/
		$sql_command = "SELECT * FROM `event_slot` INNER JOIN `booking_guests` ON `event_slot`.`Assigned_To_User`";
		$sql_command .= " = `booking_guests`.`UUID` WHERE `booking_guests`.`bookingNumber` = ? ";
		$arr_result = $this->db->query( $sql_command, Array( $bookingNumber ) )->result();
		if( count( $arr_result ) > 0 )
			return $arr_result;
		else
			return false;
	}//getSlotsUnderThisBooking(..)
	
	
	
	function isSlotBeingBookedLapsedHoldingTime( $slotObj, $ticketClassObj )
	{
		/*
			Created 01MAR2012-1214
			
			Take note: function strtotime returns the number of seconds since 
			January 1 1970 00:00:00 UTC or 08:00:00 UTC+8/PST/Manila Time
		*/	
		date_default_timezone_set('Asia/Manila');
		$holdingTimeSplitted = explode(':', $ticketClassObj->HoldingTime );
		$holdingTimeAdjustment = '+'.intval($holdingTimeSplitted[0]).' day +'.intval($holdingTimeSplitted[1]);
		$holdingTimeAdjustment .= ' min +'.intval($holdingTimeSplitted[2]).' sec';
		
		$slotLapseTimeStamp = strtotime(  			
			$holdingTimeAdjustment,
			strtotime( $slotObj->Start_Contact )
		);
		
		$currentTimeStamp = strtotime( date("Y-m-d H:i") );	// get the current time
	
		return ( $currentTimeStamp > $slotLapseTimeStamp );
	}//isSlotBeingBookedLapsedHoldingTime(..)
	
	function setSlotAsAvailable( $uuid )
	{
		/*
			Created 06FEB2012-1643
			
			Called when customer is in the process of and then cancelled booking and we want to restore
			the slots temporarily reserved back to being avaialable.
		*/
		$sql_command = "UPDATE `event_slot` SET `Status` = 'AVAILABLE', `Assigned_To_User` = NULL, `Seat_x` = NULL, ";
		$sql_command .= " `Seat_y` = NULL, `Start_Contact` = NULL WHERE `UUID` = ?";
		return $this->db->query( $sql_command, array( $uuid ) );
	}//setSlotAsAvailable
	
	function setSlotAsBeingBooked( $uuid )
	{
		/**
		*	@created 05FEB2012-2123
		*	@description Obviously
		*	@revised 20JUN2012-1523 Instead of using MySQL's CURRENT_TIMESTAMP constant, we substituted it for
				getting the time via PHP as this is much more fool-proof regarding hosting server's time differences.
		**/
		date_default_timezone_set('Asia/Manila');
		$sql_command = "UPDATE `event_slot` SET `Status` = 'BEING_BOOKED', `Start_Contact` = ? WHERE `UUID` = ?";
		return $this->db->query( $sql_command, array( date("Y-m-d H:i:s") , $uuid ) );
	}//setSlotAsBeingBooked
	
	function setSlotAsBooked( $uuid )
	{
		/*
			Created 22FEB2012-2038
						
		*/
		$sql_command = "UPDATE `event_slot` SET `Status` = 'BOOKED', `Start_Contact` = NULL WHERE `UUID` = ?";
		return $this->db->query( $sql_command, array( $uuid ) );
	}//setSlotAsBooked
	
	function setSlotAsPendingPayment( $uuid )
	{
		/*
			Created 22FEB2012-2038
						
		*/
		$sql_command = "UPDATE `event_slot` SET `Status` = 'RESERVED-PENDING_PAYMENT', `Start_Contact` = NULL WHERE `UUID` = ?";
		return $this->db->query( $sql_command, array( $uuid ) );
	}//setSlotAsPendingPayment
	
	function updateSlotLastContactTime( $uuid )
	{
		/*
			ON-HOLD : Created 05FEB2012-2125
						
		*/
		$sql_command = "UPDATE `event_slot` SET `Status` = 'BEING_BOOKED' WHERE `UUID` = ?";
		return $this->db->query( $sql_command, array( $uuid ) );
	}
}//class
?>