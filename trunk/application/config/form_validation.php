<?php
/**
*   Form Validation Rules
* 	CREATED 22JUL2012-1725
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor of Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*
*	Contains the form validation rules of the controller/functions we have!
*   Why did I just do this now????? Daf**.
**/
	
	log_message('debug','form_validation.php loaded');
	
	$config = Array(
		// seatctrl
		'seatctrl/create_step2' => Array(
			Array(
				'field' => 'rows',
				'rules' => 'required|trim|integer|greater_than[0]|less_than[27]'
			),
			Array(
				'field' => 'cols',
				'rules' => 'required|trim|integer|greater_than[0]'
			)
		),
		// sessionctrl
		'sessionctrl/login' => Array(
			Array(
				'field' => 'password',
				'rules' => 'required|trim|min_length[8]'
			)
		)
	);
	
	//log_message('debug','form_validation.php loaded '. print_r ( $config, TRUE ) );
?>