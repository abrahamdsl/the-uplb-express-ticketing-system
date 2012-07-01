<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	XML Maker Model
* 	Created 30DEC2011-1407
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Created basically for Booking Step 1 as the need for AJAX arose.
*	It is decided that XMLizing data from the server is best than returning a simple string.
*
*****
* Library "xml_writer" courtesy of Joost van Veen, 10 mrt 2009, Accent Webdesign
* Downloaded 30DEC2011 from https://github.com/accent-interactive/xml_writer
*****
*
**/


class MakeXML_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('xml_writer');
	}
	
	function toArray($xml) {
		/**
		*	@created 27JUN2012-2045
		*	@description Parses XML to Array.
		*	@author thedoc8786 at gmail dot com 23-Mar-2012 07:42
		*	@source http://php.net/manual/en/book.simplexml.php#comments
		**/
        $array = json_decode( json_encode($xml), TRUE);
        
        foreach ( array_slice($array, 0) as $key => $value ) {
            if ( empty($value) ) $array[$key] = NULL;
            elseif ( is_array($value) ) $array[$key] = $this->toArray($value);
        }
        return $array;
    }//toArray(.)
	
	function toArray_prep( $xml, $return_root = FALSE )
	{
		/**
		*	@created 27JUN2012-2130
		*	@description Gateway to $this->toArray().
		*	@returns See $this->toArray()
		**/
		$arrayized = $this->toArray( $xml );
		if( $return_root ) return $arrayized;
		else{
			// only one element, however, index 0 is named STRING
			// so to dynamically select, this is the solution.
			foreach( $arrayized as $xml_elements ) return $xml_elements;
		}
	}
	
	function readXML( $xmlFile ){
		/**
		*	@created 27JUN2012-2130
		*	@description Reads the XML file in the server and returns the content as string.
		*	@parameter $xmlFile STRING Contains the *absolute* path (except TLD ) of the XML file
		**/
		$fp = fopen( $xmlFile, "r" );
		$contents = fread( $fp , filesize( $xmlFile ) );
		fclose( $fp );
		return $contents;
	}
	
	function createTempFile(){
		/*
			Imported 30DEC2011-1412 from CMSC 150 Project. :-)
		*/
	
        $tempFile = "assets/xmltemp/temp_uxts_xml_";
		
		for($i=0;$i<15;$i++){
			$tempFile .= rand(0,9);
		}
        $tempFile .= ".xml";        
		
        if(!file_exists($tempFile)){
            return $tempFile;		
	    }else{
		    return $this->createTempfile();
		}
   }// createTempFile(..)
   
   function XMLize_AJAX_Response( 
	$type="error", $title, $resultString, $resultCode = 0, $message, $redirectTo = "", $redirectAfter = 1000
   )
   {
		/*
			Created 26MAR2012-1912
		*/
		$xml = new xml_writer;
		$xml->setRootName( 'ajaxresponse' );
		$xml->initiate();		
		$xml->addNode( 'type' , $type );
		$xml->addNode( 'resultstring' , $resultString );
		$xml->addNode( 'resultcode' , $resultCode  );
		$xml->addNode( 'title', $title );
		$xml->addNode( 'message', $message );
		if( strlen( $redirectTo ) >  0 ){
			$xml->addNode( 'redirect', $redirectTo );
			$xml->addNode( 'redirect_after', $redirectAfter );
		}
		
		return $xml->getXml();		 
   }//XMLize_AJAX_Response
   
	function XMLize_ConfiguredShowingTimes( $allConfiguredShowingTimes )
	{
		/* created 30DEC2011-1409
		
			returns string of the ff format:
				X_Y
			
			X - operation indicator, may take on { INVALID, ERROR, FILE }
			Y - exlanation of X
			
		*/
		$XMLfile = $this->createTempFile();
		$fp;
		
		if( !is_array( $allConfiguredShowingTimes ) )
		{
			return "INVALID_DATA";
		}
		
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'showingTimes' );
			$xml->initiate();
			
			foreach( $allConfiguredShowingTimes as $singleShowTime )
			{
				// start branch 1 (schedule)
				$xml->startBranch( 'schedule', array( 'UniqueID' => $singleShowTime->UniqueID ) );
				
				// start branch 1-1 (start)
				$xml->startBranch( 'start' );				
				$xml->addNode( 'date' , $singleShowTime->StartDate );
				$xml->addNode( 'time', $singleShowTime->StartTime );
				
				//end branch 1-1
				 $xml->endBranch();
				 
				 // start branch 1-2 (end)
				$xml->startBranch( 'end' );				
				$xml->addNode( 'date' , $singleShowTime->EndDate );
				$xml->addNode( 'time', $singleShowTime->EndTime );
				
				//end branch 1-2
				 $xml->endBranch();
				 
				 //end Branch 1
				 $xml->endBranch();
			}
			$xmlContent = $xml->getXml();
			// Print the XML to screen
			//fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return  "OK_".$xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
		
	}// XMLize_ConfiguredShowingTimes(..)
	
	function XMLize_GuestSeatNotAvailable( $guest_no_seat )
	{
		/**
		*	@created 27JUN2012-1652
		*	@description Turns the submitted Array ( which is a list of guests whose seats are not
				available in the new booking settings ) to XML file.
		*	@returns Array:
				index 0 - BOOLEAN
				index 1 - Message if error | Filename of the XML if success
		**/
		$XMLfile = $this->createTempFile();
		$fp;
		
		if( !is_array( $guest_no_seat ) )
		{
			return Array( FALSE, "INVALID_DATA" );
		}
		
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'guest_no_seat' );
			$xml->initiate();
			
			foreach( $guest_no_seat as $uuid => $singleGuest )
			{
				// start branch 1 (guest)
				$xml->startBranch( 'guest' );
				$xml->addNode( 'uuid' , $uuid );
				$xml->addNode( 'lname' , $singleGuest->Lname );
				$xml->addNode( 'fname', $singleGuest->Fname );
				$xml->addNode( 'mname', $singleGuest->Mname );
				$xml->addNode( 'x', $singleGuest->Seat_x );
				$xml->addNode( 'y', $singleGuest->Seat_y );
				$xml->addNode( 'v_rep', $singleGuest->v_rep );
				
				//end branch 1
				 $xml->endBranch();
			}
			$xmlContent = $xml->getXml();
			// Print the XML to screen
			fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return Array( TRUE, $XMLfile );
		}else{
			// cannot write to current disk!
			return Array( FALSE, "ERROR_CANNOT-WRITE-TO-DISK" );
		}	
	}// XMLize_GuestSeatNotAvailable(..)
	
	function XMLize_UserInfoForBooking( $mainInfo = FALSE, $uplbConstituencyInfo = FALSE )
	{
		/*
			Created 26FEB2012-2030
		*/
		$XMLfile = $this->createTempFile();
		$fp;
			
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'user' );
			$xml->initiate();
			
			//output main details - name, bla bla
			// start branch 1 ( main details)			
			$xml->startBranch( 'main_info' );
				// start branch 1-1 name
				$xml->startBranch( 'name' );
					$xml->addNode(  'first', $mainInfo->Fname );
					if( strlen($mainInfo->Mname) > 0 ) $xml->addNode(  'middle', $mainInfo->Mname );
					$xml->addNode(  'last', $mainInfo->Lname );
				$xml->endBranch();
				$xml->addNode(  'gender', $mainInfo->Gender );
				$xml->addNode(  'cellphone', $mainInfo->Cellphone );
				if( strlen($mainInfo->Landline) > 6 )		// the min number of landline # is 7
					$xml->addNode(  'landline', $mainInfo->Landline );	
				$xml->addNode(  'email', strtolower( $mainInfo->Email ) );
				$xml->endBranch();
			if( $uplbConstituencyInfo !== FALSE )
			{
				//start branch 2
				$xml->startBranch( 'uplb_info' );
				if( strlen($uplbConstituencyInfo->studentNumber) > 0 )
					$xml->addNode(  'student', $uplbConstituencyInfo->studentNumber );
				if( strlen($uplbConstituencyInfo->employeeNumber) > 0 )
					$xml->addNode(  'employee', $uplbConstituencyInfo->employeeNumber );					
				//end branch 2
				$xml->endBranch();
			}
			$xmlContent = $xml->getXml();
		}else{
			echo "ERROR_Cannot write to disk on server";
		}
		fclose( $fp );
		echo $xmlContent;
		return true;
	}// XMLize_UserInfoForBooking
	 
	 
	 
	function XMLize_SeatMap_Actual( $masterSeatMapDetails, $actualSeatsData )
	{
		/* created 12FEB2012-2256
			modified form of XMLize_SeatMap_Master(..)
			returns string of the ff format:
				X_Y
			
			X - operation indicator, may take on { INVALID, ERROR, FILE }
			Y - exlanation of X			
		*/
		$XMLfile = $this->createTempFile();
		$fp;
		
		if( !is_array( $actualSeatsData ) )
		{
			return "INVALID_DATA";
		}
		
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'seatmap' );
			$xml->initiate();
			
			//configure details first
			// start branch 1 ( details)
			
			$xml->startBranch( 'details' );			
			$xml->addNode( 'unique_id', $masterSeatMapDetails->UniqueID );
			$xml->addNode( 'name', $masterSeatMapDetails->Name );
			$xml->addNode( 'rows', $masterSeatMapDetails->Rows );
			$xml->addNode( 'cols', $masterSeatMapDetails->Cols );
			$xml->addNode( 'location', $masterSeatMapDetails->Location );
			$xml->addNode( 'status', $masterSeatMapDetails->Status );
			$xml->addNode( 'usableCapacity', $masterSeatMapDetails->UsableCapacity );
			$xml->addNode( 'mastermap', '0' );
			//end branch 1
			$xml->endBranch();
			 
			 
			 // start branch 2 ( dataproper )
			$xml->startBranch( 'dataproper' );
			foreach( $actualSeatsData as $eachSeat )
			{				
				// start branch 2-a
				$xml->startBranch( 'seat', array( 'x' => $eachSeat->Matrix_x, 'y' => $eachSeat->Matrix_y ) );
				$xml->addNode( 'row',   $eachSeat->Visual_row );
				$xml->addNode( 'colX',   $eachSeat->Visual_col );
				$xml->addNode( 'status',   $eachSeat->Status );
				$xml->addNode( 'tClass', $eachSeat->Ticket_Class_UniqueID );
				$xml->addNode( 'comments',   $eachSeat->Comments );				
				//end branch 2-a
				$xml->endBranch();
			}
			//end branch 2
			$xml->endBranch();
			
			$xmlContent = $xml->getXml();
			// Print the XML to screen
			//fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return  $xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
	}//XMLize_SeatMap_Actual(..)
	
	function XMLize_SeatMap_Master( $masterSeatMapDetails, $masterSeatMapProperData )
	{
		/* created 28JAN2012-2156
		
			returns string of the ff format:
				X_Y
			
			X - operation indicator, may take on { INVALID, ERROR, FILE }
			Y - exlanation of X
			
		*/
		$XMLfile = $this->createTempFile();
		$fp;
		
		if( !is_array( $masterSeatMapProperData ) )
		{
			 echo "ERROR_INVALID_DATA";
			 return false;
		}
		
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'seatmap' );
			$xml->initiate();
			
			//configure details first
			// start branch 1 ( details)
			$xml->startBranch( 'details' );
			$xml->addNode( 'unique_id', $masterSeatMapDetails->UniqueID );
			$xml->addNode( 'name', $masterSeatMapDetails->Name );
			$xml->addNode( 'rows', $masterSeatMapDetails->Rows );
			$xml->addNode( 'cols', $masterSeatMapDetails->Cols );
			$xml->addNode( 'location', $masterSeatMapDetails->Location );
			$xml->addNode( 'status', $masterSeatMapDetails->Status );
			$xml->addNode( 'usableCapacity', $masterSeatMapDetails->UsableCapacity );
			$xml->addNode( 'mastermap', '1' );
			//end branch 1
			$xml->endBranch();
			 
			 // start branch 2 ( dataproper )
			$xml->startBranch( 'dataproper' );
			foreach( $masterSeatMapProperData as $eachSeat )
			{
				/*
						09FEB2012-0214 : In connection with Issue 25 in Google Code / "Why still compute the rows and cols when it's in the XML?".
						So I configured it to get the rows and cols from the XML. 
							(We are talking here about Create Event Step 5 - Getting Seat Map - Assigning Rows and Columns )
						
						However, if we have the XML structure
						*************************************
							<seat x="i" y"j" >
								<row>a</row>
								<col>b</col>
								<status>c</col>
								<comment></comment>
							</seat>
						************************************
						and therefore in the JavaScript function to get col, we have to have this command
						****************************************
								$(this).find( 'col' ).text();
						****************************************						
						.. then IT DOES NOT WORK. However, here in the XML, when I change <col> to <colx>, it works!
						I've tested it twice, in Google Chrome 11, for the meantime. Why is it so, I wonder?
				*/
				// start branch 2-a
				$xml->startBranch( 'seat', array( 'x' => $eachSeat->Matrix_x, 'y' => $eachSeat->Matrix_y ) );
				$xml->addNode( 'row',   $eachSeat->Visual_row );
				$xml->addNode( 'colX',   $eachSeat->Visual_col );
				$xml->addNode( 'status',   $eachSeat->Status );
				$xml->addNode( 'comments',   $eachSeat->Comments );
				
				//end branch 2-a
				$xml->endBranch();
			}
			//end branch 2
			$xml->endBranch();
			
			$xmlContent = $xml->getXml();
			// Print the XML to screen
			//fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return  $xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
	}//function
	
	function XMLize_AllDetailsForCheckin( $detailsObj, $alreadyEntered, $alreadyExited )
	{
		$XMLfile = $this->createTempFile();
		$fp;
		
		if( !is_array( $detailsObj ) )
		{
			 echo "ERROR_INVALID_DATA";
			 return false;
		}		
		$fp = fopen( $XMLfile, "w" );
		if( $fp != NULL )
		{
			// Initiate class
			$xml = new xml_writer;
			$xml->setRootName( 'checkininfo' );
			$xml->initiate();
			
			foreach( $detailsObj as $mainInfo )
			{
				$xml->startBranch( 'guest' );	
					$xml->addNode(  'entered', ( isset( $alreadyEntered[$mainInfo->Assigned_To_User] ) ) ? 1 : 0 );				
					$xml->addNode(  'exited', ( isset( $alreadyExited[$mainInfo->Assigned_To_User] ) ) ? 1 : 0 );				
					$xml->addNode(  'uuid', $mainInfo->Assigned_To_User );				
					$xml->startBranch( 'name' );
						$xml->addNode(  'first', $mainInfo->Fname );
						if( strlen($mainInfo->Mname) > 0 ) $xml->addNode(  'middle', $mainInfo->Mname );
						$xml->addNode(  'last', $mainInfo->Lname );
					$xml->endBranch();
					$xml->startBranch( 'seat' );
						$xml->addNode(  'row', $mainInfo->Visual_row );
						$xml->addNode(  'colY', $mainInfo->Visual_col );
					$xml->endBranch();
					$xml->addNode(  'gender', $mainInfo->Gender );
					$xml->addNode(  'cellphone', $mainInfo->Cellphone );
					//if( strlen($mainInfo->Landline) > 6 )		// the min number of landline # is 7
						$xml->addNode(  'landline', $mainInfo->Landline );	
					//$xml->addNode(  'email', strtolower( $mainInfo->Email ) );
					//if( strlen($mainInfo->studentNumber) > 8 )		
						$xml->addNode(  'studentnum', $mainInfo->studentNumber );
					//if( strlen($mainInfo->employeeNumber) > 8 )		
						$xml->addNode(  'empnum', $mainInfo->employeeNumber );
				$xml->endBranch();				
			}
			$xml->endBranch( );	
			$xmlContent = $xml->getXml();			
			fclose( $fp );			
			return  $xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
	}//function
}