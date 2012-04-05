<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
*	Sari-saring seat methods.
*
*
*
*/

class SeatMaintenance{	
	var $CI;
	
	public function __construct( $params = NULL )
    {			
		$this->CI = & get_instance();
		$this->CI->load->model('clientsidedata_model');
		$this->CI->load->model('Event_model');				
		$this->CI->load->model('Guest_model');				
		$this->CI->load->model('Seat_model');				
		$this->CI->load->model('Slot_model');				
		$this->CI->load->model('TicketClass_model');				
    }
	
	public function cleanDefaultedSeats( $eventID, $showtimeID )
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
	
	public function getExistingSeatData_ForManageBooking( $guestsObjOrBookingNum, $eventID, $showtimeID, $isTicketClassChanged )
	{
		$seatDetailsOfGuest = Array();
		$guestsObj = ( is_array( $guestsObjOrBookingNum) ) ? $guestsObjOrBookingNum : $this->CI->Guest_model->getGuestDetails( $guestsObjOrBookingNum );
				
		foreach( $guestsObj as $singleGuest )
		{
			/*
				Iterate through each guest to determine seat data for use in the view.
			*/
			$seatVisualRepStr = false;
			$seatMatrixRepObj = false;
			
			$seatMatrixRepObj = $this->CI->Slot_model->getSeatAssignedToUser( $singleGuest->UUID );
			if( $seatMatrixRepObj !== false ){		// there is seat assigned for this user
				$seatVisualRepStr = $this->CI->Seat_model->getVisualRepresentation(
					$seatMatrixRepObj['Matrix_x'],
					$seatMatrixRepObj['Matrix_y'],
					$eventID,
					$showtimeID
				);
			}
			/* For now, set if seat is available for this user. */
			$seatDetailsOfGuest[ $singleGuest->UUID ] = Array(					
				'available'  => ($seatMatrixRepObj !== false) ? TRUE: FALSE,
				'Matrix_x'   => ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_x'] : "" ,
				'Matrix_y'   => ($seatMatrixRepObj !== false) ? $seatMatrixRepObj['Matrix_y'] : "",
				'visual_rep' => ($seatMatrixRepObj !== false) ? $seatVisualRepStr : ""
			);				
			if($seatMatrixRepObj !== false){
				$seatAvailabilityCheck = $this->CI->Seat_model->isSeatAvailable( 
					$seatMatrixRepObj['Matrix_x'], 
					$seatMatrixRepObj['Matrix_y'],
					$eventID,
					$showtimeID
				);
				/*
					Further checks and sets depending on whether guest
					changed ticket class or seat is not in the new class.
				*/
				if( ( $this->CI->Seat_model->isSeatInThisTicketClass( 
							$seatMatrixRepObj['Matrix_x'], 
							$seatMatrixRepObj['Matrix_y'],
							$eventID, 
							$showtimeID, 
							$this->CI->clientsidedata_model->getTicketClassGroupID(),
							$this->CI->clientsidedata_model->getTicketClassUniqueID()
						) and
						$seatAvailabilityCheck['boolean'] === true
					)=== FALSE
				){
					if( $isTicketClassChanged or $this->CI->Seat_model->isSeatAssignedToGuest( 
													$seatMatrixRepObj['Matrix_x'], 
													$seatMatrixRepObj['Matrix_y'],
													$eventID, 
													$showtimeID, 
													$singleGuest->UUID
												) === false 
					){
						$seatDetailsOfGuest[ $singleGuest->UUID ]['available'] = false;
					}
				}				
			}//if($seatMatrixRepObj !== false){
		}//foreach guest
			
		return $seatDetailsOfGuest;
	}//getExistingSeatData_ForManageBooking(..)
	
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