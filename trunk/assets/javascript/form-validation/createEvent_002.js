

function getHiddenLocator(which)
{
	if( which == "TIME" )
		return "#TF_hidden";
	else
	if( which == "DATE" )
		return "#DF_hidden";
	else
		return false;
}

function appendToHidden( which, newEntry)
{
	var currentVal;	
	var separator = "";
	var locator;
		
	locator = getHiddenLocator(which);
    if( !locator ) return;
	
	currentVal = $(locator).val();						// get contents of the hidden field
	if( currentVal != "" ) separator = "|";				// if content isn't empty, of course we have to separate via pipes
	$(locator).val( currentVal + separator + newEntry );// now set value	
	return true;
}//appendToHidden

function deleteSelectedShowingTimeInfo( data )
{
	/*
		Created 07JAN2012-1723
		
		data - an array, expected only one element, containing the ID for the showing time info ton be deleted
	*/
	
	removeFromHidden( data[0], $("option:selected").val() );
	$("option:selected").remove();
}//deleteSelectedShowingTimeInfo

function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();
	
	return [ this ];
}

function removeFromHidden( which, whatEntry )
{
	var currentVal;
	var currentVal_splitted;
	var newVal = "";
	var separator = "";
	var selector;
	var locator;
	var frameTemp;
	var add_me;
	var x;
	var y;
	
	locator = getHiddenLocator(which);
    if( !locator ) return;
	
	currentVal = $(locator).val();	
	currentVal_splitted = currentVal.split('|');
	if( currentVal != "" ) separator = "|";
	
	for( x=0, y=currentVal_splitted.length; x < y; x++ )
	{
		if( currentVal_splitted[x] != whatEntry )
		{
			newVal = newVal + currentVal_splitted[x] + separator;
		}		
	}	
	
	//PERFORM STRING CLEAN-UP
	
	// for double pipe
	newVal = newVal.replace('||', '|');		
	
	// for pipe at the very end
	y = newVal.length;
	if( newVal[y-1] == "|" ) newVal = newVal.substr( 0, y-1 ); 
	
	//for pipe at the start
	if( newVal[0] == "|" ) newVal = newVal.substr( 1 );
	
	
	$(locator).val(newVal);		// now write the new value to the hidden input field
	
	// then see if hidden value is empty. If so, restore the "Add Time/Date" texts.
	if( newVal == "" )
	{
		if( which == "TIME" ){
			selector = "#timeSelect";
			add_me = '<option value="NONE" class="timeFrames_proper" >Add Time</option>';
		}else
		if( which == "DATE" ){
			selector = "#dateSelect";
			add_me = '<option value="NONE" class="dateFrames_proper" >Add date</option>';
		}else
			return false;
		
		frameTemp = $(selector).children();				// get a DOM handle
		frameTemp.add( add_me  ).appendTo($(selector)); // append
		$(selector).attr( 'disabled', true );			// enable selection
	}
} //removeFromHidden


