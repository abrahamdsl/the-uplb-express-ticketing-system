function atc_fail( data )
{
	// as well as displaying the error in modal, put it in the page too.
	$('div#errdiv').children('ul').first().children('li').first().html( 
		$(data).find('message').text()
	);
	$('div#errdiv').show();
}
/*
	actionListener for submitting the form
*/
$(document).ready(function()
{
	/*
		ask for some form submit confirmation here
	*/
	$("#buttonOK").click(function(e){
		$('div#errdiv').hide();
		e.preventDefault();
		return $.fn.airtraffic({
			atc_success_func: '',
			msgtitle: 'logging in ...',
			msgwait: 'Checking your credentials, please wait...',
			atc_fail_func: 'atc_fail',
		});
		$(document).blur();
	});
}
);