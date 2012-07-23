var step3ButtonsDisabled = true;
var thisNewSlotClass = $('#slotDisabledClass').val();
var thisNewAdjustButtonsClass = $('#adjustDisabledClass').val(); 

function disableStep3Buttons()
{
	if( step3ButtonsDisabled ) return false;
	
	thisNewSlotClass = $('#slotDisabledClass').val();
	thisNewAdjustButtonsClass = $('#adjustDisabledClass').val(); 
	step3ButtonsDisabled = 'disabled';
	postProcessStep3Buttons();
}

function enableStep3Buttons()
{
	if( !step3ButtonsDisabled ) return false;
	thisNewSlotClass = $('#slotEnabledClass').val();
	thisNewAdjustButtonsClass = $('#adjustEnabledClass').val(); 
	step3ButtonsDisabled = false;
	postProcessStep3Buttons();
}

function postProcessStep3Buttons()
{
	$( '#reduceSlots' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#addSlots' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#slot' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#reduceSlots' ).attr( 'class', thisNewAdjustButtonsClass ); 
	$( '#addSlots' ).attr( 'class', thisNewAdjustButtonsClass ); 
	$( '#slot' ).attr( 'class', thisNewSlotClass ); 
}

$(document).ready( function() {			
	$("span#showtimeSelectionReal").hide();	//on body load
	$("span#showtimeWaiting").hide();		//on body load
	
	$('select#eventSelection').change( function(){
		
		if( !isElementNotVisible( "span#showtimeDummy" ) )
		{
			$("span#showtimeDummy").hide();		
		}
		if( !isElementNotVisible( "span#showtimeSelectionReal" ) )
		{
			$("span#showtimeSelectionReal").hide();		
		}
		if( !isElementNotVisible( "span#showtimeCustomError" ) )
		{
			$("span#showtimeCustomError").hide();
		}
			    		
		// get children of showing time selection and .remove() each
		$('#showingTimeSelection').children().each( function(){
			$(this).remove();
		});		
		
		// if the chosen event name is the default, that is "Select an Event"
		if( $(this).val() == "NULL" ){
			$("span#showtimeSelectionReal").hide();	// hide the selection that contains the events
			$("span#showtimeDummy").show();			// show the message "Select an Event first" in Showing Time panel
			disableStep3Buttons();	
			return false;		
		}
		
		$("span#showtimeWaiting").show();		// show ajax pre-loader
		// now contact server to request for "for sale" showing times of the selected event
		var requestST_POST = $.ajax({
			type: 'POST',
			url: CI.base_url + "eventctrl/getConfiguredShowingTimes" , // URL, CI.base_url found in the page
			timeout: 40000,
			data: { 'eventID': $(this).val() },						  // DATA
			success: function(data){										  // function to handle afterwards
				var splitDetails = data.split('_');
				if( splitDetails[0] == "ERROR" || splitDetails[0] == "INVALID" )
				{					
					$("span#showtimeDummy").hide();
					$("span#showtimeCustomError").html( splitDetails[1] );
					$("span#showtimeCustomError").show();					
				}else				
				if( splitDetails[0] == "OK" )
				{					
					var uniqueID;
					var entryIndicator;
					var startDate;
					var startTime;
					var endDate;
					var endTime;
					var x=0;
					var z=0;
					var uniqueID_arr = [];
					var entryIndicator_arr = [];					
					var showTimeDates = [];
					var showTimeDatesCounter = [];
					
					// for each showing time entry...
					$( splitDetails[1] ).find( 'schedule' ).each( function() {
						//add to uniqueID array
						uniqueID_arr.push( $(this).attr( 'uniqueID' ) );
						
						//now find start date and time
						$startTimeStamp_obj = $(this).find('start');
						$endTimeStamp_obj = $(this).find('end');
						
						// get individual information
						startDate = $startTimeStamp_obj.find('date').text();												
						startTime = $startTimeStamp_obj.find('time').text();						
						endDate = $endTimeStamp_obj.find('date').text();						
						endTime = $endTimeStamp_obj.find('time').text();
						
						// add a date to the optgroup identifier
						if( showTimeDates[ convertDateMonth_toText(startDate.toString() ) ] === undefined )
						{
							showTimeDates[ convertDateMonth_toText( startDate.toString() ) ] = [];							
							showTimeDatesCounter[ convertDateMonth_toText( startDate.toString() ) ] = 0;
						}
						
						// now assemble						
						entryIndicator = '<option value="' + $(this).attr( 'uniqueID' )  +'" >';
						entryIndicator += convertTimeTo12Hr( startTime );
						entryIndicator += " - ";
						entryIndicator += convertTimeTo12Hr( endTime );
						if( startDate != endDate ) // if not same date (i.e. red-eye), add end date
						{
							entryIndicator += '( ';
							entryIndicator += ( convertDateMonth_toText( endDate.toString() ) + " ") ;
							entryIndicator += ' )';
						}						
						entryIndicator += "</option>";
						showTimeDates[ convertDateMonth_toText( startDate.toString() ) ][ showTimeDatesCounter[ convertDateMonth_toText( startDate.toString() ) ]++ ] = entryIndicator;						
					});
					// get a handle to the children, i.e. the options/showing times, so that we can add <optgroup> 
					var showTimesMenu = $('#showingTimeSelection').children();
					showTimesMenu.add( '<option value="NULL">Select a showing time</option>' ).appendTo( "#showingTimeSelection" );
					for( var key in showTimeDates )
					{
						showTimesMenu.add( '<optgroup label="' + key  + '" >&nbsp;</opgroup>' ).appendTo( "#showingTimeSelection" );
						var optgroupHandle = $('select#showingTimeSelection optgroup[label="' + key  + '"]');
						for( x = 0; x < showTimeDatesCounter[key]; x++ )
						{							
							$('select#showingTimeSelection optgroup[label="' + key  + '"]').append( showTimeDates[key][x] ); //.appendTo( $('#showingTimeSelection select optgroup[label="' + key  + '"]') );							
						}
					}					
					
					// then show the selection
					$("span#showtimeSelectionReal").show();															
				}//if split..[0] == "OK"				
				// hide the ajax pre-loader
				$("span#showtimeWaiting").hide();
				enableStep3Buttons();				
			}//function		
		});//ajax
		requestST_POST.fail( function( jqXHR, textStatus ){
			$("span#showtimeWaiting").hide();
			$("span#showtimeDummy").show();		
			$.fn.nextGenModal({
				msgType: 'error', 
				title: 'Connection timeout',
				message: 'It seems you have lost your internet connection. Please try again.<br/><br/>'
			});
		});
		
	});
	
	$('input#addSlots').click( function() {			
		var selector_ChangeThis = "#slot";
		var thisVal = $( selector_ChangeThis ).val();		
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: 'Only numbers allowed for slots!'
			});
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal, 10) == 10 )
		{		
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'restriction',
			   message: 'Only 10 slots can be booked at a time.'
			});
			return false;
		}		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes				
		$( selector_ChangeThis ).val( parseInt(thisVal, 10) + 1);
		return true;		
		
	}); //input#addSlots
	
	$('input#reduceSlots').click( function() {			
		var selector_ChangeThis = "#slot";
		var thisVal = $( selector_ChangeThis ).val();		
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: 'Only numbers allowed for slots!'
			});
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal, 10) == 1 )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Minimum of 1 slot!'
			});
			return false;
		}		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes				
		$( selector_ChangeThis ).val( parseInt(thisVal, 10) - 1);
		return true;		
		
	}); //input#reduceSlots
	
	$('input#slot').focus( function() {
		/*
			When an input field is being focused, record
			its value on a hidden input field. 
		*/
		$('#lastFocus').val( $(this).val() );
	});
	
	$('input#slot').blur( function() {
		/*
			When an input field is being focused, record
			its value on a hidden input field. 
		*/
		if( !isInt( $(this).val() ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: 'Only numbers allowed for slots!'
			});
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) > 10 )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'restriction',
			   message: 'Only 10 slots can be booked at a time.'
			});
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) < 1 )
		{						
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'restriction',
			   message: 'Minimum of 1 slot!'
			});

			$(this).val( $('#lastFocus').val() );
			return false;
		}
	});
	
	$('#buttonOK').click( function(){
		var selectedEventID =  $('select#eventSelection option:selected').val();
		var selectedSchedule = $('select#showingTimeSelection option:selected').val();
		if( selectedEventID == "NULL"  )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'input needed',
			   message: 'Please select an event first.'
			});
			return false;
		}
		if( selectedSchedule == "NULL" )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'input needed',
			   message: 'Please select a showing time first.'
			});
			return false;
		}
		
		document.forms[0].submit();
	});
	
});