/*
	actionListener for submitting the form
*/
$(document).ready(function()
	{								
		/*
			ask for some form submit confirmation here
		*/
	
		$("#buttonReset").click( function() {									
			$.fn.nextGenModal({
			   msgType: 'okay',
			   title: 'Not yet :-)',
			   message: 'Feature coming later' 
			});
		});
		
		/*
			FORM SUBMISSION AREA
		*/
		$("#buttonOK").click(function() {	
			timeFrames = $( getHiddenLocator('TIME') ).val();
			dateFrames = $( getHiddenLocator('DATE') ).val();
			var decision = false;
			
			if( timeFrames == "" )
			{				
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'info required',
				   message: "We cannot proceed if you don't have time frames specified."
				});
				return;
			}
			
			if( dateFrames == "" )
			{
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'info required',
				   message: "We cannot proceed if you don't have date frames specified."
				});
				return;
			}								
			$.fn.nextGenModal({
			   msgType:  'warning',
			   title:  'Confirm',
			   message: "Are you sure that you are correct in what you have entered?\n\nYou can only change these after finishing the wizard." ,
			   yesFunctionCall: 'formSubmit'
			});
		});
		
		$("#addTimeBtn").click( function()
			{	
				var x;
				var y;
				var isShow_RedEye = document.getElementById('id_redEyeIndicator').checked;
				var timeFrames = document.getElementById('timeSelect').getElementsByTagName('option');
				var tpStart_time = $('#timepicker_start').val().split(':');
				var tpEnd_time = $('#timepicker_end').val().split(':');
				var tpStart = new Date( '8/8/2008' );
				var tpEnd = new Date( '8/8/2008' );
				var timeFrames;
				var timeFrames_obj;
				var addThisTimeframe;
				var difference_in_Millisecs;
				var add_me;
				
				//check first if they are not blank
				if( $('#timepicker_start').val() == "" )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'info required',
					   message: 'Enter time start!'
					});
					return false;
				}
				
				if( $('#timepicker_end').val() == "" )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'info required',
					   message: 'Enter time end!'
					});
					return false;
				}

				//now try determining difference
				tpStart.setHours(tpStart_time[0]);
				tpStart.setMinutes(tpStart_time[1]);
				tpEnd.setHours(tpEnd_time[0]);
				tpEnd.setMinutes(tpEnd_time[1]);

				difference_in_Millisecs = tpEnd - tpStart;
				
				// if invalid time string is submitted, difference_in_Millisecs would be NaN
				if( isNaN(difference_in_Millisecs) )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'misunderstanding',
					   message:  "Incorrect time format!" 
					});
					return;
				}
				if( !isShow_RedEye )
				{
					if( difference_in_Millisecs < 0 )
					{
						$.fn.nextGenModal({
						   msgType: 'error',
						   title: 'bad expectation',
						   message:   "Not a Red Eye show but end time is earlier than showing time!"
						});
						return;
					}
				}
				timeFrames = $('#timeSelect').children(); 				//get all children nodes - the time frames		
				timeFrames_obj = timeFrames.toArray();					//just to convert them to array for comparison later on								
				addThisTimeframe = $('#timepicker_start').val() + " - " + $('#timepicker_end').val(); // the time to be added
				// now check the existing timeframe to see if something exists already
				for( x=0, y=timeFrames_obj.length ; x < y ; x++ )
				{
					if( timeFrames_obj[x].innerHTML == addThisTimeframe )
					{					
						$.fn.nextGenModal({
						   msgType: 'error',
						   title: 'bad expectation',
						   message:  "Time exists already!"
						});	
						return;
					}
				}
				
				if( timeFrames.first().val() == "NONE" )
				{
					document.getElementById('timeSelect').innerHTML = "";   //remove the "Add time" text
					$('#timeSelect').attr( 'disabled', false );				//enable the selection
					timeFrames = $('#timeSelect').children();				//reinitialize
				}
				
				add_me = '<option class="timeFrames_proper" id="' + addThisTimeframe + '" >' + addThisTimeframe  +"</option>";
				timeFrames.add(add_me).appendTo($('#timeSelect'));
				appendToHidden( 'TIME', addThisTimeframe );
				
				//remove values for timeframe and unset red-eye checkbox
				$( '#timepicker_start' ).val( "" );
				$( '#timepicker_end' ).val( "" );
				$( '#id_redEyeIndicator' ).attr( "checked", false );
			}
		);//addTimeBtn
	
		$('#addDateBtn').click(function(){
			var dateChosen = $('#datepicker').val();			// get the value
			var dateChosen_splitted = dateChosen.split('/');	// split via the default separator
			var x;
			var y;
			var dateFrames;
			var dateFrames_obj;
			var addThisDateFrame;
			var add_me;
			
			if( dateChosen == "" )	// blank, alert!
			{
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'info required',
				   message: "Please choose a date." 
				});
				return;
			}
			
			if( dateChosen_splitted.length != 3 )
			{
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'info required',
				   message: "Invalid date."
				});
				return;
			}
			
			for( x=0; x < 3; x++ )	// array length should be fixed at 3: mm/dd/yyyy
			{
				if( !isInt(dateChosen_splitted[x]) )	// found at javascript/form-validation/generalChecks.js
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'bad expectation',
					   message: "Invalid characters detected." 
					});
					return;
				}
			}
			
			dateFrames = $('#dateSelect').children(); 				//get all children nodes - the dates
			dateFrames_obj = dateFrames.toArray();					//just to convert them to array for comparison later on
			
			//check if date already exists
			for( x=0, y=dateFrames_obj.length ; x < y ; x++ )			// now check the existing timeframe to see if something exists already
			{
					if( $(dateFrames_obj[x]).val() == dateChosen )
					{
						$.fn.nextGenModal({
						   msgType: 'error',
						   title: 'Date exists already',
						   message: "Please specify another date."
						});
						return;
					}
			}
			
			if( dateFrames.first().val() == "NONE" )
			{
					document.getElementById('dateSelect').innerHTML = "";   //remove the "Add Date" text
					$('#dateSelect').attr( 'disabled', false );				//enable the selection
					dateFrames = $('#dateSelect').children();				//reinitialize
			}

			// now add
			add_me = '<option class="dateFrames_proper" value="' + dateChosen + '" >' + convertDateMonth_toText( dateChosen ) +"</option>";
			dateFrames.add( add_me ).appendTo($('#dateSelect'));
			appendToHidden( 'DATE', dateChosen );
			//clear value of date input
			$( '#datepicker' ).val( "" );
		}); //addDateBtn
		
		/*
			Function for deleting an entry
		*/
		$('select').dblclick( function(){
			var thisVal = $(this).val();
			var whatClass = $("option:selected").attr('class');
			var optionFor = ( whatClass == "timeFrames_proper" ) ? "TIME" : "DATE";
			var userFriendlyValue;
										
			if ( thisVal == null ) return false;
			if( optionFor == "DATE" )
			{
				/*
					07JAN2012_1816: IDK why, but if i use "thisVal" instead of 
					"thisVal.toString()" as an argument, it seems an object is passed
					instead of a string
				*/
				userFriendlyValue =  convertDateMonth_toText( thisVal.toString() );
			}else{
				userFriendlyValue = thisVal;
			}								
			$.fn.nextGenModal({
			   msgType: 'warning' ,
			   title: 'Confirm',
			   message: 'Do you want to delete the following?<br/><br/> ' + userFriendlyValue,
			   yesFunctionCall: 'deleteSelectedShowingTimeInfo',
			   yFC_args: new Array( optionFor )
			});
		}); //double clicking an option to delete
	}
);
