$(document).ready( function(){
	$('#buttonReset').click( function(){
		window.history.back();
	});
	
	$('#buttonOK').click( function(){
		window.location = CI.base_url;
	});		
});