function formSubmit( args )
{
	args[0].children('form').submit();
}

function deleteBookingX( args )
{	
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'EventCtrl/cancelBooking',
		timeout: 30000,	
		data: { 'bookingNumber' : args[0] },
		beforeSend: function(){			
			$.fn.nextGenModal({
				   msgType: 'ajax',
				   title: 'processing',
				   message: 'Cancelling your booking...'
				});	
			setTimeout( function(){}, 2000 );			
		},
		success: function(data){
			var response = data.split('_');
			setTimeout( function(){}, 1000 );			
			if( response[0].startsWith( 'OK' ) )
			{
				$.fn.nextGenModal({
					   msgType: 'okay',
					   title: 'success',
					   message: 'This booking is now cancelled. You may seek refund (if any) from the payment channel you used.'
				});								
				$('h3#h_' + args[0] ).remove();
				$('div#' + args[0] ).remove();
				if( $('div#accordion h3').size() > 0 )
				{
					$('div#accordion h3').first().click();
				}else{
					$('div#accordion2').show();
				}				
			}else
			if( response[0].startsWith( 'ERROR' ) )
			{
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'error',
				   message: 'Confirmation was not processed. The server sent the following response:<br/><br/> ' + response[1]
				});
			}
		},
		error: function(jqXHR, textStatus, errorThrown){ 				
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'Something went wrong',
				   message: 'It seems you have lost your internet connection. Please try again.<br/><br/><br/>' + errorThrown
				});
				return false;
		}
	});	
}

$(document).ready( function(){
	$('div#accordion2').accordion();
	if( $('div#accordion h3').size() != 0 ) $('div#accordion2').hide();
	
	$('div.metrotile').click( function(e){
		var thisID = $(this).attr( 'name' );
		var bNumber;
		
		if( $(this).attr( 'id' ) === 'purchaseticket' ) return true;
		e.preventDefault();
		bNumber = $(this).siblings('input[name="bookingNumber"]').first().val();
		if( thisID == "cancel" )
		{
			$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'Confirm',
				   message: 'Are you sure you want to delete this booking?',
				   closeOnChoose: false,
				   yesFunctionCall: 'deleteBookingX',
				   yFC_args: new Array( bNumber )
			});
		}else{
			$(this).children('form').first().submit();
		}
		/*if( thisID == "changeseat" )
		{
			$(this).children('form').first().submit();
		}else
		if( thisID == "changeshowingtime" )
		{
			$(this).children('form').first().submit();
		}*/
		
	});
});