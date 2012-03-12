<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 05MAR2012-1733 while on the bus pauwi. hahaha. Justice for Ray Bernard!!!

This deals with transaction activity.
*/


class TransactionList_model extends CI_Model {

	function __construct()
	{
		parent::__construct();		
	}
	
	function createNewTransaction(
		$accountNum, $title, $title_Sub = NULL, $objectIdentifier = NULL,
		$desc, $dataType = "WIN5", $data
	)
	{
		/*
			If $dataType == "WIN5" then $data should be in array form
		*/
		date_default_timezone_set('Asia/Manila');
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$uniqueID = $this->generateTransactionNumber();
		$queryResult;		
		$serializedData = "";
		
		if( $dataType == "WIN5" )
		{
			if( !is_array ( $data ) or count( $data ) < 1 ) {
				$data = "NONE";
			}else{
				foreach( $data as $key => $value )
				{
					$serializedData .= ($key.'='.$value.";");
				}
				//remove trailing semi-colon
				$data = substr($serializedData, 0, strlen($serializedData)-1 );
			}
		}else{
			//for XML use later
			$data = "";
		}
	
		$sql_command="INSERT INTO `transactionlist` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$queryResult = $this->db->query( $sql_command, Array(
			$date, $time, $uniqueID, $accountNum, $title,
			$title_Sub, $objectIdentifier, $desc, $dataType, $data
		));
		if( $queryResult ) return $uniqueID;
		else
			return false;
	}//createNewTransaction
	
	function generateTransactionNumber()
	{
		$uniqueID;
		
		do{
			$uniqueID = rand( 100000, 999999 );			
		}while( $this->isTransactionExistent( $uniqueID ) );
		
		return $uniqueID;
	}//generateTransactionNumber()
	
	function getTransaction( $uniqueID )
	{
		$this->db->where('UniqueID', $uniqueID);		
		$arr_result = $this->db->get('transactionlist')->result();
		
		if( count ( $arr_result ) === 1 )
			return $arr_result[0];
		else
			return false;
	}//getTransaction
	
	function isTransactionExistent( $uniqueID )
	{
		$transObj = $this->getTransaction( $uniqueID );
		return( $transObj !== false );
	}//isTransactionExistent
}//class