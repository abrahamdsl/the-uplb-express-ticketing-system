/*
	Requires that nextGenModal is declared earlier!
*/
$(document).ready( function(){
	$.fn.makeOverlayForResponse = function( xmlText ){
		var s_title = $(xmlText).find('mtitle').text();
		var s_type = $(xmlText).find('type').text();
		var s_message = $(xmlText).find('message').text();
		var s_redirect = ($(xmlText).find('redirect').size() == 1) ? decodeURIComponent($(xmlText).find('redirect').text()) : "";
		var shouldProceed = (s_type == "okay" &&  $(xmlText).find('resultstring').text() == "PROCEED");
		$.fn.nextGenModal({
				msgType: shouldProceed ? "ajax" : s_type,
				title: s_title,
				message: shouldProceed ? "Redirecting you to the next page, please wait.." : s_message
		});
		if(  s_redirect != "" )
		{
			setTimeout( "location.href='" + s_redirect + "'", parseInt( $(xmlText).find('redirect_after').text(),10) );
		}
	};
});