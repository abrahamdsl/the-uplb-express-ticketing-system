/*
	@created 28NOV2011-1535
	@revised 21JUL2012-1135
	// 13 corresponds to ENTER key
*/
$(document).ready(function() {
	$("input[name='username']").keypress(function(event){
		if( event.which == 13){
			$("input[name='password']").focus();
		}
	});
	$("input[name='password']").keypress(function(event){
		if( !$.fn.nextGenModal.isVisible() && event.which == 13){
			event.preventDefault();
			$("input[name='username']").focus();
			return $("#buttonOK").click();
		}
	});
});