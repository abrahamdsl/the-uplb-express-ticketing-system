

$(document).ready( function() {
	// created 21DEC2011-1637
	var antiRepeater = 0;
	
	// when body loads, hide the ff stated in the next 3 lines
	$('#right_inner_fixedSameDay').show();
	$('#right_inner_fixedAfterBookingDay').hide();
	$('#right_inner_RelativeAfterBookingDay').hide();
	
	// START: selling dates start and end
	$('input[name^="selling_date"]').blur( function() {		
		var isDatepickerOpen = $(this).datepicker("widget").is(":visible");		
		
		if( isDatepickerOpen ){			
			return false;
		}		
		
		$(this).change();		
		antiRepeater = 0;
	});
	
	$('input[name^="selling_date"]').change( function() {				
		var newVal = $(this).val();
		
		if( newVal.length == 0 )	// restore the last value and class before change
		{						
			$(this).attr( 'class', "textInputSize grayGuide" );
			$(this).val( $( '#' + $(this).attr('name')+'_caption').val() );		
			return true;
		}		
		$(this).attr( 'class', "textInputSize" );	// make the class so that regular black, not italicized text will appear
	});
	
	$('input[name^="selling_date"]').focus( function() {
		//the ff 2 lines prevent repeated calls to the function. 
		// it seems that when you open the datepicker, all dates
		// will call this function. rawr.
		antiRepeater++;
		if( antiRepeater > 1 ) return;

		$('#lastFocus').val( $(this).val() );					//put old value to lastFocus
		$('#lastFocus_class').val( $(this).attr( 'class' ) );	//put old class to lastFocus_class
		$(this).val("");										//empty the contents
		
		//alert( "focus now: " + $('#lastFocus').val() + "|" + antiRepeater + "|" + $(this).val() );		
	});
	// END: selling dates start and end
	
	// START: selling times start and end
	$('input[name^="selling_time"]').blur( function() {		
		var isTimepickerOpen = $(this).timepicker("widget").is(":visible");		
		
		if( isTimepickerOpen ){						
			return false;
		}		
		
		$(this).change();		
		antiRepeater = 0;
	});
	
	$('input[name^="selling_time"]').change( function() {				
		var newVal = $(this).val();
		
		if( newVal.length == 0 )	// restore the last value and class before change
		{									
			$(this).attr( 'class', "textInputSize grayGuide" );
			$(this).val( $( '#' + $(this).attr('name') + '_caption' ).val() );		
			return true;
		}		
		$(this).attr( 'class', "textInputSize" );	// make the class so that regular black, not italicized text will appear
	});
	
	$('input[name^="selling_time"]').focus( function() {
		//the ff 2 lines prevent repeated calls to the function. 
		// it seems that when you open the timepicker, all dates
		// will call this function. rawr.
		antiRepeater++;
		if( antiRepeater > 1 ) return;

		$('#lastFocus').val( $(this).val() );					//put old value to lastFocus
		$('#lastFocus_class').val( $(this).attr( 'class' ) );	//put old class to lastFocus_class
		$(this).val("");										//empty the contents
		
		//alert( "focus now: " + $('#lastFocus').val() + "|" + antiRepeater + "|" + $(this).val() );		
	});
	// END: selling times start and end
	
	// START: #numOfDays
	$('#numOfDays').blur( function() {				
		$(this).change().delay(2000);
	});
	
	$('#numOfDays').change( function() {		
		var newVal = $(this).val();
		
		if( newVal.length == 0 )
		{			
			$(this).attr( 'class', "textInputSize_Larger  grayGuide" );
			$(this).val( $('#numOfDays_caption').val() );		
			return true;
		}
		
		if( newVal != $('#numOfDays_caption').val() ){	
			$(this).attr( 'class', $('#lastFocus_class').val() );
			if( !isInt( newVal ) ){
				alert( "Invalid number of days" );
				$(this).val( $('#lastFocus').val() );
				return false;
			}
			if( parseInt( newVal ) < 1 ) {
				alert( "Minimum of 1 day for this. " );
				$(this).val( $('#lastFocus').val() );
				return false;
			}
			
			// now make it like an ordinary input field
			$(this).attr( 'class', "textInputSize_Larger" );
		}
	});
	
	$('#numOfDays').focus( function() {
		$('#lastFocus').val( $(this).val() );					//put old value to lastFocus
		$('#lastFocus_class').val( $(this).attr( 'class' ) );			//put old class to lastFocus_class
		$(this).val("");										//empty the contents
	});
	// END: #numOfDays
	
	// START: #fixedTime
	$('#fixedTime').blur( function() {				
		$(this).change().delay(2000);
	});
	
	$('#fixedTime').change( function() {		
		var newVal = $(this).val();
						
		if( newVal.length == 0 )
		{			
			$(this).attr( 'class', "textInputSize_Larger  grayGuide" );
			$(this).val( $('#fixedTime_caption').val() );		
			return;
		}
		
		if( newVal != $('#fixedTime_caption').val() ){
			$(this).attr( 'class', "textInputSize_Larger" );
		}
				
	});
	
	$('#fixedTime').focus( function() {		
		$('#lastFocus').val( $(this).val() );	//put old value to lastFocus
		$(this).val("");						//empty the contents
	});
	// END: #fixedTime
	
	
	$("select#deadlineChoose").change( function(){
		var option = $(this).val()
		$('#deadlineSelectionVal').val( option );	// set the new value as the new value of the hidden input form since select is not being submitted
		$('span.innerRightChanging').hide();
		
		// 23DEC2011-1125: case 1 removed since case 2 and 3 uses it too, just put it at the end of the function		
		switch( option )
		{						
			case "2": $('#right_inner_fixedAfterBookingDay').show(); break;
			case "3": $('#right_inner_RelativeAfterBookingDay').show(); break;			
		}
		$('#right_inner_fixedSameDay').show(); 						
		
	});
	
	// START: The Relative times after options / Option 3
	$('#relative_days').blur( function() {				
		$(this).change().delay(2000);
	});
	
	$('#relative_days').change( function() {		
		var newVal = $(this).val();
		
		if( newVal.length == 0 )
		{			
			$(this).attr( 'class', "textInputSize grayGuide" );
			$(this).val( "Days" );		
			return true;
		}
		
		if( newVal != "Days" ){	
			$(this).attr( 'class', $('#lastFocus_class').val() );
			if( !isInt( newVal ) ){
				alert( "Invalid number of days" );
				$(this).val( $('#lastFocus').val() );
				return false;
			}			
			
			// now make it like an ordinary input field
			$(this).attr( 'class', "textInputSize" );
		}
	});
	
	$('#relative_days').focus( function() {
		$('#lastFocus').val( $(this).val() );					//put old value to lastFocus
		$('#lastFocus_class').val( $(this).attr( 'class' ) );			//put old class to lastFocus_class
		$(this).val("");										//empty the contents
	});
	
	// END: The Relative times after options / Option 3
	
	//SUBMIT BUTTON
	$('#buttonOK').click( function(){
		// START: validate online selling availability		
		var sellingDateStart = $('input[name="selling_dateStart"]').val();
		var sellingDateEnd = $('input[name="selling_dateEnd"]').val();
		var sellingTimeStart = $('input[name="selling_timeStart"]').val();
		var sellingTimeEnd = $('input[name="selling_timeEnd"]').val();
		var isShow_RedEye = document.getElementById('id_redEyeIndicator').checked;
		var decision = false;
		var numOfDays;
		var deadlineModeChosen;
		
		if( !isDateValid( sellingDateStart ) ) {
			alert('Invalid Selling Date Start');
			return false;
		}
		if( !isDateValid( sellingDateEnd ) ) {
			alert('Invalid Selling Date End');
			return false;
		}
		if( !isTimeValid( sellingTimeStart ) ){
			alert('Invalid Selling Time Start');
			return false;
		}
		if( !isTimeValid( sellingTimeEnd ) ) {
			alert('Invalid Selling Time End');
			return false;
		}
		if( !isShow_RedEye )
		{
			if ( !isTimestampGreater( sellingDateStart, sellingTimeStart, sellingDateEnd, sellingTimeEnd, isShow_RedEye) )		//found in generalChecks.js
			{
				alert("Selling date end is earlier than selling date end.");
				return false;
			}		
		}
		// END: validate online selling availability		
		
		//START: validate deadline of payment
		deadlineModeChosen = $('#deadlineChoose').val();
				
		switch( deadlineModeChosen )
		// REFACTORING HOTSPOT!!!!
		{
			case "1":	if( !isTimeValid( $('#fixedTime').val() ) ){
							alert("Invalid time specified for payment deadline.");
							return false;
						}						
						break;			
			case "2":	if( !isTimeValid( $('#fixedTime').val() ) ){
							alert("Invalid time specified for payment deadline.");
							return false;
						}		
						numOfDays = parseInt( $('#numOfDays').val() );
						if( isNaN( numOfDays ) || numOfDays < 1 ){
							alert( "Invalid number of days for payment deadline" );
							return false;
						}						
						break;
			case "3":   numOfDays = parseInt( $('#relative_days').val() );
						if( isNaN( numOfDays ) || numOfDays < 0 ){
							alert( "Invalid number of days for payment deadline" );
							return false;
						}						
						if( !isTimeValid( $('#fixedTime').val() ) ){
							alert("Invalid time specified for payment deadline.");
							return false;
						}		
						break;
		}
		//END: validate deadline of payment
		decision = confirm("Are you sure you these entries are correct? Please check one more time.");
		if( !decision ) return false;
		document.forms[0].submit();
	});
	
});

function performRitual( thisObj )
{
	alert('ritual' + thisObj );
}
