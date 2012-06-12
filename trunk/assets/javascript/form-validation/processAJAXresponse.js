/*
	Requires that nextGenModal is declared earlier!
*/

$(document).ready( function(){
	$.fn.makeOverlayForResponse = function( xmlText ){
		var s_type = $(xmlText).find('type').text();
		var s_title = $(xmlText).find('title').text();
		var s_message = $(xmlText).find('message').text();
		var s_redirect = ($(xmlText).find('redirect').size() == 1) ? $(xmlText).find('redirect').text() : "";
		$.fn.nextGenModal({
				msgType: s_type,
				title: s_title,
				message: s_message
		});
		if(  s_redirect != "" )
		{
			setTimeout( "location.href='" + s_redirect + "'", parseInt( $(xmlText).find('redirect_after').text(),10) );
		}
	};
		

});