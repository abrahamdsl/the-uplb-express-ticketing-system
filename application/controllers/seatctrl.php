<?php
/**
*	Seat Controller
* 	CREATED 16JAN2012-1235
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains most functionalities regarding the access of seating features.
*	At current, user needs to be logged in to be able to use the features of this controller.
**/
class seatctrl extends CI_Controller {

	function __construct()
	{	
		parent::__construct();
		ignore_user_abort(true);
		include_once( APPPATH.'constants/_constants.inc');
		$this->load->helper('cookie');
		$this->load->library('airtraffic');
		$this->load->library('form_validation');
		$this->load->library('functionaccess');
		$this->load->library('seatmaintenance');
		$this->load->library('sessmaintain');
		$this->load->model('atc_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('event_model');
		$this->load->model('login_model');
		$this->load->model('makexml_model');
		$this->load->model('ndx_model');
		$this->load->model('permission_model');
		$this->load->model('seat_model');
		$this->load->model('usefulfunctions_model');
		
		if( !$this->sessmaintain->onControllerAccessRitual() ) return FALSE;
	} //construct
	
	function index()
	{		
		$this->create();
	}//index
	
	private function checkAndActOnAdmin()
	{	
		/**
		*	@created <I forgot>
		*	@description Self-explanatory. Needed since most functionality here are for Admin only.
		**/
		if( !$this->permission_model->isAdministrator() )
		{   //4101
			$this->load->view( 'errorNotice', Array( 'error' => "NO_PERMISSION" ) );
			return false;
		}
		return true;
	}
	
	private function postSeatCreateCleanup()
	{
		/**
		*	@created 06JUL2012-1901
		**/
		$this->clientsidedata_model->deleteSeatMapUniqueID();
		$this->clientsidedata_model->setSessionActivity( IDLE, -1 );
	}
	
	function deleteseatmap()
	{
		/**
		*	@created <i forgot>
		*	@description Confirmation page before deleting a seat map.
		**/
		$this->checkAndActOnAdmin();
		$uniqueID = $this->input->post( 'uniqueID' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );
		$data['title'] =  "Be careful on what you wish for";
		$data['theMessage'] =  "Are you sure you want to delete this seat map?"; // EC 2850
		$data['yesURI'] = base_url().'seatctrl/deleteseatmap_process';
		$data['noURI'] = base_url().'seatctrl/manageseatmap';
		$data['formInputs'] = Array( 
			'uniqueID' => $uniqueID
		);		
		$this->load->view( 'confirmationNotice', $data );
	}
	
	function deleteseatmap_process( $inner_util = FALSE )
	{
		/**
		*	@revised 07JUL2012-1504
		*	@description Where the deletion from DB of a seat map really happens.
		**/
		$this->checkAndActOnAdmin();
		
		// this is to check whether this is utilized by another function here, or accessed thru web
		$is_inner_use = is_array( $inner_util );
		$uniqueID = $is_inner_use ? @$inner_util[0] : @$this->input->post( 'uniqueID' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );		
		$result = $this->seat_model->deleteSeatMap( $uniqueID );
		if( $result )
		{
			// EC 1850
			$this->clientsidedata_model->deleteSeatMapUniqueID();
			if( $is_inner_use ) $this->clientsidedata_model->setSessionActivity( IDLE, -1 );
			$this->load->view( 'successNotice', $this->seatmaintenance->assembleSeatMapDeletionOK( $is_inner_use ) );
			return true;
		}else{
			// EC 5850
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleSeatMapDeletionFail() );
			return false;
		}
	}
	
	function cancel_create_init(){
		if( !$this->functionaccess->preCreateSeatStep1FWCheck() ) return FALSE;
		$this->postSeatCreateCleanup();
		$this->load->view( 'successNotice', $this->seatmaintenance->assembleSeatMapDeletionOK( TRUE ) );
	}
	
