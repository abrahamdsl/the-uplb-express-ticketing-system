<?php
/**
*	Event and Showing Time Information Snippet ( PHP - NDX based )
* 	Created 21MAR2012-1319
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D
   
*	By the way, here are:  
	
	* the variables that should be present in the PHP page this is used.	
	$bookingInfo - MYSQL_OBJ - the cookie-on-server object
	
*/

if( !isset( $isActivityManageBooking  ) ) $isActivityManageBooking  = false;
?>

<div class="top">		
	<input type="hidden" id="startDate" value="<?php echo $bookingInfo->START_DATE ?>" />
	<input type="hidden" id="endDate" value="<?php echo $bookingInfo->END_DATE ?>" />
	<input type="hidden" id="startTime" value="<?php echo $bookingInfo->START_TIME ?>" />
	<input type="hidden" id="endTime" value="<?php echo $bookingInfo->END_TIME ?>" />
	<div class="start">
		<span class="deed" >
			Start
		</span>
		<span class="contentproper_time" >										
			<?php echo $bookingInfo->START_TIME;?>
		</span>
		<span class="contentproper_date" >
			<?php echo $bookingInfo->START_DATE; ?>										
		</span>
	</div>								
	<div class="end">
		<span class="deed" >
			End
		</span>									
		<span class="contentproper_time" >										
			<?php echo $bookingInfo->END_TIME;?>
		</span>
		<span class="contentproper_date" >
			<?php
				if( $bookingInfo->START_DATE != $bookingInfo->END_DATE ) echo $bookingInfo->END_DATE;
				else
					echo '&nbsp;';
			?>
		</span>
	</div>
</div>
<div class="bdtitle" >
	<?php echo $bookingInfo->EVENT_NAME; ?>
</div>
<div class="bottom">
	<?php echo $bookingInfo->EVENT_LOCATION; 
		  $slots = $bookingInfo->SLOT_QUANTITY;
	?>
	<br/>
	<br/>
	<?php 
		$stage = $this->clientsidedata_model->getSessionActivityStage();
		$is_tc_displayable = ( $stage === STAGE_MB2_SELECT_TICKETCLASS_FW or  $stage > STAGE_BOOK_2_FORWARD );
		if( !$isActivityManageBooking )
		{
	?>
			<p>
				<?php echo $slots; ?> Guest<?php if($slots > 1) echo 's'; ?>
			</p>
			<?php if( $is_tc_displayable ){ ?>
			<p>
				<?php echo $existingTCName; ?> Class
			</p>
			<?php } ?>
	<?php 
		}else{
	?>
	
			<p>
				You are booking <?php echo $slots; ?> ticket<?php if($slots > 1) echo 's'; ?>.
			</p>
			<table class="center_purest" >
			<?php if( $is_tc_displayable ){ ?>
				<tr>
					<td>Ticket class of your current booking:</td>
					<td> <?php echo $existingTCName; ?></td>
				</tr>
			<?php } ?>
				<tr>
					<td style="width: 85%;" >Total payments for your current booking:</td>
					<td>PHP <?php echo $existingPayments; ?></td>
				</tr>
			</table>
	<?php 
		}
	?>
</div>	