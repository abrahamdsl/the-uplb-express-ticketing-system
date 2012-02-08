$(document).ready(function() {
		var dateLimit =  new Date( getEarliestShowingTimeStartDate() ); 
		var timeLimit = new Date( getEarliestShowingTimeStartTime() );
		
		$.fn.tpEndOnHourShowCallback_06 = function(hour){														
			// Check if proposed hour is after or equal to selected start time hour						
			if ( hour <= timeLimit.getUTCHours() ) return true;
			// if hour did not match, it can not be selected
			return false;			
		}

		$.fn.tpEndOnMinuteShowCallback_06 = function(hour, minute) {
			var tpEndHour = timeLimit.getUTCHours();
			var tpEndMinute = timeLimit.getUTCMinutes();
			
			// Check if proposed hour is after or equal to selected start time hour			
			if( hour < tpEndHour  ) return true;			
			//Check if proposed hour is equal to selected end time hour and minutes is prior
			if ( (hour == tpEndHour) && (minute < tpEndMinute) ) { return true; }
			// if minute did not match, it can not be selected
			return false;
		}
		
		$( "#datepicker").datepicker({
			minDate: +0,
			changeMonth: true,
			changeYear: true,
			maxDate: dateLimit,
			dateFormat: 'yy/mm/dd',			
			yearRange: '2011:2099'
		});		
		$( "#datepicker2").datepicker({
			minDate: +0,
			changeMonth: true,
			changeYear: true,
			maxDate: dateLimit,
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
		
		$('#fixedTime').timepicker({
			showLeadingZero: true,				
			minutes: {
				starts: 0,
				ends: 59,
				interval: 1
			}
		});
										
		$('#timepicker_end_006').timepicker({
			showLeadingZero: true,	
			//onHourShow: tpEndOnHourShowCallback_06,
			onHourShow: $(document).tpEndOnHourShowCallback_06,
			onMinuteShow: $(document).tpEndOnMinuteShowCallback_06,
			minutes: {
				starts: 0,
				ends: 59,
				interval: 1
			}			
		});

		
});