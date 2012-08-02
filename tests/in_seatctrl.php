<?php

function testformvalidation()
	{
	
		/*$testme = Array( "aaaa@aaa.com" => TRUE, "abraham_dsl2@yahoo.com"=> TRUE, "sex@yahoo.com.ph"=> TRUE,
				"a._@yahoo.com"=> FALSE, "@_yahoo.com"=> false, "a@a.com"=> TRUE, "adsllave@uplb.edu.ph"=> TRUE,
				"meong_sung@dailynk.com.kr"=> TRUE, "_acc@att.net"=> FALSE, "kim.jong-.un@yahoo.com" => false,
				"ek.ek@yahoo.com-" => FALSE, "horizon_1965@yahoo.com.ph" => TRUE,
				"abraham.darius.llave@gmail.com" => TRUE
		);*/
		/*$testme = Array(
			"+639183981185" => TRUE,
			"09183981185" => TRUE,
			"9183981185" => TRUE,
			"418048*7" => FALSE,
			"03241-80487" => FALSE,
			"024180487" => TRUE,
			"+6324180487" => TRUE,
			"+634.94180487" => FALSE,
			// USA, MS's hotline
			"4257051900" => TRUE,
			"04257051900" => TRUE,
			"+14257051900" => TRUE,
			"0014257051900" => TRUE
		);*/
		/*$testme = Array(
			"Abraha" => TRUE,
			"" => (0) ? TRUE : FALSE,
			"Stephanie JOanne" => (1) ? TRUE : FALSE,
			"Edriara Ann" => (1) ? TRUE : FALSE,
			"Ma. Lourdes" => (1) ? TRUE : FALSE,
			"Crestitalyn-An" => (1) ? TRUE : FALSE,
			"Toni-Jan Keith" => (1) ? TRUE : FALSE,
			"Meow--Sung" => (0) ? TRUE : FALSE,
			"Meow..Sung" => (0) ? TRUE : FALSE,
			".Gagita" => (0) ? TRUE : FALSE,
			"Putang i." => (0) ? TRUE : FALSE,
			"Putang i-" => (0) ? TRUE : FALSE,
			"-Putang i" => (0) ? TRUE : FALSE,
			"P_utang i" => (0) ? TRUE : FALSE,
			"Putang  i" => (0) ? TRUE : FALSE
		);*/
		/*$testme = Array(
			"Wo" => TRUE, "" => TRUE, "Sing Yu" => TRUE, "SHONG MING  GANG" => TRUE,
			"Kang son:" => FALSE, "Wing Tang \;321=" => FALSE
		);*/
		$testme = Array(
			"200837120" => TRUE, "2008-37120" => TRUE,  "1995.20083" => FALSE,
			 "2008-39" => FALSE,  "" => FALSE, "&230" => FALSE, "2008-3712000" => FALSE,
			 "20083-7120" => FALSE
		);
	
		$results = Array();
		$output = "";
		foreach( $testme as $key => $val ){
			//$check = $this->inputcheck->is_email_valid($key);
			//$check = $this->inputcheck->is_phone_valid($key, "LANDLINE" );
			//$check = $this->inputcheck->is_name_valid($key, 2);
			$check = $this->inputcheck->is_studentNum_valid($key );
			$results[] = $check;
			$this->clientsidedata_model->deleteLastInternalError();
		}
		//output part
		$x = 0;
		echo '<table><br/><thead><tr><td>email</td><td>expected</td><td>checked</td><td>verdict</td></tr></thead><tbody>';
		foreach( $testme as $key => $val ){
			echo '&nbsp;<tr><br/>';
			echo '&nbsp;&nbsp;<td>'.$key.'</td><br/>';
			echo '&nbsp;&nbsp;<td>' . (($val) ? "TRUE" : "FALSE" ).'</td><br/>';
			echo '&nbsp;&nbsp;<td>' . (($results[$x]) ? "TRUE" : "FALSE" ).'</td><br/>';
			echo '&nbsp;&nbsp;<td>' . (($val == $results[$x++]) ? "OK" : "FAILED" ).'</td><br/>';
			echo '&nbsp;</tr><br/>';
		}
		echo '</tbody></table>';
	}