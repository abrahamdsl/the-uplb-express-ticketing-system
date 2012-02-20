$(document).ready( function(){
	
	$('#buttonOK').click( function(){	
		if( $('input[name="selectThisClass"]:checked').size() < 1 )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'are you kidding me?',
			   message: 'Please select a ticket class to continue.'
			});
			return false;
		}
		
		document.forms[0].submit();
	});
});