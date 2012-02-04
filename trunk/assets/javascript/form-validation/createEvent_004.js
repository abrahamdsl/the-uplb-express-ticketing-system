function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

$(document).ready( function () 
{
	$("#checkAll").click( function() {
		$('input[type="checkbox"]').attr( "checked", true );
	}); //checkAll button click
	
	$("#UncheckAll").click( function() {
		$('input[type="checkbox"]').attr( "checked", false );
	}); //UncheckAll button click
	
	$("#addSlots").click( function() {
		thisVal = $("#id_slots").val();
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'bad expectation', "Invalid number of slots" );						
			return;
		}
		$("#id_slots").val( parseInt(thisVal) + 1);		
	});
	
	$("#reduceSlots").click( function() {
		thisVal = $("#id_slots").val();
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'bad expectation', "Invalid number of slots" );	
			return;
		}
		$("#id_slots").val( parseInt(thisVal) - 1);		
	});
	
	$("#buttonReset").click( function() {						
			displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
	});
	
	$("#buttonOK").click( function() {						
			var allCheckBox = $('input[type="checkbox"]').get();
			var atLeastOneCheckBoxSelected = false;
			var x;
			var y;
			var decision;
			
			for( x=0, y=allCheckBox.length ; x<y; x++)
			{								
				if( $(allCheckBox[x]).attr( 'checked' ) == "checked" || $(allCheckBox[x]).attr( 'checked' ) == true )
				{
					atLeastOneCheckBoxSelected = true;
					break;
				}
			}
			
			if( !atLeastOneCheckBoxSelected )
			{
				displayOverlay( 'error' , 'info required', "Please select at least one showing time to configure." );					
				return;
			}
			
			thisVal = $("#id_slots").val();
			if( !isInt( thisVal ) )
			{
				displayOverlay( 'error' , 'bad expectation', "Invalid number of slots" );	
				return;
			}
			if( thisVal < 1 )
			{				
				displayOverlay( 'error' , 'bad expectation', "As in zero slot? Are you kidding me?" );
				return;
			}
			displayOverlay_confirm( 'warning' , 'Confirm', 'formSubmit', null, null, null, "Are you sure these are what you want to configure now?" );																		
			
			
	});
});