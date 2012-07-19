function formSubmit( args )
{
	args[0].children('form').submit();
}

function deleteBookingX( args )
{	
	var x = $.ajax({
		type: 'POST',
		url: CI.base_url + 'eventctrl/cancelBooking',
		timeout: 30000,	
		data: { 'bookingNumber' : args[0] },
		beforeSend: function(){	
			$.fn.nextGenModal({
				   msgType: 'ajax',
				   title: 'processing',
				   message: 'Cancelling your booking...'
				});	
			setTimeout( function(){}, 1000 );
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
				$('div#proper_' + args[0] ).remove();
				if( $('div#accordion h3').size() < 2 ){
					$('h3#h_nobooking').show();
					$('div#d_nobooking').show();
				}
				$('div#accordion h3').first().click();
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
	$.fn.disploading = function(){
		$.fn.nextGenModal({
		   msgType: 'ajax',
		   title: 'processing',
		   message: 'Taking you to the next page, please wait...'
		});
	};

	$('div#accordion2').accordion();
	if( $('div#accordion').children("div.ui-accordion-content").size() == 1 ) $('[id$="nobooking"]').show();
	
	$('div.metrotile').click( function(e){
		var thisID = $(this).attr( 'id' );
		var bNumber;
		
		if( thisID.startsWith( 'purchaseticket' ) ) return true;
		e.preventDefault();
		if( thisID.startsWith( "cancelbooking" ) )
		{
			bNumber = $(this).siblings('input[name="bookingNumber"]').first().val();
			$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'Confirm',
				   message: 'Are you sure you want to delete this booking?',
				   closeOnChoose: false,
				   yesFunctionCall: 'deleteBookingX',
				   yFC_args: new Array( bNumber )
			});
		}else{
			$.fn.disploading();
			var gothere = $(this).children("a").attr("href");
			setTimeout(  "location.href='" + gothere + "'", 500 );
		}
	});
});