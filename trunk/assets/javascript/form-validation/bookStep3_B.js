var doNotProcessTime = true; // to stop processing of $(document).makeTimestampFriendly(); in bookStepsCommon.js, used in the same page.


function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

$(document).ready(function(){
		$('input[type="button"][class="selectDeselectBtns"]').click( function(){
				var letter = $(this).siblings('input.letter').val();
				if( $(this).attr('id').startsWith('check') )
				{
					$( 'input[type="checkbox"][name^="' + letter + '"]' ).attr('checked', 'checked');
				}else{
					$( 'input[type="checkbox"][name^="' + letter + '"]' ).removeAttr( 'checked' );
				}
				console.log( 'input[type="checkbox"][name^="' + letter + '"]' );
		});

		$('#buttonOK').click( function(){			
			var isAtLeastOneSelected = ( $( 'input[type="checkbox"]:checked').size() > 0 );
			if( isAtLeastOneSelected )
			{		
				formSubmit();				
			}else{				
				$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'Confirm',
				   message:  "Are you sure you don't want to select any class?",
				   yesFunctionCall: 'formSubmit'
				});
			}
		}); //buttonOK click
});