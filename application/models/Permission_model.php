<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	Created 28 NOV 2011 1552
*/

class Permission_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function createDefault( $accountNum )
	{
		$data = array( "AccountNum_ID" => $accountNum );
		
		return $this->db->insert( 'grand_permission', $data);
	}
	
	function getPermissionStraight( $accountNum )
	{
		$query_obj = $this->db->get_where('grand_permission', 
							array( 'AccountNum_ID' => $accountNum )
							);
						
		$result_arr = $query_obj->result();
		
		return $result_arr[0];
	}//getPermissionStraight
	
	function isAdministrator( $accountNum )
	{
		$rowInfo = $this->getPermissionStraight( $accountNum );
		
		return $rowInfo->ADMINISTRATOR;
	} //isAdmin..
	
	function isEventManager( $accountNum )
	{
		$rowInfo = $this->getPermissionStraight( $accountNum );
		
		return $rowInfo->EVENT_MANAGER;
	} //isEventManager..
	
	function isReceptionist( $accountNum )
	{
		$rowInfo = $this->getPermissionStraight( $accountNum );
		
		return $rowInfo->RECEPTIONIST;
	} //isReceptionist
	
	function isCustomer( $accountNum )
	{
		$rowInfo = $this->getPermissionStraight( $accountNum );
		
		return $rowInfo->CUSTOMER;
	} //isAdmin..
	
	function isFaculty( $accountNum )
	{
		$rowInfo = $this->getPermissionStraight( $accountNum );
		
		return $rowInfo->FACULTY;
	} //isAdmin..
	
	
}//class

?>