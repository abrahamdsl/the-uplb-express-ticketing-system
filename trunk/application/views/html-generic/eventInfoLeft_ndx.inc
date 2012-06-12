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
	<?php echo $bookingInfo->EVENT_LOCATION; ?>
	<br/>
	<br/>	
	<p>
		You are booking <?php echo $bookingInfo->SLOT_QUANTITY; ?> ticket<?php if($bookingInfo->SLOT_QUANTITY > 1) echo 's'; ?>.
	</p>
	<?php if( $isActivityManageBooking ) {?>
	<table class="center_purest" >
		<tr>
			<td>Ticket class of your current booking:</td>
			<td> <?php echo $existingTCName; ?></td>
		</tr>
		<tr>
			<td style="width: 85%;" >Total payments for your current booking:</td>
			<td>PHP <?php echo $existingPayments; ?></td>
		</tr>								
	</table>
	<?php } ?>
</div>	