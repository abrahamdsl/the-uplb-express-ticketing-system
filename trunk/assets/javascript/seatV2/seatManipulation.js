/*
17FEB2012-1242: I am thinking of merging this with CreateEvent_005.js

Requires that cookie 'slots_being_booked' accessible (i.e., unencrypted)
*/


var aisles = new Array();
var existingGuestSeatData = new Array();
var aisleCount = 0;

function addNewAisleToList( colNum )
{
	/*
		03FEB2012-2047
		
		Parameter 'colNum' is zero-indexed
	*/
	if( isColAlreadyListed( colNum, 0, aisles.length-1 ) !== false){		
		return false;	
	}
	aisles[ aisleCount++ ] = colNum;		// add now	
	aisles.sort( sortNumber ); 				//sort by descending
}

function adjustColumnIndicators( rows, cols )
{
	/*
		Created 03FEB2012-2051
	*/
	var y = aisles.length;
	var x;
	var adjustThis;
	var concernedDiv_top;
	var concernedDiv_bottom;	
	for( x = 0; x < y; x++ )
	{		
		aisleIndex = aisles[x];		
		$('table.textUnselectable div#top_' + aisleIndex).html( 'A' );		// mark them as aisle
		$('table.textUnselectable div#bottom_' + aisleIndex).html( 'A' );
		// from the div to the right until end, adjust
		for( ++aisleIndex; aisleIndex < cols; aisleIndex++ )
		{
			concernedDiv_top = $('table.textUnselectable div#top_' + aisleIndex );			// get handle to top indicator
			concernedDiv_bottom = $('table.textUnselectable div#bottom_' + aisleIndex );	// get handle to bottom indicator
			adjustThis = concernedDiv_top.html();											// get current content
			if( adjustThis != 'A' ){
				/* so if not aisle already, get current value, parse it to integer, decrement by one
					and replace the top indicators concerned with this new value
				*/
				adjustThis = parseInt( adjustThis );
				adjustThis--;
				concernedDiv_top.html( adjustThis );				
				concernedDiv_bottom.html( adjustThis );
			}
		}
	}
}//adjustColumnIndicators

function assembleExistingGuestSeatData(){
	/*
		Created 03MAR2012-1228
		Only used during Change Booking - Change seat.
		
		Gets existing seat data from the page, stores it in
		the globally accessible Array 'existingGuestSeatData'
		
		Needs JQuery.
	*/	
	var guestCount = parseInt( $('input#_js_use_slots').val() ); 
	var x;
	var y;
	
	for( x = 1; x <= guestCount; x++ )
	{
		var guestUUID = $( 'input[name="g' + x + '_uuid"]' ).val();
		var matrixRep = $( 'input[name="g' + x + '_seatMatrix"]' ).val();
		if( matrixRep !== "0" )
		{
			existingGuestSeatData[ x-1 ] = new Array();
			existingGuestSeatData[ x-1 ]['matrixRep'] =  matrixRep;
			existingGuestSeatData[ x-1 ]['uuid'] =  guestUUID;
		}
	}
}//assembleExistingGuestSeatData(..)

function isSeatUsedByThisBooking( matrix_x, matrix_y ){
	/*
		Created 03MAR2012-1237
		
		Checks if the seat specified by the matrix indicators, belongs
		to the guests in this booking.
	*/
	var y = existingGuestSeatData.length;
	var x;
	var compareThis = matrix_x + "_" + matrix_y;
	
	for( x = 0; x<y; x++ )
	{
		if( existingGuestSeatData[x]['matrixRep'] == compareThis ) return true;
	}
	return false;
}//isSeatUsedByThisBooking(..)

function makeSeatUsedByThisBookingAvailable(){
	/*
		Created 03MAR2012-1250
		
		Removes the 'occupiedSameClass' tag on the seats used by this booking
	*/
	var y = existingGuestSeatData.length;
	var x;	
	var divID;
	
	for( x = 0; x<y; x++ )
	{
		guestConcerned = ( x + 1 );
		divID = existingGuestSeatData[x]['matrixRep'];		
		if( $('div#seatSelectionTable div#' + divID).hasClass('otherClass') === false )
		{
			$('div#seatSelectionTable div#' + divID).removeClass( 'occupiedSameClass' );		
			$('div#seatSelectionTable div#' + divID).trigger('click.ddms_item_clicked');
			manipulateGuestSeat( "SELECT", divID );
			$('div#seatSelectionTable div#' + divID).addClass( 'otherGuest' );		
			//postChooseCleanup();		
		}
	}
}// makeSeatUsedByThisBooking(..)

function isColAlreadyListed( value, low, high ){
		/*
			Basically just binary-searches if the aisle specified is already in the array.
		*/
       var mid;
	   
	   if (high < low) return false; // not found
       mid = (low + high) / 2;
       if (aisles[mid] > value)
           return isColAlreadyListed(value, low, mid-1);
       else if (aisles[mid] < value)
           return isColAlreadyListed(value, mid+1, high);
       else
           return true; // found
}// isColAlreadyListed

