<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	Created 28 NOV 2011 1552
*/

class permission_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function createDefault( $accountNum )
	{
		$data = array( "AccountNum_ID" => $accountNum );
		
		return $this->db->insert( 'grand_permission', $data);
	} //createDefault
	
	function getPermissionStraight( $accountNum )
	{
		$query_obj = $this->db->get_where('grand_permission', 
							Array( 'AccountNum_ID' => $accountNum )
					);

		$result_arr = $query_obj->result();
		if( count( $result_arr ) > 0 )
			return $result_arr[0];
		else
			return false;
	}//getPermissionStraight
	
	function isAdministrator( $accountNum = NULL )
	{
		if( $accountNum == NULL  ) $accountNum = $this->session->userdata('accountNum');
		$rowInfo = $this->getPermissionStraight( $accountNum );

		return (intval($rowInfo->ADMINISTRATOR) == 1 );
	} //isAdmin..
	
	function isEventManager( $accountNum = NULL  )
	{
		if( $accountNum == NULL  ) $accountNum = $this->session->userdata('accountNum');
		$rowInfo = $this->getPermissionStraight( $accountNum );

		return (intval($rowInfo->EVENT_MANAGER) == 1 );
	} //isEventManager..
	
	function isReceptionist( $accountNum = NULL  )
	{
		if( $accountNum == NULL  ) $accountNum = $this->session->userdata('accountNum');
		$rowInfo = $this->getPermissionStraight( $accountNum );

		return (intval($rowInfo->RECEPTIONIST) == 1 );
	} //isReceptionist
	
	function isCustomer( $accountNum = NULL  )
	{
		if( $accountNum == NULL  ) $accountNum = $this->session->userdata('accountNum');
		$rowInfo = $this->getPermissionStraight( $accountNum );

		return (intval($rowInfo->CUSTOMER) == 1 );
	} //isAdmin..
	
	function isFaculty( $accountNum = NULL  )
	{
		if( $accountNum == NULL  ) $accountNum = $this->session->userdata('accountNum');
		$rowInfo = $this->getPermissionStraight( $accountNum );

		return (intval($rowInfo->FACULTY) == 1 );
	} //isAdmin..
}//class
