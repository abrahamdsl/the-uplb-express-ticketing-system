<?php
/*
CREATED 16JAN2012-1235
*/

class SeatCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->helper('cookie');
		$this->load->model('login_model');				
		$this->load->model('MakeXML_model');				
		$this->load->model('Seat_model');				
		if( !$this->login_model->isUser_LoggedIn() ) redirect('/SessionCtrl');
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	function create()
	{
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		$this->load->view( 'createSeat/createSeat_Step1' , $data);
	}
	
	function create_step2()
	{
		// Wrong HTML is submitting some malicious form, so take control here.
		if( $this->input->post('name') === false or $this->input->post( 'rows' ) === false or
			$this->input->post('cols') === false 
		)
		{
			redirect( 'SeatCtrl/create' );
		}
		// start: temp
		// process the data submitted by the form - and storing in the DB too, seat_map details
		$this->Seat_model->createSeatMap();				
		
		$cookie = array( 'name' => 'seatMapName', 
						 'value' => $this->input->post('name'), 
						 'expire' => '7200'  );	
		$this->input->set_cookie( $cookie );				
		$cookie = array( 'name' => 'rows', 
						 'value' => $this->input->post( 'rows' ),
						 'expire' => '7200'  );	
		$this->input->set_cookie( $cookie );		
		$cookie = array( 'name' => 'cols', 
						 'value' => $this->input->post('cols'), 
						 'expire' => '7200'  );							 
		$this->input->set_cookie( $cookie );	
		
		$data['rows'] = $this->input->post( 'rows' );
		$data['cols'] = $this->input->post( 'cols' );
		// end: temp
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();						
		$this->load->view( 'createSeat/createSeat_Step2' , $data);
	}
	
	function create_step3()
	{
		$x;
		$y;
		$i;
		$j;

		// CODE MISSING: database checkpoint
		if( $this->Seat_model->createDefaultSeats() === false )					// send the data for processing here
		{
			// CODE MISSING:  database rollback			
			die('Create Seat Error: Something went wrong in actual seat data insertion to DB ');			
		}
		// CODE MISSING:  database commit

		// set them as configured
		$this->Seat_model->setSeatMapStatus( $this->input->cookie( 'seatMapUniqueID' ), 'CONFIGURED' );
	
		// delete the cookies concerned
		delete_cookie( 'seatMapName' );
		delete_cookie( 'rows' );
		delete_cookie( 'cols' );
		delete_cookie( 'seatMapUniqueID' );
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();						
		$this->load->view( 'createSeat/allConfiguredNotice' , $data);			
	}
	
	function getMasterSeatmapData()
	{
		/*
			Created 28JAN2012-2215
			
			Only for AJAX requests.
		*/
		$masterSeatMapDetails;
		$masterSeatMapProperData;
		$uniqueID = $this->input->post( 'uniqueID' );
		//$uniqueID = 9610832;
	
		// user is accessing via browser address bar, so not allowed
		if( $this->input->is_ajax_request() === false ) redirect('/');
		
		// no post data, so fail
		if( $uniqueID === false )
		{
			echo "INVALID_NO-POST-DATA";
			return false;
		}
		
		//get DB entries
		$masterSeatMapDetails = $this->Seat_model->getSingleMasterSeatMapData( $uniqueID );
		$masterSeatMapProperData = $this->Seat_model->getMasterSeatMapActualSeats( $uniqueID );
		
		//now make XML
		//die( var_dump( $this->MakeXML_model->XMLize_SeatMap_Master( $masterSeatMapDetails, $masterSeatMapProperData ) ));
		
		echo $this->MakeXML_model->XMLize_SeatMap_Master( $masterSeatMapDetails, $masterSeatMapProperData );
		return true;
	}// getMasterSeatmapData
	
}//class
?>