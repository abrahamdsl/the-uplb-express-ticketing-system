function formSubmit()
	{
		$.fn.airtraffic_v2({
			atc_success_func: '',
			msgwait: 'Saving role changes, one moment please.',
			timeout: 15000
		});
	}//formSubmit(..)
	
$(document).ready( function(){
	$('div.metrotile').children('input[type="hidden"]').each( function(){
		// assigns colors to the role tiles on page load
		if( $(this).val() == "0" )
		{
			$(this).parent('div.metrotile').addClass('inactive');
		}else{
			$(this).parent('div.metrotile').addClass('active');
		}
	});
	$( 'form#formMain' ).submit( function(e){
		e.preventDefault();
		$("#buttonOK").click();
	});
	$('div.metrotile').click( function(e){
		e.preventDefault();
		var hiddenInputHandle = $(this).children('input[type="hidden"]').first();

		if( hiddenInputHandle.val() == 0 )
		{
			$(this).removeClass('inactive');
			$(this).addClass('active');
			hiddenInputHandle.val( 1 );
		}else{
			$(this).removeClass('active');
			$(this).addClass('inactive');
			hiddenInputHandle.val( 0 );
		}
	});
	$('a#buttonReset').click( function(){
		window.location = CI.base_url + 'useracctctrl/manageUser_step2';
	});
	$('a#buttonOK').click( function(){
		formSubmit();
	});
});