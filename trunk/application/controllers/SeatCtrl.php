<?php
/*
CREATED 16JAN2012-1235
*/

class SeatCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->helper('cookie');
		$this->load->model('Event_model');		
		$this->load->model('login_model');		
		$this->load->model('MakeXML_model');				
		$this->load->model('Permission_model');				
		$this->load->model('Seat_model');				
		
		if( !$this->login_model->isUser_LoggedIn() )
		{	
			redirect('SessionCtrl/authenticationNeeded');
		}
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	private function checkAndActOnAdmin()
	{
		if( !$this->Permission_model->isAdministrator() )
		{
			$data['error'] = "NO_PERMISSION";					
			$this->load->view( 'errorNotice', $data );			
			return false;
		}
		return true;
	}
	
	function deleteseatmap()
	{
		$this->checkAndActOnAdmin();
		$uniqueID = $this->input->post( 'uniqueID' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );
		$data['title'] =  "Be careful on what you wish for";
		$data['theMessage'] =  "Are you sure you want to delete this seat map?";
		$data['yesURI'] = base_url().'SeatCtrl/deleteseatmap_process';
		$data['noURI'] = base_url().'SeatCtrl/manageseatmap';
		$data['formInputs'] = Array( 
			'uniqueID' => $uniqueID
		);		
		$this->load->view( 'confirmationNotice', $data );
	}
	
	function deleteseatmap_process()
	{
		$this->checkAndActOnAdmin();
		$uniqueID = $this->input->post( 'uniqueID' );
		if( $uniqueID === false) die( 'INVALID_INPUT-NEEDED' );		
		$result = $this->Seat_model->deleteSeatMap( $uniqueID );
		if( $result )
		{
			$data[ 'theMessage' ] = "The seat map has been successfully deleted.";			
			$data[ 'redirectURI' ] = base_url().'SeatCtrl/manageseatmap';
			$data[ 'defaultAction' ] = 'Seat Maps';
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			$data[ 'error' ] = 'CUSTOM';
			$data[ 'theMessage' ] = "Something went wrong while processing the deletion of the seat map. It may have been not deleted. <br/><br/>Please try again.";
			$data[ 'redirectURI' ] = base_url().'SeatCtrl/manageseatmap';
			$data[ 'defaultAction' ] = 'Seat Maps';
			$this->load->view( 'errorNotice', $data );
			return false;
		}
	}
	
	function manageseatmap()
	{
		$this->checkAndActOnAdmin();
		$data['seatmaps'] = $this->Seat_model->getAllSeatMaps();
		$this->load->view( 'manageSeat/manageSeat01', $data );	
	}
	
	function create()
	{
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		$this->load->view( 'createSeat/createSeat_step1' , $data);
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
		$this->load->view( 'createSeat/createSeat_step2' , $data);
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
	
	function getActualSeatsData()
	{
		/*
			Created 12FEB2012-2258
		*/
		$masterSeatMapDetails;
		$masterSeatMapProperData;						
		$eventID = $this->input->cookie( 'eventID' );
		$showtimeID = $this->input->cookie( 'showtimeID' );		
		$showingTimeObj;
		$seatMapUniqueID;
		
		$showingTimeObj = $this->Event_model->getSingleShowingTime( $eventID, $showtimeID );
		
		// user is accessing via browser address bar, so not allowed
		//if( $this->input->is_ajax_request() === false ) redirect('/');
		
		// no post data, so fail
		if( $eventID === false or $showtimeID === false )
		{
			echo "INVALID_NO-POST-DATA";
			return false;
		}
		
		//get DB entries		
		$masterSeatMapDetails = $this->Seat_model->getSingleMasterSeatMapData( $showingTimeObj->Seat_map_UniqueID );		
		$seatMapProperData = $this->Seat_model->getEventSeatMapActualSeats( $eventID, $showtimeID );
						
		echo $this->MakeXML_model->XMLize_SeatMap_Actual( $masterSeatMapDetails, $seatMapProperData );
		return true;
	}//getActualSeatsData(..)
	
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
						
		echo $this->MakeXML_model->XMLize_SeatMap_Master( $masterSeatMapDetails, $masterSeatMapProperData );
		return true;
	}// getMasterSeatmapData
	
	function areSeatsOccupied( )
	{
		/*
			Created 13FEB2012-2000
		*/
		$matrices = $this->input->post( 'matrices' );		
		$eventID = $this->input->post( 'eventID' );
		$showtimeID = $this->input->post( 'showtimeID' );
		$seatObj;
		$matrices_tokenized;
		
		
		// user is accessing via browser address bar, so not allowed
		//if( $this->input->is_ajax_request() === false ) redirect('/');
		
		if( $matrices === false or $eventID === false or $showtimeID === false )
		{
			echo "INVALID|DATA-NEEDED";
			return false;
		}
		$matrices_tokenized = explode( "-", $matrices );
		foreach( $matrices_tokenized as $singleData )
		{
			$matrixInfo = explode( "_", $singleData );
			/*
			04MAR2012-1743 - This commented block is the former algorithm.
				DEPRECATED now. I have yet to test the other algorithm so 
				I won't erase this for now.
			$seatObj = $this->Seat_model->getSingleActualSeatData( $matrixInfo[0], $matrixInfo[1], $eventID, $showtimeID );
			if( $seatObj === false )
			{
				echo "INVALID|NO-SUCH-SEAT-EXISTS";
				return false;
			}
			if( intval( $seatObj->Status) != 0 ){
				echo "OK|FALSE|".$singleData;
				return false;
			}*/
			
			$isSeatAvailableResult = $this->Seat_model->isSeatAvailable( 
				$matrixInfo[0], $matrixInfo[1], $eventID, $showtimeID 
			);
			if( !$isSeatAvailableResult['boolean'] ){			
				if( $isSeatAvailableResult['throwException'] === NULL ){
					echo "OK|FALSE|".$singleData;
					return false;
				}else{
					// error in operation, so far, only no such seat found.
					echo $isSeatAvailableResult['throwException'];
					return false;
				}
			}
		}
		echo "OK|TRUE";
		return true;
	}//areSeatsOccupied(..)

	
}//class
?>