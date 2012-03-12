/*
	Created 08MAR2012-2245. Moved from bookStep5.js
*/

$(document).ready( function(){
	$('select[name="paymentChannel"]').change( function(){
		$( 'div#pc' + $('#lastPChannel').val() + '_details' ).hide();
		if( $(this).val() == "NULL" ) return false;
		
		$( 'div#pc' + $(this).val() + '_details' ).show();				
		$('#lastPChannel').val(  $(this).val() );
	});
});