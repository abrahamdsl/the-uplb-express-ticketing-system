/**
*	Javascript validator for Confirm Booking Step 1.
* 	Created 22FEB2012-2147
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	actionListener for submitting the form
* 
*/
function formSubmit()
{
	var x = $.ajax({	
		type: 'POST',
		url: $( 'form#formMain' ).attr('action'),
		timeout: 30000,
		data: $( 'form#formMain' ).serialize(),
		beforeSend: function(){			
			$('span#ajaxind').show();
		},
		success: function(data){
			setTimeout( function(){}, 1000 );			
			$('span#ajaxind').hide();
			if( $( data ).find('resultstring').text() == 'BOOKING_CONFIRM_CLEARED' )
			{
				window.location = CI.base_url + 'EventCtrl/confirm_step2_forward';
			}else{
				$.fn.makeOverlayForResponse( data );
			}
			/*
			if( data === "true" ) window.location = CI.base_url + 'EventCtrl/confirm_step2_forward';
			else				
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'not found',
				   message: 'The booking number you have specified is not found in the system.'
				});						
			*/
		}
	});	

}
	
$(document).ready(function()
	{
		
		/*
			ask for some form submit confirmation here
		*/
		$( 'form#formMain' ).submit( function(e){
			e.preventDefault();
			$("#buttonOK").click();
		});
	
		$("#buttonOK").click( function() {			
			var bNumberHandle = $('input[name="bookingNumber"]');			
			var allOK = 0;
			
			if( bNumberHandle.val() == "" )
			{				
				bNumberHandle.parent().siblings("span.NameRequired").show();
				allOK++;				
			}					
			if( allOK < 1 ) formSubmit();			
		});
						
		// when filling out 
		$('input[name="bookingNumber"]').change( function() {		
			$(this).parent().siblings('span.NameRequired').hide();
		}); 				
	}
);