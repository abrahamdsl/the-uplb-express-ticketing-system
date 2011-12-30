$(document).ready( function() {
	$('select').change( function(){
		alert( $(this).val() );
		$.post( CI.base_url + "EventCtrl/getConfiguredShowingTimes", { eventID: $(this).val() },
		   function(data) {
			 alert("Data Loaded: " + data);
		   });
	});
	
});