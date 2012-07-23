$(document).ready( function(){
		if( $.browser.msie )
		{
			var versionTokenized = $.browser.version.split('.');
			if( parseInt( versionTokenized[0], 10 ) < 9 )
			{
				alert("OMG!!! You are using Internet Explorer. What's worse, it's earlier than version 9.\n\n\nWell, please expect this web application to be buggy while using this browser. \n\nPlease use other recent browsers as soon as possible.");
			}
		}		
		});