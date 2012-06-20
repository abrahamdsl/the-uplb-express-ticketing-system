<?php
		define( 'ADMIN_MANAGE_USER', 'ADMIN_MANAGE-USER');
		/*
			DB history indicator
		*/
		define("BOOKING_CHANGE_LAPSE_FREED","BOOKING_CHANGE_LAPSE_FREED");
		
		/*
			We define them here so that centralized ang pangalan ng mga cookies, etc.
		*/
		define( 'PAYMENT_MODE', 'paymentMode' );
		define( 'TRANSACTION_ID', 'transactionID' );
		define( 'OLD_SHOWTIME_ID', 'oldShowtimeID' );
		define( 'OLD_SHOWTIME_TC_GROUP_ID', 'oldTicketClassGroupID' );
		define( 'OLD_SHOWTIME_TC_UNIQUE_ID', 'oldTicketClassUniqueID' );
		define( 'FREE_AMOUNT', 0.0 );
		/*
			Change the corresponding entries in string_codes.php in the root accordingly
			if change is done
		*/
		define( 'FACTORY_AUTOCONFIRMFREE_UNIQUEID', 0 );
		define( 'FACTORY_PAYPALPAYMENT_UNIQUEID', 2 );
		define( 'PAYPAL_FEE_FIXED', 15.00 );
		define( 'PAYPAL_FEE_PERCENTAGE', 0.034 );
		
		/*
			Activity names
		*/
		define( 'BOOK', 'BOOK' );
		define( 'CONFIRM_RESERVATION', 'CONFIRM_BOOKING' );
		define( 'MANAGE_BOOKING', 'MANAGE_BOOKING' );
		define( 'CHECK_IN', 'CHECKIN_IN' );
		define( 'CHECK_OUT', 'CHECKIN_OUT' );
		define( 'SEAT_CREATE', 'SEAT_CREATE' );
		define( 'IDLE', 'IDLE' );
		
		/*
			Array keys
		*/
		define( 'AKEY_UNPAID_PURCHASES_ARRAY', 'unpaidPurchasesArray' );
		define( 'AKEY_PAID_PURCHASES_ARRAY', 'paidPurchasesArray' );
		define( 'AKEY_UNPAID_TOTAL', 'unpaidTotal' );
		define( 'AKEY_PAID_TOTAL', 'paidTotal' );
		define( 'AKEY_AMOUNT_DUE', 'amountDue' );
		
		/*
			Browser detection constants
		*/
		define( 'BR_ALLOWED' , 0x80 );
		define( 'BR_STRICTLY_NOT_ALLOWED', 0x81 );
		define( 'BR_UNKNOWN_BUT_PERMIT_STILL', 0x82 );
		define( 'BR_UNKNOWN_AND_DENY', 0x83 );
		define( 'BR_NOT_TESTED_BUT_PERMIT_STILL', 0x84 );
		define( 'BR_NOT_TESTED_AND_DENY', 0x85 );
		define( 'BR_BOT_SIMPLE', 0x86 );
		define( 'BR_AGENT_DENIED', 0x87 );
		
		/*
			For payment modes
		*/
		define( 'PAYMODE_PAYPAL', "paypal" );
		define( 'PAYMODE_2CO', "2checkout" );
		define( 'PAYMODE_MONYEBOOKERS', "moneybookers" );
		
		/*
			Session stages
		*/
		define( 'STAGE_BOOK_1_PROCESS', 0 );
		define( 'STAGE_BOOK_1_FORWARD', 1 );
		define( 'STAGE_BOOK_2_PROCESS', 2 );
		define( 'STAGE_BOOK_2_FORWARD', 3 );
		define( 'STAGE_BOOK_3_PROCESS', 4 );
		define( 'STAGE_BOOK_3_FORWARD', 5 );
		define( 'STAGE_BOOK_4_PROCESS', 6 );
		define( 'STAGE_BOOK_4_CLASS_1_FORWARD', 7 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_CLASS_2_FORWARD', 8 );	// only if student number/emp num is entered in book_4_forward
		define( 'STAGE_BOOK_4_FORWARD', 9 );
		define( 'STAGE_BOOK_5_PROCESS', 10 );
		define( 'STAGE_BOOK_5_FORWARD', 11 );
		define( 'STAGE_BOOK_6_PROCESS', 12 );
		define( 'STAGE_BOOK_6_PAYMENTPROCESSING', 13 );
		define( 'STAGE_BOOK_6_FORWARD', 14 );
		
		define( 'STAGE_CONFIRM_1_FORWARD', 101 );
		define( 'STAGE_CONFIRM_2_PROCESS', 102 );
		define( 'STAGE_CONFIRM_2_FORWARD', 103 );
		define( 'STAGE_CONFIRM_3_PROCESS', 104 );
		define( 'STAGE_CONFIRM_3_FORWARD', 105 );
		
		define( 'STAGE_MB0_HOME', 200 );
		define( 'STAGE_MB0_PREP_PR', 201 );
		define( 'STAGE_MB0_PREP_FW', 202 );
		define( 'STAGE_MB1_SELECT_SHOWTIME_PR', 203 );
		define( 'STAGE_MB1_SELECT_SHOWTIME_FW', 204 );
		define( 'STAGE_MB2_SELECT_TICKETCLASS_1_PR', 205 );
		define( 'STAGE_MB2_SELECT_TICKETCLASS_2_PR', 206 );
		define( 'STAGE_MB2_SELECT_TICKETCLASS_FW', 207 );
		define( 'STAGE_MB2_SELECT_TICKETCLASS_3_PR', 208 );
		//!!! define( 'STAGE_MB3_SELECT_SEAT_FORCED', 209 );
		//!!! define( 'STAGE_MB3_SELECT_SEAT_OPT', 210);
		//!!! define( 'STAGE_MB3_SELECT_SEAT_SOMEGUEST', 211 );
		define( 'STAGE_MB3_SELECT_SEAT_1_PR', 211 ); 
		define( 'STAGE_MB3_SELECT_SEAT_2_PR', 212 ); 
		define( 'STAGE_MB3_SELECT_SEAT_FW', 213 );
		define( 'STAGE_MB3_SELECT_SEAT_PR', 214 );
		define( 'STAGE_MB4_CONFIRM_PR', 217 );
		define( 'STAGE_MB4_CONFIRM_FW', 219 );
		define( 'STAGE_MB5_SELECT_PAYMENT_PR', 220 ); 
		define( 'STAGE_MB5_SELECT_PAYMENT_FW', 221 );
		define( 'STAGE_MB7_PAYMENT_PR', 222 );
		define( 'STAGE_MB7_PAYMENT_ONLINE_PR', 223 );
		define( 'STAGE_MB8_PENDINGCHANGEVD_PR', 224 );
		define( 'STAGE_MB8_PENDINGCHANGEVD_FW', 225 );
		define( 'STAGE_MB9_FINAL_PR', 226 );
		define( 'STAGE_MB9_FINAL_FW', 227 );
		/*
			In Manage Booking, these are used to signify
			stages passed.
		*/
		define( 'MB_STAGEPASS1_SHOWTIME', 'showingtime' );
		define( 'MB_STAGEPASS2_TICKETCLASS', 'ticketclass' );
		define( 'MB_STAGEPASS3_SEAT', 'seat' );
		define( 'MB_STAGEPASS4_NEWCOST', 'newcost' );
		/*
			In Manage Booking, these are used to signify whether
			a stage is passable or not.
		*/
		define( 'MB_STAGESTAT_NOTMEANT',  0 );
		define( 'MB_STAGESTAT_CANPASS',  1 );
		define( 'MB_STAGESTAT_SHOULDPASS',  2 );
		define( 'MB_STAGESTAT_PASSED',  3 );
		define( 'MB_STAGESTAT_CHANGED',  4 );
		/* 
			Post indicators
		*/
		define( 'PIND_SLOT_SAME_TC_NO_MORE_USER_NOTIFIED', 'noMoreSlotSameTicketClassNotified' );
		define( 'PIND_SEAT_SAME_TC_NO_MORE_USER_NOTIFIED', 'noMoreSeatSameTicketClassNotified' );
		define( 'PIND_CHANGE_SEAT_NOTIFIED', 'option_change_seat_notified' );
	
		/*
			Unclassified
		*/
		define( 'DO_NOT_SHOW_SEATINFO_INDICATOR', '_DO-NOT-SHOW' );
		define( 'AUTH_THEN_REDIRECT' , 'redirect_to' );
		define( 'SEAT_COORD404', '-1' );
		define( 'UP_STUDENTNUM_MINREQUIREMENT_LEN', 8);
		define( 'UP_EMPNUM_MINREQUIREMENT_LEN', 8);
		
?>