function nullifyAisleCount()
{
	aisles = null;
	aisles = new Array();
	aisleCount = 0;
}

function sortNumber(a,b)
{
	return b - a;
}

$(document).ready( function(){
	
			//lasso functionality manipulator moved to createEvent_005.js 13FEB2012-1206
						
			// overlayv2 button
			$('#overlayV2Button_OK').click( function(){					
				$('a.modalCloseImg').click();
			});
				
			//Set the default function
			$.fn.drag_drop_multi_select.defaults.after_drop_action = function($item,$old,$new,e,ui){
				// 17FEB2012-1244 : I see no apparent use or I just forgot.
				// Possible param $item_instance, $old_container, $new_container, event, helper
				var $target = $(e.target);
				$target.find('ul').append($item);
			};
							
			$.fn.manipulateSeatAJAX = function( xmlData ){
				 
				var rows;
				var cols;
				var x;
				var y;
				var detailBranch;
				var actualSeats; 
				var totalCapacity;
				var hallName;
				var masterMap;
				var managetc;
				var ticketClassBeingBooked;	// for use only during book Step 4.
				var isModeManageBookingChooseSeat;
				
				/*
					Added 03MAR2012-1221. This determines if the reason why we are in this page
					is the user already booked and he wants to change seats. This variable
					is important as later, in this function, its value will be accessed to see to
					remove the "occuppied" status on the seats of the guests on this booking.
					
					Also found in bookStep4.js
				*/
				isModeManageBookingChooseSeat = ( $( 'input#manageBookingChooseSeat' ).size() == 1 && $( 'input#manageBookingChooseSeat' ).val() == "1" );								
				if( isModeManageBookingChooseSeat ) assembleExistingGuestSeatData();
								
				$("#seatSelectionTable").children().remove();	// remove all content first
				detailBranch = $(xmlData).find( 'details' );	// get detail branch of XML				
				actualSeats = $(xmlData).find( 'dataproper' );	// get the dataproper branch								
								
				//now, if this is a master seat plan data (i.e., for create event step 5, do the ff)				
				masterMap = ( detailBranch.find('mastermap').text() == "1" ) ? true : false;								
				// or we are managing ticket classes
				managetc  = (typeof tcg_not_shared === "undefined" ) ? false : true;
				rows = parseInt( detailBranch.find('rows').text() );
				cols = parseInt( detailBranch.find('cols').text() );
				if( masterMap || managetc ){
					// these details are limited to create event step 5.
					usableCapacity = parseInt( detailBranch.find('usableCapacity').text() );				
					hallName = detailBranch.find('name').text();
					$("span#hallSeatingCapacity").html( usableCapacity );
					$("span#place").html( hallName );
				}else{
					// means, we are in booking - picking  a seat					
					ticketClassBeingBooked = parseInt( $('input#_js_use_ticketClassUniqueID').val() );  
				}
				
				/* insert the inner div on which we will attach the drag_drop_multi_select() function, and then the table
				*/
				$("div#seatSelectionTable").append('<div class="list" id="innerSeatDiv"><table class="textUnselectable"></table></div>');	
				
				// insert so much rows, +2 than the num of rows of the seats, so there's the space for the top and bottom column guides
				for(x=0; x<rows+2; x++)
				{
					$("div#seatSelectionTable div#innerSeatDiv table").append('<tr></tr>');				
				}
				// now get all TRs, so we can insert the elements concerned
				var allTRs = $("div#seatSelectionTable table tr")
				// for now, put the top and bottom indicators
				// -- dummy first (the edges of the 'rectangle')
				$(allTRs[0]).append('<td></td>');
				$(allTRs[rows+1]).append('<td></td>');
				// -- the actual guides
				for(y=1;y<=cols;y++){
						$(allTRs[0]).append('<td><div class="guide top" id="top_' + parseInt(y-1) + '" >' + y +'</div></td>');
						$(allTRs[rows+1]).append('<td><div class="guide bottom" id="bottom_' + parseInt(y-1) + '" >' + y +'</div></td>');
				}
				// then, the left indicators
				for(y=1, letter=65;y<=rows;y++, letter++){
						$(allTRs[y]).append('<td><div class="guide left" id="left_' + y + '" >' + itoa(letter) +'</div></td>');	// itoa() found in generalChecks.js
				}
				// now, the actual seat divs
				for(x=1, letter=65; x<rows+1; x++, letter++ )
				{
					for(y=0;y<cols;y++){
						$(allTRs[x]).append('<td><div id="' + parseInt(x-1) + '_' + y+ '"  class="drop dropAvailable textUnselectable ui-selectable"><span class="row" >' + '</span><span class="col" >'+  ' </span><input type="hidden" name="seat_' + parseInt(x-1) + '-' + y+ '" class="seatClass" value="0"  /></div></td>');
					}
				}
				// then, the right indicators
				for(y=1, letter=65;y<=rows;y++, letter++){
						$(allTRs[y]).append('<td><div class="guide left" id="right_' + y + '" >' + itoa(letter) +'</div></td>');	// itoa() found in generalChecks.js					
				}
				// now do put the actual seats - represented by divs
				actualSeats.find('seat').each( function(){		
					/*
						09FEB2012-0214 : In connection with Issue 25 in Google Code / "Why still compute the rows and cols when it's in the XML?".
						So I configured it to get the rows and cols from the XML. 
						
						However, if we have the XML structure
						*************************************
							<seat x="i" y="j" >
								<row>a</row>
								<col>b</col>
								<status>c</col>
								<comment></comment>
							</seat>
						************************************
						and therefore in this function to get col, we have to have this command
						****************************************
								$(this).find( 'col' ).text();
						****************************************						
						.. then IT DOES NOT WORK. However, in the XML, when I change <col> to <colx>, it works!
						I've tested it twice, in Google Chrome 11, for the meantime. Why is it so?
					*/					
					var x = $(this).attr('x');
					var y = $(this).attr('y');					
					var row = $(this).find('row').text();
					var colx = $(this).find( 'colX' ).text();
					var status = parseInt( $(this).find( 'status' ).text() );
					var seatClass = parseInt( $(this).find( 'tClass' ).text() );
					var concernedDiv = $('div#' + x + '_' + y);					
					concernedDiv.children('span.row').html( row );
					concernedDiv.children('span.col').html( colx );					
					if( masterMap === false  ){						
						if( ticketClassBeingBooked != seatClass  )
						{								
							if( !managetc ){
								concernedDiv.addClass( 'otherClass' );
								concernedDiv.addClass( 'classRestricted' );
								concernedDiv.removeClass( 'dropAvailable' );
							}else
							{	
								var parseIntResult = parseInt( seatClass );
								if( parseIntResult !== false && parseIntResult > 0  ) {
									var className = $('input#classname_' + seatClass ).val();
									concernedDiv.addClass( 'drop_' + className );							
									if( status == 1 || status == -4 ){										
										concernedDiv.addClass( 'alreadyreserved' );									
										concernedDiv.removeClass( 'ui-selectable' );
										concernedDiv.removeClass( 'dropAvailable' );
										concernedDiv.removeClass( 'ddms_selected' );
										concernedDiv.removeClass( 'ui-selected' );										
									}else{										
										concernedDiv.find('input.seatClass').val( className );
									}
									concernedDiv.addClass( 'otherClass' );
								}else{
									concernedDiv.find('input.seatClass').val( '-1' );
								}
							}
						}else{
							/*
								Modified 06MAR2012-1705. Added new status "-4" - seat is on hold
								during manage booking (pending confirmation).
							*/
							if( status == 1 || status == -4 ){
								concernedDiv.addClass( 'occupiedSameClass' );								
							}
							
						}
					}	
					if( status == -1 ){
						addNewAisleToList( y );
						concernedDiv.removeClass( 'ui-selectable' );
						concernedDiv.removeClass( 'ui-selectee' );
						concernedDiv.removeClass( 'ui-selected' );
						concernedDiv.removeClass( 'dropAvailable' );
						concernedDiv.addClass( 'dropUnavailable' );
						concernedDiv.children("input.seatClass").val( "-1" );
					}										
				});
				/*
					Remove the occupied mark from the seats that belongs to this booking.					
				*/
				if( isModeManageBookingChooseSeat ) makeSeatUsedByThisBookingAvailable();
				/*  
					Now call the function that makes the seat operations possible.
				*/
				$('div#innerSeatDiv').drag_drop_multi_select({					
					element_to_drag_drop_select:'div.dropAvailable',
					elements_to_drop:'.list',
					elements_to_cancel_select:'div.otherClass, .title, div.dropUnavailable, div.otherGuest',
					element_to_show_remainingSelectableSeatForClass: '#remainingSelectableSeatsForClass',
					element_to_show_whileSelecting: '#whileSelectingIndicator',
					element_to_show_FinishResult: '.items_selected',
					element_to_show_Warning: '#warningIndicator',
					otherClassIndicator: 'otherClass',
					maxSeatsForClass: '#maxSeatsForClass',
					lassoAvailable: masterMap,
					lasso_indicator: '#lassoWillDo',					
					retainPreviouslySelected: true					
				});
				//
				$('input[type="hidden"][id^="classname_"]').each( function(){
					var sub_thisclass = $(this).val();
					var count = $('#basic-modal-content-freeform').find('div.drop_'+sub_thisclass).size();					
					$( 'input#seatAssigned_' + sub_thisclass).val( count );
				});
				// adjust column indicators to make way for aisles
				adjustColumnIndicators( rows, cols );
				nullifyAisleCount();
				$.fn.nextGenModal.hideModal();
				alreadyConfiguredSeat = true;				
			}
						
		});