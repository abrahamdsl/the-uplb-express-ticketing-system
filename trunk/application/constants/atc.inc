<?php
/**
*	Constants of Air Traffic Controller (ATC) Model
* 	Created late 09JUL2012-1152
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Used primarily by atc_model.php
**/
		define( 'COL_DB_TABLE_NAME_ATC', '_air_traffic' );
		define( 'COL_CDATE', 'CONTACT_DATE');
		define( 'COL_CTIME', 'CONTACT_TIME');
		if( !defined( 'COL_UUID') ) define( 'COL_UUID', 'UUID' );
		define( 'COL_D1', 'DETAIL1');
		define( 'COL_D2', 'DETAIL2');
		define( 'COL_D3', 'DETAIL3');
		define( 'COL_D4', 'DETAIL4');		
		define( 'COL_D5', 'DETAIL5');		
		define( 'COL_AT', 'ATTEMPTS');
		define( 'COL_LT', 'LOOP_TIME');
		define( 'COL_IS_CUSTOM', 'IS_THERE_CUSTOM');
		define( 'COL_ON_SUCCESS', 'CALL_ON_SUCCESS');
		if( !defined( 'COL_EXPIRE_DATE' ) ) define( 'COL_EXPIRE_DATE', 'EXPIRE_DATE' );
		if( !defined( 'COL_EXPIRE_TIME' ) ) define( 'COL_EXPIRE_TIME', 'EXPIRE_TIME' );
		
		define( 'STAT_CREATED', 100 );
		define( 'STAT_ONWORK', 101 );
		define( 'STAT_SERVEROK', 102 );
		define( 'STAT_CLIENTOK', 103 );
		define( 'STAT_CLIENT_FAILED', 403 );
		define( 'STAT_SERVER_WAIT_ON_CLIENT_TIMEOUT', 405 );
		
		