/*
	actionListener for submitting the form
*/
$(document).ready(function()
	{
		/*
			ask for some form submit confirmation here
		*/
	
		$("#buttonOK").click( function() {			
			
			if( $('input[name="eventName"]').val() == "" )
			{				
				$("#NameRequired").show();
				return;
			}
			
			document.forms[0].submit();			
		});
						
		// when filling out 
		$('#id_eventName').change( function() {		
			$('#NameRequired').hide();
			
			/*$.post( 'EventCtrl/doesEventExist' ) 
				{
				  $('#id_eventName').val();
				},				
				function(data) {									
					alert(data);					
				}//function             
				
			);*/
		}); //change for #id_eventName
		
	

	}
);