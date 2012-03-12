

$(document).ready( function(){
	$('input.seatText').show();

	$('span#toggleGuestDetails').click( function(){
		if( isElementNotVisible( 'div#tabs'  ) )
		{
			$('div#tabs').show();
			$(this).html( '(Hide)' );
		}else{
			$('div#tabs').hide();
			$(this).html( '(Show)' );
		}	
	});

	
	$('a#buttonOK').click( function(){
		if( $('select[name="paymentChannel"] option:selected').val() == "NULL" ){			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'input needed',
			   message: 'Please select payment method first.'
			});
			return false;
		}
		document.forms[0].submit();
	});
});