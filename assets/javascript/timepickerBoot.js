$(document).ready(function() {
			$('#timepicker_start').timepicker({
				showLeadingZero: true,				
				minutes: {
					starts: 0,
					ends: 59,
					interval: 1
				}
			});
			
			$('#timepicker_end').timepicker({
				showLeadingZero: true,
				onHourShow: tpEndOnHourShowCallback,
				onMinuteShow: tpEndOnMinuteShowCallback,
				minutes: {
					starts: 0,
					ends: 59,
					interval: 1
				}
			});
			//START: for CreateEvent_006
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
				minutes: {
					starts: 0,
					ends: 59,
					interval: 1
				}
			});
			//END: for CreateEvent_006
});
				
		function tpEndOnHourShowCallback(hour) {
			
			var tpStartHour = $('#timepicker_start').timepicker('getHour');
			var tpStart_isEmpty = ($('#timepicker_start').val() == "" );
			var isShow_RedEye = document.getElementById('id_redEyeIndicator').checked;
			//alert('utang!!! ' + hour + "|" + tpStartHour + tpStart_isEmpty );
			
			// no start time yet specified, so no need to proceed further in this func
			if( tpStart_isEmpty ) return true;
			
			// check if it's a red-eye show (meaning, end of the show is the next day)
			if( isShow_RedEye ) return true;
			
			// Check if proposed hour is after or equal to selected start time hour			
			if ( hour >= tpStartHour) { return true; }
			// if hour did not match, it can not be selected
			return false;
		}
		function tpEndOnMinuteShowCallback(hour, minute) {
			var tpStartHour = $('#timepicker_start').timepicker('getHour');
			var tpStartMinute = $('#timepicker_start').timepicker('getMinute');
			var isShow_RedEye = document.getElementById('id_redEyeIndicator').checked;
			
			// check if it's a red-eye show (meaning, end of the show is the next day)
			if( isShow_RedEye ) return true;
			// Check if proposed hour is after selected start time hour
			if (hour > tpStartHour) { return true; }
			// Check if proposed hour is equal to selected start time hour and minutes is after
			if ( (hour == tpStartHour) && (minute > tpStartMinute) ) { return true; }
			// if minute did not match, it can not be selected
			return false;
		}