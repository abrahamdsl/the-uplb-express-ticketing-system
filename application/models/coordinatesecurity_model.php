<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 04FEB2012-1618

The purpose of this is for the manipulation of table `coordinate_security`.
This table by the way, stores indicators that a page can be accessed already.
Arose as the need to "ajaxify" when Create Event Step 6 data are submitted to server processing and we still want to load
a new whole page after the server successfully processed the data

:
	For example, the processing controller function will set that it is now ok.
	Then back in the page, the form that will submit to the processing controller that will output the new page is called.
	This controller will now check the `coordinate_security` if it is indeed okay.
*/


class coordinatesecurity_model extends CI_Model {
	
	function __construct()
	{
	
	}
	
	function createActivity( $name, $value, $type )
	{
		$uniqueID = uniqid();
		
		$sql_command = "INSERT INTO `coordinate_security` (`UUID`, `ACTIVITY_NAME`, `VALUE`, `VALUE_TYPE`) VALUES ( ?, ?, ?, ? );";
	
		if( $this->db->query(  $sql_command, array($uniqueID , $name, $value, $type ) ) == true )
			return $uniqueID;
		else
			return false;
	}// createActivity(..)
	
	function deleteActivityByName( $name )
	{
		$sql_command = "DELETE FROM `coordinate_security` WHERE `ACTIVITY_NAME` = ? ";
		
		return $this->db->query(  $sql_command, array( $name ) );
	}
	
	function deleteActivityByUUID( $uuid )
	{
		$sql_command = "DELETE FROM `coordinate_security` WHERE `UUID` = ? ";
		
		return $this->db->query(  $sql_command, array( $uuid ) );
	}
	
	function doesActivityExist( $uuid )
	{
		$entity = $this->getSingleActivity( $uuid );
		
		if( $entity === false ) return false;
		else
			return true;
	}
	
	function getSingleActivity(  $uuid )
	{
		/*
			If no entry found, returns boolean false else,
			MYSQL_OBJ.
		*/
		$mysql_obj;
		$sql_command;
		
		$sql_command = "SELECT * FROM `coordinate_security` WHERE `UUID` = ? ";		
		$mysql_obj = $this->db->query(  $sql_command, array( $uuid ) )->result();
		if( count( $mysql_obj ) < 1 ) return false;
		return $mysql_obj[0];
	}
	
	function isActivityEqual( $uuid, $value, $type )
	{
		$dbValue;
		$compareValue;
		$entity_raw;
		$entity_type;
		$dbValue;
				
		$entity_raw = $this->getSingleActivity( $uuid );			// get a record from DB
		if( $entity_raw === false ) return false;
		$entity_type = strtolower( $entity_raw->VALUE_TYPE ); 		// get its type
		// now determine what data type it is
		if( $entity_type === "int" or $entity_type === "integer" )
		{
			$dbValue = intval( $entity_raw->VALUE );			
		}else
		if( $entity_type === "str" or $entity_type === "string" )
		{
			$dbValue = (string) $entity_raw->VALUE;			
		}	
								
		$type = strtolower($type);
		// determine what data type is the data in question
		if( $type === "int" or $type === "integer" )
		{
			$compareValue = intval( $value );			
		}else
		if( $type === "str" or $type === "string" )
		{
			$compareValue = (string) $value;			
		}	
		return ( $dbValue === $compareValue );			// compare value and data type
	}
	
	function updateActivity( $uuid, $value )
	{
		$sql_command = "UPDATE `coordinate_security` SET `VALUE` = ? WHERE `UUID` = ? ";
		
		return $this->db->query(  $sql_command, array( $value, $uuid ) );
	}
}//class