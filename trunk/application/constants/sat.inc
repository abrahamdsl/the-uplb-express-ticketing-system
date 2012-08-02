<?php
/**
*	Constants of Session Activity Tracking Model
* 	Created late 09JUL2012-1152
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Used primarily by sat_model.php
**/
		define( 'COL_DB_TABLE_NAME_SAT', '_sess_act' );
		if( !defined( 'COL_START_DATE') ) define( 'COL_START_DATE', 'START_DATE');
		if( !defined( 'COL_START_TIME') ) define( 'COL_START_TIME', 'START_TIME');
		define( 'COL_CONTACT_DATE', 'CONTACT_DATE');
		define( 'COL_CONTACT_TIME', 'CONTACT_TIME');
		define( 'COL_ACT_NAME', 'ACTIVITY_NAME');
		define( 'COL_ACT_STAGE', 'ACTIVITY_STAGE');		
		if( !defined( 'COL_UUID') ) define( 'COL_UUID', 'UUID' );
		
		if( !defined( 'COL_EXPIRE_DATE' ) ) define( 'COL_EXPIRE_DATE', 'EXPIRE_DATE' );
		if( !defined( 'COL_EXPIRE_TIME' ) ) define( 'COL_EXPIRE_TIME', 'EXPIRE_TIME' );
		
		