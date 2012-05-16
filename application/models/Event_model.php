<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 07DEC2011 1156 

*COOKIE 'activity_data' definition:
	
	XXXX|AAAAA=BB;AAAAA;BB
	
	XXXX - instance identifier
	AAAA - entry identifier
	BB   - entry value
*/


class Event_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
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

			19JAN2012-1105 : REFACTOR: Might consider moving this to 'UsefulFunctions_model.php'
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
			
			Changed | 19FEB2012-1421 | Dates and times are now being got in Session data, formerly was post.
		*/
		$timeFrames = $this->session->userdata( 'timeFrames_hidden' );
		$dates = $this->session->userdata( 'dateFrames_hidden' );
		
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
		/*
			19JAN2012-1106 : BUG : No checking yet if $eventID already exists in the DB
		*/
		$eventID = rand(0, 1000);		
		
		$data = array(
			'eventID' => $eventID,
			'Name' => $this->input->post( 'eventName' ),
			'Location' => $this->input->post( 'location' ),
			'Description' => "echos",
			'FB_RSVP' => $this->input->post( 'eventFBRSVP' ),
			'ByUser' => $this->session->userdata( 'accountNum' ),
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
	
	function createShowings( $event_LastUniqueID = null, $eventID = null )
	{ 
		/*
			created 11DEC2011-1943
			
			Changed 19FEB2012 1432: New param $eventID / In the DB query below, substituted direct cookie access to just the $eventID parameter.
				It seems this is more secure and adhering to SE principles.
		*/		
		$showingTime_raw;
		$timeFrame;
		$dateEnd;
		$insertResult = FALSE;

		if( !is_int( $event_LastUniqueID ) and ( $event_LastUniqueID == null )  ) return false;
		if( !is_int( $eventID ) and ( $eventID == null )  ) return false;
		
		
		// CODE MISSING: database checkpoint
		// just checking the index so I don't think it is not safe to 
		// directly access POST instead of the CI way
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
				++$event_LastUniqueID,				// uniqueID, i.e., identifier of this showing time of a certain event
				$eventID							, 	// eventID, to reference to 'event' table
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
	
	function deleteAllEventInfo( $eventID = NULL )
	{
		// created 20DEC2011-1455
		
		// delete showing times
		$query_obj1 = $this->db->delete( 'showing_time', array( 'eventID' => $eventID  ) );
		// delete ticket class		
		$query_obj2 = $this->db->delete( 'ticket_class', array( 'EventID' => $eventID  ) );
		// delete event slot
		$query_obj3 = $this->db->delete( 'event_slot', array( 'EventID' => $eventID  ) );
		// delete pchannel
		$query_obj4 = $this->db->delete( 'payment_channel_availability', array( 'EventID' => $eventID  ) );
		// delete seats
		$query_obj5 = $this->db->delete( 'seats_actual', array( 'EventID' => $eventID  ) );
		// finally, from the event table
		$query_obj6 = $this->db->delete( 'event', array( 'eventID' => $eventID  ) );
		
		return ( $query_obj1 and $query_obj2 and $query_obj3 and $query_obj4 and $query_obj5 and $query_obj6 );
	} // deleteAllEventInfo

	function doesEventBelongToUser( $eventObjSent = NULL, $eventID, $accountNum )
	{
		//wait..
		$eventObj = (  $eventObjSent !== NULL ) ? $eventObjSent : $this->getEventInfo( $eventID );
		if( $eventObj === false) return false;
		return ( intval( $accountNum ) === intval( $eventObj->ByUser ) );
	}//doesEventBelongToUser(..)
	
	function getAllEvents()
	{
		// created 20DEC2011-1418		
		$sql_command = "SELECT * FROM `event` ORDER BY `Name` ASC";
		$query_obj = $this->db->query( $sql_command );
		$result_arr = $query_obj->result();
		
		return $result_arr;
	}//getAllEvents
	
	function getAllEventsRestricted()
	{
		// created 20DEC2011-1418
		$accountNum = $this->session->userdata( 'accountNum' );
		$sql_command = "SELECT * FROM `event` WHERE `ByUser` = ? ORDER BY `Name` ASC";
		$query_obj = $this->db->query( $sql_command, Array( $accountNum ) );
		$result_arr = $query_obj->result();
		
		return $result_arr;
	}//getAllEvents
	
	function getAllShowingTimes( $eventID = NULL )
	{
		if( $eventID == NULL ) return NULL;
		
		$query_obj = $this->db->get_where( 'showing_time', Array('eventID' => $eventID ) );		
		$result_arr = $query_obj->result();
		
		if( count($result_arr) > 0 )
			return $result_arr;	
		else
			return false;
	}//getAllShowingTimes(..)
			
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
			
	function getConfiguredShowingTimes( $eventID = NULL , $validToday = false )
	{
		/* Created 29DEC2011-2055					
		
			30DEC2011-1900: added param $validToday - which serves if to check if today's date is well within the
			showing time
		*/
		$query_obj;
		
		if( $eventID == NULL ) return NULL;
		
		if( !$validToday  )
		{
			$query_obj = $this->db->get_where(
				'showing_time', 
				array(
					'eventID' => $eventID,
					'STATUS' => 'CONFIGURED' 
				)
			);
		}else{	// compare today's date against the online selling availability of showing times
			date_default_timezone_set('Asia/Manila');
		
			// assemble today's date that is compatible with MySQL comparison
			// YYYY-MM-DD
			$dateToday = date( 'Y-m-d ' );			
			$timeToday = date( 'H:i:s' );
			
			// review 01MAR2012-2235 -  and why you didn't use constant CURRENT_TIMESTAMP but instead
			// $dateToday and $timeToday pa?
			$sql = "SELECT * FROM `showing_time` WHERE `EventID` = ? AND (`Status` = 'CONFIGURED' OR `Status` = 'STRAGGLE' )AND 
					CONCAT(`Selling_Start_Date`,' ',`Selling_Start_Time`) <= ? AND
					CONCAT(`Selling_End_Date`,' ',`Selling_End_Time`) >= ?;";
			$query_obj = $this->db->query( $sql, array(
					$eventID,
					$dateToday.$timeToday,					
					$dateToday.$timeToday
				)
			);			
		}
		
		$result_arr = $query_obj->result();
		if( count($result_arr) > 0 )
			return $result_arr;		
		else
			return false;
	}//getConfiguredShowingTimes(..)
	
	function getEventInfo( $eventID )
	{
		/*
			Created 05FEB2012-2234
		*/
		$sql_command = "SELECT * FROM `event` WHERE `EventID` = ?";
		$result_arr = $this->db->query( $sql_command, array( $eventID ) )->result();
		if( count( $result_arr ) < 1 ) return false;
		else
			return $result_arr[0];
	}//getEventInfo(..)
	
	function getForCheckInShowingTimes( $eventID = NULL )
	{
		if( $eventID == NULL ) return NULL;
		
		$query_obj = $this->db->get_where( 'showing_time', Array( 'eventID' => $eventID, 'Status' => 'CHECK-IN' ) );		
		$result_arr = $query_obj->result();
		
		if( count($result_arr) > 0 )
			return $result_arr;	
		else
			return false;
	}//getForCheckInShowingTimes(..)
	
	function getLastShowingTimeUniqueID( $eventID = null )
	{
		/*
			Created 30DEC2011-1155
			
			Created with regard to booking step 1.
			Returns an integer, or if none found, 0, meaning no showing time
			yet for this event.
		*/
		
		if( $eventID == null ) return false;
		
		$sql = "SELECT `UniqueID`,`EventID` FROM  `showing_time` WHERE  `EventID` =  ? ORDER BY  `UniqueID` DESC LIMIT 0 , 1000";
		$query_obj = $this->db->query( $sql, array( $eventID ) );
		$array_result = $query_obj->result();
		
		// now, what we want is found at the first element
		if( count( $array_result ) > 0 )
		{
			$lastInt = intval( $array_result[0]->UniqueID );
			return $lastInt;
		}else return 0;		
	}//getLastShowingTimeUniqueID
	
	function getReadyForSaleEvents( $allEvents )
	{
		/*
			Created 29DEC2011-2046
			
			Returns the showing times of the the events specified.
		*/
		$showingTimes = array();
		
		//CHECK if $allEvents is valid
		if( !is_array( $allEvents ) ) return false;
						
		foreach( $allEvents as $key => $singleEvent )
		{			
			$cfgST = $this->getConfiguredShowingTimes( $singleEvent->EventID , true );
			if( $cfgST === false ) continue;						
			if( count( $cfgST ) > 0 )
				$showingTimes[ $singleEvent->EventID ] = $cfgST;			
			else
				return false;
		}//foreach
		
		return $showingTimes;
	}// getReadyForSaleEvents(..)
			
	function getSingleShowingTime( $eventID = NULL, $showtimeID = NULL )
	{
		/*
			Created 05FEB2012-2039
			
			Arose at the need of Book Step 2. I'm so late nahhh!!!! WAHHHH!!
			
			Returns single MYSQL_OBJ (as in one entry) if successful, else
			boolean false.
			
			17MAR2012-1612 - Added INNER JOIN to `event`
		*/
		$sql_command = "SELECT * FROM `showing_time` INNER JOIN `event` ON `showing_time`.`EventID` =  `event`.`EventID` WHERE `showing_time`.`EventID` = ? AND `showing_time`.`UniqueID` = ?;";
		$mysql_result_arr = $this->db->query( $sql_command, array( $eventID, $showtimeID ) )->result();
		
		if( count( $mysql_result_arr ) !== 1 ) return false;
		else
			return $mysql_result_arr[0];
	}//getSingleShowingTime( .. )
	
	function getShowingTimePaymentDeadline( $eventID, $uniqueID )
	{
			/*
				Created 11FEB2012-0038
				
				Returns Array of the form
				( 'date' => 'YYYY-MM-DD', 'time' => 'HH:MM:SS' )
			*/			
			$showtime;
			$currentTime;
			$currentDate;
			$deadline = Array( "date" => '2011-01-01', "time" => "00:00:01" );
			
			date_default_timezone_set('Asia/Manila');
			$currentDate = date( 'Y-m-d' );
			$currentTime = date( 'H:i:s' );
			log_message( 'debug', 'Eventmodel-getShowingTimePaymentDeadline: '.$currentDate." - ".$currentTime );
			$showtime = $this->getSingleShowingTime( $eventID, $uniqueID );			
			if( $showtime === false ) return false;
			switch( $showtime->Book_Completion_Option )
			{
				case "FIXED_SAMEDAY":
						/*
							Bug as of 12feb2012-1641
							Current Time is 1642
							Book event on 28FEB2012, Book completion time is 1600
							
							Then this function/section will return 12FEB2012-1600 but lapsed already?
						*/						
						if( strtotime( $currentTime ) > strtotime( $showtime->Book_Completion_Time ) )
						{	// current time is greater than booking time deadline
							$deadline["date"] = date( 'Y-m-d', strtotime( '+1 day', strtotime( $currentDate ) ) );
						}else{
							$deadline["date"] = $currentDate;
						}						
						$deadline["time"] = $showtime->Book_Completion_Time;						 
						break;
				case "FIXED_AFTER":
						$deadline["time"] = $showtime->Book_Completion_Time;
						$deadline["date"] = date( 'Y-m-d', strtotime( '+'.$showtime->Book_Completion_Days.' day', strtotime( $currentDate ) ) );	// now add
						break;
				case "RELATIVE_AFTER":
						$BCT_exploded = explode( ":", $showtime->Book_Completion_Time );		// tokenize
						// get the time equivalent in millisecs since 1970? of the currenttime, then let's add the hours and mins
						$deadline["time"] = date( 'H:i', strtotime( '+'.$BCT_exploded[0].' hour +'.$BCT_exploded[1]." min ", strtotime( $currentTime ) ) ); 
						
						// now it's current date's turn and we add the number of days
						$deadline["date"] = date( 'Y-m-d', strtotime( '+'.$showtime->Book_Completion_Days.' day', strtotime( $currentDate ) ) );	// now add
						break;
			}
			//now compare against both showing time and date
			if( strtotime( $deadline["date"]." ".$deadline["time"]) > strtotime( $showtime->StartDate." ".$showtime->StartTime ) )
			{
				// if earlier computed deadline is later than the start time of the showing, then lessen 45 minutes from
				// the start of the showing time to make that as the new deadline
				$deadline["date"] = $showtime->StartDate;
				$deadline["time"] = date( 'H:i', strtotime( '-45 min', $showtime->StartTime ) );
			}
			log_message( 'debug', 'Eventmodel-getShowingTimePaymentDeadline computed:'.$deadline["date"]." ".$deadline["time"] );
			return $deadline;
	}//getShowingTimePaymentDeadline(..)
	
	
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
	
	function insertShowingInstance( $uniqueID, $eventID, $dateStart, $timeStart, $dateEnd, $timeEnd)
	{
		/*
			created 11DEC2011-2224
			
			Inserts to the table `showing_time` a single instance of such showing time,
			needing only the above parameters. Might change.
			
			30DEC2011-1204: added $uniqueID param.
		*/
								
		// replace slashes with dashes
		$dateStart = str_replace( '/', '-', $dateStart );
		$dateEnd = str_replace( '/', '-', $dateEnd );
		
		$data = array(
			'UniqueID' => $uniqueID,
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
	
	function isSeatSelectionRequired( $eventID = NULL, $showtimeID = NULL )
	{
		/*
			@created 23APR2012-0243
			@purpose Obviously
			
		*/
		$showtimeObj = $this->getSingleShowingTime( $eventID, $showtimeID );
		if( $showtimeObj === false ) return false;
		return( intval( $showtimeObj->SeatRequiredOnConfirmation ) === 1 );	
	}
	
	function isShowtimeOnlyOne( $eventID )
	{
		$showtimesObj = $this->getConfiguredShowingTimes( $eventID , TRUE );
		if( $showtimesObj === false ) return false;
		return ( count($showtimesObj) === 1 );
	}
	
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
	
	function retrieveSingleEventFromAll( $eventID = null, $allEvents = null )
	{
		/*
			Created 29DEC2011-2108
			
			$allEvents should be in object form. (That is, ->result() ran already ).
		*/
		//validate the params
		if( $eventID == null ) return false; 	// invalid function call
		if( !is_array( $allEvents ) ) return false;
		
		foreach( $allEvents as $singleEvent )
		{
			if( $singleEvent->EventID == $eventID ) return $singleEvent;
		}//foreach
		
		return null;
	}//retrieveSingleEventFromAll

	
	
	function setShowingTimeSeatMap( $seatMapUniqueID, $eventID, $showtimeID )
	{
		/*
			Created 13FEB2012-1237
			
			I realized that putting `Seat_map_UniqueID` in `seats_actual` is so redundant,
			it can just be in the `showing_time` table, in which there was really a column I
			reserved on since SP1. Darn it, why did I forget that??? A showing time can't have
			multiple seats by the way???
		*/
		$sql_command = "UPDATE `showing_time` SET `Seat_map_UniqueID` = ? WHERE `EventID` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, array( $seatMapUniqueID, $eventID, $showtimeID ) );
	}//setShowingTimeSeatMap
	
	function setParticulars( $eventID = NULL )
	{
		/*
			Created 29DEC2011-1339
			
			Basically created for Create Event-Step7.
			Updates the database entry of a showing time's online selling availability,
			deadline for paying if not paid immediately, seat requirments, etc.
		*/
		$sql;
		$query_result;
		$BookCompletionOption;
		$BookCompletionOption_sent;
		$noMoreSeat_StillSell;
		$seatRequiredOnConfirmation;
		$numOfDays = 0;
		$ShouldLookForNumOfDays = true;
		$lookForNumOfDays;
		
		if( $eventID == NULL) return FALSE;	//invalid call to this function
		
		// translate this deadline payment option into strings
		$BookCompletionOption_sent = intval( $this->input->post('deadlineChoose') );		
		switch( $BookCompletionOption_sent )
		{
			case 1: $BookCompletionOption = "FIXED_SAMEDAY"; 
					$ShouldLookForNumOfDays = false;
					break;
			case 2: $BookCompletionOption = "FIXED_AFTER"; 
					$lookForNumOfDays = "numOfDays_fixed";
					break;
			case 3: $BookCompletionOption = "RELATIVE_AFTER"; 
					$lookForNumOfDays = "numOfDays_relative";
					break;			
			default: die("setParticulars error: Invalid option for Deadline of payment of slots. Please contact admin.");
		}//switch		
		//echo var_dump($this->input->post( $lookForNumOfDays ) );
		if( $ShouldLookForNumOfDays and is_numeric( $this->input->post( $lookForNumOfDays ) ) )
		{
			$numOfDays = intval( $this->input->post( $lookForNumOfDays ) );
		}				
		// adjust the radio buttons value when submitted to what is acceptable to MySQL
		$noMoreSeat_StillSell = $this->input->post("seatNone_StillSell") == "YES"  ? TRUE : FALSE;
		$seatRequiredOnConfirmation = $this->input->post("confirmationSeatReqd") == "YES" ? TRUE : FALSE;
		// assemble sql query		
		$sql = "UPDATE `showing_time` SET
					`Selling_Start_Date` = ?,
					`Selling_Start_Time` = ?,
					`Selling_End_Date` = ?,
					`Selling_End_Time` = ?, 
					`Book_Completion_Option` = ?,
					`Book_Completion_Days` = ?,
					`Book_Completion_Time` = ?,					
					`NoMoreSeat_StillSell` = ?,
					`SeatRequiredOnConfirmation` = ?
				WHERE `EventID` = ? AND `Status` = 'BEING_CONFIGURED'; ";
		// now fire!		
		$query_result = $this->db->query( $sql, array(
				$this->input->post( 'hidden_selling_dateStart' ),
				$this->input->post( 'selling_timeStart' ),
				$this->input->post( 'hidden_selling_dateEnd' ),
				$this->input->post( 'selling_timeEnd' ),
				$BookCompletionOption ,
				$numOfDays,
				$this->input->post( 'bookCompletionTime' ),
				$noMoreSeat_StillSell,
				$seatRequiredOnConfirmation,
				$eventID
			)
		);		
		return $query_result;
	}//setParticulars
	
	function setSingleShowingTimeTicketClass( $eventID = null, $showtimeID = null, $ticketClassGroupID = null)
	{
		/*
			ACTUALLY, MISNOMER, SINCE *ALL* SHOWING TIMES UNDER AN EVENT IS  INVOLVED.
			Created 30DEC2011-1312
			
			Created due to bug arising from Create Event Step 6.
		*/
		if(  $eventID == null ) return false;
		if(  $showtimeID == null ) return false;
		if(  $ticketClassGroupID == null ) return false;
		
		$sql = "UPDATE `showing_time` SET `Ticket_class_GroupID` = ? WHERE `EventID` = ? AND `UniqueID` = ? ";
		$query_result = $this->db->query( $sql, array(  $ticketClassGroupID, $eventID, $showtimeID ) );
		
		return $query_result;
	}//setSingleShowingTimeTicketClass(..)
	
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
		// call this function to turn the scheduleString into our convention
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
	
	function setShowingTimeTicketClass( $eventID = null, $uniqueID = null)
	{
		/*
			ACTUALLY, MISNOMER, SINCE *ALL* SHOWING TIMES UNDER AN EVENT IS  INVOLVED.
			Use $this->setSingleShowingTimeTicketClass(..) if you want to be more specific.
			Created 30DEC2011-1312
			
			Created due to bug arising from Create Event Step 6.
		*/
		if(  $eventID == null ) return false;
		if(  $uniqueID == null ) return false;
		
		$sql = "UPDATE `showing_time` SET `Ticket_class_GroupID` = ? WHERE `EventID` = ? AND `Status` = 'BEING_CONFIGURED' ";
		$query_result = $this->db->query( $sql, array( $uniqueID, $eventID ) );
		
		return $query_result;
	}//setShowingTimeTicketClass(..)
			
	function setForCheckIn( $eventID, $showimeID )
	{
		$sql_command = "UPDATE `showing_time` SET `Status` = 'CHECK-IN' WHERE `EventID` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $eventID, $showimeID ) );
	}
	
	function setForStraggle( $eventID, $showtimeID )
	{
		date_default_timezone_set('Asia/Manila');
		$currentDate = date( 'Y-m-d' );
		$currentTime = date( 'H:i:s' );		
		$strx = strtotime( '+30 min', strtotime( $currentTime ) );	// now add
		$leeway = date( 'H:i:s', $strx );
		$sql_command = "UPDATE `showing_time` SET `Status` = 'STRAGGLE',`Selling_End_Date`=?,`Selling_End_Time`=? WHERE `EventID` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $currentDate, $leeway, $eventID, $showtimeID ) );						
	}
	
	function setAsCancelled( $eventID, $showimeID )
	{
		$sql_command = "UPDATE `showing_time` SET `Status` = 'CANCELLED' WHERE `EventID` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $eventID, $showimeID ) );
	}
	
	function setAsFinalized( $eventID, $showimeID )
	{
		$sql_command = "UPDATE `showing_time` SET `Status` = 'FINALIZED' WHERE `EventID` = ? AND `UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $eventID, $showimeID ) );
	}
	
	function stopShowingTimeConfiguration( $eventID )
	{
		/*
			Created 29DEC2011-1445
			
			- Different from $this->setShowingTimeConfigStat(..) in that this doesn't 
				require schedules
			- Basically created for Create Event - Step 7: changes configuration status to 'CONFIGURED' from 'BEING_CONFIGURED'
		*/
		$sql = "UPDATE `showing_time` SET `Status` = 'CONFIGURED' WHERE `Status` = 'BEING_CONFIGURED' AND `EventID` = ?; ";
		$dbAccessResult = $this->db->query( $sql, array( $eventID ) );
		
		return $dbAccessResult;
	}// stopShowingTimeConfiguration				

	function updateShowingTimeSchedule( $eventID, $showtimeID, $startDate, $startTime, $endDate, $endTime )
	{
		//Created 27mar2012-0720
		$data = Array (
			'StartDate' => $startDate,
			'StartTime' => $startTime,
			'EndDate' 	=> $endDate,
			'EndTime' 	=> $endTime
		);
		$where = "`EventID` = ".$eventID." AND `UniqueID` = ".$showtimeID;
		$sql_command = $this->db->update_string('showing_time', $data, $where);
		return $this->db->query( $sql_command );
	}
}//class

?>