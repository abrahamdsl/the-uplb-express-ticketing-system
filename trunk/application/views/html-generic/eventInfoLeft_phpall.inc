<?php
/**
*	Event and Showing Time Information Snippet  - All PHP (i.e., date formatting to be done by PHP and not JavaScript)
* 	Created 21MAR2012-1319
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D

*	Specially designed for Manage Booking First step. 
 	
	* the variables that should be present in the PHP page this is used.	
	 (for all cases)
		$showtimeObj - MYSQL_OBJ - the showtime object - basically, entry from the `showing_time` table.
		$eventInfo   - MYSQL_OBJ - the event object - basically, entry from the `event` table.
		$existingTCName - STRING - Existing ticket class name, used when changing to another ticket class.
	 (if manage booking stage > initial page )
		$existingPayments - STRING/FLOAT - Current payments of the booking. Only used when $isActivityManageBooking is TRUE.
	
	* variables not required
	$isActivityManageBooking - BOOLEAN - obviously
	$topOtherClass - STRING - classes (CSS) in string separated by spaces for further customization of div.top
	$bottomOtherClass - STRING - classes (CSS) in string separated by spaces for further customization of div.bottom
	$slots - STRING/INT - how many slots/tickets are being booked.
*/

if( !isset( $isActivityManageBooking  ) ) $isActivityManageBooking  = false;
if( !isset( $topOtherClass ) ) $topOtherClass  = "";
if( !isset( $bottomOtherClass ) )  $bottomOtherClass = "";
?>

<div class="top <?php echo $topOtherClass; ?>">		
	<input type="hidden" id="startDate" value="<?php echo $showtimeObj->StartDate ?>" />
	<input type="hidden" id="endDate"   value="<?php echo $showtimeObj->EndDate ?>"   />
	<input type="hidden" id="startTime" value="<?php echo $showtimeObj->StartTime ?>" />
	<input type="hidden" id="endTime"   value="<?php echo $showtimeObj->EndTime ?>"   />
	<div class="start">
		<span class="deed" >
			Start
		</span>
		<span class="contentproper_time" >										
			<?php 
				/*
					No need to show seconds if zero
				*/
				$splitted = explode(':', $showtimeObj->StartTime);
				$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
				echo date( $timeFormat." A", strtotime($showtimeObj->StartTime)); 
			?>
		</span>
		<span class="contentproper_date" >
			<?php echo date( 'Y-M-d l', strtotime($showtimeObj->StartDate)); ?>			
		</span>
	</div>								
	<div class="end">
		<span class="deed" >
			End
		</span>									
		<span class="contentproper_time" >										
			<?php 
				/*
					No need to show seconds if zero
				*/
				$splitted = explode(':', $showtimeObj->EndTime);
				$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
				echo date( $timeFormat." A", strtotime($showtimeObj->EndTime));
			?>
		</span>
		<span class="contentproper_date" >
			<?php										
				if( $showtimeObj->StartDate != $showtimeObj->EndDate ) 
					// if show ends past midnight (red eye), then display the next day's date.
					echo date( 'Y-M-d l', strtotime($showtimeObj->EndDate));
				else
					echo '&nbsp;';
			?>
		</span>
	</div>
</div>
<div class="bdtitle" >
	<?php echo $eventInfo->Name; ?>
</div>
<div class="bottom <?php echo $bottomOtherClass; ?>">
	<?php echo $eventInfo->Location; ?>
	<br/>
	<br/>		
	<?php if( $isActivityManageBooking ) {
		if( $this->clientsidedata_model->getSessionActivityStage() == 0 )
		{
	?>
			<p>
				<?php echo $slots; ?> Guest<?php if($slots > 1) echo 's'; ?>
			</p>
			<p>
				<?php echo $existingTCName; ?> Class
			</p>
	<?php 
		}else{
	?>
			<p>
				You are booking <?php echo $slots; ?> ticket<?php if($slots > 1) echo 's'; ?>.
			</p>
			<table class="center_purest" >
				<tr>
					<td>Ticket class of your current booking:</td>
					<td> <?php echo $existingTCName; ?></td>
				</tr>
				<tr>
					<td style="width: 85%;" >Total payments for your current booking:</td>
					<td> PHP <?php // echo $existingPayments; ?></td>
				</tr>								
			</table>
	<?php }
		}
	?>
</div>	