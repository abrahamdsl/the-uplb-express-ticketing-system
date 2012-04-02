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
			return false;		
		}
		
		$("span#showtimeWaiting").show();		// show ajax pre-loader
		// now contact server to request for "for sale" showing times of the selected event
		var requestST_POST = $.ajax({
			type: 'POST',
			url: CI.base_url + "EventCtrl/getConfiguredShowingTimes" , // URL, CI.base_url found in the page
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