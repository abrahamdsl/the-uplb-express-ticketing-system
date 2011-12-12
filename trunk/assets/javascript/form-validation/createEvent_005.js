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


$(document).ready( function() {
	
	$('input').focus( function() {
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
			return;
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