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