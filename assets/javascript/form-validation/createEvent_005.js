/*
	NOTE: 15DEC2011-1513: A LOT OF REFACTORING TO BE DONE here. Might look
	at createEvent_004.js too and its relation with here.
*/
var lastUsedSeatmap = -1;
var alreadyConfiguredSeat = false;

function checkSlotsTotal( theObject)
{
	/*
		13JAN2012-1112: reduced this. Most was delegated to sumTotalSlots()
	*/
	var Total = $('#totalSlotsChosen').val() ;
	
	if( Total > parseInt( $("#maxSlot").val() ) )
	{		
		displayOverlay( 'error' , 'error', "Total slots of all classes exceeds maximum allowed." );												
		theObject.val( $('#lastFocus').val() );		// restore the former value
		sumTotalSlots();							// recompute total again		
		return false;
	}	
	return true;
}//checkSlotsTotal(..)

function createSeatmapOnPage( args )
{
	/*
		Created 30JAN2012
	*/	
	//display notice
	if( args["isOverlayDisplayedAlready"] === true ) modifyAlreadyDisplayedOverlay( 'notice' , 'please wait', 'Getting seat map info ...', false );
	else{
		displayOverlay( 'notice' , 'please wait', 'Getting seat map info, this may take up to a minute ...<br/><br/>' );
	}
	// ajax-time!
	var x = $.ajax({	
		type: 'POST',
		url: 'http://localhost/species/SeatCtrl/getMasterSeatmapData',
		timeout: 50000,
		data: { 'uniqueID': args["seatMapUniqueID"] },				
		success: function(data){
			alreadyConfiguredSeat = false;
			$(document).manipulateSeatAJAX( data );			// make now the HTML
			lastUsedSeatmap =  args["seatMapUniqueID"];
			$('input[id^="seatAssigned"]').val('0'); 		// reset counters of how many seats have been already selected
		}
	});	
	x.fail(	function(jqXHR, textStatus) { 
				revertSeatMapPulldown();
				modifyAlreadyDisplayedOverlay( 'error' , 'Connection timeout', 'It seems you have lost your internet connection. Please try again.', false ); 				
				return false;
	} ) ;	
}

function formSubmitPreCheck()
{
	// created 13JAN2012-1130
	if( parseInt( $('#totalSlotsChosen').val() ) == 0 )
	{
		displayOverlay( 'error' , 'are you kidding me', "Please enter at least one slot in at least one ticket class, else click Cancel to configure this event later." );	
		return false;		
	}	
	return true;
}

function formSubmit( args )
{
	// created 7JAN2012-1547
	
	/*
		Try to submit ticket classes info first.
	*/
	// display modal/overlay
	if( args["isOverlayDisplayedAlready"] === true )
		modifyAlreadyDisplayedOverlay( 'notice' , 'processing', 'Submitting ticket classes info ...', false );	
	else
		displayOverlay( 'notice' , 'processing', 'Submitting ticket classes info ...' );	
	// now, ajax-time!		
	var ticketClassPOST = $.ajax({	
		type: 'POST',
		url: $('form#formMain').attr('action'),
		timeout: 30000,
		data:  $('form#formMain').serialize(),
		success: function(data){								
			//	Now try to submit seats									
			modifyAlreadyDisplayedOverlay( 'notice' , 'processing', 'Submitting seat assignments ...', false );	
			var seatsPOST = $.ajax({	
				type: 'POST',
				url: $('form#seatAssignments').attr('action'),
				timeout: 45000,
				data:  $('form#seatAssignments').serialize() + '&seatmapUniqueID=' + $('select#seatMapPullDown option:selected').val(),	// add seatmap id too since it's not within the form
				success: function(data){					
					$('input#eligibility').val( data );					
					hideOverlay();						
					$("form#id_forwarder").submit();
					return [ this ];
				}
			});	
			seatsPOST.fail( 
				function(jqXHR, textStatus) { 
						modifyAlreadyDisplayedOverlay( 'error' , 'Connection timeout', 'It seems you have lost your internet connection. Please try again.', false );
						return false;
				}
			);
		}
	});	
	// fail handler
	ticketClassPOST.fail( 
		function(jqXHR, textStatus) { 				
				modifyAlreadyDisplayedOverlay( 'error' , 'Connection timeout', 'It seems you have lost your internet connection. Please try again.', false );
				return false;
		}
	);		
}//formSubmit( .. )


function giveMeClass( elemID )
{	
	/*
		Created 15DEC2011-1421. Since input fields are named like:
		<functionality>_<class> example addSlots_REGULAR,
		we have to have this to extract class. Uses Javascript's split()
		and returns the second part of the array - the one containing the class.
	*/
	var y = elemID.split('_');	
	if( y.length != 2 ) return "NULL";
	
	return y[1];
}

