$(document).ready( function(){
	$.fn.bookConclusionOnloadRitual = function(){
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
	
});