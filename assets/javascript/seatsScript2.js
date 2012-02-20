function banishSeat( seatInputIndicator )
{
	var divContainingSeat = $( seatInputIndicator ).parent();
	
	$( divContainingSeat ).removeClass('dropped');						// remove class that makes it a selected seat
	$( divContainingSeat ).hide();										// physically hide
	
	// update the seat data indicator
	$( seatInputIndicator ).val( '' );
	$( divContainingSeat ).children( '[name$="status"]' ).val( '-1' );	// to be sent to server, -1 indicates invisibility/aisle
}

function formSubmit()
{
	$.fn.nextGenModal({
		msgType: 'ajax',
		title: 'please wait...',
		message: 'Submitting seat info..'
	});
	document.forms[0].submit();
}


$(document).ready( function(){
	$( '#buttonOK' ).click( function(){				
		$.fn.nextGenModal({
		   msgType: 'warning',
		   title: 'confirm',
		   message: "Are you sure you have modified the seat map according to your whims?",
		   yesFunctionCall: 'formSubmit'
		});
	});

	$('td.legend input[name^="label_up"]').change( function(){
		$('td.legend input[name^="label_down"]').val( $(this).val() );
	});

   //seatLocatedAt_2_19_presentation
	$('td.legend').click( function(){
		/*
			When a TD is clicked, get the child element with name starting with
			"presentation level", then assign its value. It will be our 'x' in the for loop.
		*/
		var mode;	// valid values: 'number' & 'letter' - determines whether we are dealing with a vertical aisle or horizontal aisle
		var mode2 = "AISLE";	// valid values: 'AISLE' + 'DEAISLE' - are we removing seats to make way for an aisle or are we going to restore the seats previously removed and thus removing the aisle?
		var thisIndicator = $( this ).children('[name^="label"]')[0]; // the td is clicked, and an indicator of the column, the input with name starting with "label" is there, so we get a handle
		var destroyThis;
		var x;
		var z;
		var z_handle;
		var decision;
		var colRowIndicatorPosition;
		var thisOpposite;
								
		//we are dealing with vertical aisle
		if( thisIndicator.name.endsWith('number') )		// endsWith() prototyped in generalChecks.js
		{
			mode ="number";
			destroyThis = parseInt( $(thisIndicator).val() );	// get the column number ( index starts at 1 )
			y = $('#rows').val();
			z = $('#cols').val();			
			z_handle = $('#cols_touchable');
			
			/*
				determine if column indicator is from top or bottom, then assemble the identifier for the matching indicator,
				so that we can set its value to 'A' too later on
			*/
			colRowIndicatorPosition = $(thisIndicator).attr('name').split('_')[1];
			if( colRowIndicatorPosition == "up" ) 
			{
				thisOpposite = "down";
			}else{
				thisOpposite = "up";				
				$.fn.nextGenModal({
				   msgType: 'okay',
				   title: 'not yet',
				   message: 'Making of vertical aisle feature by clicking on the bottom column indicators coming later.  :-)'
				});
				return false
			}			
			thisOpposite = 'input[name="label_' + thisOpposite + '_number"][value="' + destroyThis + '"]';			
		}else
		// we are dealing with horizontal aisle
		if( thisIndicator.name.endsWith('letter') )	
		{
			/*mode="letter";
			destroyThis = atoi( $(thisIndicator).val() );		// found in generalChecks.js
			y = $('#cols').val();
			z = $('#rows').val();			
			z_handle = $('#rows_touchable');*/								
			$.fn.nextGenModal({
				   msgType: 'okay',
				   title: 'not yet',
				   message: 'Making of horizontal aisle feature (might be) coming later.  :-)'
				});
			return false;
		}else{
			alert('error: "up" or "down" only needed');
			return false;
		}		

							
		if( mode2 == "AISLE" )
		{			
			if( mode == "number" )
			{
				/*
					make a vertical aisle
				*/
				decision = confirm( 'Are you sure you want to change column ' + thisIndicator.value + ' into an aisle?');
				if( !decision ) return false;
				/*
					Adjusting the column indicators, both top and bottom
				*/
				// set new value 'A' that means 'Aisle', for the input type=text where the td was clicked
				$(thisIndicator).val( 'A' );	
				$(thisOpposite).val( 'A' );				
				// do the same to the corresponding ...
																
				for( i = (destroyThis+1), newVal = i, ++z; i < z; i++) 
				{
						/*
							exact part where adjustment of the column indicators, both top and bottom happens
							value is lessened here
						*/
						var labelUpSelector = 'input[name^="label_up_'+mode+'"][value="'+i+'"]';
						var labelDownSelector = 'input[name^="label_down_'+mode+'"][value="'+i+'"]';
						
						if( $(labelUpSelector).val() != 'A' )
						{						
							//alert( $('input[name^="label_up_'+mode+'"][value="'+i+'"]').val(  ) );
							$('input[name^="label_up_'+mode+'"][value="'+i+'"]').val( newVal - 1 );
							$('input[name^="label_down_'+mode+'"][value="'+i+'"]').val( newVal - 1);
							newVal++;
						}else{
							
						}											
				}				
								
				/*
					Now, removing the seats so there's an open space. Get a handle to the hidden input that actually represents the seat,
					then pass to the concerned function.
				*/
				var inTheLine = $('input[name^="seatLocatedAt_"][name$="presentation"][value$="_'+ (  destroyThis )+'"]');		// get all seats concerned
				for( x=0, y=inTheLine.length ; x < y; x++ )
				{														
					banishSeat( $(inTheLine[x]) );									
				}
				
				/*
					Update the columns of the seats to the right
				*/
				for( x = destroyThis, y=z ; x < y; x++ )
				{
					var seatsToModify = $('input[name^="seatLocatedAt_"][name$="_'+x+'_presentation"]');	// get all seats concerned
					for( a = 0, b = seatsToModify.length; a < b; a++ )
					{
						var itsParent = $( seatsToModify[a] ).parent();										
						var spanIndicator = $(itsParent).children("span");
						var oldValue_arr = $( seatsToModify[a] ).val().split('_'); // a seat is named like 'seatLocatedAt_x_y_presentation' with value="A_B", where x,y are matrix coordinates and A,B are the displayed values on the browser
						var newValue_y = parseInt( oldValue_arr[1]) - 1;		   // so decrease the number
						
						//update hidden input
						 $( seatsToModify[a] ).val( oldValue_arr[0] + '_' + newValue_y );
						 
						 //now, update span - display new column number
						 $(spanIndicator).html( oldValue_arr[0] + '-' + newValue_y );
						
					}
				}
				
			}
		}else{
			// code for de-aisling
		}
		
	});

});