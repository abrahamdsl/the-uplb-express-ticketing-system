/**
*	@created <March 2012>
*	@revised 30JUL2012-1704
*/
$(document).ready( function(){
		$("input[name='oldPassword']").bind( 'change blur', function() {
			var x = document.getElementById("oldPasswordFldMsg");
			var func_result = isPassword_valid( own_trim(
				$(this).attr("value") ) 
			);
			updateFldMsg( $(this).attr("name"), func_result, false );
		});
		$('a#buttonReset_special').click( function(){
			document.forms[0].reset();
			$('span[id$="FldMsg"]').html('');
		});
		$('a#buttonOK_special').click( function(e){
				var areAllOK = true;
				var array_of_Validators = $('input[name$="_validate"]');
				var validatorsQuantity = array_of_Validators.length;
				var x;
				var uplbConstituency;
				var strTemp;

				e.preventDefault();
				for( x = 0; x < validatorsQuantity; x++ )
				{
					if( array_of_Validators[x].value == "0" ) 
					{
						if( $('input[name="' + array_of_Validators[x].name + '"]').isFieldRequired() )
						{
							var inputNameValidityIndicatorLen = array_of_Validators[x].name.length;
							var inputName = array_of_Validators[x].name.substring( 0, inputNameValidityIndicatorLen- 9 );
							$( 'input[name="' + inputName + '"]' ).change();
							areAllOK = false;
						}
					}
				}//for
				if( areAllOK )
				{
					$.fn.airtraffic_v2({
						atc_success_func: '',
						msgwait: 'Changing your password, one moment please.',
						timeout: 15000
					});
				}else{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'error',
					   message: 'There are still invalid entries in the form. Please review.'
					});
					return false;
				}
			});
});//$(document)..