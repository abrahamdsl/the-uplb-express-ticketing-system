var guestConcerned = -1;
var isModeManageBookingChooseSeat;
				
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
	var slots = parseInt( $('input#_js_use_slots').val() ); 
	var x;		
	var y;
	var matrices = "";		// serialized seat identifiers to be sent to the server to check if occupied
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
			/*
				These two arrays are used in case of sudden 'you-were-overtaken' error. See line 104.
			*/
			matrix_visual[ matrix ] = $('input[name="g' + parseInt( x + 1 )  + '_seatVisual"]').val();			
			matrix_count[ matrix ] = parseInt( x + 1 );
			if( isModeManageBookingChooseSeat )
			{
				/*
					When user is managing his booking - changing seat(s), then check first
					if he chose a new seat before appending to the 'matrices' string.
				*/
				var matrix_old = $('input[name="g' + parseInt( x + 1 )  + '_seatMatrix_old"]').val();			
				if( matrix != matrix_old && parseInt(matrix, 10) != 0 ) matrices += ( matrix + '-' );
			}else{				
				if( parseInt(matrix, 10) != 0 ) matrices += ( matrix + '-' );
			}
			
		}		

		//<area id="new_bookstep4_submit_js" >
		{
			var x = $.ajax({	
				type: 'POST',
				url: $('form').first().attr('action'),
				timeout: 10000,
				data: $('form').first().serialize(),				
				success: function(data){
					$.fn.makeOverlayForResponse( data );
					
					// custom processing for us
					var s_type = $(data).find('type').text();
					var s_message = $(data).find('message').text();
					var s_resultstring = $(data).find('resultstring').text();
					var resultData = s_resultstring.split('|');
					if( s_type == "error"){
						if( resultData[0] == "OCCUPIED" ) 
						{
							$('div#' + resultData[1] ).removeClass( 'otherGuest' );
							$('div#' + resultData[1] ).removeClass( 'ui-selected' );
							$('div#' + resultData[1] ).addClass( 'occupiedSameClass' );
							$('div#' + resultData[1] ).unbind();
							guestConcerned = parseInt( matrix_count[ resultData[1] ] );
							manipulateGuestSeat( "DESELECT", resultData[1] );
							if( isModeManageBookingChooseSeat )
							{
								makeSeatUsedByThisBookingAvailable();	// in seatManipulation.js - fallback to the previously selected seats
							}
							$.fn.nextGenModal({
							   msgType: 'error',
							   title: 'You were overtaken',
							   message: 'Another user is currently booking a ticket for this event and was the first to take seat ' + matrix_visual[ resultData[1] ] + ' (Guest ' + matrix_count[ resultData[1] ] + ').<br/><br/> Please choose another seat.'
							});							
							return false;
						}else
						if(resultData[0] == "SEAT_REQUIRED" ){
							$.fn.nextGenModal({
							   msgType: 'error',
							   title: 'seat selection required',
							   message: 'You must select seats for all guests in this booking.'
							});								
							return false;
						}else
						if( resultData[0] == "CIRCUMVENT_SEAT_BLOCK" ){
							$.fn.nextGenModal({
							   msgType: 'error',
							   title: 'seat selection denied',
							   message: 'The seat you have selected is not assigned to your ticket class.'
							});								
							return false;
						}if( resultData[0] == "INVALID_SEAT_DATA" ){
							$.fn.nextGenModal({
							   msgType: 'error',
							   title: 'error',
							   message: 'Invalid seat data received by the server or seat is non-existent. Are you hacking the app?'
							});								
							return false;
						}else{
							$.fn.nextGenModal({
								   msgType: 'error',
								   title: 'Internal server error',
								   message: 'Something went wrong. Please try again.<br/><br/> ' + data
							});
						}
					}
				}
			});	
		}
		x.fail(	function(jqXHR, textStatus) { 		
			$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'Connection timeout',
				   message: 'It seems you have lost your internet connection. Please try again. <br/<br/>' + textStatus
			});
			return false;
		});		
		//</area>
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

function postChooseCleanup()
{
	/*
		Created 03MAR2012-1313
		
		Moved from the 'onClose' function of the seat_modal open function
	*/
	var chosenSeat = $('#basic-modal-content-freeform').find('.ddms_selected');
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
}//postChooseCleanup(..)

$(document).ready( function(){
	var args = [];	
	args["isOverlayDisplayedAlready"] = false;
	/*
		Added 03MAR2012-1221. This determines if the reason why we are in this page
		is the user already booked and he wants to change seats. This variable
		is important as later, in this function, its value will be accessed to see to
		remove the "occuppied" status on the seats of the guests on this booking.
		
		Also found in seatManipulation.js
	*/
	isModeManageBookingChooseSeat = ( $( 'input#manageBookingChooseSeat' ).size() == 1 && $( 'input#manageBookingChooseSeat' ).val() == "1" );				
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
				/*minWidth: 926,
				minHeight: 506, 
				maxHeight: 600,
				maxWidth: 1000,*/
				autoResize: false,
				onShow: function(){					
					if( chosenSeatMatrix != "0" ) {
						$('div#' + chosenSeatMatrix ).removeClass( 'otherGuest' );
						$('div#' + chosenSeatMatrix ).addClass( 'ddms_selected' );
					}
				},
				onClose: function(){							
							$('#warningIndicator').hide();														
							postChooseCleanup();							
							$.modal.close();						
				}
			}
		);//$
	});
	
	$('#buttonOK').click( function(){
		var slots = parseInt( $('input#_js_use_slots').val() );
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