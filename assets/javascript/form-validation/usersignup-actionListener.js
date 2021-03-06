/**
*	@created 27NOV2011-1120
*	@revised 30JUL2012-1407
*/
$(document).ready(function()
	{
		// actionListener for username
		$("input[name='username']").change(function(){
		  var x = document.getElementById("usernameFldMsg");
		  var func_result = isUsername_valid( own_trim(
				$(this).attr("value") ) );
		  updateFldMsg( $(this).attr("name"), func_result, false );
		});
		// actionListener for password
		$("input[name='password']").change(function(){
			  var x = document.getElementById("passwordFldMsg");
			  var func_result = isPassword_valid( own_trim(
					$(this).attr("value") ) 
			  );
			 if( updateFldMsg( $(this).attr("name"), func_result, false ) == "OK" )
				checkPasswordDifference_and_Act("password");
		});
		//end actionListener for password
		// actionListener for password confirmation
		$("input[name='confirmPassword']")
			.change(function() {
			  var x = document.getElementById("confirmPasswordFldMsg");
			  var func_result = isConfirmPassword_valid( own_trim(
					$(this).attr("value") ) );
			  if( updateFldMsg( $(this).attr("name"), func_result, false ) == "OK" )
				checkPasswordDifference_and_Act("confirm");
			})
			.bind( 'keyup', function(e){
				e.preventDefault();
				var idx = 'a[id^="buttonOK"]';
				switch( e.which ){
					case 9: $(idx).focus(); break;
					case 13: $(idx).click(); break;
				}
			});
		//end actionListener for password confirmation
		/*
			-actionListener for selecting gender
			Basically, removes the "Please select gender" at the FldMsg beside it
		*/
		$("input[name='gender']").change(function() {
				updateFldMsg( $(this).attr("name"), "OK", false );
		} );
		//end of actionListener for selecting gender
		/*
			actionListener for changing of UPLB constituency
		*/
		$("input[name='uplbConstituentBoolean']").change(function() {
				var x = document.getElementsByName('uplbConstituentBoolean')[0];		// gets and assign a handle to the checkbox indicating UPLB constituency
				var studentnumField = document.getElementsByName("studentNumber")[0];	// like above, handle too
				var employeenumField = document.getElementsByName("employeeNumber")[0];	// like above, handle too
				var studentnumFldMsg;
				var employeenumFldMsg;
				var studentNumValidate = document.getElementsByName("studentNumber_validate")[0];
				var employeeNumValidate = document.getElementsByName("employeeNumber_validate")[0];
				studentNumValidate.value = -2;
					employeeNumValidate.value = -2;
				if( x.checked ) // if checkbox is not checked then suddenly selected, enables the fields and removes the "disabled" content
				{
					studentnumField.disabled = "";
					employeenumField.disabled = "";
					studentnumField.value = "";
					employeenumField.value = "";
					
				}else{
					/* disables checkbox again and the "disabled" text is displayed at the field to help 
						those using IE ( no visual indication input field is disabled, only cannot enter, unlike other browsers)
					*/
					studentnumField.value = "disabled";
					employeenumField.value = "disabled";
					studentnumField.disabled = !x.checked;
					employeenumField.disabled = !x.checked;
					/* gets and assigns handle to the "Field Messages" for the student number and employee:
						since we disable them earlier, we don't have the reason to let any error messages remain
					*/
					studentnumFldMsg = document.getElementById("studentNumberFldMsg");
					employeenumFldMsg = document.getElementById("employeeNumberFldMsg");
					studentnumFldMsg.innerHTML = "";
					employeenumFldMsg.innerHTML = "";
				}
		} );
		//end of UPLB constituency change detection JS
		/*
			actionListener for accepting if names are acceptable      
		*/
		$("input[name$='Name']").change(function() {                                            
					var func_result;

					func_result = isName_valid( own_trim(
							$(this).attr("value") ) );

					updateFldMsg( $(this).attr("name"), func_result, false );                       
		});
		//end of accepting if names are acceptable JS
		/*
			actionListener for accepting if cellphone number is acceptable
		*/
		$("input[name='cellPhone']").change(function() {
				var func_result;

				func_result = isPhone_valid( own_trim(
					$(this).attr("value") ), "CELL" );

				updateFldMsg( $(this).attr("name"), func_result, false );
		});
		//end of cellphone number acceptability detection JS
		/*
			actionListener for accepting if landline number is acceptable
		*/
		$("input[name='landline']").change(function() {
			var func_result;

			func_result = isPhone_valid( own_trim(
				$(this).attr("value") ), "LANDLINE" );

			updateFldMsg( $(this).attr("name"), func_result, false );
		});
		//end of landline number acceptability detection JS
		/*
			actionListener for accepting if email address is acceptable
		*/
		$("input[name='email_01_']").change(function() {
			var func_result;

			func_result = isEmail_valid( own_trim(
				$(this).attr("value") ) );

			updateFldMsg( $(this).attr("name"), func_result, false );
		});
		//end of email address acceptability detection JS
		/*
			actionListener for addresses, actually, does nothing
			only adds "OK". I do not intend to check addresses now
		*/
		$("input[name$='_addr']").change(function() {
			var func_result;
			updateFldMsg( $(this).attr("name"), "OK", false );
		});
		/*
			actionListener for accepting if a UPLB student number is acceptable
		*/
		$("input[name='studentNumber']").bind( 'change blur', function() {
			var func_result;

			func_result = isStudentNumber_valid( own_trim(
				$(this).attr("value") ) );

			updateFldMsg( $(this).attr("name"), func_result, false);
		});
		//end of UPLB student number is acceptability detection JS
		/*
			actionListener for accepting if a UPLB employee number is acceptable
		*/
		$("input[name='employeeNumber']").bind( 'change blur', function() {
			var func_result;

			func_result = isEmployeeNumber_valid( own_trim(
				$(this).attr("value") ) );

			updateFldMsg( $(this).attr("name"), func_result, false);
		});
		$("#buttonReset").click(function() {
			/*
				ask for some form reset confirmation here
			*/
			$( "form" )[ 0 ].reset();			// reset all forms in the html
			$('span[id$="FldMsg"]').html("");	// get all spans ending in 'FldMsg' and set innerHTML to none
			
			// since the resetting of form above did not include disabling the uplb student/emp numbers, then here
			$('input[name$="Number"]').prop( "disabled", !$('input[name="uplbConstituentBoolean"]').prop("checked") );
		});
		//end of actionListener clearing the form
		/*
			actionListener for submitting the form
		*/
		$("#buttonCancel").click(function() {
			$.fn.airtraffic_v2({
				atc_success_func: '',
				url_x: 'useracctctrl/cancel_manage_account',
				msgwait: 'Cancelling the process...',
				timeout: 15000
			});
		});
		
		$("#buttonOK").click(function() {
			var areAllOK = true;
			var array_of_Validators = $('input[name$="_validate"]');
			var validatorsQuantity = array_of_Validators.length;
			var x;
			var uplbConstituency;
			var uplbConstituency_fields = $('input[name$="Number_validate"]');
			var strTemp;
			//subtract 2 first as this accounts for the last two regarding uplb constituency
			validatorsQuantity -= 2;
			for( x = 0; x < validatorsQuantity; x++ )
			{
				if( parseInt( array_of_Validators[x].value, 10 ) < 1 ) 
				{
					if( $('input[name="' + array_of_Validators[x].name + '"]').isFieldRequired() )
					{
						var inputNameValidityIndicatorLen = array_of_Validators[x].name.length;
						var inputName = array_of_Validators[x].name.substring( 0, inputNameValidityIndicatorLen- 9 );
						$( 'input[name="' + inputName + '"]' ).change();
						areAllOK = false;
						//console.log("error on " + inputName);
					}
				}
			}//for
			// now determine if uplbConstituent is checked
			// if checked, either student num or employee should be filled out and okay
			uplbConstituency = document.getElementsByName('uplbConstituentBoolean')[0];
			if( areAllOK && uplbConstituency.checked )
			{
				
				if( ( uplbConstituency_fields[0].value == "-2"  && 
						uplbConstituency_fields[1].value == "-2") 
				){
					$.fn.nextGenModal({
					   msgType: "error",
					   title: "at least one",
					   message: "You have checked that you are a UPLB constituent so enter either student number or employee number."
					});
					return false;
				}else
				if( ( uplbConstituency_fields[0].value == "0"  ||
						uplbConstituency_fields[1].value == "0" ) 
				){
					areAllOK = false;
				}
			}
			if( !areAllOK )	
			{
				$.fn.nextGenModal({
				   msgType: "error",
				   title: "error",
				   message: "There are still invalid entries in the form. Please correct them."
				});
			}else{
				if( $('input#isCurrentPageUserAccount').size() < 1 )
				{
					/*
						This part is used when user is changing password (when he is logged-on in the system)
					*/
					var x = $.ajax({
						type: 'POST',
						url: CI.base_url + '/useracctctrl/isUserExisting',
						timeout: 30000,
						beforeSend: function(){
							$.fn.nextGenModal({
							   msgType: 'ajax',
							   title: 'please wait',
							   message: 'Checking your inputs...'
							});
							setTimeout( function(){ }, 500 );
						},
						data: { 
							'username' : $('input[name="username"]').val(),
							'fName' : $('input[name="firstName"]').val(),
							'mName' : $('input[name="middleName"]').val(),
							'lName'	: $('input[name="lastName"]').val(),
							'studentNum':  $('input[name="studentNumber"]').val(),
							'employeeNum': $('input[name="employeeNumber"]').val()
						},
						success: function(data){
							var splitted =  data.split('_');
							if( splitted[0].startsWith( 'OK' ) ) document.forms[0].submit();
							else{
								$.fn.nextGenModal({
								   msgType: 'error',
								   title: 'Sign-up error',
								   message: splitted[2]
								});
							}
						}
					});	
					x.fail(	function(jqXHR, textStatus) {
								$.fn.nextGenModal({
								   msgType: 'error',
								   title: 'Connection timeout',
								   message: 'It seems you have lost your internet connection. Please try again.'
								});
								return false;
					} ) ;
				}else{
					$('input[name$="_validate"]').attr('disabled','disabled');
					$.fn.airtraffic_v2({
						atc_success_func: '',
						msgwait: 'Saving account changes, one moment please.',
						timeout: 15000
					});
				}
			}
		});
	}
);//doc