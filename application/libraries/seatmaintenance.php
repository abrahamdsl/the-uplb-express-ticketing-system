<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Seat Maintenance Library
* 	Created late 01APR2012-1010
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Sari-saring seat methods.
*/
class seatmaintenance{
	var $CI;

	function __construct( $params = NULL )
    {
		$this->CI = & get_instance();
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('event_model');
		$this->CI->load->model('Guest_model');
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('seat_model');
		$this->CI->load->model('slot_model');
		$this->CI->load->model('ticketclass_model');
    }

	private function getRedirectionCommon_SeatCR( $customMsg )
	{
		/**
		*	@created 07JUL2012-1643	
		*	@description Serves the array needed for loading a view page.
		**/
		return Array(
			'error' => "CUSTOM",
			'defaultAction' => 'Create Seat',
			'redirect' => 2,
			'redirectURI' => base_url().'seatctrl/create',
			'theMessage' => $customMsg
		);
	}

	private function getRedirectionCommon_SeatMapMgmt( $customMsg )
	{
		/**
		*	@created 07JUL2012-1643	
		*	@description Serves the array needed for loading a view page.
		**/
		return Array(
			'error' => "CUSTOM",
			'defaultAction' => 'Seat Maps',
			'redirect' => 2,
			'redirectURI' => base_url().'seatctrl/manageseatmap',
			'theMessage' => $customMsg
		);
	}

