$(document).ready(function() {
		$( "#datepicker").datepicker({
			minDate: CI.serverDate,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		$( "#datepicker2").datepicker({
			minDate: CI.serverDate,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		$( "#datepicker3").datepicker({
			minDate: CI.serverDate,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		/*
		$('#datepicker').click( function() {
			$(this).datepicker();			
		});*/
});