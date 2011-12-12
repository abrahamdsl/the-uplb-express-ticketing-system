$(document).ready(function()
	{
		$('#buttonOK').click( function(){
			var decision;
			
			decision = confirm("Are you sure you have selected the appropriate showings?");
			if( !decision ) return;

			document.forms[0].submit();
		}); //buttonOK click
		
		$('input[class="selectDeselectBtns"]').click( function(){			
			var thisButtonId = $(this).attr('id');
			var thisButtonCaption = $(this).val();
			var dateConcerned = thisButtonId.split('__')[1];	//index 1 is always referenced
								
			if( thisButtonCaption == "Check all" ){
				$('input[id^="ch_'+dateConcerned+'"]').attr('checked', true);						
			}else{
				$('input[id^="ch_'+dateConcerned+'"]').attr('checked', false);						
			}
		});
	}
);