<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
created 12DEC2011 1753
*/


class TicketClass_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
	}
	
	function getDefaultTicketClasses()
	{
		/*
			created 12DEC2011 1754
			
			Obvious purpose isn't it? :-)
			BTW, an EventID of 0 in table `ticket_class` signifies that such class is a default			
		*/
		$query_obj = $this->db->get_where( 'ticket_class' , array( 'eventID' => 0 ) );
	
		return $query_obj->result();
		
	}//getDefaultTicketClasses()
		
			
		
} //class
?>