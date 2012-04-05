/*
	Ripped off from createEvent_005.js
	
*/
var lastUsedSeatmap = -1;
var alreadyConfiguredSeat = false;
var _continue = true;
function checkSlotsTotal( theObject)
{
	/*
		13JAN2012-1112: reduced this. Most was delegated to sumTotalSlots()
	*/
	var Total = $('#totalSlotsChosen').val() ;
	
	if( Total > parseInt( $("#maxSlot").val() ) )
	{				
		$.fn.nextGenModal({
		   msgType: 'error',
		   title: 'error',
		   message: 'Total slots of all classes exceeds maximum allowed.'
		});
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
	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'please wait',
	   message: 'Getting seat map info, this may take up to a minute ...<br/><br/>'
	});
	// ajax-time!	
	var x = $.ajax({	
		type: 'POST',
		url: CI.base_url + '/SeatCtrl/getActualSeatsData',
		timeout: 50000,
		data: { 'uniqueID': args["seatMapUniqueID"] },				
		success: function(data){
			alreadyConfiguredSeat = false;			
			$(document).manipulateSeatAJAX( data );			// make now the HTML
			lastUsedSeatmap =  args["seatMapUniqueID"];			
		}
	});	
	x.fail(	function(jqXHR, textStatus) { 
				revertSeatMapPulldown();			
				$.fn.nextGenModal({
				   msgType: 'error',
				   title: 'Connection timeout',
				   message: 'It seems you have lost your internet connection. Please try again.'
				});
				return false;
	} ) ;	
}

function enableNewCreation()
{
	$('input[name="share_separate"]').val( "1" );
	formSubmit();
}

function disableNewCreation()
{
	$('input[name="share_separate"]').val( "0" );	
}

function formSubmitPreCheck()
{
	var longnotice;
	
	// created 13JAN2012-1130
	if( parseInt( $('#totalSlotsChosen').val() ) == 0 )
	{		
		$.fn.nextGenModal({
		   msgType: 'error',
		   title: 'are you kidding me',
		   message: 'Please enter at least one slot in at least one ticket class, else click Cancel to configure this event later.'
		});
		return false;		
	}
	if( $("div#tcg_shared_notice").size() > 0 ){
		longnotice = "This ticket class group is being shared with other showing times. So any change you made here will also apply to them.";
		longnotice += " <br/><br/>Would you like to create a new ticket class group for this showing time so that the changes <br/>";
		longnotice += " you made will only apply to this one?"
		$.fn.nextGenModal({
			   msgType: 'warning',
			   title: 'showing time conflict',
			   message: longnotice,
			   yesFunctionCall:  'enableNewCreation',			   
			   noFunctionCall: 'formSubmit',			   
			   closeOnChoose: false,
			   closeOnChooseNO: false
			});
	}else{
		formSubmit();
	}	
}

