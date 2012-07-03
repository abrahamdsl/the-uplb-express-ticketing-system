<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	Transaction Model
* 	Created late 05MAR2012-1733 while on the bus pauwi, pagkakuha ng last proceeds ng Full Merit
		Scholarship. HAHAHA. Sorry ka, code ko to gusto kong gawing diary. LOOLS. Sige na, just this one.
		Anyways Justice for Ray Bernard!!!  (Indignation rally din today for him, he was killed yesterday).
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	This deals with transaction activity.
*	Just added the word 'list' to this model so as to avoid potential conflicts with MySQL or any
	other DB for that matter. For as we all know, there are 'transactions' in a DB.
*/

class transactionlist_model extends CI_Model {

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