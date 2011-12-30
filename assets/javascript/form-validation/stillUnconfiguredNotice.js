$(document).ready( function(){
	$('#buttonOK').click( function(){
		//document.forms[0].submit();
		$('#formMain').submit();
	});
	
	$('#buttonReset').click( function(){
		var decision;
		
		decision = confirm( "Are you sure you don't want to configure the other showing times now?" );
		if( !decision ) return false;
		
		//window.location.replace("/"); // only directs to 'www root', therefore inconvenient to change when working on live and development
		$('#formReturnHome').submit();	// used this instead because the above is not suitable for development while in WAMP i.e. localhost/species
	});
	
});