function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

function getEarliestShowingTimeStartDate()
{
	/*
		Created 05FEB2012-1257
		
		Gets date from the page, sorts and returns the string (UTC form)
		of the date which is earliest.
	*/
	var dates = new Array();	
	var x=0;
	//get dates from the page and assign to array
	$("table.schedulesCentral tr td.BCST_date input.value" ).each(
		function(){		
			dates[x++] = new Date( $(this).val() );			
		}
	);
	dates.sort( function(a,b){return a-b; } );	
	return dates[0].toUTCString().replace('-','/');
}

function getEarliestShowingTimeStartTime()
{
	/*
		Created 05FEB2012-1322
		
		Gets starting times from the page, sorts and returns the string (UTC form)
		of the time which is earliest.
	*/	
	var dates = new Array();	
	var x=0; 
	$("table.schedulesCentral tr td.BCST_time_start input.value" ).each(
		function(){			
			var date_obj = new Date( );
			var classifiedTime = classifyTime( $(this).val() );		// found in generalChecks.js
			date_obj.setUTCHours( classifiedTime["hour"] );
			date_obj.setUTCMinutes( classifiedTime["min"] );
			date_obj.setUTCSeconds( classifiedTime["sec"] );
			dates[x++] = date_obj; 
		}
	);
	dates.sort( function(a,b){return a-b; } );	
	return dates[0].toUTCString();
}

$(document).ready( function() {
	// created 21DEC2011-1637
	var antiRepeater = 0;
	
	// when body loads, hide the ff stated in the next 3 lines
	$('#right_inner_fixedSameDay').show();
	$('#right_inner_fixedAfterBookingDay').hide();
	$('#right_inner_RelativeAfterBookingDay').hide();
	
	// convert the showing time dates' months to text
	$("table.schedulesCentral tr td.BCST_date input.value" ).each(
		function(){
			$(this).parent().children('span').html( 
				convertDateMonth_toText( $(this).val() ) 
			);
		}
	);
	getEarliestShowingTimeStartTime();
	// convert the showing time times' to 12-hr
	$('table.schedulesCentral tr td[class^="BCST_time"] input.value' ).each(
		function(){
			$(this).parent().children('span').html( 
				convertTimeTo12Hr( $(this).val() )
			);
		}
	);
			
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
		var thisElemName = $(this).attr('name');
		var hiddenCounterpart = 'hidden_' + thisElemName;
		
		// if the field is now blank, restore the last value and class before change
		if( newVal.length == 0 )	
		{						
			$(this).attr( 'class', "textInputSize grayGuide" );
			$(this).val( $( '#' + thisElemName +'_caption').val() );		
			return true;
		}		
		$(this).attr( 'class', "textInputSize" );	// make the class so that regular black, not italicized text will appear		
		//copy the new value to hidden counterpart
		$( 'input[name="' + hiddenCounterpart + '"]' ).val( newVal );
		//display in user friendly size
		$(this).val( convertDateMonth_toText( newVal ) );
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
				displayOverlay( 'error' , 'error', "Invalid number of days" );				
				$(this).val( $('#lastFocus').val() );
				return false;
			}
			if( parseInt( newVal ) < 1 ) {
				displayOverlay( 'error' , 'error', "Minimum of 1 day for this. " );				
				$(this).val( $('#lastFocus').val() );
				return false;
			}
			
			// now make it like an ordinary input field
			$(this).attr( 'class', "textInputSize_Larger" );
		}
	});
	
	$('#numOfDays').focus( function() {
		$('#lastFocus').val( $(this).val() );					//put old value to lastFocus
		$('#lastFocus_class').val( $(this).attr( 'class' ) );	//put old class to lastFocus_class
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
				displayOverlay( 'error' , 'error', "Invalid number of days" );				
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
		var sellingDateStart = $('input[name="hidden_selling_dateStart"]').val();
		var sellingDateEnd = $('input[name="hidden_selling_dateEnd"]').val();
		var sellingTimeStart = $('input[name="selling_timeStart"]').val();
		var sellingTimeEnd = $('input[name="selling_timeEnd"]').val();
		var isShow_RedEye = document.getElementById('id_redEyeIndicator').checked;
		var decision = false;
		var numOfDays;
		var deadlineModeChosen;
		var earliestST_Date;
		var earliestST_Time;
		
		if( !isDateValid( sellingDateStart ) ) {
			displayOverlay( 'error' , 'bad expectation', "Invalid Selling Date Start." );									
			return false;
		}
		if( !isDateValid( sellingDateEnd ) ) {			
			displayOverlay( 'error' , 'bad expectation', "Invalid Selling Date End" );			
			return false;
		}
		if( !isTimeValid( sellingTimeStart ) ){			
			displayOverlay( 'error' , 'bad expectation', "Invalid Selling Time Start" );			
			return false;
		}
		if( !isTimeValid( sellingTimeEnd ) ) {			
			displayOverlay( 'error' , 'bad expectation', "Invalid Selling Time End" );			
			return false;
		}
		if( !isShow_RedEye )
		{
			
			if ( !isTimestampGreater( sellingDateStart, sellingTimeStart, sellingDateEnd, sellingTimeEnd, isShow_RedEye) )		//found in generalChecks.js
			{				
				displayOverlay( 'error' , 'bad expectation', "Selling end timestamp is earlier than selling start timestamp." );		
				return false;
			}		
			var earliestST_Date = new Date( getEarliestShowingTimeStartDate() );
			var earliestST_Time = new Date( getEarliestShowingTimeStartTime() );
			//	15FEB2012-1450 : Changed '-' to '/', Opera's JavaScript won't accept the dash
			var estD_str = earliestST_Date.getFullYear() + '/' + ( earliestST_Date.getMonth() + 1 ) + '/' + earliestST_Date.getDate();
			var estT_str = earliestST_Time.getUTCHours() + ':' + earliestST_Time.getUTCMinutes();			
			if ( !isTimestampGreater( sellingDateEnd, sellingTimeEnd, estD_str,  estT_str ,isShow_RedEye) )		//found in generalChecks.js
			{				
				displayOverlay( 'error' , 'bad expectation', "Selling end timestamp should be earlier than the earliest showing time ( " +  estD_str + " " +  estT_str  + " ). ");		
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
							displayOverlay( 'error' , 'error', "Invalid time specified for payment deadline." );										
							return false;
						}						
						break;			
			case "2":	if( !isTimeValid( $('#fixedTime').val() ) ){
							displayOverlay( 'error' , 'error', "Invalid time specified for payment deadline." );										
							return false;
						}		
						numOfDays = parseInt( $('#numOfDays').val() );
						if( isNaN( numOfDays ) || numOfDays < 1 ){
							displayOverlay( 'error' , 'error', "Invalid number of days for payment deadline" );										
							return false;
						}						
						break;
			case "3":   numOfDays = parseInt( $('#relative_days').val() );
						if( isNaN( numOfDays ) || numOfDays < 0 ){
							displayOverlay( 'error' , 'error', "Invalid number of days for payment deadline" );										
							return false;
						}						
						if( !isTimeValid( $('#fixedTime').val() ) ){
							displayOverlay( 'error' , 'error', "Invalid time specified for payment deadline." );										
							return false;
						}		
						break;
		}
		//END: validate deadline of payment
		displayOverlay_confirm( 'warning' , 'Confirm', 'formSubmit', null, null, null, "Are you sure that these entries are correct? Please check one more time." );		
	});
	
});
