/**
*	Javascript validator for Create Event Step 1.
* 	Created 17FEB2012-1254
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	actionListener for submitting the form
* 
*/	
$(document).ready(function()
	{
	
		/*
			ask for some form submit confirmation here
		*/
	
		$("#buttonOK").click( function() {			
			var eventNameHandle = $('input[name="eventName"]');
			var locationHandle =  $('input[name="location"]');
			var allOK = 0;
			
			if( eventNameHandle.val() == "" )
			{				
				eventNameHandle.parent().siblings("span.NameRequired").show();
				allOK++;				
			}
			
			if( locationHandle.val() == "" )
			{				
				locationHandle.parent().siblings("span.NameRequired").show();
				allOK++;				
			}			
			if( allOK < 1 ) document.forms[0].submit();			
		});
						
		// when filling out 
		$('input[name="eventName"]').change( function() {		
			$(this).parent().siblings('span.NameRequired').hide();
		}); 
		
		$('input[name="location"]').change( function() {		
			$(this).parent().siblings('span.NameRequired').hide();
		});
	

	}
);