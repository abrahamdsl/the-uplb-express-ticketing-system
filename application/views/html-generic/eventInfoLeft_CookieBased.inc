<?php
/**
*	Event and Showing Time Information Snippet - Cookie-based
* 	Created 21MAR2012-1351
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
*/
?>
<div class="top">		
	<input type="hidden" id="startDate" value="<?php echo $this->input->cookie( 'startDate' ); ?>" />
	<input type="hidden" id="endDate" value="<?php echo $this->input->cookie( 'endDate' ); ?>" />
	<input type="hidden" id="startTime" value="<?php echo $this->input->cookie( 'startTime' ); ?>" />
	<input type="hidden" id="endTime" value="<?php echo $this->input->cookie( 'endTime' ); ?>" />
	<div class="start">
		<span class="deed" >
			Start
		</span>
		<span class="contentproper_time" >										
			<?php echo $this->input->cookie( 'startTime' ); ?>
		</span>
		<span class="contentproper_date" >
			<?php echo $this->input->cookie( 'startDate' ); ?>										
		</span>
	</div>								
	<div class="end">
		<span class="deed" >
			End
		</span>									
		<span class="contentproper_time" >										
			<?php echo $this->input->cookie( 'endTime' );  ?>
		</span>
		<span class="contentproper_date" >
			<?php
				if( $this->input->cookie( 'startDate' ) != $this->input->cookie( 'endDate' ) ) echo $this->input->cookie( 'endDate' );
				else
					echo '&nbsp';
			?>
		</span>
	</div>
</div>
<div class="bdtitle" >
	<?php echo $this->input->cookie( 'eventName' ); ?>
</div>
<div class="bottom">
	<?php echo $this->input->cookie( 'location' ); ?>
	<br/>
	<br/>
	<p>
		You are booking <?php echo SLOTS; ?> ticket<?php if(SLOTS > 1) echo 's'; ?>.
	</p>
</div>