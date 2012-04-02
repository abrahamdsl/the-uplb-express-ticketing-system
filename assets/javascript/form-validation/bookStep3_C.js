
function formSubmit()
{
	// created 7JAN2012-1547
	document.forms[0].submit();			
	
	return [ this ];
}

	$(document).ready(function(){
		$('input[type="button"][class="selectDeselectBtns"]').click( function(){
				var guestUUID = $(this).siblings('input.guestUUID').val();
				if( $(this).attr('id').startsWith('check') )
				{
					$( 'input[type="checkbox"][name^="' + guestUUID + '"]' ).attr('checked', 'checked');
				}else{
					$( 'input[type="checkbox"][name^="' + guestUUID + '"]' ).removeAttr( 'checked' );
				}
		});
		
		$('#buttonOK').click( function(e){
			var isAtLeastOneSelected = ( $( 'input[type="checkbox"]:checked').size() > 0 );
			e.preventDefault();
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