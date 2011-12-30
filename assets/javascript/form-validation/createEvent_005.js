/*
	NOTE: 15DEC2011-1513: A LOT OF REFACTORING TO BE DONE here. Might look
	at createEvent_004.js too and its relation with here.
*/
function checkSlotsTotal( theObject)
{
	var eachClass_slots;
	var x;		
	var y;
	var Total;
	var addThis = 0;
		
	eachClass_slots = $('input[name^="slot"]').get();			
	for( Total = 0, x = 0, y = eachClass_slots.length; x < y; x++)	// just adding, cumulatively
	{
		if( isInt( $(eachClass_slots[x]).val() ) )
		{
			addThis = parseInt( $(eachClass_slots[x]).val() );
		}else{
			addThis = 0;
		}
		Total += addThis;
	}
	
	if( Total > parseInt( $("#maxSlot").val() ) )
	{
		alert("Total slots of all classes exceeds maximum allowed.");
		theObject.val( $('#lastFocus').val() );	// restore the former value
		return false;
	}
	
	return true;
}//checkSlotsTotal(..)

function giveMeClass( elemID )
{	
	/*
		Created 15DEC2011-1421. Since input fields are named like:
		<functionality>_<class> example addSlots_REGULAR,
		we have to have this to extract class. Uses Javascript's split()
		and returns the second part of the array - the one containing the class.
	*/
	var y = elemID.split('_');	
	if( y.length != 2 ) return "NULL";
	
	return y[1];
}

$(document).ready( function() {
	
	$('input[type="text"]').focus( function() {
		/*
			When an input field is being focused, record
			its value on a hidden input field. 
		*/
		$('#lastFocus').val( $(this).val() );
	});
	
	$('input[id^="id_seat_"]').click( function() {
		alert('Functionality coming soon');
	});
	
	$('input[id^="id_privilege_"]').click( function() {
		alert('Functionality coming soon');
	});
	
	$('input[id^="addSlots_"]').click( function() {	
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
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
		if( checkSlotsTotal( thisVal ) )
		{
			$( selector_ChangeThis ).val( parseInt(thisVal) + 1);
			return true;
		}
		else{
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
	});
	
	$('input[id^="reduceSlots_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			alert("Invalid number of slots.");
			return;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			alert("Minimum slots is zero."); return;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);
	});
	
	$('input[id^="addPrice_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_price_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			alert("Invalid number of slots.");
			return;
		}		
		$( selector_ChangeThis ).val( parseInt(thisVal) + 1);		
	});
	
	$('input[id^="reducePrice_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_price_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			alert("Invalid number of slots.");
			return;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			alert("Minimum price is zero."); return;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[name^="price"]').blur( function()	{
		if( !isFloat( $(this).val() ) )
		{
			alert('Price not valid');
			$(this).val( $('#lastFocus').val() );
			return;
		}
		if( parseFloat( $(this).val() ) < 0 )
		{
			alert('Negative amount not allowed.');
			$(this).val( $('#lastFocus').val() );
			return;
		}
		
	});
	
	$('input[name^="slot"]').blur( function()	{				
		if( !isInt( $(this).val() ) )
		{
			alert("Invalid slot quantity.");
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes
		 return checkSlotsTotal( $(this) );
		
	}); //$('input[name^="slot"]').blur(..)
	
	$("#buttonReset").click( function() {
		alert("Feature coming soon.");
	});
	
	$("#buttonOK").click( function() {
		var decision = false;
		
		decision = confirm("Are you sure you have configured them?");
		if( !decision ) return;
		
		document.forms[0].submit();
	});
	
	/*$('input[name^="slot"]').change(		
		$('input[name^="slot"]').blur();
	);*/
	
});