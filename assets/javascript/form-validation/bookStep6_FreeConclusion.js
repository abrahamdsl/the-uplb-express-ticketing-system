function formSubmit()
{
	$.fn.nextGenModal({
		   msgType: 'ajax',
		   title: 'please wait...',
		   message: 'Taking you to the homepage...'
		});
	setTimeout( "location.href='" + CI.base_url + "'", 1200 );
}


$(document).ready( function(){				
	$.fn.makeTimestampFriendly();
	$(document).bookConclusionOnloadRitual();
	
	$('#buttonOK').click( function(){		
		$.fn.nextGenModal({
		   msgType: 'warning',
		   title: 'are all ok now?',
		   message: 'Are you sure you want to leave this page now?',
		   yesFunctionCall: 'formSubmit',
		   closeOnChoose: false
		});
		
	});
});