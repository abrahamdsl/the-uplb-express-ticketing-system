<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function createAccount()
	{
		$accountNum;
		$transactionSuccess = TRUE;
		$insertionResults[] = Array(); // array for storing results of queries
		
		$accountNum =  $this->generateAccountNumber();	// generate account number
		$insertionResults['user'] = $this->insertUserBaseInfo( $accountNum );	// call function to insert into table 'user'
		
		// now decide if to insert into uplb constituency
		$insertionResults['uplbConstituency'] = FALSE;
		if( $this->input->post( 'uplbConstituentBoolean') == "1"  )
		{
			$insertionResults['uplbConstituency'] = $this->insertUPLBConstituencyData( $accountNum );
		}
		
		// now check results of database insertions
		foreach ( $insertionResults as $x )
		{
			if( $x == FALSE )
			{
				$transactionSuccess = FALSE;
				break;
			}
		}
		
		if( $transactionSuccess )
		{
			echo 'successfully inserted';
		}else{
			echo 'something went wrong';			
		}
	
		return $transactionSuccess;
	}//createAccount
		
	function insertUPLBConstituencyData( $accountNum )
	{
		/*
			created 28 NOV 2011 1309
			
			this function takes care of insertion of the user's UPLB Constituency Data, such as Student Number or Empnumber
		*/
		
		$sNum = ( $this->input->post( 'studentNumber' ) != "" ? $this->input->post( 'studentNumber' ) : NULL );
		$eNum = ( $this->input->post( 'employeeNumber' ) != "" ? $this->input->post( 'employeeNumber' ) : NULL );
		
		// let CodeIgniter do the rest: abstraction of an SQL query
		$data = array(
			'AccountNum_ID' => $accountNum,			
			'studentNumber' => $sNum,
			'employeeNumber' => $eNum
		);
		
		return( $this->db->insert( 'uplbconstituent', $data ) );
	}//insertUPLBConstituencyData
	
	function insertUserBaseInfo( $accountNum )
	{
		/*
			created 28 NOV 2011 1247
			
			this function takes care of insertion of basic user details, that is, on the table 'user'
		*/
		$transactionResult;
		
		// let CodeIgniter do the rest: abstraction of an SQL query
		$data = array(	
			'AccountNum' => $accountNum,
			'username' => strtolower( $this->input->post( 'username' ) ),
			'password' => $this->input->post('password'),
			'Fname' => strtoupper( $this->input->post( 'firstName' ) ),
			'Mname' => strtoupper( $this->input->post( 'middleName' ) ),
			'Lname' => strtoupper( $this->input->post( 'lastName' ) ),
			'Gender' => strtoupper( $this->input->post( 'gender' ) ),
			'Cellphone' => $this->input->post( 'cellPhone' ) ,
			'Landline' => $this->input->post( '4180487' ) ,
			'Email' => strtoupper( $this->input->post( 'email_01_' ) ),
			'addr_homestreet' =>  strtoupper( $this->input->post( 'homeAndStreet_addr' ) ),
			'addr_barangay' => strtoupper( $this->input->post( 'barangay_addr' ) ) ,
			'addr_cityMunicipality' => strtoupper( $this->input->post( 'cityOrMun_addr' ) ),
			'addr_province' => strtoupper( $this->input->post( 'province_addr' ) ),			
			'temp1' => NULL,
			'temp2' => NULL		
		);
		
		$transactionResult = $this->db->insert('user', $data);	// now finally do the SQL query
		
		return $transactionResult;
	}//insertUserBaseInfo();
	
	function isAccountNumExistent($accountNum)
	{
		if( $accountNum == NULL ) return false;
		
		$this->db->where('AccountNum', $accountNum);		
		$query = $this->db->get('user');
		
		// if there was one cell retrieved, then such user with the accountNumber exists
		return ( $query->num_rows == 1 );
	}
	
	function isUserExistent($username = NULL, $password = NULL)
	{
		/*
			Made 27 NOV 2011 1206
		*/
		$userInfo_obj;		// where we store the result of calling getUserInfo(..)
		
		if($username == NULL or $password == NULL) return FALSE;
		
		$userInfo_obj = $this->getUserInfo( $username, $password );
		
		// if there was one cell retrieved, then such user with the username and password exists
		return( $userInfo_obj->num_rows == 1 ); 			
	}
	
	function generateAccountNumber()
	{
		$accountNum;
		
		do{
			$accountNum = rand( 100000, 999999 );			
		}while( $this->isAccountNumExistent( $accountNum ) );
		
		return $accountNum;
	}//generateAccountNumber
	
	function getAccountNumber( $username )
	{
		$query_obj = $this->db->get_where('user', 
							array( 'username' => $username )
							);
						
		$result_arr = $query_obj->result();
		
		return intval($result_arr[0]->AccountNum);		
	}//getAccountNumber
	
	function getUser_Names( $username )
	{
		$query_obj = $this->db->get_where('user', 
							array( 'username' => $username )
							);
		$result_arr = $query_obj->result();
		
		$names = array(
			"first" => $result_arr[0]->Fname,
			"middle" => $result_arr[0]->Mname,
			"last" => $result_arr[0]->Lname,
		);
		return $names;
	}// getUser_Names()
	
	function getUserInfo($username = NULL, $password = NULL)
	/*
		27 NOV 2011 1150 | Taken from Redbana internship project model/login_model/fetch_User()
		made | abe | 05may2011_2357 | for purpose of cohesion or singularity?
	*/
	{
		if($username == NULL or $password == NULL) return NULL; 
	
		$this->db->where('username', $username);
		$this->db->where('password', $password);
		$query = $this->db->get('user');
		
		return $query;
	}
			
}//class
?>