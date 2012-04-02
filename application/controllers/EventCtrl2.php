<?php
/*
CREATED 27 MAR 2012 0341

Difference is that this is dedicated mostly to
event manager only, because EventCtrl is so ballooned already.
*/
class EventCtrl2 extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		
		/* 
			Please check EventCtrl on whether these are the same. The definitions there precede this, so
			change accordingly if different.
		*/
		/*
			We define them here so that centralized ang pangalan ng mga cookies, etc.
		*/
		define( 'PAYMENT_MODE', 'paymentMode' );
		define( 'TRANSACTION_ID', 'transactionID' );
		define( 'OLD_SHOWTIME_ID', 'oldShowtimeID' );
		define( 'OLD_SHOWTIME_TC_GROUP_ID', 'oldTicketClassGroupID' );
		define( 'OLD_SHOWTIME_TC_UNIQUE_ID', 'oldTicketClassUniqueID' );
		define( 'FREE_AMOUNT', 0.0 );
		define( 'FACTORY_AUTOCONFIRMFREE_UNIQUEID', 0 );
		define( 'FACTORY_PAYPALPAYMENT_UNIQUEID', 2 );
		define( 'PAYPAL_FEE_FIXED', 15.00 );
		define( 'PAYPAL_FEE_PERCENTAGE', 0.034 );
		
		/*
			Activity names
		*/
		define( 'BOOK', 'BOOK' );
		define( 'CONFIRM_RESERVATION', 'CONFIRM_BOOKING' );
		define( 'MANAGE_BOOKING', 'MANAGE_BOOKING' );
		define( 'CHECK_IN', 'CHECKIN_IN' );
		define( 'CHECK_OUT', 'CHECKIN_OUT' );
		
		/*
			Array keys
		*/
		define( 'AKEY_UNPAID_PURCHASES_ARRAY', 'unpaidPurchasesArray' );		
		define( 'AKEY_PAID_PURCHASES_ARRAY', 'paidPurchasesArray' );		
		define( 'AKEY_UNPAID_TOTAL', 'unpaidTotal' );		
		define( 'AKEY_PAID_TOTAL', 'paidTotal' );		
		define( 'AKEY_AMOUNT_DUE', 'amountDue' );
		
		/*
			Session stages						
		*/
		define( 'STAGE_BOOK_1_PROCESS', 0 );
		define( 'STAGE_BOOK_1_FORWARD', 1 );
		define( 'STAGE_BOOK_2_PROCESS', 2 );
		define( 'STAGE_BOOK_2_FORWARD', 3 );
		define( 'STAGE_BOOK_3_PROCESS', 4 );
		define( 'STAGE_BOOK_3_FORWARD', 5 );		
		define( 'STAGE_BOOK_4_PROCESS', 6 );
		define( 'STAGE_BOOK_4_CLASS_1_FORWARD', 7 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_CLASS_2_FORWARD', 8 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_FORWARD', 9 );
		define( 'STAGE_BOOK_5_PROCESS', 10 );
		define( 'STAGE_BOOK_5_FORWARD', 11 );
		define( 'STAGE_BOOK_6_PROCESS', 12 );
		define( 'STAGE_BOOK_6_PAYMENTPROCESSING', 13 );
		define( 'STAGE_BOOK_6_FORWARD', 14 );
		
		define( 'STAGE_CONFIRM_1_FORWARD', 101 );
		define( 'STAGE_CONFIRM_2_PROCESS', 102 );
		define( 'STAGE_CONFIRM_2_FORWARD', 103 );
		define( 'STAGE_CONFIRM_3_PROCESS', 104 );
		define( 'STAGE_CONFIRM_3_FORWARD', 105 );
		/* 
			Post indicators
		*/
		define( 'PIND_SLOT_SAME_TC_NO_MORE_USER_NOTIFIED', 'noMoreSlotSameTicketClassNotified' );
		
		
		$this->load->helper('cookie');
		$this->load->model('login_model');
		$this->load->model('Academic_model');
		$this->load->model('Account_model');
		$this->load->model('Booking_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('CoordinateSecurity_model');
		$this->load->model('Event_model');
		$this->load->model('Guest_model');
		$this->load->model('MakeXML_model');
		$this->load->model('Payment_model');
		$this->load->model('Permission_model');
		$this->load->model('Seat_model');		
		$this->load->model('Slot_model');		
		$this->load->model('TicketClass_model');
		$this->load->model('TransactionList_model');
		$this->load->model('UsefulFunctions_model');
		$this->load->library('bookingmaintenance');		
		$this->load->library('encrypt');		
		if( !$this->login_model->isUser_LoggedIn() ){		
			redirect('/SessionCtrl');
		}
	} //construct
	
	function index()
	{		
		redirect( 'EventCtrl/book' );		
	}//index
	
	function common_pre_check( $eventID, $showtimeID )
	{
		if( !(is_numeric( $eventID ) and is_numeric( $showtimeID ) ) )
		{
			$data['error'] = "NO_DATA";			
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}
	
	function manageEvent()
	{
		$eventObj = $this->Event_model->getAllEventsRestricted();
		$showingTimes = Array();
		foreach( $eventObj as $singleEvent )
		{
			$showingTimes[ $singleEvent->EventID ] = $this->Event_model->getAllShowingTimes(  $singleEvent->EventID );		
		}
		$data['myEvents'] = $eventObj;
		$data['showingTimes'] = $showingTimes;
		$this->load->view( 'manageEvent/manageEvent01', $data );
	}//manageEvent()
	
	
	function reschedule( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['eventObj'] = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID );		
		$this->load->view( 'manageEvent/manageEvent02_reschedule.php', $data);
	}//reschedule
	
	function reschedule_process()
	{
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$startDate 		= $this->input->post( 'startDate' );
		$startTime 		= $this->input->post( 'startTime' );
		$endDate 		= $this->input->post( 'endDate' );
		$endTime 		= $this->input->post( 'endTime' );
		
		// !!!! skip form validation first. 27MAR2012
		
		if( $this->Event_model-> updateShowingTimeSchedule( $eventID, $showtimeID, $startDate, $startTime, $endDate, $endTime ) )
		{
			$data[ 'theMessage' ] = "Successfully changed the showing date and times.";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}//reschedule_process()

	function seal( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to seal this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all yet unconfirmed bookings <br/>(those who are transferring to this.";
		$data['theMessage'] .= "showing time will be defaulted to their old showing time).";
		$data['yesURI'] = base_url().'EventCtrl2/seal_process';
		$data['noURI'] = base_url().'EventCtrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function seal_process()
	{	
		/*
			Pending check here if someone is booking a slot, so therefore,
			wait for them before proceeding here.
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
		
		$this->bookingmaintenance->cleanDefaultedBookings( $eventID , $showtimeID );
		$this->bookingmaintenance->cleanDefaultedSlots( $eventID , $showtimeID, NULL );
		if( $this->Event_model->setForCheckIn(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully sealed the showing time. Guests can now check-in.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
		
	function cancel( $eventID = NULL, $showtimeID = NULL )
	{
		$this->common_pre_check( $eventID, $showtimeID );
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to cancel this showing time?";
		$data['theMessage'] .= "<br/><br/>Doing so will forfeit all yet unconfirmed bookings <br/>(those who are transferring to this.";
		$data['theMessage'] .= "showing time will be defaulted to their old showing time).";
		$data['yesURI'] = base_url().'EventCtrl2/cancel_process';
		$data['noURI'] = base_url().'EventCtrl2/manageEvent';
		$data['formInputs'] = Array( 
			 'promptedIndicator' => '1',
			 'eventID' => $eventID,
			 'showtimeID' => $showtimeID,
		);
		$this->load->view( 'confirmationNotice', $data );
		return false;
	}
	
	function cancel_process()
	{	
		/*
			Pending check here if someone is booking a slot, so therefore,
			wait for them before proceeding here.
		*/
		$eventID 		= $this->input->post( 'eventID' );
		$showtimeID 	= $this->input->post( 'showtimeID' );
		$this->common_pre_check( $eventID, $showtimeID );
		
		$this->bookingmaintenance->cleanDefaultedBookings( $eventID , $showtimeID );
		$this->bookingmaintenance->cleanDefaultedSlots( $eventID , $showtimeID, NULL );
		if( $this->Event_model->setAsCancelled(  $eventID, $showtimeID ) )
		{
			$data[ 'theMessage' ] = "Successfully cancelled the showing time.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'EventCtrl2/manageEvent';
			$data[ 'defaultAction' ] = 'Manage Event';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
		}
	}
}//class
