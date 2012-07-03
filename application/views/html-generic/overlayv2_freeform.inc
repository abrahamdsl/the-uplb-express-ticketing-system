<?php
/*	
	This is used in manage ticket class if (isset($tcg_not_shared) ) === TRUE
*/
$managetc = isset($tcg_not_shared);
$submiturl = ($managetc ) ? 'eventctrl2/managetc_update_seats' : 'eventctrl/create_step6_seats';
?>
<div id="basic-modal-content-freeform" >
	<input type="hidden" name="uniqueID" id="seatMapUID" value="0" />
	<input type="hidden" name="seatMap_isMaster" id="seatMap_isMaster_indicator" value="0" />
	<div id="SeatV2Title" ><span id="titleProper" >Assign seats to <span id="seatClass"></span> class</span><span id="place" ></span></div>
	<div id="whileSelectingIndicator" >Selecting <span id="quantityBeingSelected" class="items_being_selected" >0</span> seats out of <span id="remainingSelectableSeatsForClass"></span> max</div>	
	<div id="warningIndicator" ></div>
	<div id="seatSelectionLegend"  >
		<span id="available" > Available / Still unassigned </span>		
		<span id="assigned" > Assigned seats for this class </span>
		<?php if( $managetc  ) { ?>  <span class="alreadyreserved">Already reserved (other class)</span> <?php } ?>
		<?php if( $managetc  ) { ?>  <span id="miscx" class="alreadyreserved ar_sameclass">Already reserved (same class)</span> <?php } ?>
		<span> <!--id="occupiedByOther"-->  Any other color means occupied by other classes </span>
	</div>	
		<div id="lassoInfoHolder" >
			Lasso tool will <input type="button" class="ayokongDefaultAngItsuraNgButton" value="SELECT" id="lassoWillDo" /> 
		</div>	
	<form id="seatAssignments" method="post" action="<?php echo base_url().$submiturl; ?>" >
		<div id="seatSelectionTable"  class="center_purest">
			
		</div>
	</form>
	<div id="totalSeatChosen" >
		Total Assigned for this class: <span class="items_selected" >0</span><br/>
		Total Allowed for this class: <span id="maxSeatsForClass" ></span><br/>
		Total Seat Capacity: <span id="hallSeatingCapacity" ></span><br/>
	</div>
	<div id="overlayV2ButtonsArea" >							
							<a class="button overlayV2Button" id="overlayV2Button_OK" ><span class="icon">okay</span></a>																					
	</div>	
</div>