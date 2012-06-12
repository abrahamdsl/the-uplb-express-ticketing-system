<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Client-side Data Access Model
* 	Created 08MAR2012-0044
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This centralizes our access to cookies and CodeIgniter's session data (which is actually a cookie
     called 'ci_session' by default
*   Also, first model to completely be in lowercase. Yeah, because most web hosts are using Linux and this OS
    is case-sensitive when it comes to files.
*/

class clientsidedata_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->load->helper('cookie');
		$this->load->library('session');
		include_once( APPPATH.'constants/_constants.inc');
		include_once( APPPATH.'constants/clientsidedata.inc');
	}
	
	function deleteAdminResetsPasswordIndicator()
	{
		// USES SESSION DATA
		return $this->session->unset_userdata( RESETPASS_BY_ADMIN_PRESENCE );
	}
	
	function deleteAvailabilityOfSlotInSameTicketClass()
	{
		return $this->deleteCookieUnified(SLOT_SAME_TICKETCLASS );
	}	
	
	function deleteBookingCookiesOnServerUUIDRef()
	{
		return $this->session->unset_userdata( BOOKING_COOKIES_ON_SERVER_UUID );
	}
	
	function deleteBookingProgressIndicator()
	{		
		return $this->session->unset_userdata( BOOKING_PROGRESS_INDICATOR );
	}
	
	function deleteBookInstanceEncryptionKey()
	{
		// USES SESSION DATA
		return $this->session->unset_userdata( BOOK_INSTANCE_ENCRYPTION_KEY );
	}
		
	function deleteClassUUIDs()
	{
		return $this->deleteCookieUnified( CLASS_UUIDs );
	}
			
	function deleteCookieUnified( $name )
	{
		return delete_cookie( $name );
	}
	
	function deleteBookingNumber()
	{
		return $this->deleteCookieUnified( BOOKING_NUMBER );
	}
			
	function deleteDataForPaypal()
	{
		return $this->deleteCookieUnified( PAYPAL_DATA );
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
	
	function deletePaymentDeadlineDate()
	{
		return $this->deleteCookieUnified( PAYMENT_DEADLINE_DATE );
	}
	
	function deletePaymentChannel()
	{
		// Uses SESSION data
		return $this->session->unset_userdata( PAYMENT_CHANNEL );
	}
	
	function deletePaymentDeadlineTime()
	{
		return $this->deleteCookieUnified( PAYMENT_DEADLINE_TIME );
	}
	
	function deletePaymentModeExclusion()
	{
		return $this->deleteCookieUnified( PAYMENT_MODE_EXCLUSION );
	}
	
	function deletePaypalAccessible()
	{
		// Uses SESSION data
		return $this->session->unset_userdata( PAYPAL_ACCESS_INDICATOR );
	}
	
	function deletePaypalCrucialDataErrorNoticeAccessible()
	{
		// Uses SESSION data
		return $this->session->unset_userdata( PAYPAL_CRUCIALDATA_ERR_INDICATOR );
	}
	
	function deletePaypal_IPN_Data()
	{		
		return $this->deleteCookieUnified( PAYPAL_IPN );		
	}
	function deletePurchaseCount()
	{
		// USES SESSION DATA
		return $this->session->unset_userdata( PURCHASE_COUNT );
	}
	
	function deletePurchaseIDs()
	{
		return $this->deleteCookieUnified( PURCHASE_IDS  );
	}
	
	function deletePurchaseTotalCharge()
	{
		// USES SESSION DATA
		return $this->session->unset_userdata( TOTAL_CHARGE );
	}
	
	function deleteReceptionistActivity()
	{
		return $this->deleteCookieUnified( RECEPTIONIST_ACTIVITY );
	}
	
	function deleteRedirectionURLAfterAuth()
	{
		return $this->deleteCookieUnified( AUTH_THEN_REDIRECT );
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
	
	function deleteTicketClassSlotUUIDsCookie( $uniqueID )
	{
		return $this->deleteCookieUnified( $uniqueID.SLOT_UUID_JOINER );
	}
	
	function deleteTicketClassUniqueID()
	{
		return $this->deleteCookieUnified( TICKET_CLASS_UNIQUE_ID  );
	}
			
	function deleteUPLBClassUUID()
	{
		return $this->deleteCookieUnified( UPLB_CLASS_UUID );
	}
	
	function deleteUPLBConsEmpNumPair()
	{
		return $this->deleteCookieUnified( UPLB_CONS_EMP_PAIR );
	}
	
	function deleteUPLBConsStudentNumPair()
	{
		return $this->deleteCookieUnified( UPLB_CONS_STUDENT_PAIR );
	}
	
	function deleteVisualSeatInfo()
	{
		return $this->deleteCookieUnified( VISUALSEAT_DATA );
	}
	
	function getAdminResetsPasswordIndicator()
	{
		// USES SESSION DATA
		return $this->session->userdata( RESETPASS_BY_ADMIN_PRESENCE );
	}
	
	function getAvailabilityOfSlotInSameTicketClass( )
	{
		return $this->getCookieUnified(SLOT_SAME_TICKETCLASS );
	}
	
	function getBookingCookiesOnServerUUIDRef()
	{
		return $this->session->userdata( BOOKING_COOKIES_ON_SERVER_UUID );
	}
	
	function getBookingNumber()
	{
		return $this->getCookieUnified( BOOKING_NUMBER );
	}
	
	function getBookingProgressIndicator()
	{
		return $this->session->userdata( BOOKING_PROGRESS_INDICATOR );
	}
	
	function getBookInstanceEncryptionKey()
	{
		// USES SESSION DATA
		return $this->session->userdata( BOOK_INSTANCE_ENCRYPTION_KEY );
	}
	
	function getClassUUIDs()
	{
		return $this->getCookieUnified( CLASS_UUIDs );
	}
	
	function getCookieUnified( $name )
	{
		return $this->input->cookie( $name );
	}
	
	function getDataForPaypal()
	{
		return $this->getCookieUnified( PAYPAL_DATA );
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
		/**
			Returns seat coordinates of the guests whose seats are changed.
		*/
		return $this->getCookieUnified( MANAGE_BOOKING_NEW_SEAT_MATRIX );
	}
	
	function getManageBookingNewSeatUUIDs()
	{
		/**
			Returns UUIDs of the guests whose seats are changed.
		*/
		return $this->getCookieUnified( MANAGE_BOOKING_NEW_SEAT_UUIDS );
	}
	
	function getPaymentChannel()
	{
		// Uses SESSION data
		return intval($this->session->userdata( PAYMENT_CHANNEL ));
	}
	
	function getPaymentDeadlineDate()
	{
		return $this->getCookieUnified( PAYMENT_DEADLINE_DATE );
	}
	
	function getPaymentDeadlineTime()
	{
		return $this->getCookieUnified( PAYMENT_DEADLINE_TIME );
	}
	
	function getPaymentModeExclusion()
	{
		return $this->getCookieUnified( PAYMENT_MODE_EXCLUSION );
	}
	
	function getPaypalCrucialDataErrorNoticeAccessible()
	{
		// Uses SESSION data
		return $this->session->userdata( PAYPAL_CRUCIALDATA_ERR_INDICATOR );
	}
	
	function getPaypal_IPN_Data()
	{		
		return $this->getCookieUnified( PAYPAL_IPN );		
	}
	
	function getPurchaseCount()
	{
		// USES SESSION DATA
		return $this->session->userdata( PURCHASE_COUNT );
	}
		
	function getPurchaseIDs()
	{
		return $this->getCookieUnified( PURCHASE_IDS  );
	}
	
	function getPurchaseTotalCharge()
	{
		// USES SESSION DATA
		return floatval( $this->session->userdata( TOTAL_CHARGE ) );
	}
	
	function getReceptionistActivity()
	{
		return $this->getCookieUnified( RECEPTIONIST_ACTIVITY );
	}
	
	function getRedirectionURLAfterAuth()
	{
		return $this->getCookieUnified( AUTH_THEN_REDIRECT );
	}
		
	function getShowtimeID()
	{
		return $this->getCookieUnified( SHOWTIME_ID );
	}
	
	function getSlotsBeingBooked()
	{
		return intval( $this->getCookieUnified( SLOT_QUANTITY ) );
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
	
	function getTicketClassSlotUUIDsCookie( $uniqueID )
	{
		return $this->getCookieUnified( $uniqueID.SLOT_UUID_JOINER );
	}
		
	function getTicketClassUniqueID()
	{
		return $this->getCookieUnified( TICKET_CLASS_UNIQUE_ID  );
	}
		
	function getUPLBClassUUID()
	{
		return $this->getCookieUnified( UPLB_CLASS_UUID );
	}
	
	function getUPLBConsEmpNumPair()
	{
		return $this->getCookieUnified( UPLB_CONS_EMP_PAIR );
	}
	
	function getUPLBConsStudentNumPair()
	{
		return $this->getCookieUnified( UPLB_CONS_STUDENT_PAIR );
	}
	
	function getVisualSeatInfo()
	{
		return $this->getCookieUnified( VISUALSEAT_DATA );
	}
					
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
	
	/*set part*/
	function setAdminResetsPasswordIndicator( $concernedUserAccountNum )
	{
		// USES SESSION DATA
		return $this->session->set_userdata( RESETPASS_BY_ADMIN_PRESENCE, $concernedUserAccountNum );
	}
	
	function setAvailabilityOfSlotInSameTicketClass( $value, $expiry = 3600)
	{
		return $this->setCookieUnified(SLOT_SAME_TICKETCLASS, $value, $expiry );
	}
	
	function setBookingProgressIndicator( $value )
	{
		// USES SESSION DATA
		@$this->session->unset_userdata( BOOKING_PROGRESS_INDICATOR );
		return $this->session->set_userdata( BOOKING_PROGRESS_INDICATOR, $value );
	}
	
	function setBookInstanceEncryptionKey()
	{
		// USES SESSION DATA
		 // just some random range
		  $bookInstanceEncryptionKey = rand( 9928192, 139124824 );
		  return $this->session->set_userdata( BOOK_INSTANCE_ENCRYPTION_KEY, $bookInstanceEncryptionKey );
	}
	
	function setClassUUIDs($value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( CLASS_UUIDs, $value, $expiry );
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
	{	// DEPRECATED-NDX
		return $this->setCookieUnified( BOOKING_NUMBER, $value, $expiry );
	}
	
	function setDataForPaypal( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( PAYPAL_DATA, $value, $expiry );
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
	
	function setPaymentChannel( $value )
	{
		// Uses SESSION data
		return $this->session->set_userdata( PAYMENT_CHANNEL, $value );
	}
	
	function setPaymentDeadlineDate( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( PAYMENT_DEADLINE_DATE, $value, $expiry );
	}
	
	function setPaymentDeadlineTime( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( PAYMENT_DEADLINE_TIME, $value, $expiry );
	}
	
	function setPaymentModeExclusion( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( PAYMENT_MODE_EXCLUSION, $value, $expiry );
	}
	
	function setPaypal_IPN_Data( $value = NULL, $expiry = 3600 )
	{		
		return $this->setCookieUnified( PAYPAL_IPN, $value, $expiry );
	}
	
	function setPurchaseCount( $value )
	{
		// USES SESSION DATA
		return $this->session->set_userdata( PURCHASE_COUNT, $value );
	}	
	
	function setPurchaseIDs( $value = NULL, $expiry = 3600 )
	{
		@$this->deleteCookieUnified( PURCHASE_IDS );
		return $this->setCookieUnified( PURCHASE_IDS, $value, $expiry );
	}
	
	function setPurchaseTotalCharge( $value )
	{
		// USES SESSION DATA
		return $this->session->set_userdata( TOTAL_CHARGE, $value );
	}
	
	function setPaypalAccessible()
	{
		// Uses SESSION data
		return $this->session->set_userdata( PAYPAL_ACCESS_INDICATOR, 1 );
	}
	
	function setPaypalCrucialDataErrorNoticeAccessible()
	{
		// Uses SESSION data
		return $this->session->set_userdata( PAYPAL_CRUCIALDATA_ERR_INDICATOR, 1 );
	}
	
	function setReceptionistActivity( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified(  RECEPTIONIST_ACTIVITY, $value, $expiry );
	}	
	
	function setRedirectionURLAfterAuth( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( AUTH_THEN_REDIRECT, $value, $expiry );
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
	
	function setTicketClassSlotUUIDsCookie( $uniqueID, $value = NULL, $expiry = 3600 )
	{	// DEPRECATED
		return $this->setCookieUnified( $uniqueID.SLOT_UUID_JOINER, $value, $expiry );
	}
	
	function setTicketClassUniqueID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( TICKET_CLASS_UNIQUE_ID , $value, $expiry );
	}
	
	function setUPLBClassUUID( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( UPLB_CLASS_UUID, $value, $expiry );
	}
	
	function setUPLBConsEmpNumPair( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( UPLB_CONS_EMP_PAIR, $value, $expiry );
	}
	
	function setUPLBConsStudentNumPair( $value = NULL, $expiry = 3600 )
	{
		return $this->setCookieUnified( UPLB_CONS_STUDENT_PAIR, $value, $expiry );
	}
			
	function setVisualSeatInfo( $value = NULL, $expiry = 3600 )
	{
		@$this->deleteCookieUnified( VISUALSEAT_DATA );
		return $this->setCookieUnified( VISUALSEAT_DATA, $value, $expiry );
	}
	
	/**
	*	Those above are about setting session data and cookies.
	**/
	
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
		delete_cookie( $this->getTicketClassUniqueID().'_slot_UUIDs' );		
		delete_cookie( PAYMENT_DEADLINE_DATE );
		delete_cookie( PAYMENT_DEADLINE_TIME );
		delete_cookie( UPLB_CONS_STUDENT_PAIR );
		delete_cookie( UPLB_CONS_EMP_PAIR );
		delete_cookie( BOOKING_PROGRESS_INDICATOR );
		delete_cookie( PAYPAL_IPN );
		delete_cookie( PAYPAL_DATA );
		foreach( $cookie_names as $singleCookie ) delete_cookie( $singleCookie );
	}
	
	function getAccountNum()
	{
		//session data!
		return $this->session->userdata( ACCOUNT_NUM );
	}
	
	function getSessionActivity( )
	{
		/*
			Created 04MAR2012-1241
		*/
		$returnThis = Array();
		$returnThis[0] = $this->session->userdata( ACTIVITY_NAME );
		$returnThis[1] = $this->session->userdata( ACTIVITY_STAGE );
		return $returnThis;
	}//
	
	function getSessionActivityStage()
	{
		/*
			Created for callers who just need stage information, so
			as not to directly access array bla bla back there.
		*/
		$sessionObj = $this->getSessionActivity();
		return intval( $sessionObj[1] );
	}
	
	function getSessionActivityDataEntry( $field )
	{
		// CREATED 05MAR2012-1902		
		$activityData = $this->input->cookie( ACTIVITY_DATA );
		$activityData_tokenized;
		if( $activityData !== false )
		{			
			//separates the data part and the 'book_instance_number' value
			$activityData_tokenized1 = explode('|', $activityData );
			//separates the entries now 
			$activityData_tokenized2 = explode(';', $activityData_tokenized1[1] );			
			$activityData_tokenized3 = Array();
			// "array-ize" the data		
			for( $x = 0, $y = count( $activityData_tokenized2 )-1; $x < $y; $x++ )
			{
					$dataProper = explode('=', $activityData_tokenized2[$x] );
					$activityData_tokenized3[ $dataProper[0] ] = (isset($dataProper[1])) ? $dataProper[1] : 0;
			}
			//now search for the key, if found then yes.		
			foreach( $activityData_tokenized3 as $key => $value ) if( $field == $key )return $value;
			return false;
		}else{
			return false;
		}		
	}//getSessionActivityDataEntry
	
	function isPaypalAccessible()
	{
		// Uses SESSION data
		$thatSessData = $this->session->userdata( PAYPAL_ACCESS_INDICATOR );
		return ( $thatSessData !== FALSE or  intval($thatSessData) === 1 );
	}//isPaypalAccessible()
	
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
	}//isSessionActivityDataEntryEqualTo
	
	
	function setBookingCookies( $cookie_values )
	{
		/*
			Created 09FEB2012-0052. Moved from EventCtrl for refactoring purposes.
			
			Sets cookies needed in booking process. First called in EventCtrl/book_step2
		*/		
		$cookie_names = $this->getBookingCookieNames();
		$y = count($cookie_names);
		//<area type="debug">
		$cookie = Array(
				'name' =>  'debug-PRE',
				'value' => 'tang',
				'expire' => 3600				// change later to how long ticketclass hold time
			);
		$this->input->set_cookie($cookie);
		//</area>
		for( $x=0; $x<$y; $x++ )
		{
			$cookie = Array(
				'name' =>  $cookie_names[ $x ],
				'value' => $cookie_values[ $x ],
				'expire' => 3600				// change later to how long ticketclass hold time				 				
			);
			$this->input->set_cookie($cookie);			
		}
		//<area type="debug">
		$cookie = Array(
				'name' =>  'debug-POST',
				'value' => 'eight-o-clock',
				'expire' => 3600				// change later to how long ticketclass hold time
			);
		$this->input->set_cookie($cookie);		
		//</area>
		$y = count($cookie_names);
		for( $x=0; $x<$y; $x++ )
		{
			log_message('DEBUG', 'Cookie try| ' .$cookie_names[ $x ]."|".$cookie_values[ $x ] );
		}
	}// setBookingCookies
	
	function setBookingCookiesOnServerUUIDRef( $UUID )
	{
		return $this->session->set_userdata( BOOKING_COOKIES_ON_SERVER_UUID, $UUID );
	}
	
	function setSessionActivity( $name, $stage, $data = NULL )
	{
		/*
			Created 02MAR2012-2055
		*/		
		$this->session->set_userdata( ACTIVITY_NAME, $name );	
		$this->updateSessionActivityStage( $stage );
		$this->updateSessionActivityData( $data );
	}//setSessionActivity()
		
	function updateSessionActivityData( $data )
	{
		// CREATED 04MAR2012-1238
	
		$dataLen = strlen( $data );
		if( $data[ $dataLen-1 ] != ';' ) $data .= ";";
		$cookie = array(
			'name'   => ACTIVITY_DATA,
			'value'  => 'TEMP|'.$data,
			'expire' => 3600
		);
		$this->input->set_cookie($cookie);
	}
	
	function updateSessionActivityStage( $stage )
	{
		// CREATED 04MAR2012-1238
		@$this->session->unset_userdata( ACTIVITY_STAGE );
		$this->session->set_userdata( ACTIVITY_STAGE, $stage );
	}
	
}