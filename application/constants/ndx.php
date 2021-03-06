<?php
/**
*	Constants of New Data Access For Booking Model
* 	Created late 09JUN2012-1201
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Used primarily by ndx_model.php
**/
		define( 'COL_DB_TABLE_NAME', '_booking_cookies_on_server' );
		if( !defined( 'COL_EXPIRE_DATE' ) ) define( 'COL_EXPIRE_DATE' , 'EXPIRE_DATE' );
		if( !defined( 'COL_EXPIRE_TIME' ) ) define( 'COL_EXPIRE_TIME' , 'EXPIRE_TIME' );
		if( !defined( 'COL_UUID') )  define( 'COL_UUID' , 'UUID' );
		define( 'COL_BOOKING_NUMBER' , 'BOOKING_NUMBER' );
		define( 'COL_EVENT_ID' , 'EVENT_ID' );
		define( 'COL_SHOWTIME_ID' , 'SHOWTIME_ID' );
		define( 'COL_TICKET_CLASS_GROUP_ID' , 'TICKET_CLASS_GROUP_ID' );
		define( 'COL_TICKET_CLASS_UNIQUE_ID' , 'TICKET_CLASS_UNIQUE_ID' );
		define( 'COL_PURCHASE_IDS' , 'PURCHASE_IDS' );
		define( 'COL_SLOTS_UUID' , 'SLOTS_UUID' );
		define( 'COL_SLOT_QUANTITY' , 'SLOT_QUANTITY' );
		define( 'COL_VISUALSEAT_DATA' , 'VISUALSEAT_DATA' );
		define( 'COL_EVENT_NAME' , 'EVENT_NAME' );
		if( !defined( 'COL_START_DATE') ) define( 'COL_START_DATE', 'START_DATE');
		if( !defined( 'COL_START_TIME') ) define( 'COL_START_TIME', 'START_TIME');
		define( 'COL_END_DATE' , 'END_DATE' );
		define( 'COL_END_TIME' , 'END_TIME' );
		define( 'COL_EVENT_LOCATION' , 'EVENT_LOCATION' );
		define( 'COL_PAYMENT_DEADLINE_DATE', 'PAYMENT_DEADLINE_DATE' );
		define( 'COL_PAYMENT_DEADLINE_TIME', 'PAYMENT_DEADLINE_TIME' );
		define( 'COL_UPLB_STUDENTNUM_DATA', 'UPLB_STUDENTNUM_DATA' );
		define( 'COL_UPLB_EMPNUM_DATA', 'UPLB_EMPNUM_DATA' );

		/*
			For manage booking ndx.
		*/

		define( 'COL_DB_MB_TABLE_NAME', '_managebooking_cookies' );
		define( 'COL_UUID_MB' , 'UUID_MB' );
		define( 'COL_GO_SHOWTIME', 'GO_SHOWTIME' );
		define( 'COL_GO_TICKETCLASS', 'GO_TICKETCLASS' );
		define( 'COL_GO_SEAT', 'GO_SEAT' );
		define( 'COL_GO_PAYMENT', 'GO_PAYMENT' );
		define( 'COL_CURRENT_UUID', 'CURRENT_UUID' );
		define( 'COL_NEW_UUID', 'NEW_UUID' );
?>