<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
*	Function Access Eligibility Check library
* 	Created late March 2012
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Important! In the Controller where this is declared, the constants
*   found throughout this file should have been declared earlier in the file.
*
*	Validations if it is alright that user is in this function of a controller.
*/

class FunctionAccess{
	var $sessionActivity_x;
	var $CI;
	
	public function __construct()
    {
		$this->CI = & get_instance();
		$this->CI->load->model('clientsidedata_model');
		
        $this->__reinit();
    }
	
	public function __reinit(){
		$this->sessionActivity_x =  $this->CI->clientsidedata_model->getSessionActivity();
	}
	
	public function isActivityBookingTickets()
	{
		return ( $this->sessionActivity_x[0] == BOOK );
	}
	
	public function isActivityConfirmBooking()
	{		
		return ( $this->sessionActivity_x[0] == CONFIRM_RESERVATION );		
	}
	
	public function isActivityManageBooking()
	{
		return ( $this->sessionActivity_x[0] == MANAGE_BOOKING );		
	}
	
	public function isActivityManageBookingAndChooseSeat()
	{				
		return ($this->isActivityManageBooking() and $this->sessionActivity_x[1] == STAGE_BOOK_4_FORWARD );
	}
	
	public function preBookCheckAJAXUnified( $checkArraysBool, $outputError = true, $stage )
	{
		$notAllowed    = "ERROR_You are not allowed to access this functionality/page."; // 4100
		$notAllowedYet = "ERROR_You are not allowed in this stage yet."; //4102
		$redirect	   = "REDIRECT_to stage ".$this->sessionActivity_x[1]; //3100
		$crucialData   = "ERROR_Crucial data missing."; //4002
		
		if( $this->isActivityBookingTickets() or $this->isActivityManageBooking() or $this->isActivityConfirmBooking() )
		{			
			if( count( $checkArraysBool ) === 0 or !in_array( false, $checkArraysBool ) )
			{
				if( $this->sessionActivity_x[1] < $stage )
				{
					if( $outputError )
					{
						 echo $notAllowedYet;
						 return false;
					 }else{
						return $notAllowedYet;
					 }
				}else
				if( $this->sessionActivity_x[1] > $stage )
				{					
					if( $outputError )
					{
						echo $redirect;
						return false;
					 }else{
						return $redirect;
					 }
				}else{
					return true;
				}
			}else{				 
				 if( $outputError )
				 {
					 echo $crucialData;
					 return false;
				 }else{
					return $crucialData;
				 }
			}
		}else{
			 if( $outputError )
			 {
				 echo $notAllowed;
				 return false;
			 }else{
				return $notAllowed;
			 }
		}
	}//preBookCheckAJAXUnified
	
	public function preBookCheckUnified( $checkArraysBool, $stage )
	{
		
		if( $this->isActivityBookingTickets() or $this->isActivityManageBooking() or $this->isActivityConfirmBooking() )
		{				
			if( count( $checkArraysBool ) === 0 or !in_array( false, $checkArraysBool, true ) )
			{
				if( $this->sessionActivity_x[1] < $stage )
				{
					 $data['error'] = "CUSTOM";
					 $data['theMessage'] = "You are not allowed in this stage yet."; //4102
					 $this->CI->load->view( 'errorNotice', $data );
					 return false;
				}else
				if( $this->sessionActivity_x[1] > $stage )
				{
					$this->redirectBookForward();
				}else{
					return true;
				}
			}else{
				$data['error'] = "CUSTOM";
				 $data['theMessage'] = "Crucial data missing in accessing this page or you are not allowed yet to be here."; //4102
				 $this->CI->load->view( 'errorNotice', $data );				 
				 return false;
				 
			}
		}else{
			 redirect('EventCtrl/book');
			 return false;
		}
	}//preBookCheckUnified
	
	public function preBookStep2Check( $eventID, $showtimeID, $slots, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $slots ), $stage );
	}
	
	public function preBookStep2FWCheck( $eventID, $showtimeID, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID ), $stage );
	}
	
	public function preBookStep3PRCheck( $eventID, $showtimeID, $TC_GID, $TC_UID, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $TC_GID, $TC_UID ), $stage );
	}
	
	public function preBookStep3FWCheck( $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage );
	}
	
	public function preBookStep4PRCheck( $eventID, $showtimeID, $TC_GID, $TC_UID, $slots, $g_uuid, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $TC_GID, $TC_UID, $slots, $g_uuid ), $stage );
	}
	
	public function preBookStep4FWCheck( $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage );
	}
	
	public function preBookStep5PRCheck( $eventID, $showtimeID, $slots, $bookingNumber, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $slots, $bookingNumber ), $stage );
	}
	
	public function preBookStep5FWCheck( $eventID, $showtimeID, $slots, $bookingNumber, $totalCharges, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $slots, $bookingNumber, $totalCharges ), $stage );
	}
	
	public function preBookStep6PRCheck( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots ), $stage );
	}
	
	public function preBookStep6FWCheck( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $bookingNumber, $totalCharges, $paymentChannel, $slots ), $stage );
	}
	
	public function preCancelBookingProcesss( $eventID, $tcGID, $stage  )
	{
		return $this->preBookCheckUnified( Array( $eventID, $tcGID ), $stage );
	}
	
	public function preConfirmStep2FWCheck( $bNumber, $stage )
	{
		return $this->preBookCheckUnified( Array( $bNumber ), $stage );
	}
	
	public function preConfirmStep3PRCheck( $bNumber, $accountNum, $stage )
	{
		return $this->preBookCheckAJAXUnified( Array( $bNumber, $accountNum ), true, $stage );
	}
	
	private function redirectBookForward()
	{
		/*
			You have to change redirection address if you change function names
			in EventCtrl, and EventCtrl itself.
		*/
		
		switch( intval( $this->sessionActivity[1] ) )
		{
			case STAGE_BOOK_1_PROCESS: redirect('EventCtrl/book'); break;
			case STAGE_BOOK_1_FORWARD: redirect('EventCtrl/book_forward'); break;
			case STAGE_BOOK_2_PROCESS: redirect('EventCtrl/book_step2'); break;
			case STAGE_BOOK_2_FORWARD: redirect('EventCtrl/book_step2_forward'); break;
			case STAGE_BOOK_3_PROCESS: redirect('EventCtrl/book_step3'); break;
			case STAGE_BOOK_3_FORWARD: redirect('EventCtrl/book_step3_forward'); break;
			case STAGE_BOOK_3_CLASS_1_FORWARD: redirect('EventCtrl/meow'); break;
			case STAGE_BOOK_3_CLASS_2_FORWARD: redirect('EventCtrl/meow2'); break;
			case STAGE_BOOK_4_PROCESS: redirect('EventCtrl/book_step4'); break;
			case STAGE_BOOK_4_FORWARD: redirect('EventCtrl/book_step4_forward'); break;
			case STAGE_BOOK_5_PROCESS: redirect('EventCtrl/book_step5'); break;
			case STAGE_BOOK_5_FORWARD: redirect('EventCtrl/book_step5_forward'); break;
			case STAGE_BOOK_6_PROCESS: redirect('EventCtrl/book_step6'); break;
			case STAGE_BOOK_6_PAYMENTPROCESSING: redirect('paypal/process'); break;
			case STAGE_BOOK_6_FORWARD: redirect('EventCtrl/book_step6_forward'); break;
			default: die("INTERNAL-SERVER-ERROR_I don't know where to redirect you."); //3999
		}
	}//redirectBookForward()
}//class