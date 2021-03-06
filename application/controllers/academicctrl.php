<?php
/*
	Created 13MAR2012-0945
*/
class academicctrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
		
		include_once( APPPATH.'constants/_constants.inc');
		
		$this->load->library('session');
		$this->load->library('sessmaintain');
		$this->load->model('login_model');
		$this->load->model('academic_model');
		$this->load->model('account_model');
		$this->load->model('booking_model');
		$this->load->model('event_model');
		$this->load->model('clientsidedata_model');
		$this->load->model('Guest_model');
		$this->load->model('makexml_model');
		$this->load->model('permission_model');
		$this->load->model('usefulfunctions_model');
		
		if( !$this->sessmaintain->onControllerAccessRitual() ) return FALSE;
	}
	
	function index()
	{
		redirect( '/' );
	}
	
	private function createClassData_preCheck(
		$title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, $functionNext = 'createClass', $functionCaption = 'Create Class' )
	{
		if( strlen($title) <1 or (strlen($num) < 1) or (strlen($lectSect) < 1)
			or (strlen($term) < 1) or (strlen($ay1) < 1) or (strlen($ay2) < 1) 
		)
		{
			$data[ 'error' ] = "CUSTOM";
			$data[ 'theMessage' ] = "Please specify valid data!";
			$data[ 'redirect' ] = FALSE;
			$data[ 'redirectURI' ] = base_url().'academicctrl/'.$functionNext;
			$data[ 'defaultAction' ] = $functionCaption;		
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		return true;
	}
	
	function addEventToClass( $classID )
	{
		if( $classID === false or strlen( $classID ) < 4 )
		{
			die("Invalid class ID");
		}
		$this->clientsidedata_model->setSessionActivity( ACTIVITY_CREATE_CLASS, 2 );
		$this->clientsidedata_model->setUPLBClassUUID( $classID );		 
		redirect( 'academicctrl/createClass_step2_forward');	
	}// addEventToClass(..)
	
	function associateClassToBooking()
	{		
		$eventID    	= $this->clientsidedata_model->getEventID();
		$showtimeID 	= $this->clientsidedata_model->getShowtimeID();
		$bookingNumber =  $this->clientsidedata_model->getBookingNumber();
		$activeClasses  = $this->academic_model->getActiveClasses( $eventID, $showtimeID );
		
		if( count( $activeClasses ) < 1 ){
			$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD );
			redirect('eventctrl/book_step4_forward');
		}
		$uplbConstituent = Array();
		
		$guestObj = $this->Guest_model->getGuestDetails( $bookingNumber );		
		foreach( $guestObj as $eachGuest )
		{			
			if( $eachGuest->studentNumber != NULL or $eachGuest->employeeNumber != NULL )
			{
				$uplbConstituent[ $eachGuest->UUID ] = Array(
					'studentNumber' => $eachGuest->studentNumber ,
					'employeeNumber' => $eachGuest->employeeNumber
				);
			}
		}	
		$data['guests'] = $guestObj;
		$data['activeClasses'] = $activeClasses;		
		$this->load->view('book/bookStep3_B', $data );
	}
	
	function associateClassToBooking_process1()
	{	
		$serialized = "";
		$selectedOnes = Array();
		$bookingNumber = $this->clientsidedata_model->getBookingNumber();
		// UNSAFE!!!! ESCAPE $key first!!!
		foreach( $_POST as $key => $value )
		{
			$splitted = explode('_', $key );
			$serialized .= $splitted[2]."-";			
			$selectedOnes[ $splitted[2]  ] = $this->academic_model->getSingleClass_ByUUID(  $splitted[2] );
		}
		
		$data['guests'] = $this->Guest_model->getGuestDetails( $bookingNumber );
		$data['val' ] = $selectedOnes;
		$this->session->set_userdata( 'book_step', 3 );
		$this->load->view( 'book/bookStep3_C', $data );
		$serialized = substr(0, strlen($serialized)-1 );
		$this->clientsidedata_model->setClassUUIDs( $serialized );
	}
	
	function associateClassToBooking_process2()
	{	
		if( count( $_POST ) > 0 )
		{
			foreach( $_POST as $key => $value )
			{
				$splitted = explode('_', $key );
				if( is_array( $splitted ) and count( $splitted ) == 2 )
				{
					$this->academic_model->insertAttendanceForClass( $splitted[0], $splitted[1]	);
				}
			}
		}
		$this->clientsidedata_model->updateSessionActivityStage( STAGE_BOOK_4_FORWARD );
		redirect( 'eventctrl/book_step4_forward');
	}
	
	function createClassAccess_preCheck( $stageAccepted, $forward = false )
	{
		$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
		if( $sessionActivity[0] != ACTIVITY_CREATE_CLASS )
		{
			die('You shouldn\t be here but on "'.$sessionActivity[0].'"' );
		}else{
			//echo "Your stage : ".$sessionActivity[1]."Required : ". $stageAccepted;
			if( $sessionActivity[1] < $stageAccepted ){
				die('Not yet here!');
			}else
			if( $sessionActivity[1] > $stageAccepted ){
				die('advance now!');
			}
		}//if session..
	}//createClass_preCheck
	
	function createClass()
	{	
		$this->clientsidedata_model->setSessionActivity( ACTIVITY_CREATE_CLASS, 1 );
		$this->load->view( 'createClass/createClass01');
	}
	
	function createClass_step2()
	{	
		$this->createClassAccess_preCheck( 1, false );
		
		$title 		= $this->input->post( 'title');
		$num 		= $this->input->post('number' ); 
		$lectSect 	= $this->input->post('lectsect' );
		$recitSect 	= $this->input->post('recitsect' ); 
		$term 		= $this->input->post(  'term'); 
		$ay1 		= $this->input->post( 'acadyear_1' ); 
		$ay2 		= $this->input->post('acadyear_2' );	

		if( $this->createClassData_preCheck($title, $num, $lectSect, $recitSect, $term, $ay1, $ay2 ) === false ) die('ACCESS-NOT-GRANTED');
		
		if( !$this->academic_model->isClassExisting( $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2 ) 
		)
		{
			$classNum = $this->academic_model->createNewClass();
			if(  $classNum !== false )
			{			
			 $this->clientsidedata_model->updateSessionActivityStage( 2 );
			 $this->clientsidedata_model->setUPLBClassUUID( $classNum );			 
			 redirect( 'academicctrl/createClass_step2_forward');		
			}else{
			 die('Something went wrong while creating your class.');
			}
		}else{
			die('Class already exists.<br/><br/><a href="javascript: window.history.back();">Go back</a>');
		}	
	}//createClass_step2()
	
	function createClass_step2_forward()
	{
		$this->createClassAccess_preCheck( 2, false );
		$configuredEventsInfo = array();		
		// get all events first		
		$allEvents = $this->event_model->getAllEvents();		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view			
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		$data['configuredShowingTimes' ] = $showingTimes;
	
		$this->load->view( 'createClass/createClass02', $data );
	}//createClass_step2_forward()
	
	function createClass_step3()
	{			
		$this->createClassAccess_preCheck( 2, false );
		$facultyAccountNum = $this->session->userdata( 'accountNum' );
		$classID = $this->clientsidedata_model->getUPLBClassUUID();
		$eventScheds = Array();
		/*
			Iterate through the POST-ed values and scan which showing times are selected.
			Organize thru an Array.
		*/
		foreach( $_POST as $key => $val)
		{
			/*
				A showing time entry's name is represented by 'st-XX-YY' where
				XX - eventID ; YY-showtimeID
			*/
			$thisCombination = mysql_real_escape_string( $key );
			$splittedData_temp = explode('-', $thisCombination );
			$splittedData = Array( intval($splittedData_temp[1]), intval($splittedData_temp[2]) );
			if( !isset( $eventScheds[ $splittedData[0] ] ) ) 
			{
				$eventScheds[ $splittedData[0] ] = Array();
			}
			$eventScheds[ $splittedData[0] ][] = $splittedData[1];			
		}
	
		/*
			Now, perform DB insertion.
		*/
		foreach( $eventScheds  as $eventID => $val )
		{
			foreach( $val as $showtimeID )
			{
				$this->academic_model->createEventClassPair( $eventID, $showtimeID, $classID );
			}
		}
		$data['theMessage'] = "You have successfully associated your class to the events you have selected.";
		$data['redirect'] = TRUE;
		$data['redirectURI'] = base_url().'academicctrl/manageClasses';
		$data['defaultAction'] = 'Manage Classes';
		$this->load->view( 'successNotice', $data );
		$this->clientsidedata_model->updateSessionActivityStage( -1 );
	}//createClass_step3()
	
	function check_start( $activity )
	{		
		// get all events first		
		$allEvents = $this->event_model->getAllEvents();		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view			
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		$data['activity'] = $activity;		
		$this->clientsidedata_model->setReceptionistActivity( $activity );
		if( $activity == 1 || $activity == 2 ){
			$this->load->view('attendanceOnSite/attend00.php', $data );		
		}else
			redirect( '/' );
	}//checkin_start()
	
	function checkin_main()
	{				
			$eventID 	 = $this->input->post( 'events' );
			$showtimeID  = $this->input->post( 'showingTimes' );
			
			$this->clientsidedata_model->setEventID( $eventID );
			$this->clientsidedata_model->setShowtimeID( $showtimeID );
			redirect( 'academicctrl/checkin_main_forward' );
	}//checkin_main()
	
	function checkin_main_forward()
	{
			$eventID 	 = $this->clientsidedata_model->getEventID();
			$showtimeID  = $this->clientsidedata_model->getShowtimeID();
			$activity = $this->clientsidedata_model->getReceptionistActivity( );
			
			$eventObj	 = $this->event_model->getEventInfo( $eventID );
			$showtimeObj = $this->event_model->getSingleShowingTime( $eventID, $showtimeID );
			if( $eventObj === false or $showtimeObj === false )
			{
				die("INTERNAL-SERVER-ERROR_Invalid inputs.");
			}
			$data['eventObj'] = $eventObj ;
			$data['showtimeObj'] = $showtimeObj ;
			$data['null'] = 'wala lang';
			$data['activity'] = $activity;
			if( $activity == 1 ) $this->load->view('attendanceOnSite/attend01.php', $data );
			else $this->load->view('attendanceOnSite/attend01-b.php', $data );
	}//checkin_main_forward()
	
	function deleteClass( $uniqueID )
	{		
		$data['title'] = 'Be careful on what you wish for ...';
		$data['theMessage'] = "Are you sure you want to delete this class? ";				
		$data['yesURI'] = base_url().'academicctrl/deleteClassProcess';
		$data['noURI'] = base_url().'academicctrl/manageClasses';
		$data['formInputs'] = Array( 			 
			 'classID' => $uniqueID
		);
		$this->load->view( 'confirmationNotice', $data );		
	}
	
	function deleteClassProcess()
	{
		$classID = $this->input->post( 'classID' );
		if( $classID === false ) redirect( 'sessionctrl' );
		$this->academic_model->deleteClassEventAssociationViaClass( $classID );
		$this->academic_model->deleteUPLBClassStudentPairViaClass( $classID );
		$this->academic_model->deleteUPLBCLass( $classID );
		$data[ 'theMessage' ] = "Successfully deleted your class.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'academicctrl/manageClasses';
			$data[ 'defaultAction' ] = 'Manage Class';	
			$this->load->view( 'successNotice', $data );
			return true;
	}//deleteClassProcess()
	
	function deleteClassEventAssociation()
	{
		$uniqueID = $this->input->post( 'ec_uniqueid' );
		$this->academic_model->deleteClassEventAssociation( $uniqueID );
		redirect('academicctrl/manageClasses');
	}
		
	function isBookingFineForConsumption()
	{
		/*
						
			Returns 
			"FALSE_<xxxx>" where <xxxx> is the message indicating error.
			
			"TRUE_<yyyy>" where <yyyy> is the XML representation of guest info
		*/
		if( !$this->input->is_ajax_request() ) redirect('/');
		
		$eventID 	 = $this->clientsidedata_model->getEventID();
		$showtimeID  = $this->clientsidedata_model->getShowtimeID();
		$guestDetails;
		
		if( $eventID === false or $showtimeID === false ) 
		{
			echo "FALSE_Required cookies not set. Try accessing this functionality from the homepage again.";
			return false;
		}
		$bookingNumber = $this->input->post( 'bookingNumber' );
		$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber );
		if( $bookingObj === FALSE ){
			echo "FALSE_Booking number not found!";
			return false;
		}

		if( ($bookingObj->EventID == $eventID and $bookingObj->ShowingTimeUniqueID == $showtimeID )
			=== false
		){
			echo "FALSE_This booking is not for this showing time!";
			return false;
		}
		
		
		if( $bookingObj === false )	// signifies non-existence
		{
			echo "FALSE_Booking number does not exist."; return false;
		}		
		$guestDetails = $this->Guest_model->getGuestDetails_ForCheckIn( $bookingNumber );
		$alreadyEntered = $this->Guest_model->getGuestsAlreadyEnteredEvent( $bookingNumber );
		$alreadyExited = $this->Guest_model->getGuestsAlreadyExitedEvent( $bookingNumber );
		$XMLed_guestDetails = $this->makexml_model->XMLize_AllDetailsForCheckin( $guestDetails, $alreadyEntered, $alreadyExited );
		echo "TRUE_".$XMLed_guestDetails;
		return true;
	}
	
	function confirmActivity()
	{
		/*
			Param definition
			
			$activity : if 1 - entry , if 2 - exit
		*/
		if( !$this->input->is_ajax_request() ) redirect('/');
		$processedCount = 0;
		$bookingNumber = $this->input->post( 'bookingNumber2' );
		$activity 	   = intval($this->input->post( 'activity' ));
		$bookingObj = $this->booking_model->getBookingDetails( $bookingNumber );
		if( $bookingObj === false )	// signifies non-existence
		{
			echo "FALSE_Booking number does not exist."; return false;
		}
		
		// do unset so now, we'll be only left with the guestUUIDs
		unset( $_POST[ 'bookingNumber2'] );
		unset( $_POST[ 'activity'] );
		
		// check first, anti-hack
		$alreadyEntered = $this->Guest_model->getGuestsAlreadyEnteredEvent( $bookingNumber );
		
		// now for each of the guestUUIDs, insert them
		foreach( $_POST as $key => $value )
		{
			$guestUUID = mysql_real_escape_string( $key );
			if( $activity == 1 )
			{	//check-in
				if( isset( $alreadyEntered[ $key] ) ) continue;
				$this->academic_model->recordEntry( $guestUUID );
			}else{
				if( !isset( $alreadyEntered[ $key] ) )
				{
					$this->academic_model->recordEntry( $guestUUID );
				}
				$this->academic_model->recordExit( $guestUUID );
			}
			$processedCount++;
		}
		// compare guest details of the booking with those newly inserted - which means they already entered event
		$guestDetails = $this->Guest_model->getGuestDetails( $bookingNumber );
		if( $activity == 1  )
		{
			if( count( $guestDetails ) === count( $alreadyEntered ) )
			{
				$this->booking_model->markAsConsumed_Full( $bookingNumber );
			}else{
				$this->booking_model->markAsConsumed_Partial( $bookingNumber );
			}
		}
		echo "TRUE_Okay succeeded.";
		return true;
	}//confirmActivity()
	
	
	function manageClasses()
	{
		$accountNum  = $this->session->userdata( 'accountNum' );
		$myClasses = $this->academic_model->getFacultyClasses( $accountNum );
		if( $myClasses === false )
		if( $myClasses === false )
		{			
			$data['error'] = "CUSTOM";
			$data['theMessage'] = "You have no classes. Click \"Create Class\" in the home page first.";
			$this->load->view( 'errorNotice', $data );
			return false;
		}
		$data['myClasses'] = $myClasses;
		$data['eventClassPair'] = Array();
		foreach( $myClasses as $singleClass )
		{
			$data['eventClassPair'][ $singleClass->UUID ] = $this->academic_model->getClassEventPairing( $singleClass->UUID );
		}		
		$this->load->view( 'seeAttendanceRecord/attendance01-b.php', $data );
	}
	
	function modifyClass( $uniqueID )
	{
		$data['classObj'] = $this->academic_model->getSingleClass_ByUUID( $uniqueID );
		$this->load->view('createClass/createClass01.php', $data );
	}// modifyClass(..)
	
	function seeAttendingStudents()
	{	
		/*
			02APR2012-2111!!!! Hotspot for refactoring!!! (I.e. refactor this to 
			use SQL joins instead!)
		*/
		$EC_UniqueID = $this->input->post( 'ec_uniqueid' ); 
		$attendanceData = Array();
		
		if( $EC_UniqueID === false ){
			die("Data required");
		}
							
		$ECPairObj = $this->academic_model->getClassEventPairing_ByEC_UID( $EC_UniqueID );
		$classData = $this->academic_model->getSingleClass_ByUUID( $ECPairObj->UPLBClassID );
		$studentsUnderEvent = $this->academic_model->getClassStudentPairing( $ECPairObj->UPLBClassID );
		if( $studentsUnderEvent !== false )
		{
			foreach( $studentsUnderEvent as $key => $eachStudent )
			{
				$extendedBookingInfo = $this->Guest_model->getSingleGuestExtended( $eachStudent->GuestUUID );
				if( (@$extendedBookingInfo->EventID === @$ECPairObj->EventID //!!! ERROR - Found during Best SP presentation, fix later.
					and 
					$extendedBookingInfo->ShowingTimeUniqueID === $ECPairObj->ShowtimeID )
					=== FALSE
				)
				{
					unset( $studentsUnderEvent[ $key ] );
				}
			}
		}
		
		if( is_array( $studentsUnderEvent ) and count($studentsUnderEvent) > 0 )
		{
			foreach( $studentsUnderEvent as $singleStudent )
			{
				$transResult =  $this->academic_model->getAttendanceRecord( $singleStudent->GuestUUID, $EC_UniqueID );
				if( $transResult !== false ) $attendanceData[] = $transResult;
			}		
		}
		$data['attendanceData'] = $attendanceData;
		$data['singleClass'] = $classData;
		$data['showingTime'] = $this->event_model->getSingleShowingTime( $ECPairObj->EventID, $ECPairObj->ShowtimeID );
		$this->load->view( 'seeAttendanceRecord/attendance02.php', $data );
	}
	
	function updateClass()
	{
		$classID    = $this->input->post( 'classID' );
		$title 		= $this->input->post( 'title');
		$num 		= $this->input->post('number' ); 
		$lectSect 	= $this->input->post('lectsect' );
		$recitSect 	= $this->input->post('recitsect' ); 
		$term 		= $this->input->post(  'term'); 
		$ay1 		= $this->input->post( 'acadyear_1' ); 
		$ay2 		= $this->input->post('acadyear_2' );
		$comments 	= $this->input->post('comments' );
		
		if( strlen( $classID ) < 1 ) redirect('/');
		if( $this->createClassData_preCheck($title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, 'modifyClass/'.$classID, 'Modify Class'  ) === false ) die();
		
		if( $this->academic_model->isClassExisting( $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, $classID ) 
		)
		{
			echo 'Class already exists.<br/><br/><a href="javascript: window.history.back();">Go back</a>';
			return false;
		}	
		
		if( $this->academic_model->updateClassDetails($classID, $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, $comments ) )
		{
			$data[ 'theMessage' ] = "Successfully updated your class details.";
			$data[ 'redirect' ] = TRUE;
			$data[ 'redirectURI' ] = base_url().'academicctrl/manageClasses';
			$data[ 'defaultAction' ] = 'Manage Classes';	
			$this->load->view( 'successNotice', $data );
			return true;
		}else{
			echo "HTTP 500: Something wrong happened.";
			return false;
		}
	}
}//class