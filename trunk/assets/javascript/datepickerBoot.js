$(document).ready(function() {
		$( "#datepicker").datepicker({
			minDate: +0,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		$( "#datepicker2").datepicker({
			minDate: +0,
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		$( "#datepicker3").datepicker({
			minDate: +0,
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