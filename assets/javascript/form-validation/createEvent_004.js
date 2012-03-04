function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

$(document).ready( function () 
{
	/*
		If there is only one showing time specified, choose
		it automatically.
	*/
	if( $('input[type="checkbox"][id^="ch_"]').size() == 1 )
	{
		$('input[type="checkbox"][id^="ch_"]').attr( 'checked', 'checked' );		
	}
	
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: "Invalid number of slots"
			});			
			return;
		}
		$("#id_slots").val( parseInt(thisVal) + 1);		
	});
	
	$("#reduceSlots").click( function() {
		thisVal = $("#id_slots").val();
		if( !isInt( thisVal ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: "Invalid number of slots"
			});
			return;
		}
		$("#id_slots").val( parseInt(thisVal) - 1);		
	});
	
	$("#buttonReset").click( function() {									
			$.fn.nextGenModal({
			   msgType: 'okay',
			   title: 'Not yet :-)',
			   message: 'Feature coming later'
			});
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
				$.fn.nextGenModal({
				   msgType: 'error',
				   title:  'info required',
				   message: "Please select at least one showing time to configure."
				});
				return;
			}
			
			thisVal = $("#id_slots").val();
			if( !isInt( thisVal ) )
			{							
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'bad expectation',
				   message: "Invalid number of slots"
				});
					return;
			}
			if( thisVal < 1 )
			{								
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'bad expectation',
				   message: "As in zero slot? Are you kidding me?" 
				});
				return;
			}			
			$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'Confirm',
			   message: "Are you sure these are what you want to configure now and you have specified the number of slots correctly?",
			   yesFunctionCall: 'formSubmit'
			});
			
			
	});
});