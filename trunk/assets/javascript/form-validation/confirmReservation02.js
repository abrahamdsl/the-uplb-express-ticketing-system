var confirmed = false;

function goHome(){
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait',
	   message: 'Taking you to the home page...'
	});	
	setTimeout( "location.href='" + CI.base_url + "'", 600 );
}

function atc_success( data ){
	if( $(data).find('type').text() == "okay" ){
		$('div#paymentDeadline').html( 'CONFIRMED' );
		$('div#deadlineCaption').html( 'Congratulations. Enjoy the show.' );
		confirmed = true;
		$('a#buttonOK').remove();
	}
	$.fn.makeOverlayForResponse( data );
}

function formSubmit()
{
	go_submit(
		'atc_success', 
		'please wait...',
		'Confirming reservation, one moment please.',
		'eventctrl/confirm_step3',
		30000,
		'',
		''
	);
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