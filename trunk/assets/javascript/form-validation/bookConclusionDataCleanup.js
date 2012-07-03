$(window).unload( function() { 	
	/*
		Delete cookies relevant to booking process when user leaves the page
		(i.e., close tab/window, new address )
	*/
	var _dev_test_proceed = true;	// for debugging purposes - set to false in google chrome's console
	
	if( _dev_test_proceed === false ) return false;
	
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
		url: CI.base_url + 'eventctrl/postBookingCleanUp',
		timeout: 5000,		
	});	
    return true;
});