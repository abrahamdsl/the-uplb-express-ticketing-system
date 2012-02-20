$(document).ready( function(){
		/* since we hide all error divs by default, show the one for the gender on load since
			no gender is selected on load	*/
		$('span[id$="genderFldMsg"]').parent().show();
		
		$.fn.isFieldRequired = function()
		{
			/*
				Created 10FEB2012-1009 - Dependent on the DOM structure of the page the default DOM when this was 
				made is like:
				****************************
				<div class="row" id="g1">
					<div class="label">
						<label .....
						<span class="critical">*</span>
					</div>
					<div class="collection">
						<input type="text" name=".." .../>
						<input type="hidden" name".._validate' .../>
					</div>
				</div>				
				******************************
				It is the "span.critical: we will be basing on.
				This function is attached to div.row div.collection input[type="text"
			*/
			var divClassMsgContainer = $(this).parent().parent().children("div.label").children("span.critical");
			
			return ( divClassMsgContainer.size() == 1 );
		};//isFieldRequired
							
		$('input[type!="hidden"]').focus( function(){
			if( $(this).isFieldRequired() )  updateValidIndicator( $(this).attr('name'), false );
		});
		
		$('input[type!="hidden"]:not([name$="gender"])').blur( function(){
			$(this).change();
		});
		
		$('input[type="text"][name$="Name"]').change( function(){
			var thisName = $(this).attr('name');
			var result = isName_valid( $(this).val() );
			var thisValLen = $(this).val().length;
			
			if( result == "OK" || ( !$(this).isFieldRequired() && thisValLen < 1) )
			{
				hideFldMsg( thisName );
				updateValidIndicator( thisName, true );
			}else{
				if( ( thisValLen > 0) || $(this).isFieldRequired() )				
				{
					displayFldMsg( thisName, result );
					updateValidIndicator( thisName, false );
				}
			}
		});
		
		$('input[type="text"][name$="cellphone"]').change( function(){
			var thisName = $(this).attr('name');
			var result =  isPhone_valid( $(this).val(), "CELL" );
			
			if( result == "OK" )
			{
				hideFldMsg( thisName );
				updateValidIndicator( thisName, true );
			}else{
				displayFldMsg( thisName, result );
				updateValidIndicator( thisName, false );
			}
		});
		
		$('input[type="text"][name$="landline"]').change( function(){
			var thisName = $(this).attr('name');
			var result =  isPhone_valid( $(this).val() ,"LANDLINE" );
			var thisVal = $(this).val().length;
			
			if( result == "OK" )
			{
				hideFldMsg( thisName );
				updateValidIndicator( thisName, true );
			}else{					   					
				if( (result.toLowerCase() == "insufficient digits") && ( thisVal < 1) ) 
				{		
					// meaning, if user does not enter any number or character for that matter
					// then we don't care.
					hideFldMsg( thisName );
					updateValidIndicator( thisName, true );					
				}else{	
					displayFldMsg( thisName, result );
					updateValidIndicator( thisName, false );
				}
			}
		});
		
		$('input[type="text"][name$="email_01"]').change( function(){
			var thisName = $(this).attr('name');
			var result =  isEmail_valid( $(this).val() );
			
			if( result == "OK" )
			{
				hideFldMsg( thisName );
				updateValidIndicator( thisName, true );
			}else{				
				displayFldMsg( thisName, result );
				updateValidIndicator( thisName, false );
			}
		});
		
		$("input[name$='-gender']").change(function() {								
				hideFldMsg( $(this).attr("name") );
				updateValidIndicator( $(this).attr("name"), true );
		} );
		
		$('#buttonOK').click( function(){
			var slots = parseInt( ( getCookie('slots_being_booked')) ); 
			var x;
			var y;
			var allInputs;			
			var validityIndicators;
			var submittable = true;
			
			
			for( x = 0; x < slots; x++ )
			{
				var identifier = 'g' + (x+1);
				var identifierDashed = identifier + '-';
				var eachLeftDivs;
				var eachRightDivs;
				
					var checkForValidity = [];
					var i;
					var	j = 0;
					
					allInputs = $('input[type="text"][name^="' + identifier + '"]');		// get all inputs
					allInputs.each( function(){
						// if required, call the form-validator functions, which is contained by the .change() function
						// then add to the checkForValidity array that is being checked later
						if( $(this).isFieldRequired() ){
							$(this).change();
							checkForValidity[ j++ ] = $(this).attr('name');
						}
					});
					
					// add gender since it was not included above (input="radio")
					checkForValidity[ j++ ] = (identifier + "-gender");					
					for( i=0; i<j; i++ )
					{																
						var elementNeeded = $( 'input[name="' + checkForValidity[i] + '_validate"]' );	// get all validity indicators						
						if( elementNeeded.val() == "0" ) submittable = false;
					}																	
			}
									
			
			if( submittable ){			
				// check if there is a duplicate name
				var appendedNames = [];
				for( x=0; x<slots; x++){
					var names = $('input[type="text"][name^="g' + parseInt(x+1) + '"][name$="Name"]');				
					var appended = "";
					names.each( function(){						
						appended += $(this).val().toLowerCase();	// append the names onto a single string
					});									
					if( appendedNames[appended] == undefined ) 	    // if not yet exists in the array, go assign 1
					{
						appendedNames[appended] = x+1;
					}else{											// name already exists so, dead.					
						$.fn.nextGenModal({
						   msgType: "error",
						   title: 'duplicate names not allowed',
						   message: "Guest " + appendedNames[appended] + " and Guest " + parseInt(x+1) + " have same names!"
						});
						return false;
					}					
				}
				$('input[type="hidden"][name$="validate"]').attr('disabled', 'disabled'); // since these are just validity indicators no need to submit
				document.forms[0].submit();
			}else{				
				$.fn.nextGenModal({
				   msgType: "error",
				   title: "error",
				   message: "There are still invalid entries in your guest form. Please correct them."
				});
			}
		});
});