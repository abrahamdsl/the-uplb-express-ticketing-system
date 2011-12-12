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
		$this->load->model('Permission_model');
		$this->load->model('TicketClass_model');
		
		if( !$this->login_model->isUser_LoggedIn() ) redirect('/SessionCtrl');
	} //construct
	
	function index()
	{		
		$this->create();		
	}//index
	
	function create()
	{				
		$cookie = array(
			'name'   => "createEvent_step",
			'value'  => 1,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
		
		$data['userData'] = $this->login_model->getUserInfo_for_Panel();			
		$this->load->view('createEvent/createEvent_001b', $data);
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
	
	function create_step4()
	{		
		$unconfiguredShowingTimes; 
		
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
						
		// now, with the data, create showings and insert them to the database
		$this->Event_model->createShowings();
		
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
		
		$data['beingConfiguredShowingTimes'] = $this->Event_model->getBeingConfiguredShowingTimes( $this->input->cookie( 'eventID' ) );
		
		//iterate through and assign
		$classesCount = 0;
		foreach( $_POST as $key => $val)
		{
			/*
				PSEUDOCODE 12DEC2011-2140
				
				if( $key starts with "price" )
				{
					$prices[ explode("_",$key)[1] ] = $val;					
				}else
				if( $key starts with "slot" )
				{
					$slots[ explode("_",$key)[1] ] = $val;
				}
				$classes[ $classesCount ] = explode("_",$key)[1];
			
			$classesCount++;	//count how many classes
			*/
		}
		
		// ON-HOLD: 12DEC2011-2145: now, insert ticket classes
		/*
		for( $x = 0; $x < $classesCount; $x++ )
		{
			$this->Event_model->createTicketClass(
				$this->input->cookie( 'eventID' ),
			);
		}
		
		
		*/
		
		die( "12DEC2011-2152<br/><br/>This iteration is only up to this far.<br/>Please check later for more updates.<br/><br/> :-) " ); 
	}//create_step5(..)
	
	function doesEventExist()
	{
		$name = $this->input->post( 'eventName' ); 
		
		if( $name == NULL) return FALSE;
		return $this->Event_model->isEventExistent( $name );	
	} //doesEventExist
	
} //class
?>