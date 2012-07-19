function cancel_meh(){
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait...',
	   message: 'Cancelling operation, one moment please.'
	});
	setTimeout( 'location.href="' + CI.base_url + 'seatctrl/cancel_create_init' + '"', 500 );
}

$(document).ready( function(){
	var errShow_pre = "#info_req_";
	var errShowInvalid_pre = "#info_invalid_";
	
	$('input').focus( function(){
		$( this ).parent().siblings( '.fieldErrorNotice' ).hide();			// hide any error messages while typing
	});

	$('#buttonReset').click( function(){
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'confirm',
			   closeOnChoose: false,
			   message: "Are you sure you want to cancel the seat creation process?",
			   yesFunctionCall: 'cancel_meh'
		});
	});
	
	$('#buttonOK').click( function(){
		/*
		*	@review 18JUL2012-1336 These form input validations should be consistent with those in
				seatctrl/create_step2
		*/
		var somethingWrong = false;
		var computed;
		
		//check name
		if( $('input[name="name"]').val() == "" )
		{		
			$(  errShow_pre + 'name' ).show();
			somethingWrong = true;
		}		
		//check rows
		if( $('input[name="rows"]').val() == "" )
		{	// empty
			$(  errShow_pre + 'rows' ).show();
			somethingWrong = true;
		}else{
			if( isInt( $('input[name="rows"]').val() ) === false ) 
			{	// not an integer
				$( errShowInvalid_pre + 'rows' ).show();
				somethingWrong = true;
			}else{
				if( parseInt( $('input[name="rows"]').val(), 10 ) > 26 ) 
				{   // only up to 26 allowed
					$( '#info_tooMuch_rows' ).show();
					somethingWrong = true;
				}else
				if( parseInt( $('input[name="rows"]').val(), 10 ) < 1 )   
				{   // value less than 1
					$( '#info_countingOnly_rows' ).show();
					somethingWrong = true;
				}
			}
		}
		//check cols
		if( $('input[name="cols"]').val() == "" )
		{
			//empty
			$(  errShow_pre + 'cols' ).show();
			somethingWrong = true;
		}else{
			if( isInt( $('input[name="cols"]').val() ) === false ) 
			{	// not an int
				$( errShowInvalid_pre + 'cols' ).show();
				somethingWrong = true;
			}else{
				if( parseInt( $('input[name="cols"]').val(), 10 ) < 1 ) 
				{   // value less than 1
					$( '#info_countingOnly_cols' ).show();
					somethingWrong = true;
				}
			}
		}
		if( somethingWrong ) return false;
		document.forms[0].submit();
	});

})