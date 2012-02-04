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
			concernedDiv_top = $('table.textUnselectable div#top_' + aisleIndex );
			concernedDiv_col = $('table.textUnselectable div[id$="_' + aisleIndex + '"] span.col' );
			concernedDiv_bottom = $('table.textUnselectable div#bottom_' + aisleIndex );
			adjustThis = concernedDiv_top.html();						
			if( adjustThis != 'A' ){
				adjustThis = parseInt( adjustThis );
				adjustThis--;
				concernedDiv_top.html( adjustThis );
				concernedDiv_col.html( adjustThis );
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
	
			//lasso functionality manipulator
			$('#lassoWillDo').click( function(){
				var opt1 = "SELECT";
				var opt2 = "DESELECT";
				var currentVal = $(this).val();
				var newVal;
				
				newVal = ( currentVal == opt1 ) ? opt2 : opt1;
				$(this).val( newVal );
			});
			
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
				
				$("#seatSelectionTable").children().remove();	// remove all content first
				detailBranch = $(xmlData).find( 'details' );	// get detail branch of XML				
				actualSeats = $(xmlData).find( 'dataproper' );	// get the dataproper branch								
				rows = parseInt( detailBranch.find('rows').text() );
				cols = parseInt( detailBranch.find('cols').text() );
				usableCapacity = parseInt( detailBranch.find('usableCapacity').text() );				
				hallName = detailBranch.find('name').text();								
				$("span#hallSeatingCapacity").html( usableCapacity );
				$("span#place").html( hallName );
				
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
						$(allTRs[x]).append('<td><div id="' + parseInt(x-1) + '_' + y+ '"  class="drop dropAvailable textUnselectable ui-selectable"><span class="row" >' + itoa(letter) + '</span><span class="col" >'+ (y+1) + ' </span><input type="hidden" name="seat_' + parseInt(x-1) + '-' + y+ '" class="seatClass" value="0"  \/></div></td>');
					}
				}
				// then, the right indicators
				for(y=1, letter=65;y<=rows;y++, letter++){
						$(allTRs[y]).append('<td><div class="guide left" id="right_' + y + '" >' + itoa(letter) +'</div></td>');	// itoa() found in generalChecks.js					
				}
				
				actualSeats.find('seat').each( function(){					
					var x = $(this).attr('x');
					var y = $(this).attr('y');
					var status = parseInt( $(this).find( 'status' ).text() );
					var concernedDiv = $('div#' + x + '_' + y);
					concernedDiv.children('input.seatClass').val( status );
					if( status == -1 ){
						addNewAisleToList( y );
						concernedDiv.removeClass( 'ui-selectable' );
						concernedDiv.removeClass( 'ui-selectee' );
						concernedDiv.removeClass( 'ui-selected' );
						concernedDiv.removeClass( 'dropAvailable' );
						concernedDiv.addClass( 'dropUnavailable' );
					}			
				});
				
				// now call the function to be able to select these items
				$('div#innerSeatDiv').drag_drop_multi_select({					
					element_to_drag_drop_select:'div.dropAvailable',
					elements_to_drop:'.list',
					elements_to_cancel_select:'div.otherClass, .title, div.dropUnavailable',
					element_to_show_remainingSelectableSeatForClass: '#remainingSelectableSeatsForClass',
					element_to_show_whileSelecting: '#whileSelectingIndicator',
					element_to_show_FinishResult: '.items_selected',
					element_to_show_Warning: '#warningIndicator',
					otherClassIndicator: 'otherClass',
					maxSeatsForClass: '#maxSeatsForClass',
					lasso_indicator: '#lassoWillDo',					
					retainPreviouslySelected: true
				});
				
				// adjust column indicators to make way for aisles
				adjustColumnIndicators( rows, cols );
				nullifyAisleCount();
				// 
			}
			
			$('#armageddon').click( function(){
				$(document).manipulateSeatAJAX( '' );			
			});
		});