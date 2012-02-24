function formSubmit()
{
	window.location = CI.base_url;
}


$(document).ready( function(){			
	$(document).bookConclusionOnloadRitual();
	
	$('#buttonOK').click( function(){		
		$.fn.nextGenModal({
		   msgType: 'warning',
		   title: 'are all ok now?',
		   message: 'Are you sure you want to leave this page now?',
		   yesFunctionCall: 'formSubmit'	   
		});
		
	});
});