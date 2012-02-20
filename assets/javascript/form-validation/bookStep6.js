function formSubmit()
{
	window.location = CI.base_url;
}

$(window).unload( function() { 	
	/*
		Delete cookies relevant to booking process when user leaves the page
		(i.e., close tab/window, new address )
	*/
    var x = $.ajax({	
		type: 'POST',
		async: false,
		beforeSend: function(){
			$.fn.nextGenModal({
				msgType: 'ajax',
				title: 'please wait...',
				message: 'Deleting relevant data to your transaction...'
			});
		},
		url: CI.base_url + 'EventCtrl/postBookingCleanUp',
		timeout: 5000,		
	});	
    return true;
});

$(document).ready( function(){	
	$.fn.onLoadRitual = function(){
		$('input.seatText').show();
		$('div.pChannelDetails').show();
		$('div#tabs').show();
		$('div#paymentDeadline span#date').html(
			convertDateMonth_toText( $('input#pDead_Date').val() )
		);	
		$('div#paymentDeadline span#time').html(
			convertTimeTo12Hr( $('input#pDead_Time').val() )
		);			
	}
	
	$(document).onLoadRitual();
	
	$('#buttonOK').click( function(){		
		$.fn.nextGenModal({
		   msgType: 'warning',
		   title: 'are all ok now?',
		   message: 'Are you sure you want to leave this page now?',
		   yesFunctionCall: 'formSubmit'	   
		});
		
	});
});