/*
	NOTE: 15DEC2011-1513: A LOT OF REFACTORING TO BE DONE here. Might look
	at createEvent_004.js too and its relation with here.
*/
function sumTotalSlots()
{
	/*
		Created 13JAN2012-1054
		
		It is assumed all fields called here have the proper integer values.
	*/
	var eachClass_slots;
	var x;		
	var y;
	var Total;
	var addThis = 0;	
		
	eachClass_slots = $('input[name^="slot"]').get();			
	for( Total = 0, x = 0, y = eachClass_slots.length; x < y; x++)	// just adding, cumulatively
	{				
		Total += parseInt( $(eachClass_slots[x]).val() );		
	}
	$('#totalSlotsChosen').val( Total );
}//sumSlotsTotal(..)

function checkSlotsTotal( theObject)
{
	/*
		13JAN2012-1112: reduced this. Most was delegated to sumTotalSlots()
	*/
	var Total = $('#totalSlotsChosen').val() ;
	
	if( Total > parseInt( $("#maxSlot").val() ) )
	{		
		displayOverlay( 'error' , 'error', "Total slots of all classes exceeds maximum allowed." );												
		theObject.val( $('#lastFocus').val() );		// restore the former value
		sumTotalSlots();							// recompute total again
		return false;
	}	
	return true;
}//checkSlotsTotal(..)

function formSubmitPreCheck()
{
	// created 13JAN2012-1130
	if( parseInt( $('#totalSlotsChosen').val() ) == 0 )
	{
		displayOverlay( 'error' , 'are you kidding me', "Please enter at least one slot in at least one ticket class, else click Cancel to configure this event later." );	
		return false;		
	}	
	return true;
}

function formSubmit()
{
	// created 7JAN2012-1547
	
	document.forms[0].submit();			
	
	return [ this ];
}

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
		$seatMapUniqueID = $('#seatMap option:selected').val();		
		if( isNaN( parseInt( $seatMapUniqueID ) ) )
		{
			displayOverlay( 'error' , 'first and foremost', 'Please choose a seat map first.' );
			return false;
		}
		displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
	});
	
	$('input[id^="id_privilege_"]').click( function() {
		displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
	});
	
	$('input[id^="addSlots_"]').click( function() {	
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		var retVal = true;
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{			
			displayOverlay( 'error' , 'error', "Invalid slot quantity." );										
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes
		if( checkSlotsTotal( thisVal ) )
		{
			$( selector_ChangeThis ).val( parseInt(thisVal) + 1);			
		}
		else{
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value			
			retVal = false;
		}
		sumTotalSlots();
		return  retVal;
	});
	
	$('input[id^="reduceSlots_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{			
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
			return false;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum slots is zero." );
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);
		sumTotalSlots();
		return true;
	});
	
	$('input[id^="addPrice_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_price_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
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
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
			return;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum price is zero." );return;
		}		
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[id^="addHoldingTime_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_HoldingTime_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of minutes for holding time." );
			return false;
		}
		if( thisVal == 59 )
		{
			displayOverlay( 'error' , 'error',"Maximum holding time is 59 minutes." );
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) + 1);		
	});
	
	$('input[id^="reduceHoldingTime_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_HoldingTime_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of holding time." );
			return false;
		}		
		if( parseInt(thisVal) == 2 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum holding time is two minutes." );return;
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[name^="price"]').blur( function()	{
		if( !isFloat( $(this).val() ) )
		{
			displayOverlay( 'error' , 'bad expectation',"Price not valid." );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) < 0 )
		{			
			displayOverlay( 'error' , 'bad expectation','Negative amount not allowed.' );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		
	});
	
	$('input[name^="slot"]').blur( function()	{				
		if( !isInt( $(this).val() ) )
		{
			displayOverlay( 'error' , 'error', "Invalid slot quantity." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes
		sumTotalSlots();
		return checkSlotsTotal( $(this) );
		
	}); //$('input[name^="slot"]').blur(..)
	
	$('input[name^="holdingTime"]').blur( function()	{				
		var thisVal = $(this).val();
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of holding time." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}						
		if( parseInt(thisVal) < 2 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum holding time is two minutes." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) >= 59 )
		{
			displayOverlay( 'error' , 'error',"Maximum holding time is 59 minutes." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
	}); //$('input[name^="holdingTime"]').blur(..)
	
	$("#buttonReset").click( function() {		
		displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
		return false;
	});
	
	$("#buttonOK").click( function() {		
		if( formSubmitPreCheck() ){
			displayOverlay_confirm( 'warning' , 'Confirm', 'formSubmit', null, "Are you sure you have configured them?");																				
		}
		return false;
	});
	
	/*$('input[name^="slot"]').change(		
		$('input[name^="slot"]').blur();
	);*/
	
});