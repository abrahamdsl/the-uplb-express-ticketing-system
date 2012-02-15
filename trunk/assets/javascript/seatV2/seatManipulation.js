var aisles = new Array();
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
		$('table.textUnselectable div#top_' + aisleIndex).html( 'A' );
		$('table.textUnselectable div#bottom_' + aisleIndex).html( 'A' );
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
}

function isColAlreadyListed( value, low, high ){
       var mid;
	   
	   if (high < low) return false; // not found
       mid = (low + high) / 2;
       if (aisles[mid] > value)
           return isColAlreadyListed(value, low, mid-1);
       else if (aisles[mid] < value)
           return isColAlreadyListed(value, mid+1, high);
       else
           return true; // found
}

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
				var ticketClassBeingBooked;	// for use only during book Step 4.
				
				$("#seatSelectionTable").children().remove();	// remove all content first
				detailBranch = $(xmlData).find( 'details' );	// get detail branch of XML				
				actualSeats = $(xmlData).find( 'dataproper' );	// get the dataproper branch								
				
				//now, if this is a master seat plan data (i.e., for create event step 5, do the ff)				
				masterMap = ( detailBranch.find('mastermap').text() == "1" ) ? true : false;				
				
					rows = parseInt( detailBranch.find('rows').text() );
					cols = parseInt( detailBranch.find('cols').text() );
				if( masterMap ){
					// these details are limited to create event step 5.
					usableCapacity = parseInt( detailBranch.find('usableCapacity').text() );				
					hallName = detailBranch.find('name').text();								
					$("span#hallSeatingCapacity").html( usableCapacity );
					$("span#place").html( hallName );
				}else{
					// means, we are in booking - picking  a seat
					ticketClassBeingBooked = getCookie( 'ticketClassUniqueID' ); 
				}
				
				/* insert the inner div on which we will attach the drag_drop_multi_select() function, and then the table
				*/
				$("div#seatSelectionTable").append('<div class="list" id="innerSeatDiv" style="overflow:none;" ><table class="textUnselectable"></table></div>');	
				
				// insert so much rows, +2 than the num of rows of the seats, for the top and bottom column guides
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
				
				actualSeats.find('seat').each( function(){		
					/*
						09FEB2012-0214 : In connection with Issue 25 in Google Code / "Why still compute the rows and cols when it's in the XML?".
						So I configured it to get the rows and cols from the XML. 
						
						However, if we have the XML structure
						*************************************
							<seat x="i" y"j" >
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
					if( masterMap === false ){						
						if( ticketClassBeingBooked != seatClass )
						{
							concernedDiv.addClass( 'otherClass' );
							concernedDiv.addClass( 'classRestricted' );
							concernedDiv.removeClass( 'dropAvailable' );
						}else{
							if( status == 1 ){
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
				
				// now call the function to be able to select these items
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
				
				// adjust column indicators to make way for aisles
				adjustColumnIndicators( rows, cols );
				nullifyAisleCount();
				hideOverlay();
				alreadyConfiguredSeat = true;				
			}
						
		});