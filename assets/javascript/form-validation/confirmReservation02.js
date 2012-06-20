var confirmed = false;

function goHome(){
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait',
	   message: 'Taking you to the home page...'
	});	
	setTimeout( "location.href='" + CI.base_url + "'", 1200 );
}

function formSubmit()
{
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + 'EventCtrl/confirm_step3',
		timeout: 30000,		
		beforeSend: function(){			
			$.fn.nextGenModal({
				   msgType: 'ajax',
				   title: 'processing',
				   message: 'Confirming reservation...'
				});						
		},
		success: function(data){
			var response = data.split('|');
			setTimeout( function(){}, 1000 );			
			if( response[0].startsWith( 'OK' ) )
			{
				$.fn.nextGenModal({
					   msgType: 'okay',
					   title: 'success',
					   message: 'This booking is now confirmed.'
				});
				$('div#paymentDeadline').html( 'CONFIRMED' );
				$('div#deadlineCaption').html( 'Congratulations. Enjoy the show.' );
				confirmed = true;
				$('a#buttonOK').remove();
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
	$(document).makeTimestampFriendly();
	$(document).bookConclusionOnloadRitual();
	
	$('a#buttonOK').click( function(){		
		$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'confirm',
				   message: 'This will confirm payment of ' + $('table#total td#value_proper span.cost').html() + ' for this booking.<br/></br>Continue?',
				   yesFunctionCall: 'formSubmit',
				   closeOnChoose: false
				});	
	});
	
	$('a#buttonReset').click( function(){		
		if( !confirmed )
		{
			$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'leaving already?',
				   message: 'Are you sure you want to leave this page without confirming this reservation?',
				   yesFunctionCall: 'goHome',
				   closeOnChoose: false
			});
		}else{
			goHome();
		}
	});
});