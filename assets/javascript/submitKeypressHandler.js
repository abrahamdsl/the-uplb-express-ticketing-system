/*
	created 28 NOV 2011 1535
*/
$(document).ready(function() {
	$("input[name='username']").keypress(function(event) {
		if( event.which == 13){
			$("input[name='password']").focus();
		}
	});
	$("input[name='password']").keypress(function(event) {
		if( event.which == 13) $("#buttonOK").click();	// 13 corresponds to ENTER key	 		
	});
});