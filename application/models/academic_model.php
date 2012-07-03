<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	Created 13MAR2012-0535
*/
class academic_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');		
	}
	
	function createEventClassPair( $eventID, $showtimeID, $classID )
	{
		$data = Array(			
			'EventID' =>  $eventID,
			'ShowtimeID' => $showtimeID,
			'UPLBClassID' => $classID
		);
		$sql_command = "INSERT INTO `event_and_class_pair` (`EventID`,`ShowtimeID`,`UPLBClassID`) VALUES (?,?,?)";
		return( $this->db->query( $sql_command, $data ) );
	}//createEventClassPair(
		
	function createNewClass()
	{
		$classNum = $this->generateClassNumber();
		$data = array(
			'UUID' => $classNum,
			'CourseTitle' => strtoupper($this->input->post( 'title')),
			'CourseNum' => strtoupper($this->input->post('number' )),
			'LectureSect' => strtoupper($this->input->post('lectsect' )),
			'RecitSect' => strtoupper($this->input->post('recitsect' )),
			'Term' => $this->input->post(  'term'),			
			'AcadYear1' => $this->input->post( 'acadyear_1' ),			
			'AcadYear2' => $this->input->post('acadyear_2' ),			
			'FacultyAccountNum' => $this->session->userdata( 'accountNum' ),
			'Comments' => $this->input->post('comments' )			
		);
		$sql_command = "INSERT INTO `uplb_class` VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );  ";
		$transResult = $this->db->query( $sql_command , $data );
		if( $transResult )
		{
			return $classNum;
		}else{
			die( var_dump(  $transResult ) );
		}
	}//createNewClass()
	
	function deleteClassEventAssociation( $uniqueID )
	{
		$sql_command = "DELETE FROM `event_and_class_pair` WHERE `EC_UniqueID` = ?";
		return $this->db->query( $sql_command, Array( $uniqueID ) );
	}
	
	function deleteClassEventAssociationViaClass( $classID )
	{
		$sql_command = "DELETE FROM `event_and_class_pair` WHERE `UPLBClassID` = ?";
		return $this->db->query( $sql_command, Array( $classID ) );
	}
	
	function deleteUPLBClassStudentPairViaClass( $classID )
	{
		$sql_command = "DELETE FROM `uplb_class_and_student_pair` WHERE `UPLBClassUUID` = ?";
		return $this->db->query( $sql_command, Array( $classID ) );
	}
	
	function deleteUPLBCLass( $classID )
	{
		$sql_command = "DELETE FROM `uplb_class` WHERE `UUID` = ?";
		return $this->db->query( $sql_command, Array( $classID ) );
	}
	
	function generateClassNumber()
	{
		$classNum;
		
		do{
			$classNum = rand( 880000, 889999 );			
		}while( $this->isClassNumExistent( $classNum ) );
		
		return $classNum;;
	}//generateClassNumber()
	
	function isClassNumExistent( $classNum )
	{
		if( $classNum == NULL ) return false;
		
		$this->db->where('UUID', $classNum);		
		$query = $this->db->get('uplb_class');
		
		// if there was one cell retrieved, then such user with the accountNumber exists
		return ( $query->num_rows == 1 );
	}
	
	function getActiveClasses( $eventID, $showtimeID )
	{
		$alphabet = Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$classes = Array();
		foreach( $alphabet as $letter )
		{	
			//$arr_result = $this->db->query( "SELECT * FROM  `uplb_class`  INNER JOIN `user` on `uplb_class`.`FacultyAccountNum` =   `user`.`AccountNum` WHERE  `uplb_class`.`CourseTitle` LIKE  '".$letter."%'" )->result();
			$sql_command = "SELECT * FROM  `event_and_class_pair`  INNER JOIN `uplb_class` on `event_and_class_pair`.`UPLBClassID` = `uplb_class`.`UUID`";
			$sql_command .= "  INNER JOIN `user` on `uplb_class`.`FacultyAccountNum` =   `user`.`AccountNum`";
			$sql_command .= " WHERE `event_and_class_pair`.`EventID` = ? AND `event_and_class_pair`.`ShowtimeID` = ?";
			$sql_command .= " AND `uplb_class`.`CourseTitle` LIKE  '".$letter."%'";
			$arr_result = $this->db->query( $sql_command, Array( $eventID, $showtimeID ) )->result();
			if( count( $arr_result ) > 0 ) $classes[ $letter ] = $arr_result;		
		}
		return $classes;
	}
	
	function getAttendanceRecord( $guestUUID, $EC_UniqueID )
	{
		$sql_command = "SELECT * FROM `event_attendance_real` INNER JOIN `booking_guests` ON `booking_guests`.`UUID` = `event_attendance_real`.`GuestUUID`";
		$sql_command .= " WHERE `event_attendance_real`.`GuestUUID` = ?";		
		
		//die( var_dump($sql_command) );
		$arr_result = $this->db->query( $sql_command, Array( $guestUUID,  $EC_UniqueID ) )->result();
		
		if( count($arr_result) === 1 )
			return $arr_result[0];
		else
			return false;
	}//getAttendanceRecord( $guestUUID )
	
	function getClassEventPairing_ByEC_UID( $EC_UniqueID )
	{
		$sql_command = "SELECT * FROM `event_and_class_pair` WHERE `EC_UniqueID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $EC_UniqueID ) )->result();
		
		if( count($arr_result) === 1 )
			return $arr_result[0];
		else
			return false;
	}
	
	function getClassEventPairing( $classUUID )
	{
		$sql_command = "SELECT * FROM `event_and_class_pair` INNER JOIN `event` ON `event_and_class_pair`.`EventID` = `event`.`EventID`";
		$sql_command .= " INNER JOIN `showing_time` ON `event`.`EventID` = `showing_time`.`EventID` and ";
		$sql_command .= " `event_and_class_pair`.`ShowtimeID` = `showing_time`.`UniqueID` WHERE   `event_and_class_pair`.`UPLBClassID` = ?";
		$arr_result = $this->db->query( $sql_command, Array( $classUUID) )->result();
		if( count( $arr_result) > 0 )
		{
			return $arr_result;
		}else{
			return false;
		}
	}//getClassEventPairing( )
	
	function getStudentClassPairingSingleByUniqueID( $uniqueID )
	{				
		$sql_command = "SELECT * FROM `event_and_class_pair` INNER JOIN `uplb_class_and_student_pair`
 ON `event_and_class_pair`.`UPLBClassID` =`uplb_class_and_student_pair`.`UPLBClassUUID` 
 WHERE `event_and_class_pair`.`EC_UniqueID` = ?";
			
		$arr_result = $this->db->query( $sql_command, Array( $uniqueID ))->result();
		
		if( count( $arr_result ) > 0 ) return $arr_result[0];
		else
			return false;
	}
	
	function getFacultyClasses( $accountNum )
	{		
		$sql_command = "SELECT * FROM `uplb_class` WHERE `FacultyAccountNum` = ? ORDER BY  `CourseTitle` AND `CourseNum` ASC ";
		$arr_result  = $this->db->query( $sql_command, Array($accountNum) )->result();
		if( count( $arr_result ) > 0 ) return $arr_result;
		else
			return false;
	}
	

	function getClassStudentPairing( $classUUID )
	{			
		$this->db->where('UPLBClassUUID', $classUUID  );		
		$arr_result = $this->db->get('uplb_class_and_student_pair')->result();
		
		if( count( $arr_result ) > 0 ) return $arr_result;
		else
			return false;
	}
	
	function getSingleClass_AllDetails( $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2 )
	{
		$this->db->where('CourseTitle' , strtoupper($title));		
		$this->db->where('CourseNum' , strtoupper($num));		
		$this->db->where('LectureSect' , strtoupper($lectSect));		
		$this->db->where('RecitSect' , strtoupper($recitSect));		
		$this->db->where('Term' , $term );
		$this->db->where('AcadYear1' , $ay1);		
		$this->db->where('AcadYear2' , $ay2);			
		$query = $this->db->get('uplb_class');
		$arr_result = $query->result();
		
		if( count( $arr_result ) > 0 ) return $arr_result[0];
		else
			return false;
	}
	
	function getSingleClass_ByUUID( $uuid )
	{		
		$sql_command = "SELECT * FROM  `uplb_class`  INNER JOIN `user` on `uplb_class`.`FacultyAccountNum` = `user`.`AccountNum` WHERE  `uplb_class`.`UUID` = ?" ;
		$arr_result = $this->db->query( $sql_command, Array( $uuid ) )->result();		
		
		if( count( $arr_result ) > 0 ) return $arr_result[0];
		else
			return false;
	}
	
	function insertAttendanceForClass( $guestUUID, $classUUID )
	{
		$data = Array(
			'GuestUUID' =>$guestUUID ,			
			'UPLBClassUUID' =>  $classUUID
		);
		return $this->db->insert('uplb_class_and_student_pair', $data);
	}
	
	function isClassExisting( $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, $classID = false )
	{
		
		$singleClassObj = $this->getSingleClass_AllDetails( $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2 );
		if( $classID === false )
			return ( $singleClassObj !== false );
		else
			return ( intval( $singleClassObj->UUID ) !== intval( $classID ) );
	}
				
	function makeUPLBConstituencyDataToString( $dataArray )
	{
		/*
			--------------------------------
			For DEPRECIATION - 17APR2012-1519
			*Formerly used by eventctrl/book_step4 but not anymore
			
			---------------------------------
			Created 21MAR2012-1416
			
			Structure of  $dataArray parameter.
		
			 $dataArray[ $eachGuest->UUID ] = Array(
				'studentNumber' => $eachGuest->studentNumber ,
				'employeeNumber' => $eachGuest->employeeNumber
			);
		*/
		$guest_StudentNumPair = "";
		$guest_EmpNumPair = "";
		
		foreach( $dataArray as $guestUUID => $val )
		{
			if( strlen($val['studentNumber']) == 9 ) $guest_StudentNumPair .= $guestUUID.'_'.$val['studentNumber'].'|';
			if( strlen($val['employeeNumber']) >= 9 ) $guest_EmpNumPair .= $guestUUID.'_'.$val['employeeNumber'].'|';
		}
		//remove trailing pipe
		if( strlen($guest_StudentNumPair) > 0 ) $guest_StudentNumPair = substr( $guest_StudentNumPair, 0, strlen($guest_StudentNumPair)-1 );	
		if( strlen($guest_EmpNumPair ) > 0 ) 	$guest_EmpNumPair  = substr( $guest_EmpNumPair, 0, strlen($guest_EmpNumPair)-1 );	
		
		return Array( $guest_StudentNumPair, $guest_EmpNumPair );
	}//makeUPLBConstituencyDataToString
	
	function recordEntry( $guestUUID )
	{
		date_default_timezone_set('Asia/Manila');
		$data = Array(
			'GuestUUID' =>  $guestUUID ,
			'EntryDate' => date('Y-m-d'),
			'EntryTime' => date('H:i:s')
		);
		return $this->db->insert('event_attendance_real', $data);	
	}
	
	function recordExit( $guestUUID )
	{
		date_default_timezone_set('Asia/Manila');
		$sql_command = "UPDATE `event_attendance_real` SET `ExitDate` = ?, `ExitTime` = ? WHERE `GuestUUID` = ? ";
		$data = Array(			
			date('Y-m-d'),
			date('H:i:s'),
			$guestUUID
		);
		return $this->db->query( $sql_command, $data);	
	}
	
	function updateClassDetails($classID, $title, $num, $lectSect, $recitSect, $term, $ay1, $ay2, $comments )
	{
		$data = Array(		
			'CourseTitle'   => $title, 
			'CourseNum'		=> $num,  
			'LectureSect'   => $lectSect, 
			'RecitSect'		=> $recitSect,
			'Term'		    => $term,
			'AcadYear1'	    => $ay1,
			'AcadYear2'	    => $ay2,
			'Comments'	    => $comments
		);		
		$where = "`UUID` = ".$classID; 
		$sql_command = $this->db->update_string('uplb_class', $data, $where);
		return $this->db->query( $sql_command );
	}
}//class
	