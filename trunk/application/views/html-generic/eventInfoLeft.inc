<?php
/**
*	Event and Showing Time Information Snippet
* 	Created 21MAR2012-1319
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D
   
*	By the way, here are:  
	* CONSTANTS declared at the top of the page this is used, via define(.., ..)
	SLOTS - STRING/INT - how many slots/tickets are being booked.
	
	* the variables that should be present in the PHP page this is used.	
	$showtimeObj - MYSQL_OBJ - the showtime object - basically, entry from the `showing_time` table.
	$eventInfo   - MYSQL_OBJ - the event object - basically, entry from the `event` table.
	$isActivityManageBooking - BOOLEAN - obviously
	$existingTCName - STRING - Existing ticket class name, used when changing to another ticket class.
	$existingPayments - STRING/FLOAT - Current payments of the booking. Only used when $isActivityManageBooking is TRUE.
	
*/

if( !isset( $isActivityManageBooking  ) ) $isActivityManageBooking  = false;
?>

<div class="top">		
	<input type="hidden" id="startDate" value="<?php echo $showtimeObj->StartDate ?>" />
	<input type="hidden" id="endDate" value="<?php echo $showtimeObj->EndDate ?>" />
	<input type="hidden" id="startTime" value="<?php echo $showtimeObj->StartTime ?>" />
	<input type="hidden" id="endTime" value="<?php echo $showtimeObj->EndTime ?>" />
	<div class="start">
		<span class="deed" >
			Start
		</span>
		<span class="contentproper_time" >										
			<?php echo $showtimeObj->StartTime;?>
		</span>
		<span class="contentproper_date" >
			<?php echo $showtimeObj->StartDate; ?>										
		</span>
	</div>								
	<div class="end">
		<span class="deed" >
			End
		</span>									
		<span class="contentproper_time" >										
			<?php echo $showtimeObj->EndTime;?>
		</span>
		<span class="contentproper_date" >
			<?php
				if( $showtimeObj->StartDate != $showtimeObj->EndDate ) echo $showtimeObj->EndDate;
				else
					echo '&nbsp';
			?>
		</span>
	</div>
</div>
<div class="bdtitle" >
	<?php echo $eventInfo->Name; ?>
</div>
<div class="bottom">
	<?php echo $eventInfo->Location; ?>
	<br/>
	<br/>	
	<p>
		You are booking <?php echo SLOTS; ?> ticket<?php if(SLOTS > 1) echo 's'; ?>.
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