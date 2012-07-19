function go_submit( atc_success_func, msgtitle, msgwait, url_x, timeout, ser_data, atc_fail_func )
{
	/**
	*	@created 09JUL2012-1700
	*	@description Air Traffic JavaScript - handles form submission in connection and client-side
			user redirection in line with our "Air Traffic" principle.
	*	@param atc_success_func - Function to call when upon successful ACK of clearance to proceed.
	*	@param msgtitle, msgwait - Title and message to be displayed in modal when submitting information.
	*	@param url_x - Where to submit information
	*	@param timeout - Timeout in millisecs (JQuery rules) how long should an AJAX call last.
	*	@param ser_data - Serialized form data to be submitted to url_x
	*	@param atc_fail_func - Function to call when the first function call's "type" tag is not equal to "okay"
	*	@dependencies nextGenModal, JQuery, CI.base_url should be declared/loaded. before this is loaded.
	**/
	var x = $.ajax({
		type: 'POST',
		url: CI.base_url + url_x,
		timeout: timeout,
		data: ser_data,
		beforeSend: function(){
			$.fn.nextGenModal({ msgType: 'ajax', title: msgtitle, message: msgwait });
		},
		success: function(data){
			var earlycall = data;
			if( $(data).find('type').text() != "okay" ){
				if( atc_fail_func == '' ){
					$.fn.makeOverlayForResponse( data );
				}else{
					window[ atc_fail_func ]( data );
				}
				return false;
			}
			var x_inner = $.ajax({
				type: 'POST',
				url: CI.base_url + 'sessionctrl/contact_tower',
				data: 'request=1',
				timeout: timeout,
				beforeSend: function(){
					$.fn.nextGenModal({
					   msgType: 'ajax',
					   title: 'verifying...',
					   message: 'Getting clearance for takeoff, please wait...'
					});
				},
				success: function(data){
					// call the "success" function
					window[ atc_success_func ]( data );
				}
			});
			x_inner.fail( function(jqXHR, textStatus){
				if( jqXHR.status == 0 ){
					cannotFindServer();
				}else{
					$.fn.makeOverlayForResponse( jqXHR.responseText );
				}
				return false;
			});
		}
	});
	x.fail(	function(jqXHR, textStatus){
		if( jqXHR.status == 0 ){
			cannotFindServer();
		}else{
			$.fn.makeOverlayForResponse( jqXHR.responseText );
		}
		return false;
	});
}

function cannotFindServer(){
	$.fn.nextGenModal({ msgType: 'error', title: "can't find server", message: 'Did you just lose your Internet connection?' });
}