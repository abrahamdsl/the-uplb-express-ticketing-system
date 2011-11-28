/*
	actionListener for submitting the form
*/
$(document).ready(function()
	{
		/*
			ask for some form submit confirmation here
		*/
	
		$("#buttonOK").click(function() {			
			//alert( $('input[name="username"]').val() );			
			document.forms[0].submit();			
		});
	}
);