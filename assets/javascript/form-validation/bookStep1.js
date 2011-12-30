$(document).ready( function() {
	/*
		30DEC2011-1843
		NO LOGIC YET FOR SERVER TIME OUTS/NO VALID REPLY for some period !!!
	*/
	$("span#showtimeSelectionReal").hide();	//on body load
	//$("span#showtimeWaiting").hide();		//on body load
	
	
	
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
		
	    		
		// get children of selection and .remove() each
		$('#showingTimeSelection').children().each( function(){
			$(this).remove();
		});		
		
		if( $(this).val() == "NULL" ){
			$("span#showtimeSelectionReal").hide();
			$("span#showtimeDummy").show();
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
						entryIndicator = startDate;
						entryIndicator += " ";
						entryIndicator += startTime;
						entryIndicator += " - ";
						if( startDate != endDate ) // if not same date (i.e. red-eye), add end date
						{
							entryIndicator += ( endDate + " ") ;
						}
						entryIndicator += endTime;					
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
		);
	});
	
	$('input#addSlots').click( function() {			
		var selector_ChangeThis = "#slot";
		var thisVal = $( selector_ChangeThis ).val();		
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{
			alert("Invalid slot quantity.");
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
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
			alert("Invalid slot quantity.");
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) == 1 )
		{
			alert( "Minimum of 1 slot!" );
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
			alert('Only numbers allowed for slots!');
			$(this).val( $('#lastFocus').val() );
			return;
		}
		if( parseFloat( $(this).val() ) < 1 )
		{
			alert( "Minimum of 1 slot!" );
			$(this).val( $('#lastFocus').val() );
			return;
		}
	});
	
	$('#buttonOK').click( function(){
		document.forms[0].submit();
	});
	
});