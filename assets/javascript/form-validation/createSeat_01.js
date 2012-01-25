$(document).ready( function(){
	var errShow_pre = "#info_req_";

	$('input').focus( function(){		
		//alert( $( errShow_pre + $(this).attr( 'name' ) ).content() );
		$( errShow_pre + $(this).attr( 'name' ) ).hide();
	});

	$('#buttonOK').click( function(){		
		var somethingWrong = false;
		
		//check name		
		if( $('input[name="name"]').val() == "" )
		{		
			$(  errShow_pre + 'name' ).show();
			somethingWrong = true;
		}		
		//check rows
		if( $('input[name="rows"]').val() == "" )
		{		
			$(  errShow_pre + 'rows' ).show();
			somethingWrong = true;
		}
		//check cols
		if( $('input[name="cols"]').val() == "" )
		{		
			$(  errShow_pre + 'cols' ).show();
			somethingWrong = true;
		}
		if( somethingWrong ) return false;
		document.forms[0].submit();
	});

})