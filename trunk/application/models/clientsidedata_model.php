<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 08MAR2012-0044

Okay, so I just realized how messy my processes are in accessing client-side data (cookies and
CI-session). So now, I made this to centralize them all. It's like Object-oriented programming. hehehe.

Also, first model to completely be in lowercase. Yeah, because most web hosts are using Linux
and this OS is case-sensitive when it comes to files.

So for now, there are only get, set and delete operations.
*/


class clientsidedata_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
	
		/*define define define*/
		define("EVENT_NAME", 'eventName');
		define("EVENT_LOCATION", 'location');
		define("EVENT_ID", 'eventID');
		define("SHOWTIME_ID", 'showtimeID');
		define("TICKET_CLASS_UNIQUE_ID", 'ticketClassUniqueID');
		define("TICKET_CLASS_GROUP_ID", 'ticketClassGroupID');
		define("START_DATE", 'startDate');
		define("START_TIME", 'startTime');
		define("END_DATE", 'endDate');
		define("END_TIME", 'endTime');
		define("SLOT_QUANTITY", 'slots_being_booked');
		define("SLOT_SAME_TICKETCLASS", 'is_there_slot_in_same_tclass');
		define("BOOKING_NUMBER", 'bookingNumber');
		define("PURCHASE_IDS",  "purchases_identifiers");
		define("VISUALSEAT_DATA",  "visualseat_data");
		define("MANAGE_BOOKING_NEW_SEAT_UUIDS",  "mbooknsu");
		define("MANAGE_BOOKING_NEW_SEAT_MATRIX",  "mbooknsm");
	}

	function deleteAvailabilityOfSlotInSameTicketClass()
	{
		return $this->deleteCookieUnified(SLOT_SAME_TICKETCLASS );
	}	
	
	function deleteCookieUnified( $name )
	{
		return delete_cookie( $name );
	}
	
	function deleteBookingNumber()
	{
		return $this->deleteCookieUnified( BOOKING_NUMBER );
	}
			
	function deleteEndDate()
	{
		return $this->deleteCookieUnified( END_DATE );
	}
	
	function deleteEndTime()
	{
		return $this->deleteCookieUnified( END_TIME );
	}
	
	function deleteEventID()
	{
		return $this->deleteCookieUnified( EVENT_ID );
	}
	
	function deleteEventLocation()
	{
		return $this->deleteCookieUnified( EVENT_LOCATION );
	}
	
	function deleteEventName()
	{
		return $this->deleteCookieUnified( EVENT_NAME );
	}
	
	function deleteManageBookingNewSeatMatrix()
	{
		return $this->deleteCookieUnified( MANAGE_BOOKING_NEW_SEAT_MATRIX );
	}
	
	function deleteManageBookingNewSeatUUIDs()
	{
		return $this->deleteCookieUnified( MANAGE_BOOKING_NEW_SEAT_UUIDS );
	}
	
	function deletePurchaseIDs()
	{
		return $this->deleteCookieUnified( PURCHASE_IDS  );
	}
	
	function deleteShowtimeID()
	{
		return $this->deleteCookieUnified( SHOWTIME_ID );
	}
	
	function deleteSlotsBeingBooked()
	{
		return $this->deleteCookieUnified( SLOT_QUANTITY );
	}
	
	function deleteStartDate()
	{
		return $this->deleteCookieUnified( START_DATE );
	}
	
	function deleteStartTime()
	{
		return $this->deleteCookieUnified( START_TIME );
	}
	
	function deleteTicketClassGroupID()
	{
		return $this->deleteCookieUnified( TICKET_CLASS_GROUP_ID );
	}
	
	function deleteTicketClassUniqueID()
	{
		return $this->deleteCookieUnified( TICKET_CLASS_UNIQUE_ID  );
	}
	
	function deleteVisualSeatInfo()
	{
		return $this->deleteCookieUnified( VISUALSEAT_DATA );
	}
	
	function getAvailabilityOfSlotInSameTicketClass( )
	{
		return $this->getCookieUnified(SLOT_SAME_TICKETCLASS );
	}
	
	function getCookieUnified( $name )
	{
		return $this->input->cookie( $name );
	}
	
	function getBookingNumber()
	{
		return $this->getCookieUnified( BOOKING_NUMBER );
	}
			
	function getEndDate()
	{
		return $this->getCookieUnified( END_DATE );
	}
	
	function getEndTime()
	{
		return $this->getCookieUnified( END_TIME );
	}
	
	function getEventID()
	{
		return $this->getCookieUnified( EVENT_ID );
	}
	
	function getEventLocation()
	{
		return $this->getCookieUnified( EVENT_LOCATION );
	}
	
	function getEventName()
	{
		return $this->getCookieUnified( EVENT_NAME );
	}
	
	function getManageBookingNewSeatMatrix()
	{
		return $this->getCookieUnified( MANAGE_BOOKING_NEW_SEAT_MATRIX );
	}
	
	function getManageBookingNewSeatUUIDs()
	{
		return $this->getCookieUnified( MANAGE_BOOKING_NEW_SEAT_UUIDS );
	}
	
	function getPurchaseIDs()
	{
		return $this->getCookieUnified( PURCHASE_IDS  );
	}
	
	function getShowtimeID()
	{
		return $this->getCookieUnified( SHOWTIME_ID );
	}
	
	function getSlotsBeingBooked()
	{
		return $this->getCookieUnified( SLOT_QUANTITY );
	}
	
	function getStartDate()
	{
		return $this->getCookieUnified( START_DATE );
	}
	
	function getStartTime()
	{
		return $this->getCookieUnified( START_TIME );
	}
	
	function getTicketClassGroupID()
	{
		return $this->getCookieUnified( TICKET_CLASS_GROUP_ID );
	}
	
	function getTicketClassUniqueID()
	{
		return $this->getCookieUnified( TICKET_CLASS_UNIQUE_ID  );
	}
	
	function getVisualSeatInfo()
	{
		return $this->getCookieUnified( VISUALSEAT_DATA );
	}
	
	/*set part*/
	
			
	function getBookingCookieNames()
	{		
		/*
			08MAR2012-0049 | Moved from Event_model
		
			09FEB2012-0059 | These cookies are for use in the booking steps.			
			10FEB2012-2255 |  Added "ticketClassGroupID"
			11FEB2012-0024 |  Added "bookingNumber"
			29FEB2012-1238 | Added 'purchases_identifiers', 'visualseat_data'
			
		*/	
		return ( Array( 
				EVENT_ID, SHOWTIME_ID, TICKET_CLASS_GROUP_ID, EVENT_NAME, START_DATE, 
				START_TIME, END_DATE, END_TIME, SLOT_QUANTITY, EVENT_LOCATION, BOOKING_NUMBER,
				TICKET_CLASS_UNIQUE_ID, PURCHASE_IDS, VISUALSEAT_DATA
			) 
		);
	}//getBookingCookieNames()
	
	function setAvailabilityOfSlotInSameTicketClass( $value, $expiry = 3600)
	{
		return $this->setCookieUnified(SLOT_SAME_TICKETCLASS, $value, $expiry );
	}
	
	function setCookieUnified( $name, $value, $expiry )
	{
		$cookie = Array(
			'name' => $name,
			'value' => $value,
			'expire' => $expiry		
		);
		$this->input->set_cookie($cookie);
	}
	
	function setBookingNumber( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( BOOKING_NUMBER, $value, $expiry );
	}
			
	function setEndDate( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( END_DATE, $value, $expiry );
	}
	
	function setEndTime( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( END_TIME, $value, $expiry );
	}
	
	function setEventID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( EVENT_ID, $value, $expiry );
	}
	
	function setEventLocation( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( EVENT_LOCATION, $value, $expiry );
	}
	
	function setEventName( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( EVENT_NAME, $value, $expiry );
	}
	
	function setManageBookingNewSeatMatrix( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( MANAGE_BOOKING_NEW_SEAT_MATRIX , $value, $expiry);
	}
	
	function setManageBookingNewSeatUUIDs( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( MANAGE_BOOKING_NEW_SEAT_UUIDS, $value, $expiry );
	}
	
	function setPurchaseIDs( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( PURCHASE_IDS , $value, $expiry );
	}
	
	function setShowtimeID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( SHOWTIME_ID, $value, $expiry );
	}
	
	function setSlotsBeingBooked( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( SLOT_QUANTITY, $value, $expiry );
	}
	
	function setStartDate( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( START_DATE, $value, $expiry );
	}
	
	function setStartTime( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( START_TIME, $value, $expiry );
	}
	
	function setTicketClassGroupID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( TICKET_CLASS_GROUP_ID, $value, $expiry );
	}
	
	function setTicketClassUniqueID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( TICKET_CLASS_UNIQUE_ID , $value, $expiry );
	}
	
	function setVisualSeatInfo( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( VISUALSEAT_DATA, $value, $expiry );
	}
	
	// lalalala
	function appendSessionActivityDataEntry( $field, $value )
	{
		// CREATED 05MAR2012-1902		
		$activityData1 = $this->input->cookie( 'activity_data' );
		
		$activityData = explode('|', $activityData1 );	// the zeroth index is just the 'book_instance_num'
		
		if( is_array( $activityData ) and count( $activityData ) === 2 ) 
		{
			if( $activityData[1] !== false ) $activityData[1] .= ($field.'='.$value.';');
		}else{
			die('you need to create session activity first' );
		}		
		$this->updateSessionActivityData( $activityData[1] );
	}//appendSessionActivityDataEntry
	
	function appendSessionActivityDataEntryLong( $data_x )
	{
		/* CREATED 05MAR2012-1902		
		
			Form of parameter should be { XXXX=YYY | {XXXX=YYY;}* }
		*/
		$activityData1 = $this->input->cookie( 'activity_data' );		
		$activityData = explode('|', $activityData1 );	// the zeroth index is just the 'book_instance_num'		
		if( is_array( $activityData ) and count( $activityData ) === 2 ) 
		{
			if( $activityData[1] !== false ) $activityData[1] .= $data_x;
		}else{
			die('you need to create session activity first' );
		}		
		$this->updateSessionActivityData( $activityData[1] );
	}//appendSessionActivityDataEntryLong
	
	function changeSessionActivityDataEntry( $field, $newValue )
	{
		// CREATED 05MAR2012-1902		
		$activityData = $this->input->cookie( 'activity_data' );
		$activityData_tokenized;
		if( $activityData !== false )
		{			
			//separates the data part and the 'book_instance_number' value
			$activityData_tokenized1 = explode('|', $activityData );
			//separates the entries now 
			$activityData_tokenized2 = explode(';', $activityData_tokenized1[1] );			
			$activityData_tokenized3 = Array();
			// "array-ize" the data
			//echo var_dump( $activityData_tokenized1 );
			for( $x = 0, $y = count( $activityData_tokenized2 ), --$y; $x < $y; $x++ )
			{					
					$dataProper = explode('=', $activityData_tokenized2[$x] );
					$activityData_tokenized3[ $dataProper[0] ] = $dataProper[1];
			}
			//now search for the key, if found then yes.
			foreach( $activityData_tokenized3 as $key => $value )
			{
				if( $field == $key ) {
					$activityData_tokenized3[ $key ] = $newValue;
					$serializedData = "";
					foreach( $activityData_tokenized3 as $key2 => $value2 )
					{
						$serializedData .= ( $key2."=".$value2.";" );						
					}
					$this->updateSessionActivityData( $serializedData );
					return true;
				}
			}
			return false;
		}else{
			return false;
		}		
	}//getSessionActivityDataEntry
	
	function deleteBookingCookies()
	{
		/*
			Created 06FEB2012-1734
		*/
		$cookie_names = $this->getBookingCookieNames();
		// delete first the cookie for slot UUIDs
		//$this->getTicketClassUniqueID();
		delete_cookie( $this->getTicketClassUniqueID().'_slot_UUIDs' );
		foreach( $cookie_names as $singleCookie ) delete_cookie( $singleCookie );
	}
	
	function getSessionActivity( )
	{
		/*
			Created 04MAR2012-1241
		*/
		$returnThis = Array();
		$returnThis[0] = $this->session->userdata( 'activity_name' );
		$returnThis[1] = $this->session->userdata( 'activity_stage' );
		return $returnThis;
	}//
	
	function getSessionActivityDataEntry( $field )
	{
		// CREATED 05MAR2012-1902		
		$activityData = $this->input->cookie( 'activity_data' );
		$activityData_tokenized;
		if( $activityData !== false )
		{			
			//separates the data part and the 'book_instance_number' value
			$activityData_tokenized1 = explode('|', $activityData );
			//separates the entries now 
			$activityData_tokenized2 = explode(';', $activityData_tokenized1[1] );			
			$activityData_tokenized3 = Array();
			// "array-ize" the data
			//echo var_dump(  $activityData_tokenized2 );
			for( $x = 0, $y = count( $activityData_tokenized2 )-1; $x < $y; $x++ )
			{
					$dataProper = explode('=', $activityData_tokenized2[$x] );
					$activityData_tokenized3[ $dataProper[0] ] = $dataProper[1];
			}
			//now search for the key, if found then yes.
			//echo var_dump(  $activityData_tokenized3 );
			foreach( $activityData_tokenized3 as $key => $value ) if( $field == $key )return $value;
			return false;
		}else{
			return false;
		}		
	}//getSessionActivityDataEntry
		
	function isSessionActivityDataEntryEqualTo( $field, $intendedValue, $type = "STRING" )
	{
		/*
			Created 08MAR2012-0259
		*/
		$valueInCookie = $this->getSessionActivityDataEntry( $field );
		if( $valueInCookie === false ) return false;
		$type = strtoupper( $type );
		switch( $type )
		{
			case "INT": return ( intval( $intendedValue) === intval( $valueInCookie) );
			case "FLOAT": return ( floatval( $intendedValue) === floatval( $valueInCookie) );
			case "STRING": return ( strval( $intendedValue) === strval( $valueInCookie) );		
		}	
	}//isSessionActivityDataEntryE
	
	
	function setBookingCookies( $cookie_values )
	{
		/*
			Created 09FEB2012-0052. Moved from EventCtrl for refactoring purposes.
			
			Sets cookies needed in booking process. Called in EventCtrl/book_step2
		*/		
		$cookie_names = $this->getBookingCookieNames();
		$y = count($cookie_names);
		//unset( $cookie_names["ticketClassUniqueID"] );		// this is to be set next page ( after current - ticket class selection) so removed.	
		for( $x=0; $x<$y; $x++ )	// $cookie_names is global - found in construct, less 1 for y initially due to unset(..) earlier
		{
			$cookie = Array(
				'name' => $cookie_names[ $x ],
				'value' => $cookie_values[ $x ],
				'expire' => 3600				// change later to how long ticketclass hold time
			);
			$this->input->set_cookie($cookie);		
		}	
	}// setBookingCookies
	
	function setSessionActivity( $name, $stage, $data = NULL )
	{
		/*
			Created 02MAR2012-2055
		*/		
		$this->session->set_userdata( 'activity_name', $name );	
		$this->updateSessionActivityStage( $stage );
		$this->updateSessionActivityData( $data );
	}//setSessionActivity()
	
	function updateSessionActivityData( $data )
	{
		// CREATED 04MAR2012-1238				
		$cookie = array(
			'name'   => 'activity_data',
			'value'  => 'TEMP|'.$data,
			'expire' => '3600'
		);
		$this->input->set_cookie($cookie);
	}
	
	function updateSessionActivityStage( $stage )
	{
		// CREATED 04MAR2012-1238
		@$this->session->unset_userdata( 'activity_stage' );
		$this->session->set_userdata( 'activity_stage', $stage );
	}
	
}