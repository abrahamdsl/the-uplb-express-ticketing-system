function cancelBookingProcess()
{	
	var whichMsg = ( typeof buttonReset2 == 'undefined' && typeof buttonResetp == 'undefined' ) ? 'Cancelling your reserved slots ...' : 'Cleaning-up some data...';
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'processing',
	   message: whichMsg
	});
	// now, ajax-time!
	var cancelPOST = $.ajax({
		type: 'POST',
		url: CI.base_url + 'eventctrl/cancelBookingProcess',
		timeout: 10000,
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
	if( typeof doNotProcessTime == 'undefined' ) $(document).makeTimestampFriendly();
	var longmsg = 'All slots and seats we have temporarily reserved for you will be made as available again for others';
	var cb = 'Cancel booking ';
	var yfc = 'cancelBookingProcess';
	
	$('#buttonReset').click( function(){
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: cb + 'process?',
			   message: 'Are you sure you want to do this? ' + longmsg + '.',
			   yesFunctionCall: yfc,
			   closeOnChoose: false
			});
	});
	
	$('#buttonReset2').click( function(){
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: cb + 'changes?',
			   message: longmsg + ', continue?',
			   yesFunctionCall: yfc,
			   closeOnChoose: false
			});
	});
	// for payment mode
	$('#buttonResetp').click( function(){
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'confirm',
			   message: 'Are you sure you want to roll back payment arrangement for this booking?',
			   yesFunctionCall: yfc,
			   closeOnChoose: false
			});
	});
		
});