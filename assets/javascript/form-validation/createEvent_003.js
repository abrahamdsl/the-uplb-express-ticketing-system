var confirmOnClick = true;

function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

$(document).ready(function()
	{
		/*
			If there is only one showing time specified, choose
			it automatically and proceed to the next page.
		*/
		if( $('input[type="checkbox"][id^="ch_d"]').size() == 1 )
		{			
			$('input[type="checkbox"][id^="ch_d"]').attr( 'checked', 'checked' );
			confirmOnClick = false;			
			$.fn.nextGenModal({
			   msgType: 'okay',
			   title: 'processing',
			   message:  "Taking you automatically to the next page...",
			});
			setTimeout( function(){ $('a#buttonOK').click(); }, 500 );
		}
	
		$('#buttonOK').click( function(){			
			if( confirmOnClick )
			{			
				$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'Confirm',
				   message:  "Are you sure you have selected the appropriate showings?",
				   yesFunctionCall: 'formSubmit'
				});
			}else{				
				formSubmit();
			}
		}); //buttonOK click
		
		$('input[class="selectDeselectBtns"]').click( function(){			
			var thisButtonId = $(this).attr('id');
			var thisButtonCaption = $(this).val();
			var dateConcerned = thisButtonId.split('__')[1];	//index 1 is always referenced
								
			if( thisButtonCaption == "Check all" ){
				$('input[id^="ch_'+dateConcerned+'"]').attr('checked', true);						
			}else{
				$('input[id^="ch_'+dateConcerned+'"]').attr('checked', false);						
			}
		});
	}
);