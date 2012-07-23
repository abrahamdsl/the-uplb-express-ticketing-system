/**
*	Air Traffic JavaScript 
* 	Created 09JUL2012-1700
*	Revised 21JUL2012-2219
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	Handles form submission in connection and client-side user redirection in line with our "Air Traffic" principle.
*	@dependencies nextGenModal, JQuery, CI.base_url should be declared/loaded. before this is loaded.
**/
function cannotFindServer(){
	$.fn.nextGenModal({ msgType: 'error', title: "can't find server", message: 'Did you just lose your Internet connection?' });
}
	
(function($){
	// the settings of this air traffic instance
	var config;

	//	Our constructor function
	$.fn.airtraffic = function( settings ){
		config = null;
		config = $.fn.airtraffic.defaults;
		
		if (settings) config = $.extend($.fn.airtraffic.defaults, settings);
		var x = $.ajax({
			type: 'POST',
			// if cconfig.url_x is not supplied during construction, just get from the first form
			url: CI.base_url + (  ( config.url_x == '' ) ? $('form').first().attr('action') : config.url_x ),
			timeout: config.timeout,
			// if config.ser_data is not supplied during construction, just get from the first form
			data: ( config.ser_data == '' ) ? $( 'form' ).first().serialize() : config.ser_data,
			beforeSend: function(){
				$.fn.nextGenModal({ msgType: 'ajax', title: config.msgtitle, message: config.msgwait });
			},
			success: function(data){
				var earlycall = data;
				if( $(data).find('type').text() != "okay" ){
					if( config.atc_fail_func != '' ){
						window[ config.atc_fail_func ]( data );
						if( config.atc_ff_mode == 0 ) return false;
					}
					$.fn.makeOverlayForResponse( data );
					return false;
				}
				var x_inner = $.ajax({
					type: 'POST',
					url: CI.base_url + 'sessionctrl/contact_tower',
					data: 'request=1',
					timeout: config.timeout,
					beforeSend: function(){
						$.fn.nextGenModal({
						   msgType: 'ajax',
						   title: 'verifying...',
						   message: 'Getting clearance for takeoff, please wait...'
						});
					},
					success: function(data){
						// call the "success" function
						if( config.atc_success_func != '' ){
							window[ config.atc_success_func ]( data );
							if( config.atc_sf_mode == 0 ) return false;
						}
						$.fn.makeOverlayForResponse( data );
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
	};//constructor

	$.fn.airtraffic.defaults = {
		// Function to call when upon successful ACK of clearance to proceed.
		atc_success_func : 'atc_success',
		// Title and message to be displayed in modal when submitting information.
		msgtitle: 'please wait ...',
		msgwait: 'Contacting server for your request, one moment please.',
		// URI to send ajax request!!! - Should be supplied
		url_x: '', //$('form').first().attr('action'),
		// Timeout in millisecs (JQuery rules) how long should an AJAX call last.
		timeout: 30000,
		// Serialized form data to be submitted to url_x. Default is from the first form.
		ser_data: '',
		// Function to call when the first function call's "type" tag is not equal to "okay"
		atc_fail_func: '',
		/*
			INT. How to deal with atc_fail_func/atc_success_func
				0 - call the specified func and terminate
				1 - call the specified func but continue execution of the ajax's success func.
			   * Value doesn't matter if atc_fail_func/atc_success_func is not set or blank.
		*/
		atc_ff_mode: 1,
		atc_sf_mode: 1
	};
})(jQuery);