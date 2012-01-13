<?php
/*
CREATED 28 NOV 2011 2035
*/

class EventCtrl extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('login_model');
		$this->load->model('Account_model');
		$this->load->model('Event_model');
		$this->load->model('MakeXML_model');
		$this->load->model('Permission_model');
		$this->load->model('TicketClass_model');
		$this->load->model('UsefulFunctions_model');
		
		if( !$this->login_model->isUser_LoggedIn() ) redirect('/SessionCtrl');
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	function book()
	{
		/*
			Created 29DEC2011-2048
		*/
		$configuredEventsInfo = array();
		
		$allEvents = $this->Event_model->getAllEvents();		// get all events first		
		// using all got events, check ready for sale ones (i.e. configured showing times)
		$showingTimes = $this->Event_model->getReadyForSaleEvents( $allEvents );	
		// get event info from table `events` 
		foreach( $showingTimes as $key => $singleShowingTime )
		{
			$configuredEventsInfo[ $key ] = $this->Event_model->retrieveSingleEventFromAll( $key, $allEvents );
		}
		//store to $data for passing to view
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		$data['showingTimes'] = $showingTimes;
		$data['configuredEventsInfo'] =  $configuredEventsInfo;
		
		$this->load->view( "book/bookStep1", $data );		
	}//book(..)
	
	function book_step2()
	{
		/*
			Created 30DEC2011-1855
		*/
		die(var_dump( $_POST ) );
		
	}//book_step2(..)
	
	function create()
	{				
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		$this->load->view('createEvent/createEvent_001', $data);
	}//create
	
	function create_step2()
	{		
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		$cookie = array(
			'name'   => "eventName",
			'value'  => $this->input->post( 'eventName' ),
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		//if user is accessing this directly, redirect
		if( $this->input->post( 'eventName' ) == FALSE )
		{
			redirect('/');
		}
	
		
		// is it existent
		if( $this->Event_model->isEventExistent( $this->input->post( 'eventName' ) ) )
		{
			echo('Page under construction<br/>');
			echo('Event exists. Please specify a new name. <br/>');
			echo('<a href="javascript: window.history.back();">Back</a>');
			die();
		}
		
		//now insert basic to DB
		$this->Event_model->createEvent_basic();		
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		
		// cookie does not immediately take effect so just have to pass it
		$data['eventName'] = $this->input->post( "eventName" );		 
		$this->load->view('createEvent/createEvent_002', $data);		
	}//create_step2;
	
	function create_step3()
	{
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		$schedules = array();
		
		// 11DEC2011-1609: I am using the PHP $_POST acess method instead of CI's
		// 		since I am just to check if the fields are included in the submitted data
		if( !isset( $_POST['timeFrames_hidden'] ) and
			!isset( $_POST['dateFrames_hidden'] ) 
		){
			redirect('/');
		}
		
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['scheduleMatrix'] = $this->Event_model->constructMatrix();
		
		$this->load->view( 'createEvent/createEvent_003', $data );		
	}//create_step3
	
	function create_step4( $repeat = false )
	{		
		$unconfiguredShowingTimes; 
		
		/* we go through here if first time configuring: first time configuration left some
			showing times unconfigured then finished configuring and then there are still 
			unconfigured so go back here
		*/
		$repeatPOST =  $this->input->post( 'repeat' );
		
		if( !is_bool( $repeatPOST ) )
		{
			if( strtolower( $repeatPOST ) == "true" ) $repeat = true;
		}		
		
		if( !$repeat ){
			$cookie = array(
				'name'   => "createEvent_step",
				'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
				'expire' => '3600'
			);
			$this->input->set_cookie($cookie);
			
			
			
			if( $_COOKIE['createEvent_step'] != 3 ){
				// 11DEC2011-2213 RE-ENABLE ON PRODUCTION 
				//redirect('/');
			}
			// let's get the last uniqueID for this event if ever
			$lastUniqueID = $this->Event_model->getLastShowingTimeUniqueID( $this->input->cookie('eventID') );
			
			// now, with the data, create showings and insert them to the database			
			$this->Event_model->createShowings( $lastUniqueID );
		} //if
		
		// now, get such showings straight from the DB
		$unconfiguredShowingTimes = $this->Event_model->getUnconfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		if( $unconfiguredShowingTimes == NULL)
		{			
			// CODE MISSING:  what to do on error?
			return FALSE;
		}		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['unconfiguredShowingTimes'] = $unconfiguredShowingTimes;
		$this->load->view('createEvent/createEvent_004', $data);				
	}//create_step4(..)
	
	function create_step5()
	{
		/*
			CREATED 12DEC2011-1604
			
			Assumption for $_POST: last element is 'slots' / not a showing time to be configured
		*/
		$x;		
		$y = count($_POST);
		$slots = $this->input->post( 'slots' );
		
		/* correct page submitting if the 'slots' index exists and array size or
		   contents of $_POST is > 1 (i.e., for 'slots' and at least one showing time to configure
		 */
		if( !( $slots and $y > 1 ) )
		{
			redirect('/');			
		}
		
		// re-set cookie
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => intval($this->input->cookie( "createEvent_step" )) + 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		/*			
			make some changes to showing times
		*/
		$x = 0;	// we need this counters / loop vars for considering the 'slots' index of $_POST
		$y--;
		foreach( $_POST as $key => $val)
		{		
			if( $x == $y ) break;	// this $_POST index now is 'slots'
			
			//set status of the showing times to "being_configured"
			$this->Event_model->setShowingTimeConfigStat( 
				$this->input->cookie( 'eventID' ),
				$key,
				"BEING_CONFIGURED"
			);
			
			//set slots of the showing times to the new one
			$this->Event_model->setShowingTimeSlots( 
				$this->input->cookie( 'eventID' ),
				$key,
				$slots
			);
			
			$x++;
		}//foreach(..)
		
		// get ticket classes
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();
		$data['ticketClasses_default'] = $this->TicketClass_model->getDefaultTicketClasses();
		$data['maxSlots'] = $slots;
		if( $data == NULL ){
			// CODE MISSING / EXPAND: What to do?
			die('No default ticket classes!!!! Seek admin\'s help.');			
		}
		//die(var_dump( $data['ticketClasses_default'] ));
		$this->load->view('createEvent/createEvent_005', $data);				
	}//create_step5(..)
	
	function create_step6()
	{
		/*
			CREATED 12DEC2011-2109
			
		
		*/
		$x;
		$classesCount;
		$classes = array();
		$prices = array();
		$slots = array();
		$holdingTime = array();
		$temp; 
		$lastTicketClassUniqueID;		
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		
		//iterate through submitted values, tokenize them into respective classes and assign
		$x = 0;
		$classesCount = 0;
		foreach( $_POST as $key => $val) // isn't this somewhat a security risk?
		{										
				if( $this->UsefulFunctions_model->startsWith( $key, "price" ) )
				{
					$temp = explode("_",$key);
					$prices[ $temp[1] ] = $val;					
				}else
				if( $this->UsefulFunctions_model->startsWith( $key, "slot" ) )
				{					
					$temp = explode("_",$key);
					$slots[ $temp[1] ] = $val;
				}else
				if( $this->UsefulFunctions_model->startsWith( $key, "holdingTime" ) )
				{					
					$temp = explode("_",$key);
					$holdingTime[ $temp[1] ] = $val;
				}
				if( $x % 3 == 0) $classes[ $classesCount++ ] = $temp[1];			
			$x++;	//count how many classes			
		}
		
		/* 15DEC2011-1715: DEBUGGING PURPOSES ONLY REMOVE WHEN COMFORTABLE
		echo var_dump(
			$_POST
		);
		echo var_dump(
			$classes
		);		
		echo var_dump(
			$prices
		);
		echo var_dump(
			$slots
		);
		echo var_dump( $classesCount );
		*/
				
		$databaseSuccess = TRUE;
		$lastTicketClassUniqueID = $this->TicketClass_model->getLastTicketClassUniqueID( $this->input->cookie( 'eventID' ) );
		$lastTicketClassUniqueID++;
		// CODE MISSING: database checkpoint
		for( $x = 0; $x < $classesCount; $x++ )
		{
			
			$databaseSuccess = $this->TicketClass_model->createTicketClass(
				$lastTicketClassUniqueID,
				$this->input->cookie( 'eventID' ),
				$classes[ $x ],
				$prices[ $classes[ $x ] ],
				$slots[ $classes[ $x ] ],
				"IDK",
				"IDY",
				0,
				$holdingTime[ $classes[ $x ] ]
			);
			if( !$databaseSuccess ){
				// CODE MISSING:  database rollback
				echo 'insertion failed';
				break;			
			}
		}//for
		
		// now set ticket class's unique id for the being configured events
		$this->Event_model->setShowingTimeTicketClass( $this->input->cookie( 'eventID' ), $lastTicketClassUniqueID );
				
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		$this->load->view('createEvent/createEvent_006', $data);				
	}//create_step6(..)
	
	function create_step7()
	{
		//echo( var_dump($_POST ) );
		// START: validation if correct page is being submitted
		$correctPage = true;
		$stillUnconfiguredEvents;
		
		if( !$this->input->post("hidden_selling_dateStart") ) $correctPage = false;
		if( !$this->input->post("hidden_selling_dateEnd") ) $correctPage = false;
		if( !$this->input->post("selling_timeStart") ) $correctPage = false;
		if( !$this->input->post("selling_timeEnd") ) $correctPage = false;
		if( !$this->input->post("deadlineChoose") ) $correctPage = false;
		if( !$this->input->post("bookCompletionTime") ) $correctPage = false;
		if( !$this->input->post("seatNone_StillSell") ) $correctPage = false;
		if( !$this->input->post("confirmationSeatReqd") ) $correctPage = false;
		
		if( !$correctPage )	// invalid page submitting to this or directly accessing
		{
			redirect("/");
		}
		// END: validation if correct page is being submitted
		
		if( !$this->Event_model->setParticulars( $this->input->cookie( 'eventID' ) ) )
		{
			echo "Create Step 7 Set Particulars Fail.";
			die();
		}
		$this->Event_model->stopShowingTimeConfiguration( $this->input->cookie( 'eventID' ) );	// now mark these as 'CONFIGURED'
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		// get still unconfigured events
		$stillUnconfiguredEvents = $this->Event_model->getUnconfiguredShowingTimes(  $this->input->cookie( 'eventID' )  );
		if( count( $stillUnconfiguredEvents ) > 0 )
		{											
			$this->load->view('createEvent/stillUnconfiguredNotice', $data);
		}else{
			$this->load->view('createEvent/allConfiguredNotice', $data);
		}		
		//$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		//$this->load->view('createEvent/createEvent_007', $data);				
		//die( var_dump( $_POST ) );
	}//create_step7

	function deleteEventCompletely()
	{
		$deleteResult;
		//die( var_dump ($_POST ));
		$deleteResult = $this->Event_model->deleteAllEventInfo( $this->input->post( 'eventID' ) );
		if( $deleteResult )
		{
			echo "Success";
		}else{
			echo "Fail";
		}
		
		$this->manage();
	}//deleteEventCompletely
	
	function doesEventExist()
	{
		$name = $this->input->post( 'eventName' ); 
		
		if( $name == NULL) return FALSE;
		return $this->Event_model->isEventExistent( $name );	
	} //doesEventExist
	
	function getConfiguredShowingTimes( $eventID = null )
	//function getConfiguredShowingTimes( $eventID = 686 )
	{
		/*
			Created 30DEC2011-1053
		*/
		$allConfiguredShowingTimes;
		
		//if( $eventID == null ) return "false";
		//echo var_dump( $eventID );
		/*if( $eventID == null )
			$eventID = 686;
		else*/
			$eventID = $this->input->post( 'eventID' );
		$allConfiguredShowingTimes = $this->Event_model->getConfiguredShowingTimes( $eventID , true);
		if( count( $allConfiguredShowingTimes ) == 0 )
		{
			echo "ERROR_No configured showing times.";
			return false;
		}
		$xmlResult = $this->MakeXML_model->XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes );
		
		echo $xmlResult;
		return true;
		//die( var_dump( $xmlResult ) );
	}//getConfiguredShowingTimes(..)
	
	function manage()
	{
		//created 20DEC2011-1423
		$data['events'] = $this->Event_model->getAllEvents();
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();				
		
		$this->load->view('manageEvent/home', $data);
	}//manageEvent
	
} //class
?>