function revertSeatMapPulldown( )
{
	/*
		Created 03FEB2012-1546. Used when event manager already selected seat map, but wants
		to change to another one - so he/she is notified that doing so would erase the previous
		work done on the current seat map. If he/she declines, then this is called to revert the
		selected seat map displayed on the page.
	*/
	$('#seatMapPullDown option:selected').attr( "selected", "false");						// unselect	
	$('#seatMapPullDown option[value="' + lastUsedSeatmap + '"]').attr('selected', 'selected');   // revert to the old one		
}


function sumTotalSlots()
{
	/*
		Created 13JAN2012-1054
		
		It is assumed all fields called here have the proper integer values.
	*/
	var eachClass_slots;
	var x;		
	var y;
	var Total;
	var addThis = 0;	
		
	eachClass_slots = $('input[name^="slot"]').get();			
	for( Total = 0, x = 0, y = eachClass_slots.length; x < y; x++)	// just adding, cumulatively
	{				
		Total += parseInt( $(eachClass_slots[x]).val() );		
	}
	$('#totalSlotsChosen').val( Total );
}//sumSlotsTotal(..)

$(document).ready( function() {	
	
	$('div[class="drop"]').click( function(){
		$('#warningIndicator').html( $(this).attr('style') );
		$('#warningIndicator').show();

	});
	
	$('#seatMapPullDown').change( function(){
		// called when user selects a seat map from the pull down list
		var args = new Array();
		
		if( $(this).val() == "null" ) return false;		// selects the blank entry, so don't do anything
		args["seatMapUniqueID"] = $(this).val();		
		if( args["seatMapUniqueID"] == lastUsedSeatmap ) return false;
		if( alreadyConfiguredSeat ){
			args["isOverlayDisplayedAlready"] = true;
			displayOverlay_confirm_NoCloseOnChoose( 'warning' , 'Confirm', 'createSeatmapOnPage', args, 'revertSeatMapPulldown', null, "Your previous seat assignments would be erased. Continue?");			
		}
		else{
			args["isOverlayDisplayedAlready"] = false;
			createSeatmapOnPage( args );			
		}
	});
	
	$('input[type="text"]').focus( function() {
		/*
			When an input field is being focused, record
			its value on a hidden input field. 
		*/
		$('#lastFocus').val( $(this).val() );
	});
	
	$('input[id^="id_seat_"]').click( function() {
		
		var thisClass = giveMeClass( $(this).attr('name') );				
		var seatMapUniqueID = $('#seatMapPullDown option:selected').val();		
		var thisClassSlots;
		var thisClassAssignedSeatQuantity;
		
		if( isNaN( parseInt( seatMapUniqueID ) ) )
		{
			displayOverlay( 'error' , 'first and foremost', 'Please choose a seat map first.' );
			return false;
		}
		thisClassSlots = parseInt( $( '#id_slot_' + thisClass).val() );
		thisClassAssignedSeatQuantity = parseInt( $( '#seatAssigned_' + thisClass).val() );
		$('#basic-modal-content-freeform').find('.items_selected').html( thisClassAssignedSeatQuantity );		
		if( isNaN( thisClassSlots ) || thisClassSlots < 1 )
		{
			displayOverlay( 'error' , 'input needed', 'Please enter at least 1 slot for ' + thisClass + ' class ' );
			return false;
		}
		
		$('#basic-modal-content-freeform').find('span#maxSeatsForClass').html( $( '#id_slot_' + thisClass).val() );
		$('#basic-modal-content-freeform').find('span#seatClass').html( thisClass );
		$('#basic-modal-content-freeform').modal( 
			{ 
				persist: true ,			
				maxHeight: 600,
				maxWidth: 1000,
				onShow: function(){
					$('#basic-modal-content-freeform').find( '.drop_' + thisClass ).each( function(){
						$(this).removeClass( 'drop_' + thisClass );
						$(this).removeClass('otherClass');	
						$(this).addClass('ddms_selected');
						$(this).addClass('ui-selected');
					});
				},
				onClose: function(){
							/* Bug as of 31JAN2012-1121 - Lasso tool
									When selected seats has reached maximum allowed for class, drag to have lasso appear,
									then poof, it will stop 'growing' after some point and won't vanish. So, the solution,
									set the position to negative so that it will move outside the visible area of the page.
									If .hide() is used instead, the lasso won't be visible after the modal is closed and reopened.
							*/
							$('.ui-selectable-helper').css( 'top', '-10px' );
							$('.ui-selectable-helper').css( 'left', '-10px' );
							
							$('#warningIndicator').hide();							
							$( '#seatAssigned_' + thisClass).val( $('#basic-modal-content-freeform').find('.items_selected').html() );	// reassign
							$('#basic-modal-content-freeform').find('.ddms_selected').each( function(){								
								$(this).find('input.seatClass').val( thisClass );								
								$(this).removeClass('ddms_selected');
								$(this).removeClass('ui-selected');
								$(this).removeClass('ui-selectee');
								$(this).removeClass('ui-selectable');								
								$(this).removeClass('dropAvailable');								
								$(this).addClass('otherClass');								
								$(this).addClass( 'drop_' + thisClass );
							});
							$.modal.close();						
						}
			}
		);				
	});
	
	$('input[id^="id_privilege_"]').click( function() {
		displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
	});
	
	$('input[id^="addSlots_"]').click( function() {	
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		var retVal = true;
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{			
			displayOverlay( 'error' , 'error', "Invalid slot quantity." );										
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		/* now, sum them up
		*/
		// get all values of input for slots of all classes
		if( checkSlotsTotal( thisVal ) )
		{
			$( selector_ChangeThis ).val( parseInt(thisVal) + 1);			
		}
		else{
			$( selector_ChangeThis ).val( $('#lastFocus').val() );	// restore the former value			
			retVal = false;
		}
		sumTotalSlots();
		return  retVal;
	});
	
	$('input[id^="reduceSlots_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{			
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
			return false;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum slots is zero." );
			return false;
		}
		if( $('input#seatAssigned_' + thisClass ).val() == thisVal )
		{
			displayOverlay( 'error' , 'error',"You have assigned " + thisVal + " seats for this class, so you cannot easily reduce the slots. Deselect some seats first and try again." );
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);
		sumTotalSlots();
		return true;
	});
	
	$('input[id^="addPrice_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_price_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
			return;
		}				
		$( selector_ChangeThis ).val( parseInt(thisVal) + 1);		
	});
	
	$('input[id^="reducePrice_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_price_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();				
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of slots." );
			return;
		}		
		if( parseInt(thisVal) == 0 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum price is zero." );return;
		}		
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[id^="addHoldingTime_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_HoldingTime_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of minutes for holding time." );
			return false;
		}
		if( thisVal == 59 )
		{
			displayOverlay( 'error' , 'error',"Maximum holding time is 59 minutes." );
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) + 1);		
	});
	
	$('input[id^="reduceHoldingTime_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_HoldingTime_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of holding time." );
			return false;
		}		
		if( parseInt(thisVal) == 2 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum holding time is two minutes." );return;
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[name^="price"]').blur( function()	{
		if( !isFloat( $(this).val() ) )
		{
			displayOverlay( 'error' , 'bad expectation',"Price not valid." );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) < 0 )
		{			
			displayOverlay( 'error' , 'bad expectation','Negative amount not allowed.' );
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		
	});
	
	$('input[name^="slot"]').blur( function(){				
		var thisClass = giveMeClass( $(this).attr("name") );
		var thisVal = $(this).val();
		var currentlyAssignedSeats;
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error', "Invalid slot quantity." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		currentlyAssignedSeats = parseInt( $('input#seatAssigned_' + thisClass ).val() );
		if( currentlyAssignedSeats > parseInt(thisVal) )
		{
			displayOverlay( 'error' , 'error',"You have assigned " + currentlyAssignedSeats + " seats for this class, so you cannot easily reduce the slots. Deselect some seats first and try again." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		/* now, sum them up
		*/
		// get all values of input for slots of all classes
		sumTotalSlots();
		return checkSlotsTotal( $(this) );
		
	}); //$('input[name^="slot"]').blur(..)
	
	$('input[name^="holdingTime"]').blur( function()	{				
		var thisVal = $(this).val();
		
		if( !isInt( thisVal ) )
		{
			displayOverlay( 'error' , 'error',"Invalid number of holding time." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}						
		if( parseInt(thisVal) < 2 ) 
		{
			displayOverlay( 'error' , 'bad expectation',"Minimum holding time is two minutes." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) >= 59 )
		{
			displayOverlay( 'error' , 'error',"Maximum holding time is 59 minutes." );
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
	}); //$('input[name^="holdingTime"]').blur(..)
	
	$("#buttonReset").click( function() {		
		displayOverlay( 'okay' , 'Not yet :-)', 'Feature coming later' );						
		return false;
	});
	
	$("#buttonOK").click( function() {
		var args = new Array();
	
		args["isOverlayDisplayedAlready"] = true;		
		if( formSubmitPreCheck() ){
			displayOverlay_confirm_NoCloseOnChoose( 'warning' , 'Confirm', 'formSubmit', args, 'hideOverlay', null, "Are you sure you have configured them?");													
		}
		return false;
	});
	
	/*$('input[name^="slot"]').change(		
		$('input[name^="slot"]').blur();
	);*/
	
});