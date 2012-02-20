function cancelBookingProcess()
{	
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'processing',
	   message: 'Cancelling your reserved slots ...'
	});
	// now, ajax-time!
	var cancelPOST = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'EventCtrl/book_step3_cancel',
		timeout: 10000,
		data:  null,
		success: function(data){				
			window.location = CI.base_url;
		}
	});	
	cancelPOST.fail(
		function(jqXHR, textStatus) { 
			var message = 'It seems you have lost your internet connection. Please try again.<br/><br/>';
			message += "You can also leave this page manually but the other users would have to wait for at least";
			message += " 20 minutes for your slot to be freed. Kaya ba ng kunsensya mo yun? ;-)";			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'Connection timeout',
			   message: message + '<br/><br/><br/>' + textStatus,
			});
			return false;
			
		}
	);
}

$(document).ready( function(){

	$.fn.onLoadRitual = function(){
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
	
	$(document).onLoadRitual();
	
	$('#buttonReset').click( function(){			
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'Cancel booking process?',
			   message: 'Are you sure you want to do this? All slots we have temporarily reserved will be made as available again for others.',
			   yesFunctionCall: 'cancelBookingProcess'
			});
	});
		
});