	function assembleCreateDefaultSeatFail()
	{
		/**
		*	@created 07JUL2012-1648
		**/
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
				"error", 
				"error", 
				"CPROPER_DB_ERR",
				0, 
				"Failed to create default seats. Please try resubmitting.",
				""
		);
		return FALSE;
	}//assembleCreateDefaultSeatFail()

	function assembleGenericDBFail()
	{
		/**
		*	@created 07JUL2012-1630	
		**/
		return $this->getRedirectionCommon_SeatCR( "Seat Map Creation Fail - Database Error." );
	}//assembleGenericDBFail

	function assembleGenericFormValidationFail()
	{
		/**
		*	@created 07JUL2012-1630	
		**/
		return $this->getRedirectionCommon_SeatCR( "Invalid input values, are you trying to hack the app by circumventing the JS check?" );
	}//assembleGenericFormValidationFail
	
	function assembleSeatMapDeletionFail()
	{
		/**
		*	@created 07JUL2012-1630
		**/
		$msg = "Something went wrong while processing the deletion of the seat map. It may have been not deleted. <br/><br/>Please try again.";
		return $this->getRedirectionCommon_SeatMapMgmt( $msg );
	}//assembleSeatMapDeletionFail()

	function assembleSeatMapDeletionOK( $is_inner_use = FALSE )
	{
		/**
		*	@created 07JUL2012-1630
		**/
		$msg = $is_inner_use ? "Seat map creation successfully cancelled." : "The seat map has been successfully deleted."; 
		return $this->getRedirectionCommon_SeatMapMgmt( $msg );
	}//assembleSeatMapDeletionOK()

	function assembleSeatMapNameExists()
	{
		/**
		*	@created 07JUL2012-1630	
		**/
		return $this->getRedirectionCommon_SeatCR( "Seat Map Name exists. Please choose another one." );
	}//assembleSeatMapNameExists

	function assembleSeatUID404()
	{
		/**
		*	@created 07JUL2012-1630	
		**/		
		echo $this->CI->makexml_model->XMLize_AJAX_Response(
			"error", 
			"error", 
			"SMAPUID404",
			0, 
			"Cannot find seat map specified! Did you just accidentally erase it?<br/><br/>You will be redirected to seat maps in 5 seconds.",
			"seatctrl/manageseatmap",
			5000
		);
		return FALSE;
	}//assembleSeatUID404

	function assembleSeatToDelete404()
	{
		/**
		*	@created 07JUL2012-1648
		**/
		return $this->getRedirectionCommon_SeatMapMgmt( "Nothing to delete!");
	}

	function areSeatsOccupied( $seat_assignments, $eventID, $showtimeID )
	{
		/**
		*	@created 11JUN2012-1201
		*	@description Checks if the seats specified by the coordinates
				are occupied/not available for selection.
		*	@param  $seat_assignments ARRAY ASSOCIATIVE 2D
					indices are guest numbers - 1 ( zero-indexing ).
					Then second dimension has indices:
						- uuid : guest's UUIDs
						- x    : seat matrix X coordinate
						- y    : seat matrix Y coordinate
						- old_st : Array(
								- x : old seat matrix X coordinate
								- y : old seat matrix Y coordinate
						)
		*	@history This is formerly seatctrl/areSeatsOccupied
		*	@returns Array with indices
					 0 : BOOLEAN, if FALSE - no seats are occupied. Else, there is or an error
					 1 : For use only if index 0 is TRUE: specifies what occurred
					 2 : Expounds on index 1
		**/		
		$slots = count( $seat_assignments );
		
		if( $slots  < 1 ) return Array( TRUE, "INVALID", "DATA" );
		for( $x = 0; $x < $slots; $x++ )
		{
			/* if either seat matrix coordinate is -1, this means no seat 
				is selected by guest so there's no point in checking if it is available
				or not
			*/
			if( $seat_assignments[ $x ][ 'x' ] == "-1" or $seat_assignments[ $x ][ 'y' ] == "-1" ) continue;
			if( isset( $seat_assignments[ $x ][ "old_st" ] ) )
			{
				// If submitted seat is the same as the old seat, skipped for availability check.
				if( $seat_assignments[ $x ][ 'x' ] == $seat_assignments[ $x ][ "old_st" ][ "x" ]
					and $seat_assignments[ $x ][ 'y' ] == $seat_assignments[ $x ][ "old_st" ][ "y" ] )
				{
					continue;
				}
			}
			$isSeatAvailableResult = $this->CI->seat_model->isSeatAvailable( 
				$seat_assignments[ $x ][ 'x' ],
				$seat_assignments[ $x ][ 'y' ],
				$eventID,
				$showtimeID
			);
			if( !$isSeatAvailableResult['boolean'] ){
				if( $isSeatAvailableResult['throwException'] === NULL ){
					return Array( TRUE, "OCCUPIED", $seat_assignments[ $x ][ 'x' ]."_".$seat_assignments[ $x ][ 'y' ] );
				}else{
				// error in operation, so far, only no such seat found.
					return Array( TRUE, "INVALID", $x );
				}
			}
		}
		return Array( FALSE, NULL, NULL );
	}//areSeatsOccupied(..)

	function arrayizeGuestNoSeatInfo()
	{
		/**
		*	@created 28JUN2012-1242
		*	@description Converts back the XML-ized guest-no-seat info to array.
		*	@returns Array
		*		index 0 - BOOLEAN indicator of whether success or not
		*		index 1 - if index 0 is FALSE, string containing the error info
		*				  else, the Array we want
		**/
		$xmlfile = $this->CI->clientsidedata_model->getGuestNoSeatXMLFile();
		if( $xmlfile === FALSE ) return Array( FALSE, 'FILEPTR404' );
		$xml_contents = $this->CI->makexml_model->readXML( $xmlfile );
		if( $xml_contents === FALSE ) return Array( FALSE, 'FILE404' );
		return Array( TRUE, $this->CI->makexml_model->toArray_prep( $xml_contents) );
	}//arrayizeGuestNoSeatInfo()

	function cleanDefaultedSeats( $eventID, $showtimeID )
	{
		/*
			Determine if there are seats marked as 'pending-payment' ( `Status` = -4 )
			that lapsed the payment period for it to be 'confirmed' (`Status` = 1 ).
			If there are, make it available.
		*/
		$lapsedSeatsArray = $this->CI->seat_model->getLapsedHoldingTimeSeats( $eventID, $showtimeID );
		if( $lapsedSeatsArray == false ) return false;
		foreach( $lapsedSeatsArray as $singleSeatObj )
		{
			 $this->CI->seat_model->markSeatAsAvailable( 
				$eventID, 
				$showtimeID, 
				$singleSeatObj->Matrix_x, 
				$singleSeatObj->Matrix_y,
				"HOLDING_PERIOD_LAPSED" 
			);
		}
	}//cleanDefaultedSeats(..)

	function getAllGuestSeatData( $slots, $currentBookingInfo, $bookingInfo, &$seat_assignments )
	{
	/**
	*	@created 22JUN2012-1451
	*	@description Gets guests' seat data depending on the slots assigned to them.
			For Manage Booking uses only.
	*	@remarks The parameter $seat_assignments is directly manipulated back in the
			function since it is passed to here by reference!
	*	@history moved from eventctrl/book_step5::area#bookstep5_pr_seat_finally_assign_db
	*	@calledby eventctrl/book_step5; eventctrl/cancelBookingProcess
	**/
		for( $x = 0; $x < $slots; $x++ )
		{
			$slot_old_st;
			$slot_new_st;
			if( isset( $currentBookingInfo ) or !is_null( $currentBookingInfo) ){
				$slot_old_st = $this->CI->slot_model->getSlotAssignedToUser_MoreFilter( 
					$currentBookingInfo->EVENT_ID,
					$currentBookingInfo->SHOWTIME_ID,
					$currentBookingInfo->TICKET_CLASS_GROUP_ID,
					$currentBookingInfo->TICKET_CLASS_UNIQUE_ID,
					$seat_assignments[ $x ][ "uuid" ]
				);
			}
			if( isset( $bookingInfo ) or !is_null( $bookingInfo) ){
				$slot_new_st = $this->CI->slot_model->getSlotAssignedToUser_MoreFilter(
					$bookingInfo->EVENT_ID,
					$bookingInfo->SHOWTIME_ID,
					$bookingInfo->TICKET_CLASS_GROUP_ID,
					$bookingInfo->TICKET_CLASS_UNIQUE_ID,
					$seat_assignments[ $x ][ "uuid" ]
				);
			}
			if( !isset( $slot_old_st ) or is_null($slot_old_st->Seat_x) or is_null($slot_old_st->Seat_y) )
			{
				$seat_assignments[ $x ][ "old_st" ]= FALSE;
			}else{
				$seat_assignments[ $x ][ "old_st" ][ "x" ] = $slot_old_st->Seat_x;
				$seat_assignments[ $x ][ "old_st" ][ "y" ] = $slot_old_st->Seat_y;
			}
			if( !isset( $slot_new_st ) or is_null($slot_new_st->Seat_x) or is_null($slot_new_st->Seat_y) )
			{
				$seat_assignments[ $x ][ "new_st" ]= FALSE;
			}else{
				$seat_assignments[ $x ][ "new_st" ][ "x" ] = $slot_new_st->Seat_x;
				$seat_assignments[ $x ][ "new_st" ][ "y" ] = $slot_new_st->Seat_y;
			}
		}//for
	}//getAllGuestSeatData(..)

	function getSeatRepresentationsOfGuests( $eventID, $showtimeID, $guest_arr,
		$ticketClassGroupID = NULL, $ticketClassUniqueID = NULL
	)
	{
		/**
		*	@created 03MAR2012-1147
		*	@description Gets seat representations of guests.
		*	@history 11MAR2012-1441 Added params $ticketClassGroupID, $ticketClassUniqueID
		*	@history 28JUN2012-1438 Moved from eventctrl
		*	@returns Array, structure:
				{ "uuid" -> Array2ndDim, "uuid" -> Array2ndDim }
			 Structure of Array2ndDim:
				Array(
					'matrix_x' => <INT> or "",
					'matrix_y' => <INT> or "",
					'visual_rep' =>  "X-Y" or "NONE" or (boolean) FALSE
				)
		**/
		$seatDetailsOfGuest = Array();
		foreach( $guest_arr as $singleGuest )
		{
			$seatVisualRepStr = "NONE";
			$seatMatrixRepObj = false;

			if( $ticketClassGroupID != NULL and $ticketClassUniqueID != NULL )
			{
				 $slotObj = $this->CI->slot_model->getSlotAssignedToUser_MoreFilter(
					 $eventID, 
					 $showtimeID,
					 $ticketClassGroupID, 
					 $ticketClassUniqueID,
					 $singleGuest->UUID 
				);
				log_message('debug', "seatmaintenance::getSeatRepresentationsOfGuests slotObj . " . print_r( $slotObj , TRUE ) );
				if( $slotObj === FALSE ){
					$seatMatrixRepObj = FALSE;
				}else{
					$seatMatrixRepObj = Array(
						'matrix_x' => (is_null ($slotObj->Seat_x) ) ? "" : $slotObj->Seat_x,
						'matrix_y' => (is_null ($slotObj->Seat_y) ) ? "" : $slotObj->Seat_y
					);
				}
			}else{
				$seatMatrixRepObj = $this->CI->slot_model->getSeatAssignedToUser( $singleGuest->UUID );
			}
			if( $seatMatrixRepObj !== false ){	// there is seat assigned for this user
				if( !(is_null($slotObj->Seat_x) or is_null($slotObj->Seat_y) ) )
				{
					$seatVisualRepStr = $this->CI->seat_model->getVisualRepresentation(
						$seatMatrixRepObj['matrix_x'],
						$seatMatrixRepObj['matrix_y'],
						$eventID,
						$showtimeID
					);
				}
			}
			$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(
				'matrix_x' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['matrix_x'] : ""  ),
				'matrix_y' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['matrix_y'] : ""  ),
				'visual_rep' =>  $seatVisualRepStr
			);
		}
		return $seatDetailsOfGuest;
	}//getSeatRepresentationsOfGuests(..)

	function insertSeatsOnEventManipulate( $eventID, $showtimeID, $tcgID, $seatmapUID, $createEvent = true, $tcgChanged = true )
	{
		$ticketClasses = NULL;
		// get the ticket classes of the events being configured
		$ticketClasses_obj = $this->CI->ticketclass_model->getTicketClasses( $eventID,  $tcgID );
		if( $createEvent ){
			//update the seat map of the showing time
			$this->CI->event_model->setShowingTimeSeatMap( $seatmapUID, $eventID, $showtimeID );
			// duplicate seat pattern to the table containing actual seats
			$this->CI->seat_model->copyDefaultSeatsToActual( $seatmapUID, $eventID,  $showtimeID );
			// turn the previously retrieved ticket classes into an array accessible by the class name
		}
		$ticketClasses = $this->CI->ticketclass_model->makeArray_NameAsKey( $ticketClasses_obj );
		// get seat map object to access its rows and cols, for use in the loop later
		$seatmap_obj = $this->CI->seat_model->getSingleMasterSeatMapData( $seatmapUID );
		/*
			Now, update data for each seat.
		*/
		for( $x = 0; $x < $seatmap_obj->Rows; $x++)
		{
			for( $y = 0; $y < $seatmap_obj->Cols; $y++)
			{
				$seatValue = $this->CI->input->post( 'seat_'.$x.'-'.$y );
				$status;
				$ticketClassUniqueID;

				$sql_command = "UPDATE `seats_actual` SET `Status` = ? ";
				$sql_command_End = "WHERE `EventID` = ? AND `Showing_Time_ID` = ? AND `Matrix_x` = ? AND `Matrix_y` = ?";
				if( $seatValue === "0" or $seatValue === false )
				{
					// aisle
					$status = -2;
					$this->CI->db->query( 	$sql_command.$sql_command_End, array(
											$status,
											$eventID,
											$showtimeID,
											$x,
											$y
										)
					);
				}else if( $seatValue === "-1" )
				{
					// no class assigned
					$status = -1;
					$this->CI->db->query( 	$sql_command.$sql_command_End, array(
											$status,
											$eventID,
											$showtimeID,
											$x,
											$y
										)
					);
				}else if( $seatValue === "0" )
				{
					//no action
				}else{	// contains class in string
					$status = 0;
					$ticketClassUniqueID = $ticketClasses[ $seatValue ]->UniqueID;
					$this->CI->db->query( 	$sql_command.", `Ticket_Class_GroupID` = ?, `Ticket_Class_UniqueID` = ? ".$sql_command_End,
										Array(
											$status,
											$tcgID,
											$ticketClassUniqueID,
											$eventID,
											$showtimeID,
											$x,
											$y
										)
					);
				}
			}
		}//for
		return true;
	}// insertSeatsOnEventManipulate()
}//class