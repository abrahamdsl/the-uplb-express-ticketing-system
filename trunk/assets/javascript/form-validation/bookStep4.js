var guestConcerned = -1;

function createSeatmapOnPage( args )
{
	/*
		Created 13FEB2012-1202
	*/	
	//display notice	
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait',
	   message: 'Getting seat map info, this may take up to a minute ...'
	});
	// ajax-time!
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + '/SeatCtrl/getActualSeatsData',
		timeout: 50000,
		/*data: { 'uniqueID': args["seatMapUniqueID"] },*/	// data is determined by the cookies, erase this on finality
		success: function(data){
			alreadyConfiguredSeat = false;			
			$(document).manipulateSeatAJAX( data );			// make now the HTML						
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
}

function formSubmit( ){
	var slots = parseInt( ( getCookie('slots_being_booked')) ); 
	var x;		
	var y;
	var matrices = "";	
	var matrix_visual= [];
	var matrix_count = [];
	var ajaxObj;
		
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait',
	   message: 'Verifying seat availability ...'
	});
		
	
		// get seat matrix data
		for( x = 0; x< slots; x++ )
		{				
			var matrix = $('input[name="g' + parseInt( x + 1 )  + '_seatMatrix"]').val();			
			matrix_visual[ matrix ] = $('input[name="g' + parseInt( x + 1 )  + '_seatVisual"]').val();			
			matrix_count[ matrix ] = parseInt( x + 1 );
			matrices += ( matrix + '-' );
		}		
				
			ajaxObj = $.ajax({	
				type: 'POST',
				aynsc: false,
				url: CI.base_url + 'SeatCtrl/areSeatsOccupied',
				timeout: 50000,
				data: { 'matrices' : matrices.substring( 0, matrices.length-1 ),
						'eventID' : getCookie( 'eventID' ),
						'showtimeID' : getCookie( 'showtimeID' )
				},
				success: function(data){						
						if( data.startsWith("OK") )
						{
							resultData = data.split('|');
							if( resultData[1] == "FALSE" )
							{
								$('div#' + resultData[2] ).removeClass( 'otherGuest' );
								$('div#' + resultData[2] ).removeClass( 'ui-selected' );
								$('div#' + resultData[2] ).addClass( 'occupiedSameClass' );
								$('div#' + resultData[2] ).unbind();
								guestConcerned = parseInt( matrix_count[ resultData[2] ] );
								manipulateGuestSeat( "DESELECT", resultData[2] )								
								$.fn.nextGenModal({
								   msgType: 'error',
								   title: 'You were overtaken',
								   message: 'Another user is currently booking a ticket for this event and was the first to take seat ' + matrix_visual[ resultData[2] ] + ' (Guest ' + matrix_count[ resultData[2] ] + ').<br/><br/> Please choose another seat.'
								});
								return false;
							}else{
								$('input[type!="hidden"]').attr( 'disabled', 'disabled' );	
								document.forms[0].submit();
							}
						}else{							
							$.fn.nextGenModal({
								   msgType: 'error',
								   title: 'Internal server error',
								   message: 'Something went wrong. Please try again.<br/><br/> ' + data
							});
							stillLoop = false;
							return false;
						}
				}
			});	
			ajaxObj.fail(	function(jqXHR, textStatus) { 		
						$.fn.nextGenModal({
							   msgType: 'error',
							   title: 'Connection timeout',
							   message: 'It seems you have lost your internet connection. Please try again. <br/<br/>' + textStatus
						});
						return false;
			});				
}
function manipulateGuestSeat( mode, matrixInfo )
{						
	/*
		Created 13FEB2012-1852.
		
		Parameter def'n:
		mode	- string - { "SELECT", "DESELECT" }
		matrixInfo - string -  in X_Y form, X & Y are numbers and identifier of the divs representing the seats
	*/
	var seatMatrixIdentifier = "g" +  guestConcerned + "_seatMatrix";	// in bookStep4.js
	var seatVisualIdentifier = "g" +  guestConcerned + "_seatVisual";
	var seatChooseBtnIdentifier = "g" +  guestConcerned + "_chooseSeat";
	var seatMatrixSelected = $('input[name=' + seatMatrixIdentifier + ']').val();
	var divConcerned;
	
	if( mode == 'SELECT' )
	{	
		divConcerned = $('div#' + matrixInfo );
		if( seatMatrixSelected != "0" )
		{	//seat assigned now to guest
			$( 'div#' + seatMatrixSelected ).removeClass( 'ui-selected' );		// remove first the old seat
			$( 'div#' + seatMatrixSelected ).removeClass( 'ddms_selected' );										
		}else{
			$( 'input#' + seatChooseBtnIdentifier  ).val( "Change seat" );		// change the caption of the seat button
		}		
		$( 'input[name="' + seatMatrixIdentifier + '"]' ).val( matrixInfo );	// assign seat matrix info to the field to be submitted
		$( 'input[name="' + seatVisualIdentifier + '"]' ).val( divConcerned.children("span.row").html() + "-" + divConcerned.children("span.col").html() ) ;
		$( 'input[name="' + seatVisualIdentifier + '"]' ).show();
	}else{
		$( 'input#' + seatChooseBtnIdentifier  ).val( "Choose seat" );		// revert values
		$( 'input[name="' + seatMatrixIdentifier + '"]' ).val( "0" );		
		$( 'input[name="' + seatVisualIdentifier + '"]' ).hide();
	}
}//manipulateGuestSeat( .. )

