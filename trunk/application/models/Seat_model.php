<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 19JAN2012-1104

This deals with all of tables `seat_map`, `seats_actual`, `seats_default`

*/


class Seat_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
			
	function createDefaultSeats()
	{
		/*
			Created 19JAN2012-1206
			
			Based on the rows and cols of the seat map, loops through the submitted POST data to
			insert to the database the seat's coordinates, status and comments.
		*/
		$usableSeats;
		
		for( $x = 0, $y = $this->input->cookie('rows'), $usableSeats = 0; $x < $y; $x++ )
		{
			$present;
			for( $i = 0, $j = $this->input->cookie('cols'); $i < $j; $i++ )
			{
				$status =  $this->input->post( 'seatLocatedAt_'.$x.'_'.$i.'_status' );
				$Visual_row = NULL;
				$Visual_col = NULL;	
				$transactionResult;
				if( $status > - 1 ) // for values <= -1, seats are to be not displayed, thus don't bother caring about the presentation
				{
					$usableSeats++; 	// indicator of usable seats in the DB
					$presentationCompounded = $this->input->post( 'seatLocatedAt_'.$x.'_'.$i.'_presentation' );
					$presentationSeparated = explode( '_', $presentationCompounded );
					$Visual_row = $presentationSeparated[0];
					$Visual_col = $presentationSeparated[1];
				}
				$transactionResult = $this->insertDefaultSingleSeatData( $this->input->cookie( 'seatMapUniqueID' ), $x, $i, $Visual_row, $Visual_col, $status, 'COMMENT' );
				if( $transactionResult === FALSE )
				{
					return FALSE;
				}				
			}			
		}	
		$this->updateSeatMapUsableCapacity( $this->input->cookie( 'seatMapUniqueID' ), $usableSeats );
		return true;
	}//createDefaultSeats
	
	function copyDefaultSeatsToActual( $seatMapUniqueID )
	{
		/*
			Created 04FEB2012-1415
			
			Called during Create Event Step 6 - Saving the seat configuration of the events being configured.
			This is the first step, copy the entries of the seat map in the table `seats_default` to 
			`seats_actual` then we just update the copied entries in the latter table when processing sent 
			information to the server.
			
			Changed 13FEB2012-1235 -  Removed `Seat_map_UniqueID` as one of the selected columns
		*/
		$fields = "`Matrix_x`, `Matrix_y`, `Visual_row`, `Visual_col`, `Status`, `Comments`";
		$sql_command = "INSERT `seats_actual` ( ".$fields." ) SELECT ".$fields." FROM `seats_default`	WHERE `Seat_map_UniqueID` = ? ";
				
		return $this->db->query( $sql_command, array( $seatMapUniqueID ) );	
	}//copyDefaultSeatsToActual(..)
	
	function createSeatMap()
	{
		$uniqueID;
		
		if( $this->isSeatMapNameExistent( $this->input->post('name') )  )
		{
			die( 'Seat Map Name exists. Please choose another one.' );
		}
		$uniqueID = $this->generateSeatMapUniqueID();
		// CODE MISSING: database checkpoint
		if( $this->insertSeatMapBaseInfo( $uniqueID ) == false)
		{
			// CODE MISSING:  database rollback			
			die('Seat Map Creation Fail - Database Error');
		}
		// CODE MISSING:  database commit
		
		// now, set some cookie for use in Create Seat Step3
		$cookie = array( 'name' => 'seatMapUniqueID', 
						 'value' => $uniqueID,
						 'expire' => '7200'  );	
		$this->input->set_cookie( $cookie );
		
	}// createSeatMap
	
	function generateSeatMapUniqueID()
	{
		/*
			Created 19JAN2012-1118
			
			Just generates random numbers between the range specified, checks if it already been used.
			If so, do the process again. When found some number that is not yet in use, return it.
		*/
		$uniqueID;
		
		do{
			$uniqueID = rand( 8888888, 9999999 );			// numbers just due to the upcoming Chinese New Year on 23JAN2012. ;D
		}while( $this->isSeatMapUniqueIDExistent( $uniqueID ) );
		
		return $uniqueID;
	}//generateAccountNumber

	function getEventSeatMapActualSeats( $eventID, $showtimeID )
	{
		/*
			Created 12FEB2012-2303
		*/
		$sql_command = "SELECT `Matrix_x`,`Matrix_y`,`Visual_row`,`Visual_col`,`Ticket_Class_UniqueID`,`Status`,`Comments` ";
		$sql_command .= "FROM `seats_actual` WHERE `EventID` = ?  AND `Showing_Time_ID` = ? ";
		$arrayResult = $this->db->query( $sql_command, array( $eventID, $showtimeID ) )->result();
		
		return $arrayResult;
	}// getEventSeatMapActualSeats(..)
	
	function getMasterSeatMapActualSeats( $uniqueID )
	{
		/*
			Created 28JAN2012-2226
			
			Gets all seat info of the seat map whose uniqueID was supplied, from `seats_default`
			
			Returns a MySQL result object since we only need one.
		*/
		
		/* we opted to not use '*' because by doing so, `UniqueID` would be included in the results that are quite large, 
			which we are avoiding since it will just consume memory space and involve processing time which are not necessary.
		*/
		$sql_command = "SELECT `Matrix_x`,`Matrix_y`,`Visual_row`,`Visual_col`,`Status`,`Comments` FROM `seats_default` WHERE `Seat_map_UniqueID` = ? ";
		$arrayResult = $this->db->query( $sql_command, array( $uniqueID ) )->result();
		
		return $arrayResult;
	}// getMasterSeatMapActualSeats
	
	function getSeats_User( $guests )
	{
		/*
			Created 19FEB2012-1712
			
			Param definition: $guests - array of guests' MYSQL OBJ OF table `booking_guests`
		*/
		if( !is_array( $guests ) ) return false;
		foreach( $guests as $singleGuest )
		{
		
		}
	}//getSeats_User
	
	function getSingleMasterSeatMapData( $uniqueID )
	{
		/*
			Created 28JAN2012-2222
			
			Simply gets a master seat map's info from table `seat_map`
			
			Returns a MySQL result object since we only need one.
		*/
		$sql_command = "SELECT * FROM `seat_map` WHERE `UniqueID` = ?";
		$arrayResult = $this->db->query( $sql_command, array( $uniqueID ) )->result();
		
		return $arrayResult[0];
	}// getSingleMasterSeatMap
	
	function getSingleActualSeatData( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/*
			Created 13FEB2012-2009
		*/
		$sql_command = "SELECT * FROM `seats_actual` WHERE `Matrix_x` = ? AND `Matrix_y` = ? AND `EventID` = ? AND `Showing_Time_ID` = ? ";
		$arr_result = $this->db->query( $sql_command, Array( $matrix_x, $matrix_y, $eventID, $showtimeID ) )->result();
		
		if( count( $arr_result ) > 0 )
			return $arr_result[0];
		else
			return false;
	}//getSingleActualSeatData
	
	function getSingleActualSeatData_ByClientUUID( $UUID )
	{
		/*
			Created 19FEB2012-1716 ON-HOLD
			
			Gets from `seats_actual` the record specified using the client's UUID (found in `booking_guests` table )
		*/
		$sql_command = "SELECT FROM `seats_actual` where ";
	}//getSingleActualSeatData_ByClientUUID
	
	function getUsableSeatMaps( $requestedCapacity )
	{
		/*
			Created 20JAN2012-1200
			
			For the pull-down seat map selection in Create Event Step 5.
			
			Argument definition:
			$requestedCapacity - (int) minimum number of seats that a seat map should have. or if
								 (boolean) false, then we don't need to check usableCapacity.
		*/
		$arrayResult;
		$filters = array();
		
		$sql_command = "SELECT * FROM `seat_map` WHERE `Status` = 'CONFIGURED'"; 
		// check if $requestedCapacity is not equal to (boolean) false, and if so, add this filter
		if( $requestedCapacity !== false ){
			$sql_command .= " AND `UsableCapacity` >= ? ";
			$filters[ 'UsableCapacity' ] = $requestedCapacity;
		}		
		$arrayResult = $this->db->query( $sql_command, $filters )->result();
		
		return $arrayResult;
	}//getUsableSeatMaps
	
	/*
		ON HOLD !!!!!!
		
	function getMatrixRepresentation( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/*
			Created 02MAR2012-2105
			
			Returns the matrix identifiers of the specifiedseat visualization of the string form 
			"X-Y" where X is the row and Y is the column
		*/
		/*$seatObj = $this->getSingleActualSeatData( $matrix_x, $matrix_y, $eventID, $showtimeID );
		return ( $seatObj->Visual_row."-".$seatObj->Visual_col );
	//getMatrixRepresentation(..)*/
	
	function getVisualRepresentation( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/*
			Created 14FEB2012-1822.
			 
			Returns the seat visualization of the string form 
			"X-Y" where X is the row and Y is the column
		*/
		$seatObj = $this->getSingleActualSeatData( $matrix_x, $matrix_y, $eventID, $showtimeID );
		if( $seatObj === false )
			return false;
		else
			return ( $seatObj->Visual_row."-".$seatObj->Visual_col );
	
	}//getVisualRepresentation()
		
	
	function insertSeatMapBaseInfo( $uniqueID = NULL )
	{
		/*
			Created 19JAN2012-1131
			
			Just inserts to the table `seat_map`.
		*/
		$data;

		$data = array(
			'UniqueID' => $uniqueID,
			'Name' => $this->input->post('name'),
			'Rows' => $this->input->post( 'rows' ),
			'Cols' => $this->input->post('cols'),
			'Status' => 'BEING_CONFIGURED'
		);
		
		return ( $this->db->insert( 'seat_map', $data ) );
	}// insertSeatMapBaseInfo
	
	function insertDefaultSingleSeatData( $seatMapUniqueID, $matrix_x, $matrix_y, $Visual_row, $Visual_col, $status, $comment )
	{
		/*
			Created 19JAN2012-1207
			
			Should I initialize the parameters to be NULL? Should I still perform value checking for these parameters (for error-checking)?
		*/
		$data = array(
			'Seat_map_UniqueID' =>  $seatMapUniqueID,
			'Matrix_x' => $matrix_x,
			'Matrix_y' => $matrix_y,
			'Visual_row' => $Visual_row,
			'Visual_col' => $Visual_col,
			'Status' => $status,
			'Comments' => $comment
		);
		return ( $this->db->insert( 'seats_default', $data ) );
	}//insertDefaultSingleSeatData
	
	function isSeatMapNameExistent( $name = NULL )
	{
		/*
			Created 19JAN2012-1118
			
			Checks if a string submitted is the name of some `seat_map` entry.
		*/
		if( $name == NULL ) return false;
		
		$this->db->where('Name', $name );		
		$query = $this->db->get('seat_map');
		
		// if there was one cell retrieved, then such seat map with the UniqueID exists
		return ( $query->num_rows == 1 );
	}//isSeatMapNameExistent
	
	function isSeatMapUniqueIDExistent( $uniqueID = NULL )
	{
		/*
			Created 19JAN2012-1118
			
			Checks if a number submitted is the UniqueID of some `seat_map` entry.
		*/
		if( $uniqueID == NULL ) return false;
		
		$this->db->where('UniqueID', $uniqueID );		
		$query = $this->db->get('seat_map');
		
		// if there was one cell retrieved, then such seat map with the UniqueID exists
		return ( $query->num_rows == 1 );
	}//isSeatMapUniqueIDExistent
	
	function markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, $status )
	{
		$sql_command = "UPDATE `seats_actual` SET `Status` = ? WHERE `EventID` = ? AND `Showing_Time_ID` = ? ";
		$sql_command .= " AND `Matrix_x` = ? AND `Matrix_y` = ?";
		return $this->db->query( $sql_command, Array( $status, $eventID, $showtimeID, $matrix_x, $matrix_y ) );
	}	
	
	function markSeatAsAssigned( $eventID, $showtimeID, $matrix_x, $matrix_y )
	{	
		/*
			Created 14JAN2012-0909
		*/
		return $this->markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, '1' );
	}//markSeatAsAssigned
	
	function markSeatAsAvailable( $eventID, $showtimeID, $matrix_x, $matrix_y )
	{	
		/*
			Created 14JAN2012-0909
		*/
		return $this->markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, '0' );
	}//markSeatAsAssigned
	
	function setSeatMapStatus( $seatMapUniqueID, $status )
	{
		/*
			Created 19JAN2012-2333
			
			Made for the end of configuring of a seat map, but can be used on other occassions though.
			
			$status may take on: 'CONFIGURED', 'UNCONFIGURED', 'BEING_CONFIGURED'
		*/
		$sql_command = "UPDATE `seat_map` SET `Status` = ? WHERE `UniqueID` = ? ";
		$transactionResult = $this->db->query( $sql_command, array(
									$status,
									$seatMapUniqueID
								)
							);
							
		return $transactionResult;	
	}// setSeatMapStatus
	
	function updateNewlyCopiedSeats( $eventID, $showtimeID )
	{
		/*
			Created 04FEB2012-1421
			
			Function to be called after $this->copyDefaultSeatsToActual(..). Since the newly copied entries' 
			`EventID` and `Showing_Time_ID` are both set to zero, we update them to reflect the proper values,
			as submitted to the server.
		*/
		$sql_command = "UPDATE `seats_actual` SET `EventID` = ?, `Showing_Time_ID` = ? WHERE `EventID` = '0' AND `Showing_Time_ID` = '0'";
		
		return $this->db->query( $sql_command, array( $eventID, $showtimeID ) );
	}//updateNewlyCopiedSeats(..)
	
	function updateSeatMapUsableCapacity( $uniqueID, $usableSeats ){
		/*
			Created 20JAN2012-1130
		*/
		$transactionResult;
		$sql_command;
		
		$sql_command = "UPDATE `seat_map` SET `UsableCapacity` = ? WHERE `UniqueID` = ?";
		$transactionResult = $this->db->query( $sql_command,  array( $usableSeats, $uniqueID ) );
		
		return $transactionResult;
	}//updateSeatMapUsableCapacity
}//class


?>