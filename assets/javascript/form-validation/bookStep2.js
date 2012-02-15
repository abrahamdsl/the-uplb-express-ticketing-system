$(document).ready( function(){
	
	$('#buttonOK').click( function(){	
		if( $('input[name="selectThisClass"]:checked').size() < 1 )
		{
			displayOverlay( 'error' , 'are you kidding me?', "Please select a ticket class to continue." );				
			return false;
		}
		
		document.forms[0].submit();
	});
});