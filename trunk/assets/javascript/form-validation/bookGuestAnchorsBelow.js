$(document).ready( function(){		
		$(".anchor_below").click( function(){
			var clickThis = $(this).attr( 'id' ).split('-')[0];
			$( 'a#' + clickThis ).click();
		});		
})		