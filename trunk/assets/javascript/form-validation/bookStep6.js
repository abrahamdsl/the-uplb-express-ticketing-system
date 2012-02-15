function formSubmit()
{
	window.location = CI.base_url;
}

$(document).ready( function(){	
	$.fn.onLoadRitual = function(){
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
		displayOverlay_confirm( 'confirm', 'are all ok now?', 'formSubmit', null, null, null, "Are you sure you want to leave this page now?" );
	});
});