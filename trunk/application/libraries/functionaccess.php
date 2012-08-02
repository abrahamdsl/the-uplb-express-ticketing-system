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
		$this->CI->load->model('makexml_model');
		$this->CI->load->model('ndx_model');
		$this->CI->load->model('sat_model');
		
        $this->__reinit();
		include_once( APPPATH.'constants/_constants.inc');
    }
	
	private function reactOnShouldBeAdvanced( $outputError, $redirectXML, $stage, $updateCISession = FALSE, $delATCData = FALSE )
	{
		/**
		*	@created 14JUL2012-1504
		*	@description Outputs an XML string or SimpleXMLElement object of our AJAX response
				if a user is deemed to be moved to another page.
		*	@remarks Formerly part of preBookCheckAJAXUnified(), now called from within it.
		**/
		log_message('DEBUG', 'functionaccess::reactOnShouldBeAdvanced accessed');
		$sx_element = new SimpleXMLElement($redirectXML);
		$sx_element->redirect = $this->getRedirectionURL( $stage );
		if( $updateCISession ) $this->CI->clientsidedata_model->updateSessionActivityStage( $stage  );
		if( $delATCData ){
			// we also delete these GUIDs because the previous AJAX might have failed when
			//  server is returning the response headers that are already cleared of this
			$this->CI->clientsidedata_model->deleteAuthGUID();
			$this->CI->clientsidedata_model->delete_ATC_Guid();;
		}
		if( $outputError )
		{
			echo $sx_element->asXML();
			return false;
		 }else{
			return ( new SimpleXMLElement($redirect ) );
		 }
	}

	public function __reinit(){
		$this->sessionActivity_x =  $this->CI->clientsidedata_model->getSessionActivity();
	}
	
	public function isActivityAdminManagingUser()
	{
		return ( $this->sessionActivity_x[0] == ADMIN_MANAGE_USER );
	}
	
	public function isActivityAdminManagingPaymentMode()
	{
		return ( $this->sessionActivity_x[0] == ADMIN_MANAGE_PAYMENTMODE );
	}
	
	public function isActivityBookingTickets()
	{
		return ( $this->sessionActivity_x[0] == BOOK );
	}
	
	public function isActivityConfirmBooking()
	{		
		return ( $this->sessionActivity_x[0] == CONFIRM_RESERVATION );
	}
	
	public function isActivityManageAccount()
	{
		return ( $this->sessionActivity_x[0] == MANAGE_ACCOUNT );
	}
	
	public function isActivityManageBooking()
	{
		return ( $this->sessionActivity_x[0] == MANAGE_BOOKING );
	}
	
	public function isActivityManageSeatMap()
	{
		return ( $this->sessionActivity_x[0] == MANAGE_SEATMAP );
	}
	
	public function isActivityCreatingSeatMap()
	{
		return ( $this->sessionActivity_x[0] == SEAT_CREATE );
	}
	
	public function isActivityManageBookingAndChooseSeat()
	{	
		/*
			@DEPRECATED 16JUN2012-1410
		*/
		return ($this->isActivityManageBooking() and $this->sessionActivity_x[1] == STAGE_BOOK_4_FORWARD );
	}
	
	public function isChangingPaymentMode()
	{
		return ($this->CI->clientsidedata_model->getPaymentModeExclusion() !== FALSE );
	}
	
	function isIdle(){
		return( $this->sessionActivity_x[0] == 'IDLE' AND $this->sessionActivity_x[1] == -1 );
	}
	
	public function preBookCheckAJAXUnified( $checkArraysBool, $outputError = true, $stage, $bookingInfoObj, $stagePR = NULL )
	{
		/**
		*	With the introduction of $this->getRedirectionURL(.), we should remove underscore as our
		*   delimiter since there can be underscores in a URL.
		*	@returns if param $outputError
				- TRUE : outputs XML string to stdoutput first and then BOOLEAN
				- FALSE : simpleXMLElement object
		*/
		if( !$this->CI->input->is_ajax_request() ){
			echo "You need to access this via XMLHttpRequest/AJAX!"; //ec 4103
			return FALSE;
		}
		$notAllowed    = $this->CI->load->view("_stopcodes/4100.xml", '', TRUE);
		$notAllowedYet = $this->CI->load->view("_stopcodes/4103.xml", '', TRUE);
		$redirect	   = $this->CI->load->view("_stopcodes/3100.xml", '', TRUE);
		$crucialData   = $this->CI->load->view("_stopcodes/4002.xml", '', TRUE);
		$cns_404	   = $this->CI->load->view("_stopcodes/4800.xml", '', TRUE);
		$previousStill = $this->CI->load->view("_stopcodes/4900.xml", '', TRUE);

		if( $bookingInfoObj === FALSE )
		{			
			 if( $outputError )
			{
				 echo $cns_404;
				 return false;
			 }else{
				return ( new SimpleXMLElement($cns_404) );
			 }
		}
		if( $this->isActivityBookingTickets() or $this->isActivityManageBooking() or $this->isActivityConfirmBooking()
  		    or $this->isActivityCreatingSeatMap() or $this->isActivityManageAccount() or $this->isActivityAdminManagingUser()
			or $this->isActivityAdminManagingPaymentMode()
		){
			if( count( $checkArraysBool ) === 0 or !in_array( false, $checkArraysBool ) )
			{
				if( $this->sessionActivity_x[1] < $stage )
				{
					if( $outputError )
					{
						 echo $notAllowedYet;
						 return false;
					 }else{
						return ( new SimpleXMLElement($notAllowedYet) );
					 }
				}else
				if( $this->sessionActivity_x[1] > $stage )
				{	
					 return $this->reactOnShouldBeAdvanced( $outputError, $redirect, $this->sessionActivity_x[1] );
				}else{
					$adv_check = $this->CI->sat_model->isOnDB_RecordAdvanced( 
						$this->CI->clientsidedata_model->getActivityGUID(),
						$this->sessionActivity_x[1] 
					);
					if( $adv_check[1] == 0 )
					{
						if( $adv_check[0] )
						{	
							if( !is_null( $stagePR ) ) {
								if( $stagePR == $adv_check[4] ){
									if( $outputError )
									 {
										 echo $previousStill;
										 return false;
									 }else{
										return ( new SimpleXMLElement( $previousStill ) );
									 }
								}
							}
							return $this->reactOnShouldBeAdvanced( $outputError, $redirect, $adv_check[4], TRUE, TRUE );
						}
					}else
					if( $adv_check[1] == -1 )
					{
						if( $outputError )
						 {
							 echo $notAllowed;
							 return false;
						 }else{
							return ( new SimpleXMLElement($notAllowed) );
						 }
					}
					return true;
				}
			}else{
				 if( $outputError )
				 {
					 echo $crucialData;
					 return false;
				 }else{
					return ( new SimpleXMLElement($crucialData) );
				 }
			}
		}else{
			 if( $outputError )
			 {
				 echo $notAllowed;
				 return false;
			 }else{
				return ( new SimpleXMLElement($notAllowed) );
			 }
		}
	}//preBookCheckAJAXUnified
	
	public function preManageBookCheckUnified( $checkArraysBool, $stage, $m_bookingInfoObj )
	{
		/**
		*	@created 14JUN2012-1334
		**/
		if( !$this->isActivityManageBooking() ){
			//ON-HOLD if( $this->sessionActivity_x[0] == IDLE ) return TRUE;	// idle too is okay.
			echo 'Your activity is not manage booking!';
			return FALSE;
		}
		if( $m_bookingInfoObj === FALSE ){
			echo 'M COOKIE ON SERVER NOT FOUND';
			return FALSE;
		}		
		$bookingInfoObj = ( $m_bookingInfoObj === TRUE ) ? TRUE : $this->CI->ndx_model->get( @$m_bookingInfoObj->CURRENT_UUID );
		return $this->preBookCheckUnified( $checkArraysBool, $stage, $bookingInfoObj );
	}

	public function preBookCheckUnified( $checkArraysBool, $stage, $bookingInfoObj )
	{
		if( $bookingInfoObj === FALSE )
		{
			 $data['error'] = "CUSTOM";
			 $data['theMessage'] = "Cookie data on server not found! You might still be not allowed to access this stage.";
			 $this->CI->load->view( 'errorNotice', $data );
			 return false;
		}
		if( $this->isActivityBookingTickets() or $this->isActivityManageBooking() or $this->isActivityConfirmBooking() 
			or $this->isActivityCreatingSeatMap() OR $this->isActivityAdminManagingUser() OR $this->isActivityAdminManagingPaymentMode()
		)
		{
			if( count( $checkArraysBool ) === 0 or !in_array( false, $checkArraysBool, true ) )
			{
				if( $this->sessionActivity_x[1] < $stage )
				{
					 $data['error'] = "CUSTOM";
					 $data['theMessage'] = "You are not allowed in this stage yet."; //4102
					 $data['redirect'] = 2;
					 $data['redirectURI'] = base_url().$this->getRedirectionURL( $this->sessionActivity_x[1] );
					 $data[ 'defaultAction' ] = "Earlier Stage";
					 $this->CI->load->view( 'errorNotice', $data );
					 return false;
				}else
				if( $this->sessionActivity_x[1] > $stage )
				{
					$this->redirectBookForward( $this->sessionActivity_x[1] );
				}else{
					return true;
				}
			}else{
				if( $this->sessionActivity_x[1] > $stage )
				{
					$this->redirectBookForward( $this->sessionActivity_x[1] );
				}else{
					$data['error'] = "CUSTOM";
					 $data['theMessage'] = "Crucial data missing in accessing this page or you are not allowed yet to be here."; //4102
					 $this->CI->load->view( 'errorNotice', $data );
					 return false;
				}
			}
		}else{
			 redirect('eventctrl/book');
			 return false;
		}
	}//preBookCheckUnified
	
	public function preBookStep2Check( $eventID, $showtimeID, $slots, $stage )
	{		
		return $this->preBookCheckUnified( Array( $eventID, $showtimeID, $slots ), $stage, true );
	}
	
	public function preBookStep2FWCheck( $bookingInfoObj, $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfoObj );
	}
	
	public function preBookStep3PRCheck( $bookingInfoObj, $ticketClassUniqueID, $stage )
	{		
		return $this->preBookCheckUnified( Array( $ticketClassUniqueID ), $stage, $bookingInfoObj );
	}
	
	public function preBookStep3FWCheck( $bookingInfoObj, $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfoObj );
	}
	
	public function preBookStep4PRCheck( $bookingInfo, $stage )
	{		
		return $this->preBookCheckUnified( Array( ), $stage, $bookingInfo );
	}
	
	public function preBookStep4FWCheck( $bookingInfo, $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfo );
	}
	
	public function preBookStep5PRCheck( $bookingInfo, $stage )
	{
		return $this->preBookCheckAJAXUnified( Array(), TRUE, $stage, $bookingInfo );
	}
		
	public function preBookStep5FWCheck( $bookingInfo, $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfo );
	}
	
	public function preBookStep6PRCheck( $paymentChannel, $bookingInfo, $stage )
	{		
		return $this->preBookCheckUnified( Array( $paymentChannel ), $stage, $bookingInfo );
	}
	
	public function preBookStep6PR_OnlinePayment_Check( $stage )
	{		
		return $this->preBookCheckUnified( Array( ), $stage, TRUE );
	}
	
	public function preBookStep6FWCheck( $bookingInfo, $stage )
	{		
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfo );
	}
	
	public function preCancelBookingProcesss( $eventID, $tcGID, $stage  )
	{
		return $this->preBookCheckUnified( Array( $eventID, $tcGID ), $stage );
	}
	
	function preConfirmStep2PRCheck( $bookingNum ){
		return $this->preBookCheckAJAXUnified( Array( $bookingNum ), TRUE, STAGE_CONFIRM_1_FORWARD, true );
	}
	
	public function preConfirmStep2FWCheck( $bookingInfo, $stage )
	{
		return $this->preBookCheckUnified( Array(), $stage, $bookingInfo );
	}
	
	public function preConfirmStep3PRCheck( $accountNum, $bookingInfo, $stage )
	{
		return $this->preBookCheckAJAXUnified( Array( $accountNum ), true, $stage, $bookingInfo );
	}
	
	public function preCreateSeatStep1PRCheck(){
		if( !( ($this->isActivityManageSeatMap() AND $this->sessionActivity_x[1] === STAGE_MS0_HOME) 
			  or $this->sessionActivity_x[1] === -1 ) 
		){
			$this->redirectBookForward( $this->sessionActivity_x[1] );
			return FALSE;
		}
		return TRUE;
	}
	
	public function preCreateSeatStep1FWCheck()
	{
		if( !($this->isActivityCreatingSeatMap() AND $this->sessionActivity_x[1] === STAGE_CR_SEAT1 ) ){
			$this->redirectBookForward( $this->sessionActivity_x[1] );
			return FALSE;
		}
		return TRUE;
	}
	
	public function preCreateSeatStep2PRCheck( $stage )
	{
		return $this->preBookCheckUnified( Array(), $stage, TRUE );
	}

	public function preCreateSeatStep2FWCheck( $stage )
	{
		return $this->preBookCheckUnified( Array(), $stage, TRUE );
	}
	
	public function preCreateSeatStep3PRCheck()
	{		
		//log_message('debug', 'functionaccess::preCreateSeatStep3PRCheck session ' . print_r( $this->CI->clientsidedata_model->getSessionActivity(),TRUE ) );
		if( $this->CI->clientsidedata_model->getSessionActivityStage() == STAGE_CR_SEAT3_PR )
		{
			echo "WAIT FOR THE OTHER SCRIPT TO FINISH!!!";
			return FALSE;
		}
		
		return $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_CR_SEAT2_FW, TRUE, STAGE_CR_SEAT3_PR );
	}
	
	function preManageBookingChangeSeatCheck( $bNum, $mbookingInfo , $allowedStages ){
		
		return $this->preManageBookCheckUnified( Array( $bNum ), $allowedStages, $mbookingInfo );
	}
	
	function preManageBookingCancelChanges( $mbookingInfo , $stage ){
		
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preManageBookingChangeShowtimeCheck( $mbookingInfo , $stage ){
		
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preManageBookingChangeShowtimePRCheck( $showtimeID, $mbookingInfo , $stage ){
		
		return $this->preManageBookCheckUnified( Array( $showtimeID ), $stage, $mbookingInfo );
	}
	
	function preManageBookingChangeShowtimePR2Check( $mbookingInfo , $stage ){
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preManageBookingCheck()
	{
		if( !($this->sessionActivity_x[0] == MANAGE_BOOKING or $this->sessionActivity_x[0] == IDLE)  ){
			if( !( $this->sessionActivity_x[1] == STAGE_BOOK_1_FORWARD 
					or $this->sessionActivity_x[1] == STAGE_MB1_SELECT_SHOWTIME_FW ) 
			){  // if when (managing a ) booking and in the event and showtime selection page only, then it's
				// just okay to access manage booking instead.
				$this->redirectBookForward( $this->sessionActivity_x[1] );
				return FALSE;
			}
		}
		return TRUE;
	}
	
	function preManageBookingChangePMode( $bookingNumber, $mbookingInfo, $stage ){
		return $this->preManageBookCheckUnified( Array( $bookingNumber ), $stage, $mbookingInfo );
	}
	
	function preManageBookingConfirm( $mbookingInfo , $stage ){
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preManageBookingFinalize( $paymentMode, $mbookingInfo , $stage ){
		return $this->preManageBookCheckUnified( Array( $paymentMode ), $stage, $mbookingInfo );
	}
	
	function preManageBookingFinalizeFW( $mbookingInfo, $stage ){
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preManageBookingPendingViewDetails( $bookingNumber, $mbookingInfo, $stage ){
		return $this->preManageBookCheckUnified( Array( $bookingNumber ), $stage, $mbookingInfo );
	}
	
	function preManageBookingNoSeatAllCheck( $stage ){
		return $this->preBookCheckUnified( Array(), $stage, TRUE );
	}
	
	function preManageBookingUpgTC_Check( $mbookingInfo , $stage ){
		return $this->preManageBookCheckUnified( Array( ), $stage, $mbookingInfo );
	}
	
	function preUseracctctrl__addpaymentmode(){
		$pre_allowed = Array(
			STAGE_MPAY_EDIT_1_FW, STAGE_MPAY_ADD_1_FW, 
			STAGE_MPAY_DEL_1_FW
		);
		if( in_array( $this->sessionActivity_x[1], $pre_allowed )
		) return TRUE;
		return $this->preBookCheckUnified( Array( ), STAGE_MPAY_1_FW, TRUE );
	}

	function preUseracctctrl__addpaymentmode_step2(){
		return $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MPAY_ADD_1_FW, TRUE, STAGE_MPAY_ADD_2_PR );
	}

	function preUseracctctrl__managepaymentmode_edit()
	{
		$pre_allowed = Array(
			STAGE_MPAY_EDIT_1_FW, STAGE_MPAY_ADD_1_FW, 
			STAGE_MPAY_DEL_1_FW
		);
		if( in_array( $this->sessionActivity_x[1], $pre_allowed )
		) return TRUE;
		return $this->preBookCheckUnified( Array( ), STAGE_MPAY_1_FW, TRUE );
	}
	
	function preUseracctctrl__managepaymentmode_save(){
		return $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MPAY_EDIT_1_FW, TRUE, STAGE_MPAY_ADD_2_PR );
	}
	
	function preUseracctctrl__changePassword_step2(){
		return ( $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MACCT0_HOME, TRUE, STAGE_MACCT1_PR )
		);
	}
	
	function preUseracctctrl__manageAccountSave(){
		ob_start();
		$result = $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MACCT0_HOME, TRUE, STAGE_MACCT1_PR );
		if( !$result ){
			ob_clean();
			$result = $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MU_2_FW, TRUE, STAGE_MACCT1_PR );
		}
		@ob_end_flush();
		flush();
		return $result;
	}
	
	function preUseracctctrl__managepaymentmode(){
		if( $this->isIdle() or $this->sessionActivity_x[1] == 1201 ) return TRUE;
		return $this->preBookCheckUnified( Array(), -1, TRUE );
	}
	
	function preUseracctctrl__manageuser(){
		if( $this->isIdle() ) return TRUE;
		echo 'You should not be here. Anyway, to admin, please turn on redirection service.';
		//return $this->preBookCheckUnified( Array(), -1, TRUE );
	}
	
	function preUseracctctrl__manageuser_editrole_save(){
		return $this->preBookCheckAJAXUnified( Array(), TRUE, STAGE_MU_2_FW, TRUE, STAGE_MACCT1_PR );
	}

	function preUseracctctrl__manageUser_step2(){
		
		return $this->preBookCheckUnified( Array(), STAGE_MU_2_FW, TRUE );
	}
	
	function preUseracctctrl__myAccountPR(){
		if( !( ($this->isActivityManageAccount() AND $this->sessionActivity_x[1] === STAGE_MS0_HOME) 
			  or $this->sessionActivity_x[1] === -1 ) 
		){
			$this->redirectBookForward( $this->sessionActivity_x[1] );
			return FALSE;
		}
		return TRUE;
	}

	function preUseracctctrl__myAccountFW()
	{
		if( !($this->isActivityManageAccount() AND $this->sessionActivity_x[1] === STAGE_MACCT0_HOME ) ){
			$this->redirectBookForward( $this->sessionActivity_x[1] );
			return FALSE;
		}
		return TRUE;
	}
	
	function redirectBookForward( $stage_sent = FALSE )
	{
		/*
			You have to change redirection address if you change function names
			in eventctrl, and eventctrl's filename itself.
		*/
		$stage = ( $stage_sent === FALSE ) ? $this->sessionActivity_x[1] : $stage_sent;
		redirect( $this->getRedirectionURL( $stage ) );
	}//redirectBookForward()
	
	function getRedirectionURL( $stage )
	{
		log_message('DEBUG', 'getredirectionurl ' . $stage );
		switch( $stage )
		{
			case -1 : return 'sessionctrl'; break;
			case STAGE_BOOK_1_PROCESS: return 'eventctrl/book';  break;
			case STAGE_BOOK_1_FORWARD: return 'eventctrl/book_forward';  break;
			case STAGE_BOOK_2_PROCESS: return 'eventctrl/book_step2';  break;
			case STAGE_BOOK_2_FORWARD: return 'eventctrl/book_step2_forward';  break;
			case STAGE_BOOK_3_PROCESS: return 'eventctrl/book_step3';  break;
			case STAGE_BOOK_3_FORWARD: return 'eventctrl/book_step3_forward';  break;
			case STAGE_BOOK_4_PROCESS: return 'eventctrl/book_step4';  break;
			case STAGE_BOOK_4_CLASS_1_FORWARD: return 'eventctrl/meow';  break;
			case STAGE_BOOK_4_CLASS_2_FORWARD: return 'eventctrl/meow2';  break;
			case STAGE_BOOK_4_FORWARD: return 'eventctrl/book_step4_forward';  break;
			case STAGE_BOOK_5_PROCESS: return 'eventctrl/book_step5';  break;
			case STAGE_BOOK_5_FORWARD: return 'eventctrl/book_step5_forward';  break;
			case STAGE_BOOK_6_PROCESS: return 'eventctrl/book_step6';  break;
			case STAGE_BOOK_6_PAYMENTPROCESSING: return 'paypal/process';  break;
			case STAGE_BOOK_6_FORWARD: return 'eventctrl/book_step6_forward';  break;
			case STAGE_MB3_SELECT_SEAT_PR: return 'eventctrl/managebooking_changeseat_complete';
			//case STAGE_MB4_CONFIRM_PR: 
			case STAGE_MB4_CONFIRM_FW: return 'eventctrl/managebooking_confirm'; break;
			case STAGE_MB0_HOME: return 'eventctrl/managebooking'; break;
			case STAGE_CR_SEAT1: return 'seatctrl/create_forward'; break;
			case STAGE_CR_SEAT2_FW: return 'seatctrl/create_step2_forward'; break;
			case STAGE_CR_SEAT3_FW: return 'seatctrl/create_step3_forward'; break;
			case STAGE_MACCT0_HOME: return 'useracctctrl/myaccount_forward'; break;
			default: return "sessionctrl/redirect_unknown/".$stage; //3999
		}
	}
	
}//class