$(document).ready( function(){
	var args = [];	
	args["isOverlayDisplayedAlready"] = false;
	createSeatmapOnPage( args );
	
	$('input[type="button"][class="seatChooser"]').click( function(){
		var guestNum;
		var seatMatrixIdentifier;
		var seatVisualIdentifier;
		var seatChooseBtnIdentifier;
		var chosenSeatMatrix;
		var chosenSeat;
		
		guestNum = $(this).attr('id').split('_')[0].substring( 1 );
		guestConcerned = parseInt( guestNum );							// global var, on the top
		seatMatrixIdentifier = "g" +  guestConcerned + "_seatMatrix";	// in bookStep4.js
		seatVisualIdentifier = "g" +  guestConcerned + "_seatVisual";
		seatChooseBtnIdentifier = "g" +  guestConcerned + "_chooseSeat";
		chosenSeatMatrix = $('input[name="' + seatMatrixIdentifier +'"]').val();		
		$('#basic-modal-content-freeform').modal( 
			{ 	// show modal
				persist: true ,			
				maxHeight: 600,
				maxWidth: 1000,
				onShow: function(){					
					if( chosenSeatMatrix != "0" ) {
						$('div#' + chosenSeatMatrix ).removeClass( 'otherGuest' );
						$('div#' + chosenSeatMatrix ).addClass( 'ddms_selected' );
					}
				},
				onClose: function(){							
							$('#warningIndicator').hide();														
							chosenSeat = $('#basic-modal-content-freeform').find('.ddms_selected');
							if( chosenSeat.size() > 0 )
							{
								chosenSeat.each( function()
								{
									$( this).removeClass( 'ddms_selected' );
									$( this ).addClass( 'otherGuest' );								
								});
							}else{
								manipulateGuestSeat( "DESELECT", "" );
							}
							guestConcerned = -1;
							$.modal.close();						
				}
			}
		);//$
	});
	
	$('#buttonOK').click( function(){
		var slots = parseInt( ( getCookie('slots_being_booked')) ); 
		var x;
		var y;
		var seatMatrixIdentifier;
		var guestWithoutSeats = [];
		var message;		
		
		for( x=0, y=0; x < slots; x++ )
		{
			seatMatrixIdentifier = "g" +  (x+1) + "_seatMatrix";
			if( $('input[name="' +  seatMatrixIdentifier + '"]').val() == "0" )
			{
				guestWithoutSeats[ y++ ] = x+1;
			}
		}
		
		if( y > 0 )
		{			
			message = "Are you sure you don't want to select seat for the following guest(s)? <br/><br/> ";
			for( x = 0; x < y; x++ ){
				message += ( guestWithoutSeats[x] + '| ' + $('div#g' + guestWithoutSeats[x] + '-firstNameFld').html() + ' ' + $('div#g' + guestWithoutSeats[x] + '-lastNameFld').html()  + '<br/>' );				
			}			
			$.fn.nextGenModal({
				   msgType: 'warning',
				   title: 'confirm',
				   closeOnChoose: false,
				   message: message,
				   yesFunctionCall: 'formSubmit'
			});
		}else{			
			formSubmit();
		}
		guestWithoutSeats = null;
	});
	
	
});