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
		$this->CI->load->model('Guest_model');				
		$this->CI->load->model('Seat_model');				
		$this->CI->load->model('Slot_model');				
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
	
}