	function cancel_create_process()
	{
		/**
		*	@created 07JUL2012-1501
		*	@description Handles cancellation of the seat map creation process.
		**/
		//access validity check
		if( !$this->functionaccess->preCreateSeatStep2FWCheck( STAGE_CR_SEAT2_FW ) ) return FALSE;
		
		$uniqueID = $this->clientsidedata_model->getSeatMapUniqueID();
		if( $uniqueID === FALSE ){
			$this->postSeatCreateCleanup();
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleSeatToDelete404() );
			return FALSE;
		}
		return $this->deleteseatmap_process( Array( $uniqueID ) );
	}
	
	function create(){
		/**
		*	@created <November 2011>
		*	@revised 18JUL2012-1344
		**/
		if( !$this->functionaccess->preCreateSeatStep1PRCheck() ) return FALSE;
		$this->clientsidedata_model->setSessionActivity( SEAT_CREATE, STAGE_CR_SEAT1 );	
		redirect( 'seatctrl/create_forward');
	}
	
	function create_forward()
	{
		/**
		*	@created 18JUL2012-1344
		*	@description Initial page for seat creation. Name and dimensions keyed in here.
		**/
		if( !$this->functionaccess->preCreateSeatStep1FWCheck() ) return FALSE;
		$this->load->view( 'createSeat/createSeat_step1');
	}
	
	function create_step2()
	{
		/**
		*	@created <November 2011>
		*	@description Submit page for $this->create_forward
		*	@revised 06JUL2012-1737
		**/
		// access validity check
		if( !$this->functionaccess->preCreateSeatStep2PRCheck( STAGE_CR_SEAT1 ) ) return FALSE;

		// form-validation, though back in the client the form is checked via JS
		// we still put this as the JS check can be circumvented.
		$this->form_validation->set_rules('name', 'Name', 'required|min_length[1]');
		$this->form_validation->set_rules('rows', 'Rows', 'required|integer|greater_than[0]|less_than[27]');
		$this->form_validation->set_rules('cols', 'Cols', 'required|integer|greater_than[0]');
		if($this->form_validation->run() == FALSE)
	    {
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleGenericFormValidationFail() );
			return FALSE;
		}
		if( $this->seat_model->isSeatMapNameExistent( $this->input->post('name') ) )
		{
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleSeatMapNameExists() );
			return FALSE;
		}
		
		/* 
			Process the data submitted by the form - and storing in the DB too.
			The posted data are directly accessed thru $this->input->post() there.
		*/
		$csm_result = $this->seat_model->createSeatMap();
		if( $csm_result  === FALSE )
		{
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleGenericDBFail() );
			return FALSE;
		}else{
			// set the seat map id in the CI sess data to be used further down the road.
			$this->clientsidedata_model->setSeatMapUniqueID( $csm_result );
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_CR_SEAT2_FW );
		redirect( 'seatctrl/create_step2_forward');
	}//create_step2()
	
	function create_step2_forward()
	{
		/**
		*	@created 06JUL2012-1900
		*	@description The main page for seat map creation.
		**/
		if( !$this->functionaccess->preCreateSeatStep2FWCheck( STAGE_CR_SEAT2_FW ) ) return FALSE;
		$uniqueID = $this->clientsidedata_model->getSeatMapUniqueID();
		$seatmap  = $this->seat_model->getSingleMasterSeatMapData( $uniqueID );
		if( $seatmap === FALSE )
		{
			$this->postSeatCreateCleanup();
			$this->load->view( 'errorNotice', $this->seatmaintenance->assembleSeatUID404() );
			return FALSE;
		}
		$data['name'] = $seatmap->Name;
		$data['rows'] = $seatmap->Rows;
		$data['cols'] = $seatmap->Cols;
		$this->load->view( 'createSeat/createSeat_step2' , $data);
	}

	function create_step3()
	{
		/**
		*	@created <i forgot>
		*	@description Processing page of the seat map creation.
		**/	
		$x;
		$y;
		$i;
		$j;
		$attempts = 30;
		$guid;
		$looptime = 1;
		$uniqueID;
		$seatmap;
		$sessionActivity;
		
		if( !$this->functionaccess->preCreateSeatStep3PRCheck() ) return FALSE;
		$uniqueID = $this->clientsidedata_model->getSeatMapUniqueID();
		$seatmap  = $this->seat_model->getSingleMasterSeatMapData( $uniqueID );
		if( $seatmap === FALSE )
		{
			$this->postSeatCreateCleanup();
			$this->seatmaintenance->assembleSeatUID404();
			return FALSE;
		}

		// initialize air traffic - i.e., URI and session activity name and stage to be set on success
		$guid = $this->usefulfunctions_model->guid();
		$sessionActivity = $this->clientsidedata_model->getSessionActivity();
		if( !$this->airtraffic->initialize(
				$guid, $sessionActivity, $sessionActivity[0], STAGE_CR_SEAT3_FW, 
				'seatctrl/create_step3_forward', NULL, $attempts, $looptime )
		){
			$this->airtraffic->terminateService( $guid, TRUE );
			return FALSE;
		}
		
		$this->db->trans_begin();	// database checkpoint
		// NOW DO OUR ACTIVITIES!
		// send the data for processing here and set the seat map as configured
		if( !$this->seat_model->createDefaultSeats( $uniqueID, $seatmap->Rows, $seatmap->Cols)
			or !$this->seat_model->setSeatMapStatus( $uniqueID, 'CONFIGURED', NULL )
		){	
			log_message('DEBUG','create_step3|'.$guid.'|rolledback|proper');
			$this->db->trans_rollback();							//database rollback
			$this->airtraffic->terminateService( $guid, TRUE );
			return $this->seatmaintenance->assembleCreateDefaultSeatFail();	
		}
		// now, seek clearance and decide whether or not to commit or rollback
		if( $this->airtraffic->clearance( $guid, $attempts, $looptime ) and
			$this->airtraffic->terminateService( $guid )
		){
			$this->db->trans_commit();
			log_message('DEBUG','create_step3 cleared for take off ' . $guid);			
		}else{
			$this->db->trans_rollback();
			log_message('DEBUG','create_step3_final clearance error '. $guid);		
		}
		$this->airtraffic->deleteXML( $guid );
	} //create_step3()
	
	function create_step3_forward()
	{
		/**
		*	@created 07JUL2012-1618
		*	@description Landing page for seat map creation success;
		**/
		if( !$this->functionaccess->preCreateSeatStep2FWCheck( STAGE_CR_SEAT3_FW ) ) return FALSE;
		$this->postSeatCreateCleanup();
		$this->load->view( 'createSeat/allConfiguredNotice');
	}

	function getActualSeatsData()
	{
		/**
		*	@created 12FEB2012-2258
		*	@description Gets the seating details of a showing time - used in booking a ticket.
		**/
		$masterSeatMapDetails;
		$masterSeatMapProperData;
		$eventID;
		$showtimeID;
		$showingTimeObj;
		$seatMapUniqueID;
		$guid;
		$bookingInfo;
		
		// user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
		
		$guid = $this->clientsidedata_model->getBookingCookiesOnServerUUIDRef();
		$bookingInfo = $this->ndx_model->get( $guid );
		if( $bookingInfo === false ){
			echo 'ERROR|Cannot find server-on-cookie';
			return false;
		}
		$eventID = $bookingInfo->EVENT_ID;
		$showtimeID = $bookingInfo->SHOWTIME_ID;
		$showingTimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID );

		// no post data, so fail
		if( $eventID === false or $showtimeID === false )
		{
			echo "INVALID_NO-POST-DATA";
			return false;
		}
		
		//get DB entries
		$masterSeatMapDetails = $this->seat_model->getSingleMasterSeatMapData( $showingTimeObj->Seat_map_UniqueID );
		$seatMapProperData = $this->seat_model->getEventSeatMapActualSeats( $eventID, $showtimeID );
		echo $this->makexml_model->XMLize_SeatMap_Actual( $masterSeatMapDetails, $seatMapProperData );
		return true;
	}//getActualSeatsData(..)
	
	function manageseatmap()
	{
		/**
		*	@created <I forgot>
		*	@description Landing page for managing seat maps.
		**/
		$this->checkAndActOnAdmin();
		$data['seatmaps'] = $this->seat_model->getAllSeatMaps();
		$this->clientsidedata_model->setSessionActivity( MANAGE_SEATMAP, STAGE_MS0_HOME );
		$this->load->view( 'manageSeat/manageSeat01', $data );	
	}
	
	function getMasterSeatmapData()
	{
		/**
		*	@created 28JAN2012-2215
		*	@description Used when creating a show and assigning seats.
		*	@remarks Only for AJAX requests.
		**/
		$masterSeatMapDetails;
		$masterSeatMapProperData;
		$uniqueID = $this->input->post( 'uniqueID' );

		// user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
		
		// no post data, so fail
		if( $uniqueID === false )
		{
			echo "INVALID_NO-POST-DATA";
			return false;
		}
		
		//get DB entries
		$masterSeatMapDetails = $this->seat_model->getSingleMasterSeatMapData( $uniqueID );
		$masterSeatMapProperData = $this->seat_model->getMasterSeatMapActualSeats( $uniqueID );
						
		echo $this->makexml_model->XMLize_SeatMap_Master( $masterSeatMapDetails, $masterSeatMapProperData );
		return true;
	}// getMasterSeatmapData
}//class
?>