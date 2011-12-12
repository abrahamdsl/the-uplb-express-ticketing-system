<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 07DEC2011 1156
*/


class Event_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function addOneDay( $thisDate )
	{	
		/*
			Created 12DEC2011-1146
			
			Receives a string, which is a date in the form of YYYY/MM/DD,
			then adds one day to it and returns that next day date in the form
			of string too.
			
			Doesn't matter if the separators are '/' (forward slash), 
			they would be replaced with dashes.			
		*/
		if ( $thisDate == ""  ) return "2000-01-01";	// sent an empty string
			
		$thisDate = str_replace( array("/","\\"), "-", $thisDate);	//replace slashes with dash		
		$newDate = strtotime( '+1 day', strtotime( $thisDate ) );	// now add
		$newDate = date( 'Y-m-j', $newDate );						// form a new date obj
		
		return $newDate;											// return
	}//addOneDay
	
	function constructMatrix( )
	{	/*
			Created 11DEC2011-1632
		*/
		$timeFrames = $this->input->post( 'timeFrames_hidden' );
		$dates = $this->input->post( 'dateFrames_hidden' );
		
		// tokenize
		$timeFrames_array = explode("|", $timeFrames);
		$dates_array = explode("|", $dates);		
		
		sort($timeFrames_array);
		sort($dates_array);
		
		foreach($dates_array as $dx)
		{
			$x = 0;
			foreach( $timeFrames_array as $ux )
			{
				$schedules[ $dx ][$x++] = $ux;
			}
		}
		
		return $schedules;
	} //constructMatrix
	
	function createEvent_basic( )
	{
		$eventID = rand(0, 1000);		
		
		$data = array(
			'eventID' => $eventID,
			'Name' => $this->input->post( 'eventName' ),
			'Description' => "echos",
			'FB_RSVP' => $this->input->post( 'eventFBRSVP' ),
			'temp' => 100
		);		
		$this->db->insert( 'event', $data );
		
		// now set cookies
		$cookie = array(
			'name' => 'eventID',
			'value' => $eventID,
			'expire' => 7200			
		);
		$this->input->set_cookie($cookie);
		
	}//createEvent_basic
	
	function createShowings()
	{ 
		/*
			created 11DEC2011-1943
		*/
		// just checking the index so I don't think it is not safe to 
		// directly access POST instead of the CI way
		$showingTime_raw;
		$timeFrame;
		$dateEnd;
		$insertResult = FALSE;;
		
		// CODE MISSING: database checkpoint
		foreach( $_POST as $key => $val )
		{	
			/* Variable $key is of the form [ YYYY/MM/DDxHH:MM_-_HH:MM ]
			   wheret the first 10 characters are the starting date of the showing time,
			   then it is separated by the letter 'x' from the starting time and ending times.
			   That time part in turn is separated by a dash. The underscores there were
			   formerly spaces that were converted into such to suit the W3 standards for 
			   valid characters in attributes/selectors of HTML elements.
			*/
			$showingTime_raw = explode( "x", $key );		// separate date and time parts 
			$timeFrame = $showingTime_raw[1];				// this is the time part, assign to some other part
			$showingTime_raw[1] = null;						// nullify this index, so we can use it again
			$showingTime_raw[1] = explode( "-", $timeFrame );					// now, split/explode the time part (start and ending time)			
			// remove the underscores
			$showingTime_raw[0] = str_replace( '_', '' ,$showingTime_raw[0]);	
			$showingTime_raw[1] = str_replace( '_', '' ,$showingTime_raw[1]);					
			// check if red eye show, if yes add one day.
			if( $this->isRedEye( $showingTime_raw ) )
			{
				$dateEnd = $this->addOneDay( $showingTime_raw[0] ); 				
			}else{
				$dateEnd = $showingTime_raw[0];
			}
			//now insert			
			$insertResult = $this->insertShowingInstance( 
				$this->input->cookie('eventID'), 	// eventID, to reference to 'event' table
				$showingTime_raw[0],				// start date
				$showingTime_raw[1][0],				// start time
				$dateEnd,							// obviously
				$showingTime_raw[1][1]				// end time	
			);
			if( !$insertResult )
			{
				// CODE MISSING:  database rollback
				return FALSE;
			}
		}
		// CODE MISSING:  database commit
				
		return true;
	}//createShowings(..)
	
	function createTicketClass( 
		$eventID  = NULL, $class = NULL, $price = 0, $slots = 0, $privileges = NULL,  $restrictions = NULL 
	)
	{
		/*
			CREATED 12DEC2011-2132
			
		*/
		$data = array(
			'EventID' => $eventID,
			'Name' => $class,
			'Price' => $price,
			'Slots' => $slots,
			'Privileges' => $privileges,
			'Restrictions' => $restrictions
		);
		
		return $this->db->insert( 'ticket_class', $data );
	}//createTicketClasses(..)
	
	function getBeingConfiguredShowingTimes( $eventID = NULL )
	{
		/*
			Created 12DEC2011-2127
			Made solely for Create Event Step 5 purpose - getting all
			being configured events for use in setting price.
		*/
		if( $eventID == NULL ) return NULL;
		
		$query_obj = $this->db->get_where(
			'showing_time', 
			array(
				'eventID' => $eventID,
				'STATUS' => 'BEING_CONFIGURED' 
			)
		);
		
		$result_arr = $query_obj->result();
		
		return $result_arr;	
	}// getBeingConfiguredShowingTimes(..)
	
	function getUnconfiguredShowingTimes( $eventID = NULL )
	{
		/* Created 12DEC2011-1227
		
			Made solely for Create Event Step 4 purpose - getting all
			unconfigured events which are newly created for them to be
			configured.
		*/
		if( $eventID == NULL ) return NULL;
		
		$query_obj = $this->db->get_where(
			'showing_time', 
			array(
				'eventID' => $eventID,
				'STATUS' => 'UNCONFIGURED' 
			)
		);
		
		$result_arr = $query_obj->result();
		
		return $result_arr;		
	}//getUnconfiguredShowingTimes(..)
	
	function insertShowingInstance( $eventID, $dateStart, $timeStart, $dateEnd, $timeEnd)
	{
		/*
			created 11DEC2011-2224
			
			Inserts to the table `showing_time` a single instance of such showing time,
			needing only the above parameters. Might change.
		*/
				
		/* DEBUGGING CODE : Remove on production
		echo "----------------<br/> I have received:";
		echo "eID: ".$eventID."<br/>"."dS: ".$dateStart."<br/>"."tS".$timeStart."<br/>"."dE :".$dateEnd."<br/>"."tE :".$timeEnd."<br/>";
		*/
		
		// replace slashes with dashes
		$dateStart = str_replace( '/', '-', $dateStart );
		$dateEnd = str_replace( '/', '-', $dateEnd );
		
		$data = array(
			'EventID' => $eventID,
			'StartDate' => $dateStart,
			'StartTime' => $timeStart,
			'EndDate' => $dateEnd,
			'EndTime' => $timeEnd
		);
		
		return $this->db->insert( 'showing_time', $data );
	}//insertShowings(..)
		
	function isEventExistent( $thisEvent )
	{
		$dbEntries;
		$query;
	
		if( $thisEvent == NULL ) return false;
		
		// $thisEvent = $this->input->post( 'eventName' ); // FOR AJAX Update method
		
		$this->db->like( 'name', $thisEvent );	// LIKE lang first muna
		$query = $this->db->get( 'event' );
		
		$dbEntries = $query->result();
		for( $x = 0, $y = $query->num_rows; $x < $y; $x++ ) 
		{
			if( strtolower( $thisEvent ) == strtolower( $dbEntries[ $x ]->Name) ) return true;			
		}
		
		return false;
	}//isEventExistent(..)
	
	function isRedEye( $thisScheduleArray )
	{
		/*
			Created 12DEC2011-1131
			
			Obvious purpose.
			Takes on of the Array form:
			--------------------------
			array
				  0 => string '2011/12/13' (length=10)	[ STRING - DATE ]
				  1 => 									[ ARRAY ]
					array
					  0 => string '11:25' (length=5)	[ STRING - TIME ]
					  1 => string '17:26' (length=5)	[ STRING - TIME ]
			------------------------------
		*/
		$timeStart = strtotime( $thisScheduleArray[1][0] );
		$timeEnd = strtotime( $thisScheduleArray[1][1] );
		
		if( $timeStart > $timeEnd ) return true;
		else return false;		
	}//isRedEye(..)
	
	function processShowingTimeRepresentation_SQL( $thisShowingTimeName = "" )
	{
		/*	 
			Created 12DEC2011-1613
			
			Basically created for Create Event-Step 5, after having some difficulties. :-)
			Accepts $thisShowingTimeName as [ YYYY/MM/DDxHH_MM_SS-HH_MM_SS ]. where MM && DD can be only one char each
				(for months earlier than October).
			Since a showing time is identified by an id/name of the form [ YYYY/MM/DDxHH_MM_SS-HH_MM_SS ]
			we have to split/tokenize them to have our preferred data structure in the form of array where
			it is of the following form:
			--------------------------
			array
				  0 => string '2011/12/13' (length=10)	[ STRING - DATE ]
				  1 => 									[ ARRAY ]
					array
					  0 => string '11:25' (length=5)	[ STRING - TIME ]
					  1 => string '17:26' (length=5)	[ STRING - TIME ]
			------------------------------			
		*/
		$showingTime_raw;
		$time_temp;		
		
		// replace slashes with dashes and then tokenize
		$showingTime_raw = explode( "x", str_replace( "/", "-", $thisShowingTimeName ) );		
		$time_temp = $showingTime_raw[1];							// this is the time part, assign to some other part
		$showingTime_raw[1] = null;									// nullify this index, so we can use it again
		$showingTime_raw[1] = explode( "-", $time_temp );			// now, split/explode the time part (start and ending time)			
		$showingTime_raw[1][0] = str_replace( '_', ':' ,$showingTime_raw[1][0]);	// now replace '_' with ':'
		$showingTime_raw[1][1] = str_replace( '_', ':' ,$showingTime_raw[1][1]);
		
		return $showingTime_raw;
	}//processShowingTimeRepresentation(..)
	
	function setShowingTimeConfigStat( $eventID = NULL, $thisScheduleString = NULL, $newStat = "UNCONFIGURED" )
	{
		/*
			Created 12DEC2011-1610
			
			Basically created for Create Event-Step 5.
			Updates the database entry of a showing time's configuration status.
		*/
		$showingTimeNewLook;
		$sql;
		$dbAccessResult = FALSE;
		
		if( $eventID == NULL or $thisScheduleString == NULL or $thisScheduleString == "" )
		{
			return FALSE;
		}
		// call this function to turn the scheduleString into our convetion
		$showingTimeNewLook = $this->processShowingTimeRepresentation_SQL( $thisScheduleString );
		
		$sql = "UPDATE `showing_time` SET `Status` = ? WHERE `EventID` = ? AND `StartDate` = ? AND `StartTime` = ? AND `EndTime` = ? ";
		$dbAccessResult = $this->db->query( $sql, array(
				$newStat,
				$eventID,
				$showingTimeNewLook[0], //start date
				$showingTimeNewLook[1][0], //start time
				$showingTimeNewLook[1][1], //end time			
			) 
		);
		
		return $dbAccessResult;
	}//setShowingTimeConfigStat(..)
	
	function setShowingTimeSlots( $eventID = NULL, $thisScheduleString = NULL, $newSlot )
	{
		/*
			Created 12DEC2011-1707
			
			Basically created for Create Event-Step 5.
			Updates the database entry of a showing time's slots.
		*/
		$showingTimeNewLook;
		$sql;
		$dbAccessResult = FALSE;
		
		if( $eventID == NULL or $thisScheduleString == NULL or $thisScheduleString == "" )
		{
			return FALSE;
		}
		// call this function to turn the scheduleString into our convetion
		$showingTimeNewLook = $this->processShowingTimeRepresentation_SQL( $thisScheduleString );
		
		$sql = "UPDATE `showing_time` SET `Slots` = ? WHERE `EventID` = ? AND `StartDate` = ? AND `StartTime` = ? AND `EndTime` = ? ";
		$dbAccessResult = $this->db->query( $sql, array(
				$newSlot,
				$eventID,
				$showingTimeNewLook[0], //start date
				$showingTimeNewLook[1][0], //start time
				$showingTimeNewLook[1][1], //end time			
			) 
		);
		
		return $dbAccessResult;
	}//setShowingTimeConfigStat(..)
	
}//class

?>