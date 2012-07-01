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
class SeatMaintenance{	
	var $CI;
	
	public function __construct( $params = NULL )
    {			
		$this->CI = & get_instance();
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('Event_model');				
		$this->CI->load->model('Guest_model');				
		$this->CI->load->model('MakeXML_model');				
		$this->CI->load->model('Seat_model');				
		$this->CI->load->model('Slot_model');				
		$this->CI->load->model('TicketClass_model');				
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
		*	@history This is formerly SeatCtrl/areSeatsOccupied
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
			$isSeatAvailableResult = $this->CI->Seat_model->isSeatAvailable( 
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
		$xml_contents = $this->CI->MakeXML_model->readXML( $xmlfile );
		if( $xml_contents === FALSE ) return Array( FALSE, 'FILE404' );
		return Array( TRUE, $this->CI->MakeXML_model->toArray_prep( new SimpleXMLElement($xml_contents) ) );
	}//arrayizeGuestNoSeatInfo()
	
	function cleanDefaultedSeats( $eventID, $showtimeID )
	{
		/*
			Determine if there are seats marked as 'pending-payment' ( `Status` = -4 )
			that lapsed the payment period for it to be 'confirmed' (`Status` = 1 ).
			If there are, make it available.
		*/
		$lapsedSeatsArray = $this->CI->Seat_model->getLapsedHoldingTimeSeats( $eventID, $showtimeID );		
		if( $lapsedSeatsArray == false ) return false;	
		foreach( $lapsedSeatsArray as $singleSeatObj )
		{				
			 $this->CI->Seat_model->markSeatAsAvailable( 
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
	*	@remarks The parameter $seat_assignments is directly manipulated back in the
			function since it is passed to here by reference!
	*	@history moved from EventCtrl/book_step5::area#bookstep5_pr_seat_finally_assign_db
	*	@calledby EventCtrl/book_step5; EventCtrl/cancelBookingProcess
	**/
		for( $x = 0; $x < $slots; $x++ )
		{
			$slot_old_st = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter( 
				$currentBookingInfo->EVENT_ID,
				$currentBookingInfo->SHOWTIME_ID,
				$currentBookingInfo->TICKET_CLASS_GROUP_ID,
				$currentBookingInfo->TICKET_CLASS_UNIQUE_ID,
				$seat_assignments[ $x ][ "uuid" ]
			);
			$slot_new_st = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter(
				$bookingInfo->EVENT_ID,
				$bookingInfo->SHOWTIME_ID,
				$bookingInfo->TICKET_CLASS_GROUP_ID,
				$bookingInfo->TICKET_CLASS_UNIQUE_ID,
				$seat_assignments[ $x ][ "uuid" ]
			);
			if( is_null($slot_old_st->Seat_x) or is_null($slot_old_st->Seat_y) )
			{
				$seat_assignments[ $x ][ "old_st" ]= FALSE;
			}else{
				$seat_assignments[ $x ][ "old_st" ][ "x" ] = $slot_old_st->Seat_x;
				$seat_assignments[ $x ][ "old_st" ][ "y" ] = $slot_old_st->Seat_y;
			}
			if( is_null($slot_new_st->Seat_x) or is_null($slot_new_st->Seat_y) )
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
		*	@history 28JUN2012-1438 Moved from EventCtrl
		**/
		$seatDetailsOfGuest = Array();
		foreach( $guest_arr as $singleGuest )
		{
			$seatVisualRepStr = false;
			$seatMatrixRepObj = false;

			if( $ticketClassGroupID != NULL and $ticketClassUniqueID != NULL )
			{
				 $slotObj = $this->CI->Slot_model->getSlotAssignedToUser_MoreFilter(
					 $eventID, 
					 $showtimeID,
					 $ticketClassGroupID, 
					 $ticketClassUniqueID,
					 $singleGuest->UUID 
				);
				$seatMatrixRepObj = Array(
					'Matrix_x' => (is_null ($slotObj->Seat_x) ) ? "" : $slotObj->Seat_x,
					'Matrix_y' => (is_null ($slotObj->Seat_y) ) ? "" : $slotObj->Seat_y
				);
			}else{
				$seatMatrixRepObj = $this->CI->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );
			}
			if( $seatMatrixRepObj !== false ){	// there is seat assigned for this user
				if( is_null($slotObj->Seat_x) or is_null($slotObj->Seat_y) )
				{
					 $seatVisualRepStr = "NONE";
				}else{
					$seatVisualRepStr = $this->CI->Seat_model->getVisualRepresentation(
						$seatMatrixRepObj['Matrix_x'],
						$seatMatrixRepObj['Matrix_y'],
						$eventID,
						$showtimeID
					);
				}
			}
			$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(
				'matrix_x' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_x'] : ""  ),
				'matrix_y' => ( ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_y'] : ""  ),
				'visual_rep' =>  $seatVisualRepStr
			);
		}
		return $seatDetailsOfGuest;
	}//getSeatRepresentationsOfGuests(..)
	
	function insertSeatsOnEventManipulate( $eventID, $showtimeID, $tcgID, $seatmapUID, $createEvent = true, $tcgChanged = true )
	{
		$ticketClasses = NULL;
		// get the ticket classes of the events being configured
		$ticketClasses_obj = $this->CI->TicketClass_model->getTicketClasses( $eventID,  $tcgID );
		if( $createEvent ){
			//update the seat map of the showing time
			$this->CI->Event_model->setShowingTimeSeatMap( $seatmapUID, $eventID, $showtimeID );
			// duplicate seat pattern to the table containing actual seats
			$this->CI->Seat_model->copyDefaultSeatsToActual( $seatmapUID );
			// update the eventID and UniqueID of the newly duplicated seats
			$this->CI->Seat_model->updateNewlyCopiedSeats( $eventID,  $showtimeID );
			// turn the previously retrieved ticket classes into an array accessible by the class name			
		}
		$ticketClasses = $this->CI->TicketClass_model->makeArray_NameAsKey( $ticketClasses_obj );
		// get seat map object to access its rows and cols, for use in the loop later
		$seatmap_obj = $this->CI->Seat_model->getSingleMasterSeatMapData( $seatmapUID );
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
										array(
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
	

}