function formSubmit( )
{
	// created 7JAN2012-1547
	
	/*
		Try to submit ticket classes info first.
	*/

	$.fn.nextGenModal({
	   msgType: 'ajax',
	   title: 'processing',
	   message: 'Submitting ticket classes info ...'
	});
	// now, ajax-time!		
	var ticketClassPOST = $.ajax({	
		type: 'POST',
		url: $('form#formMain').attr('action'),
		timeout: 50000,
		data:  $('form#formMain').serialize(),
		success: function(data){
			disableNewCreation();
			var responseX = data.split('_');
			//Now try to submit seats				
			$.fn.nextGenModal({
			   msgType: 'ajax',
			   title: 'processing',
			   message: 'Submitting seat assignments ...'
			});
			var seatsPOST = $.ajax({	
				type: 'POST',
				url: $('form#seatAssignments').attr('action'),
				timeout: 72000,				
				// add seatmap id too since it's not within the form
				data:  $('form#seatAssignments').serialize() + '&seatmapUniqueID=' + $('input#seatMapPullDown').val() + '&_tcg=' + responseX[1],	
				success: function(data){
					if( data == "OKAY" )
					{
						window.location = CI.base_url + 'EventCtrl2/managetc_success';
					}else{
						alert('Response from server: ' + data);
					}
				}
			});	
			seatsPOST.fail( 
				function(jqXHR, textStatus) { 						
						$.fn.nextGenModal({
						   msgType: 'error',
						   title: 'Connection timeout',
						   message: 'It seems you have lost your internet connection. Please try again.<br/><br/><br/>' + textStatus
						});
						return false;
				}
			);
		}
	});	
	// fail handler
	ticketClassPOST.fail( 
		function(jqXHR, textStatus) { 				
				$.fn.nextGenModal({
						   msgType: 'error',
						   title: 'Connection timeout',
						   message: 'It seems you have lost your internet connection. Please try again.<br/><br/><br/>' + textStatus
				});
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
	$.fn.onloadCreateSeatMap = function(){
		var args = [];
		
		sumTotalSlots();
		args["seatMapUniqueID"] = $('input#seatMapPullDown').val();				
		createSeatmapOnPage( args );		
	};
	
	$.fn.onloadCreateSeatMap();
	
	$('#lassoWillDo').click( function(){
		var opt1 = "SELECT";
		var opt2 = "DESELECT";
		var currentVal = $(this).val();
		var newVal;
		
		newVal = ( currentVal == opt1 ) ? opt2 : opt1;
		$(this).val( newVal );
	});
			
	$('div[class="drop"]').click( function(){
		$('#warningIndicator').html( $(this).attr('style') );
		$('#warningIndicator').show();

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
		var seatMapUniqueID = $('input#seatMapPullDown').val();		
		var thisClassSlots;
		var thisClassAssignedSeatQuantity;
				
		thisClassSlots = parseInt( $( '#id_slot_' + thisClass).val() );
		thisClassAssignedSeatQuantity = parseInt( $( '#seatAssigned_' + thisClass).val() );
		$('#basic-modal-content-freeform').find('.items_selected').html( thisClassAssignedSeatQuantity );		
		if( isNaN( thisClassSlots ) || thisClassSlots < 1 )
		{
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'input needed',
			   message: 'Please enter at least 1 slot for ' + thisClass + ' class '
			});
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
						if( !$(this).hasClass('alreadyreserved') ) {							
							$(this).removeClass( 'drop_' + thisClass );
							$(this).removeClass('otherClass');	
							$(this).addClass('ddms_selected');
							$(this).addClass('ui-selected');
						}else{
							$(this).addClass('ar_sameclass');
						}
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
							$('#basic-modal-content-freeform').find('.ar_sameclass').each( function(){																
								if($(this).attr('id') != 'miscx' ) $(this).removeClass('ar_sameclass');
							});
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
		$.fn.nextGenModal({
		   msgType: 'okay',
		   title: 'Not yet :-)',
		   message: 'Feature coming later'
		});
	});
	
	$('input[id^="addSlots_"]').click( function() {	
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_slot_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		var retVal = true;
		
		$( selector_ChangeThis ).focus();
		if( !isInt( thisVal ) )
		{						
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid slot quantity.'
			});
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of slots.'
			});
			return false;
		}		
		if( parseInt(thisVal) == 0 ) 
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Minimum slot should be zero.'
			});
			return false;
		}
		if( $('input#seatAssigned_' + thisClass ).val() == thisVal )
		{		
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: "You have assigned " + thisVal + " seats for this class, so you cannot easily reduce the slots. Deselect some seats first and try again." 
			});
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of slots.'
			});
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of slots.'
			});
			return;
		}		
		if( parseInt(thisVal) == 0 ) 
		{		
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Minimum price is zero.'
			});
			return;
		}		
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[id^="addHoldingTime_"]').click( function() {
		var thisClass = giveMeClass( $(this).attr("id") );
		var selector_ChangeThis = "#id_HoldingTime_" + thisClass;		
		var thisVal = $( selector_ChangeThis ).val();		
		
		if( !isInt( thisVal ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of minutes for holding time.'
			});
			return false;
		}
		if( thisVal == 59 )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: "Maximum holding time is 59 minutes."
			});
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of minutes for holding time.'
			});
			return false;
		}		
		if( parseInt(thisVal) == 2 ) 
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: "Minimum holding time is two minutes."
			});
			return false;
		}
		$( selector_ChangeThis ).val( parseInt(thisVal) - 1);	
	});
	
	$('input[name^="price"]').blur( function()	{
		if( !isFloat( $(this).val() ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: "Price not valid."
			});
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		if( parseFloat( $(this).val() ) < 0 )
		{						
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: "Negative amount not allowed."
			});
			$(this).val( $('#lastFocus').val() );
			return false;
		}
		
	});
	
	$('input[name^="slot"]').blur( function(){				
		var thisClass = giveMeClass( $(this).attr("name") );
		var thisVal = $(this).val();
		var currentlyAssignedSeats;
		var longMsg;
		
		if( !isInt( thisVal ) )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'bad expectation',
			   message: "Invalid slot quantity."
			});
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		
		currentlyAssignedSeats = parseInt( $('input#seatAssigned_' + thisClass ).val() );
		if( currentlyAssignedSeats > parseInt(thisVal) )
		{
			longMsg = "You have assigned " + currentlyAssignedSeats + " seats for this class, so you cannot easily reduce the slots. Deselect some seats first and try again." ;
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'duh duh duh duhhhhhh',
			   message: longMsg
			});
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
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: 'Invalid number of minutes for holding time.'
			});
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}						
		if( parseInt(thisVal) < 2 ) 
		{
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: "Minimum holding time is two minutes."
			});
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
		if( parseInt(thisVal) >= 59 )
		{			
			$.fn.nextGenModal({
			   msgType: 'error',
			   title: 'error',
			   message: "Maximum holding time is 59 minutes." 
			});
			$(this).val( $('#lastFocus').val() );	// restore the former value
			return false;
		}
	}); //$('input[name^="holdingTime"]').blur(..)
	
	$("#buttonReset").click( function() {				
		window.location = CI.base_url + 'EventCtrl2/manageEvent';
	});
	
	$("#buttonOKspecial").click( function() {
		sumTotalSlots();
		formSubmitPreCheck();		
	});
	
	/*$('input[name^="slot"]').change(		
		$('input[name^="slot"]').blur();
	);*/
	
});