$(document).ready( function(){
	$.fn.makeTimestampFriendly = function(){
		$('div.start span.contentproper_time').html(
			convertTimeTo12Hr( $('input#startTime').val() )
		);	
		$('div.start span.contentproper_date').html(
			convertDateMonth_toText( $('input#startDate').val() ) + ' ' + getDayInTextOfDate($('input#startDate').val())
		);
		$('div.end span.contentproper_time').html(
			convertTimeTo12Hr( $('input#endTime').val() )
		);
		var theValue = $('input#endDate').val();
		if( theValue === $('input#startDate').val() ) return false;
		$('div.end span.contentproper_date').html(		
			convertDateMonth_toText( theValue ) + ' ' + getDayInTextOfDate( theValue )
		);
	}

});