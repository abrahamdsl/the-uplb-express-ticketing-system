<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 30DEC2011-1407

Created basically for Booking Step 1 as the need for AJAX arose.
It is decided that XMLizing data from the server is best than returning a simple string.

*****
* Library "xml_writer" courtesy of Joost van Veen, 10 mrt 2009, Accent Webdesign
* Downloaded 30DEC2011 from https://github.com/accent-interactive/xml_writer
*****

*/


class MakeXML_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('xml_writer');
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
		    return createTempfile();
		}
   }// createTempFile(..)
   
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
			fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return  "OK_".$xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
		
	}// XMLize_ConfiguredShowingTimes(..)
	
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
			$xml->addNode( 'mastermap', '1' );
			//end branch 1
			$xml->endBranch();
			 
			 // start branch 2 ( dataproper )
			$xml->startBranch( 'dataproper' );
			foreach( $masterSeatMapProperData as $eachSeat )
			{
				// start branch 2-a
				$xml->startBranch( 'seat', array( 'x' => $eachSeat->Matrix_x, 'y' => $eachSeat->Matrix_y ) );
				$xml->addNode( 'row',   $eachSeat->Visual_row );
				$xml->addNode( 'col',   $eachSeat->Visual_col );
				$xml->addNode( 'status',   $eachSeat->Status );
				$xml->addNode( 'comments',   $eachSeat->Comments );
				
				//end branch 2-a
				$xml->endBranch();
			}
			//end branch 2
			$xml->endBranch();
			
			$xmlContent = $xml->getXml();
			// Print the XML to screen
			fwrite( $fp,  $xmlContent );
			fclose( $fp );
			return  $xmlContent;
		}else{
			// cannot write to current disk!
			return "ERROR_CANNOT-WRITE-TO-DISK";
		}
	}
}