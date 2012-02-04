var step3ButtonsDisabled = true;

function toggleStep3Buttons()
{
	/*
		08JAN2012-1845
	*/
	var thisNewSlotClass;
	var thisNewAdjustButtonsClass;
	
	if( step3ButtonsDisabled )
	{
		thisNewSlotClass = $('#slotEnabledClass').val();
		thisNewAdjustButtonsClass = $('#adjustEnabledClass').val(); 
	}else{
		thisNewSlotClass = $('#slotDisabledClass').val();
		thisNewAdjustButtonsClass = $('#adjustDisabledClass').val(); 
	}
	step3ButtonsDisabled = !step3ButtonsDisabled; // reverse
	// toggle buttons
	/*alert( step3ButtonsDisabled );	
	alert( thisNewAdjustButtonsClass );
	alert( thisNewSlotClass );*/
	$( '#reduceSlots' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#addSlots' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#slot' ).attr( 'disabled', step3ButtonsDisabled ); 
	$( '#reduceSlots' ).attr( 'class', thisNewAdjustButtonsClass ); 
	$( '#addSlots' ).attr( 'class', thisNewAdjustButtonsClass ); 
	$( '#slot' ).attr( 'class', thisNewSlotClass ); 
}//enableStep3Buttons

$(document).ready( function() {
	/*
		30DEC2011-1843
		NO LOGIC YET FOR SERVER TIME OUTS/NO VALID REPLY for some period !!!
	*/
	
	
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
			toggleStep3Buttons();	
			return false;		
		}
		
		$("span#showtimeWaiting").show();		// show ajax pre-loader
		// now contact server to request for "for sale" showing times of the selected event
		$.post(
			CI.base_url + "EventCtrl/getConfiguredShowingTimes" , // URL, CI.base_url found in the page
			{ 'eventID': $(this).val() },						  // DATA
			function(data){										  // function to handle afterwards
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
					var uniqueID_arr = [];
					var entryIndicator_arr = [];
					
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
						
						// now assemble
						entryIndicator = convertDateMonth_toText( startDate.toString() );												
						entryIndicator += " ";
						entryIndicator += convertTimeTo12Hr( startTime );
						entryIndicator += " - ";
						if( startDate != endDate ) // if not same date (i.e. red-eye), add end date
						{
							entryIndicator += ( convertDateMonth_toText( endDate.toString() ) + " ") ;
						}
						entryIndicator += convertTimeTo12Hr( endTime );					
						entryIndicator_arr.push( entryIndicator );	// add to the array
						//#showingTimeSelection
					});
					// get a handle to the children, i.e. the options/showing times 
					var showTimesMenu = $('#showingTimeSelection').children();
					for( x = 0; x < entryIndicator_arr.length; x++)
					{
						/*
							Loop through the accumulated showing times earlier
							and assemble an <option> HTML element and then append to
							the selection.
						*/
						var addThisHTML = '<option value="';
						addThisHTML += uniqueID_arr[x];
						addThisHTML += '" >';
						addThisHTML += entryIndicator_arr[x];
						addThisHTML += '</option>';
						showTimesMenu.add( addThisHTML ).appendTo( "#showingTimeSelection" );				
					}																		
					// then show the selection
					$("span#showtimeSelectionReal").show();															
				}//if split..[0] == "OK"				
				// hide the ajax pre-loader
				$("span#showtimeWaiting").hide();	
			}//function		
		); // $.post		
		toggleStep3Buttons();
	});
	
	$('input#addSlots').click( function() {			
		var selector_ChangeThis = "#slot";
		var thisVal = $( selector_ChangeThis ).val();		
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'bad expectation', "Only numbers allowed for slots!" );
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) == 10 )
		{
			displayOverlay( 'error' , 'restriction', "Only 10 slots can be booked at a time." );
			return false;
		}		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes				
		$( selector_ChangeThis ).val( parseInt(thisVal) + 1);
		return true;		
		
	}); //input#addSlots
	
	$('input#reduceSlots').click( function() {			
		var selector_ChangeThis = "#slot";
		var thisVal = $( selector_ChangeThis ).val();		
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'bad expectation', "Only numbers allowed for slots!" );
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) == 1 )
		{
			displayOverlay( 'error' , 'error', "Minimum of 1 slot!" );
			return false;
		}		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes				
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);
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
			displayOverlay( 'error' , 'bad expectation', "Only numbers allowed for slots!" );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) < 1 )
		{			
			displayOverlay( 'error' , 'error', "Minimum of 1 slot!" );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
	});
	
	$('#buttonOK').click( function(){
		var selectedEventID =  $('select#eventSelection option:selected').val();
		
		if( selectedEventID == "NULL" )
		{
			displayOverlay( 'error' , 'error', "Please select an event first." );
			return false;
		}
		
		document.forms[0].submit();
	});
	
});