$(document).ready( function() {		
				
		$( "#datepicker2").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		$( "#datepicker3").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy/mm/dd',
			yearRange: '2011:2099'
		});		
		
		$('#timepicker3').timepicker({
				showLeadingZero: true,
				onHourShow: tpEndOnHourShowCallback,
				onMinuteShow: tpEndOnMinuteShowCallback,
				minutes: {
					starts: 0,
					ends: 59,
					interval: 1
				}
		});
		
});