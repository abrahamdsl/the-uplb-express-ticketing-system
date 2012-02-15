

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

	$('select[name="paymentChannel"]').change( function(){
		$( 'div#pc' + $('#lastPChannel').val() + '_details' ).hide();
		if( $(this).val() == "NULL" ) return false;
		
		$( 'div#pc' + $(this).val() + '_details' ).show();				
		$('#lastPChannel').val(  $(this).val() );
	});
	
	$('a#buttonOK').click( function(){
		if( $('select[name="paymentChannel"] option:selected').val() == "NULL" ){
			displayOverlay('error','input needed','Please select payment method first.');
			return false;
		}
		document.forms[0].submit();
	});
});