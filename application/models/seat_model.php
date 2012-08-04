<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Seat Model
* 	Created  19JAN2012-1104
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This deals with all of tables `seat_map`, `seats_actual`, `seats_default`
**/
class seat_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	private function generateSeatMapUniqueID()
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
	}//generateSeatMapUniqueID()
	
	private function insertSeatMapBaseInfo( $uniqueID = NULL )
	{
		/**
		*	@created 19JAN2012-1131
		*	@description Just inserts to the table `seat_map`.
		*	@remarks The posted data from the caller are directly accessed thru $this->input->post() here.
		*	@revised 07JUL2012-1358
		**/
		$data;

		$data = array(
			'UniqueID' => $uniqueID,
			'Name' => $this->input->post('name'),
			'Rows' => $this->input->post('rows' ),
			'Cols' => $this->input->post('cols'),
			'Status' => 'BEING_CREATED',
			// expiration of creation activity is one hour from now.
			'Status2' => date( 'Y-m-d H:i:s', strtotime( '+1 hour', strtotime(date( 'Y-m-d H:i:s' )) ) )
		);
		
		return ( $this->db->insert( 'seat_map', $data ) );
	}// insertSeatMapBaseInfo
	
	private function isSeatMapUniqueIDExistent( $uniqueID = NULL )
	{
		/**
		*	@created 19JAN2012-1118
		*	@description Checks if a number submitted is the UniqueID of some `seat_map` entry.
		*	@returns BOOLEAN.
		**/
		if( $uniqueID == NULL ) return false;

		$this->db->where('UniqueID', $uniqueID );
		$query = $this->db->get('seat_map');

		// if there was one cell retrieved, then such seat map with the UniqueID exists
		return ( $query->num_rows == 1 );
	}//isSeatMapUniqueIDExistent
	
	private function createDefaultSeats_CheckIndividual( $x, $i, &$visual_row, &$visual_col, &$status, &$usableSeats )
	{
		/**
		*	@created 18JUL2012-1505
		*	@description Ceremonies regarding a submitted default seat data.
		*	@remarks Some parameters are referenced to their original locations.
		*	@history Formerly contained within $this->createDefaultSeats
		**/
		$submitted_val = $this->input->post( 'seatLocatedAt_'.$x.'_'.$i.'_status' );
		// if input field is non-existent CI will return false, and thus let's assign -5 to signify non-existence.
		$status = ( $submitted_val === FALSE ) ? -5 :  intval($submitted_val);
		
		if( $status > - 1 ) // for values <= -1, seats are to be not displayed, thus don't bother caring about the presentation
		{
			$presentationCompounded = $this->input->post( 'seatLocatedAt_'.$x.'_'.$i.'_presentation' );
			$input_is_false = ( $presentationCompounded === FALSE );
			if( !$input_is_false ){
				$presentationSeparated = explode( '_', $presentationCompounded );
				if( count( $presentationSeparated ) == 2 ){
					$usableSeats++; 	// indicator of usable seats in the DB
					$visual_row = $presentationSeparated[0];
					$visual_col = $presentationSeparated[1];
				}
			}
		}
	}//createDefaultSeats_CheckIndividual(..)

	function createDefaultSeats( $uniqueID, $rows, $cols )
	{
		/**
		*	@created 19JAN2012-1206
		*	@description Based on the rows and cols of the seat map, loops through the submitted POST data
				to insert to the database the seat's coordinates, status and comments.
		*	@revised 07JUL2012-1612 Just for fool-proofing.
		*	@revised 18JUL2012-1506 Instead of multiple "INSERT" SQL statements for each seat - there is now only one
				with values appended - this is said to save nearly O(n) of space as well as savings due to one
				single communication with the SQL server.
		**/
		$usableSeats = 0;
		$row_less = $rows - 1;
		$col_less = $cols - 1;
		$sql_command = "INSERT INTO `seats_default` VALUES ";
		$x = 0;
		$i;
		// all, minus last row
		for(; $x < $row_less; $x++ )
		{
			for( $i=0; $i < $cols; $i++ )
			{
				$visual_row = NULL;
				$visual_col = NULL;
				$status;
				$this->createDefaultSeats_CheckIndividual( $x, $i, $visual_row, $visual_col, $status, $usableSeats );
				$sql_command .= "( ".$uniqueID.",".$x.",".$i.",'".$visual_row."','".$visual_col."',".$status.",NULL), ";
			}
		}
		// last row less its last column
		for( $i = 0; $i < $col_less; $i++ ){
			$visual_row = NULL;
			$visual_col = NULL;
			$status;
			$this->createDefaultSeats_CheckIndividual( $x, $i, $visual_row, $visual_col, $status, $usableSeats );
			$sql_command .= "( ".$uniqueID.",".$x.",".$i.",'".$visual_row."','".$visual_col."',".$status.",NULL), ";
		}
		// and lastly: last row, last column
		$visual_row = NULL;
		$visual_col = NULL;
		$status;
		$this->createDefaultSeats_CheckIndividual( $x, $i, $visual_row, $visual_col, $status, $usableSeats );
		$sql_command .= "( ".$uniqueID.",".$x.",".$i.",'".$visual_row."','".$visual_col."',".$status.",NULL); ";
		// finally, query it
		$this->db->query( $sql_command );
		$this->updateSeatMapUsableCapacity( $uniqueID, $usableSeats );
		return $this->db->trans_status();
	}//createDefaultSeats
	
	function createSeatMap()
	{
		/**
		*	@created <i forgot>
		*	@description Creates seat map entry in DB.
		*	@remarks The posted data from the caller are directly accessed thru $this->input->post() here.
		*	@revised 07JUL2012-1357
		**/
		$uniqueID = $this->generateSeatMapUniqueID();
		// CODE MISSING: database checkpoint
		if( $this->insertSeatMapBaseInfo( $uniqueID ) === FALSE )
		{
			// CODE MISSING:  database rollback				
			return FALSE;
		}
		// CODE MISSING:  database commit
		
		return $uniqueID;
	}// createSeatMap
	
	function copyDefaultSeatsToActual( $seatMapUniqueID, $eventID, $showtimeID )
	{
		/**
		*	@created 04FEB2012-1415
		*	@description Called during Create Event Step 6 - Saving the seat configuration of the events being configured.
				This is the first step, copy the entries of the seat map in the table `seats_default` to 
				`seats_actual` then we just update the copied entries in the latter table when processing sent 
				information to the server.
		*	@revised 04AUG2012-1514 - Now includes EventID and ShowtimeID when copying. We have to since we have fixed
				the SQL structure of `seats_actual` wherein previously, the fields `EventID`,`Showing_Time_ID`
				can be NULL.
		*/
		$actual_addtl = "`EventID`,`Showing_Time_ID`,";
		$addendum = $eventID . " AS `EventID`, " . $showtimeID ." AS `Showing_Time_ID`,";
		$fields = "`Matrix_x`, `Matrix_y`, `Visual_row`, `Visual_col`, `Status`, `Comments`";

		$sql_command = "INSERT `seats_actual` ( " . $actual_addtl . $fields." ) SELECT ". $addendum .$fields." FROM `seats_default` WHERE `Seat_map_UniqueID` = ? ";
		return $this->db->query( $sql_command, Array( $seatMapUniqueID ) );
	}//copyDefaultSeatsToActual(..)

	function deleteSeatMap( $uniqueID )
	{
		/**
		*	@created <i forgot>
		*	@description Deletes a seat map from the table containing its info as well as its individual seats.
		*	@revised 07JUL2012-1742
		**/
		$sql_command  = "DELETE FROM `seat_map` WHERE `UniqueID` = ? ";
		$sql_command2 = "DELETE FROM `seats_default` WHERE `Seat_map_UniqueID` = ? ";
		$seatmap_data = $this->db->query( $sql_command, Array( $uniqueID ) );
		$seat_piece   = $this->db->query( $sql_command2, Array( $uniqueID ) );
		return ( $seatmap_data and $seat_piece );
	}

	function getAllSeatMaps()
	{
		$sql_command = "SELECT * FROM `seat_map` WHERE 1 ORDER BY `Name` ASC";
		$arr_result = $this->db->query( $sql_command )->result();
		
		if( count( $arr_result ) < 1 )
			return false;
		else
			return $arr_result;
	}
	
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
	
	function getLapsedHoldingTimeSeats( $eventID, $showtimeID )
	{
		/**
		*	@created 08MAR2012-1214
		*	@revised 23JUN2012-1433 Instead of using MySQL's CURRENT_TIMESTAMP constant, we substituted it for
			getting the time via PHP as this is much more fool-proof regarding hosting server's time differences.
		**/
		date_default_timezone_set('Asia/Manila');
		$sql_command = "SELECT * FROM `seats_actual` WHERE `EventID` = ?  AND `Showing_Time_ID` = ? AND";
		$sql_command .= " `Status` = -4 AND ? >= `Comments`";
		$arrayResult = $this->db->query( $sql_command, array( $eventID, $showtimeID, date("Y-m-d H:i:s") ) )->result();
		
		if( count( $arrayResult) === 0 )
			return false;
		else
			return $arrayResult;
		
	}//getLapsedHoldingTimeSeats(..)
	
	function getMasterSeatMapActualSeats( $uniqueID )
	{
		/**
		*	@created 28JAN2012-2226
		*	@description Gets all seat info of the seat map whose uniqueID was supplied, from `seats_default`
		*	@returns Array of MySQL result object.
		*/
		/* 
			We opted to not use '*' because by doing so, `UniqueID` would be included in the results,
			which we are avoiding since it will just consume memory space and involve processing time which are not necessary.
		*/
		$sql_command = "SELECT `Matrix_x`,`Matrix_y`,`Visual_row`,`Visual_col`,`Status`,`Comments` FROM `seats_default` WHERE `Seat_map_UniqueID` = ? ";
		$arrayResult = $this->db->query( $sql_command, array( $uniqueID ) )->result();
		
		return $arrayResult;
	}// getMasterSeatMapActualSeats

	function getSingleActualSeatData( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/**
		*	@created 13FEB2012-2009
		*	@description Gets a record from the table containing the actual seats used in an event.
		**/
		$sql_command = "SELECT * FROM `seats_actual` WHERE `Matrix_x` = ? AND `Matrix_y` = ? AND `EventID` = ? AND `Showing_Time_ID` = ? ";
		$arr_result = $this->db->query( $sql_command, Array( $matrix_x, $matrix_y, $eventID, $showtimeID ) )->result();	
		if( count( $arr_result ) > 0 )
			return $arr_result[0];
		else
			return false;
	}//getSingleActualSeatData

	function getSingleMasterSeatMapData( $uniqueID )
	{
		/**
		*	@created 28JAN2012-2222
		*	@description Simply gets a master seat map's info from table `seat_map`
		*	@returns MySQL result object since we only need one or BOOLEAN FALSE on non-existence.
		*/
		$sql_command = "SELECT * FROM `seat_map` WHERE `UniqueID` = ?";
		$arrayResult = $this->db->query( $sql_command, array( $uniqueID ) )->result();
		
		if( count($arrayResult) === 1 )		
			return $arrayResult[0];
		else
			return false;
	}// getSingleMasterSeatMap

	function getUsableSeatMaps( $requestedCapacity )
	{
		/**
		*	@created 20JAN2012-1200
		*	@description For the pull-down seat map selection in Create Event Step 5.
		*	@param $requestedCapacity - (int) minimum number of seats that a seat map should have. or if
								  (boolean) false, then we don't need to check usableCapacity.
		**/
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
	
	function getVisualRepresentation( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/**
		*	@created 14FEB2012-1822.
		*	@returns the seat visualization of the string form 
				"X-Y" where X is the row and Y is the column
		**/
		$seatObj = $this->getSingleActualSeatData( $matrix_x, $matrix_y, $eventID, $showtimeID );
		if( $seatObj === false )
			return false;
		else
			return ( $seatObj->Visual_row."-".$seatObj->Visual_col );
	}//getVisualRepresentation()
	
	function insertDefaultSingleSeatData( $seatMapUniqueID, $matrix_x, $matrix_y, $Visual_row, $Visual_col, $status, $comment )
	{
		/**
		*	@DEPRECATED 18JUL2012-1530
		*	@created 19JAN2012-1207
		*	@followup Should I initialize the parameters to be NULL? Should I still perform value checking for these parameters (for error-checking)?
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
	
	function isSeatAssignedToGuest( $matrix_x, $matrix_y, $eventID, $showtimeID, $testThisGuestUUID )
	{
		/**
		*	@created 08MAR2012-1331
		*	@description Checks if the seat is assigned to the guest in question.
		*	@remarks Though this should be in the seats.. but Assigned_To_User is in `event_slot`
		*	@returns BOOLEAN
		**/
		$sql_command = "SELECT *  FROM `event_slot` JOIN `seats_actual` ON `event_slot`.`EventID` = 
		`seats_actual`.`EventID` AND `event_slot`.`Showtime_ID` = `seats_actual`.`Showing_Time_ID` AND 
		`event_slot`.`Seat_x` =   `seats_actual`.`Matrix_x` AND `event_slot`.`Seat_y` =  
		`seats_actual`.`Matrix_y` WHERE  `event_slot`.`EventID` = ? AND `Showtime_ID` = ? AND 
		`event_slot`.`Seat_x` = ? AND `event_slot`.`Seat_y` = ? AND   `event_slot`.`Assigned_To_User` = ?;";
		$arr_result = $this->db->query( $sql_command, Array(
				$eventID, $showtimeID, $matrix_x, $matrix_y, $testThisGuestUUID
			)
		)->result();
		return ( count( $arr_result === 1 ) );
	}
	
	function isSeatAvailable( $matrix_x, $matrix_y, $eventID, $showtimeID )
	{
		/**
		*	@created 04MAR2012-1737
		* 	@returns An Array with the specified keys and values.
				- If the specified seat does not exist array is:			
					'boolean' => FALSE
					'throwException' = 'INVALID|NO-SUCH-SEAT-EXISTS'
				So therefore, to know what if a seat is not available, back in the calling function,
				the code should be like:
				
				$isSeatAvailableResult = $this->seat_model->isSeatAvailable( .. );
				if( $isSeatAvailableResult['boolean'] )
					// seat is available
				else
					if( $isSeatAvailableResult['throwException'] === NULL )
						// seat is not available
					else
						// error in operation, so far, only no such seat found.
					
		**/
		$returnThis = Array(
			'boolean' => TRUE,
			'throwException' => NULL
		);
		$sql_command = "SELECT `Status` FROM `seats_actual` WHERE `Matrix_x` = ? AND `Matrix_y` = ?";
		$sql_command .= " AND `EventID` = ? AND `Showing_Time_ID` = ?";
		$seatObj = $this->db->query( $sql_command, Array( $matrix_x, $matrix_y, $eventID, $showtimeID) )->result();
		if( count($seatObj) === 0 )
		{
			$returnThis[ 'boolean' ] = FALSE;
			$returnThis[ 'throwException' ] = 'INVALID|NO-SUCH-SEAT-EXISTS';
			return $returnThis;
		}
		$returnThis[ 'boolean' ] = ( intval( $seatObj[0]->Status) === 0 );
		return $returnThis;
	}//isSeatAvailable
	
	function isSeatInThisTicketClass( 
		$matrix_x, $matrix_y, $eventID, $showtimeID, $ticketClassGroupID, $ticketClassUniqueID
	)
	{
		/**
		*	@created 04MAR2012-2153
		**/
		$sql_command = "SELECT `Ticket_Class_GroupID`,`Ticket_Class_UniqueID` FROM `seats_actual` WHERE `Matrix_x` = ? AND `Matrix_y` = ?";
		$sql_command .= " AND `EventID` = ? AND `Showing_Time_ID` = ?";
		$seatObj = $this->db->query( $sql_command, Array( $matrix_x, $matrix_y, $eventID, $showtimeID) )->result();
		if( count($seatObj) === 0 )  return FALSE;
		return(
			intval($seatObj[0]->Ticket_Class_GroupID) === $ticketClassGroupID and
			intval($seatObj[0]->Ticket_Class_UniqueID) === ticketClassUniqueID
		);
	}// isSeatInThisTicketClass( ..)
	
	function isSeatMapNameExistent( $name = NULL )
	{
		/**
		*	@created 19JAN2012-1118
		*	@description Checks if a string submitted is the name of some `seat_map` entry.
		**/
		if( $name == NULL ) return false;

		$this->db->where('Name', $name );
		$query = $this->db->get('seat_map');

		// if there was one cell retrieved, then such seat map with the UniqueID exists
		return ( $query->num_rows == 1 );
	}//isSeatMapNameExistent
	
	function make_array_visualSeatData( $guestObj, $visualSeatData )
	{
		/**
		*	@created <June 2012>
		*	@description visualizes seat data in a booking C-O-S.
		**/
		$vsd_tokenized = explode( '.', $visualSeatData );
		$returnThis = Array();
		$x = 0;
		foreach( $vsd_tokenized as $value )
		{
			log_message("DEBUG",'seat_model::make_array_visualSeatData seat value : ' . $value );
			$returnThis[ $guestObj[ $x++ ]->UUID ] = ( strval($value) === "0" or strval($value) === "FALSE" ) ? "NONE" : $value;
		}
		return $returnThis;
	}
	
	function markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, $status, $comment = "", 
		$ticketClassGroupID = NULL, $ticketClassUniqueID= NULL
	)
	{
		/**
		*	@created <i forgot, maybe February 2012>
		*	@description Unified function for updating an actual seat's status.
		*	@returns BOOLEAN Whether MySQL transaction is successful or not.
		**/
		$sql_command = "UPDATE `seats_actual` SET `Status` = ?, `Comments` = ? WHERE `EventID` = ? AND `Showing_Time_ID` = ? ";
		$sql_command .= " AND `Matrix_x` = ? AND `Matrix_y` = ?";
		$parameters = Array( $status, $comment, $eventID, $showtimeID, $matrix_x, $matrix_y );

		return $this->db->query( $sql_command, $parameters );
	}
	
	function markSeatAsAssigned( $eventID, $showtimeID, $matrix_x, $matrix_y, 
		$ticketClassGroupID = NULL, $ticketClassUniqueID= NULL
	)
	{	
		/**
		*	@created 14JAN2012-0909
		*	@revised 12MAR2012-1704 - Added last two params
		*	@returns See $this->markSeat_Unified(..)
		**/
		return $this->markSeat_Unified(
			$eventID, $showtimeID, $matrix_x, $matrix_y, 1, $ticketClassGroupID, $ticketClassUniqueID
		);
	}//markSeatAsAssigned
	
	function markSeatAsAvailable( $eventID, $showtimeID, $matrix_x, $matrix_y, $comment = "" )
	{	
		/**
		*	@created 14JAN2012-0909
		*	@returns See $this->markSeat_Unified(..)
		**/
		return $this->markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, 0, $comment );
	}//markSeatAsAssigned
	
	function markSeatAsPendingPayment( $eventID, $showtimeID, $matrix_x, $matrix_y, $resetOnTimestamp )
	{
		/**
		*	@created 08MAR2012-1135
		*	@description This effectively makes a seat seen as 'occupied' by others. 
			In the database, `status` = -4, and the deadline so that the `status` can be changed to 1
			is written on the `comment` column, on the form of "YYYY-MM-DD HH:MM:SS" where obviously..
			:D
		*	@returns See $this->markSeat_Unified(..)
		**/
		return $this->markSeat_Unified( $eventID, $showtimeID, $matrix_x, $matrix_y, -4, $resetOnTimestamp );
	}//SeatAsPendingPayment
	
	function setSeatMapStatus( $seatMapUniqueID, $status, $status2 = NULL )
	{
		/**
		*	@created 19JAN2012-2333
		*	@description Made for the end of configuring of a seat map, but can be used on other occassions though.
		*	@param $status may take on: 'CONFIGURED', 'UNCONFIGURED', 'BEING_CONFIGURED', 'BEING_CREATED'
		*	@returns BOOLEAN Whether MySQL transaction is successful or not.
		*	@revised 07JUL2012-1622
		*/
		return $this->db->query(
				"UPDATE `seat_map` SET `Status` = ?,`Status2`=? WHERE `UniqueID` = ? ",
				Array(
					$status,
					$status2,
					$seatMapUniqueID
				)
		);
	}// setSeatMapStatus
	
	function updateNewlyCopiedSeats( $eventID, $showtimeID )
	{
		/**
		*	@created 04FEB2012-1421
		*	@description Function to be called after $this->copyDefaultSeatsToActual(..). Since the newly copied entries' 
			`EventID` and `Showing_Time_ID` are both set to zero, we update them to reflect the proper values,
			as submitted to the server.
		*	@returns BOOLEAN Whether MySQL transaction is successful or not.
		*/
		$sql_command = "UPDATE `seats_actual` SET `EventID` = ?, `Showing_Time_ID` = ? WHERE `EventID` = '0' AND `Showing_Time_ID` = '0'";
		
		return $this->db->query( $sql_command, array( $eventID, $showtimeID ) );
	}//updateNewlyCopiedSeats(..)
	
	function updateSeatMapUsableCapacity( $uniqueID, $usableSeats )
	{
		/**
		*	@created 20JAN2012-1130
		*	@returns BOOLEAN Whether MySQL transaction is successful or not.
		**/
		return $this->db->query(
			"UPDATE `seat_map` SET `UsableCapacity` = ? WHERE `UniqueID` = ?",
			Array( $usableSeats, $uniqueID ) 
		);
	}//updateSeatMapUsableCapacity

	function updateSingleSeatComment( $comments, $eventID, $showtimeID, $x, $y )
	{
		/**
		*	@created 01JUL2012-1554
		*	@description  Just updates the comment field of an entry in `seats_actual`.
						Arose due to the need to set in that field/column the expiration date of the holding
						time for that seat, notwithstanding the lapse of the payment deadline for the booking
						that holds the seat.
		*	@returns BOOLEAN Whether MySQL transaction is successful or not.
		**/
		return $this->db->query(
			"UPDATE `seats_actual` SET `Comments` = ? WHERE `EventID` = ? AND `Showing_Time_ID` = ? AND `Matrix_x` = ? AND `Matrix_y` = ?",
			Array(
				$comments, $eventID, $showtimeID, $x, $y
			)
		);
	}//updateSingleSeatComment	
}//class