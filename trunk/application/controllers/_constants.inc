<?php
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
		
		/*
			Array keys
		*/
		define( 'AKEY_UNPAID_PURCHASES_ARRAY', 'unpaidPurchasesArray' );		
		define( 'AKEY_PAID_PURCHASES_ARRAY', 'paidPurchasesArray' );		
		define( 'AKEY_UNPAID_TOTAL', 'unpaidTotal' );		
		define( 'AKEY_PAID_TOTAL', 'paidTotal' );		
		define( 'AKEY_AMOUNT_DUE', 'amountDue' );
		
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
		
		/* 
			Post indicators
		*/
		define( 'PIND_SLOT_SAME_TC_NO_MORE_USER_NOTIFIED', 'noMoreSlotSameTicketClassNotified' );
		
?>