<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Manage Booking";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->				
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking01.css'; ?>"/>		
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/manageBooking01.js'; ?>" ></script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<script type="text/javascript" >		
		$(document).ready( function(){
			$('a.notyet').click( function(){
				$.fn.nextGenModal({
				   msgType: 'okay',
				   title: 'Not yet :-)',
				   message: 'Feature coming later'
				});
			});
		});
	</script>
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
?>	
<div id="main_container">
	<div id="header">    	    	        
		<?php
			$this->load->view('html-generic/headerimage.inc');
		?>
        <?php
			$this->load->view('html-generic/menu-bar.inc');
		?>		
		<?php
			$this->load->view('html-generic/userInfo-bar.inc');
		?>			
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer" class="homepageSpecial" > 
			<div id="page_title">
				Manage Booking
			</div>
			<div style="padding-left:10px; clear: both">
				Please select the booking you want to modify and click the tile specifying
				what operation you want to do. Only unused bookings are shown so far.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >
			<?php				
				if( $bookings === false )
				{
					//
			?>			
			<?php
				}else{
					foreach( $bookings as $singleBooking )
					{
					$displayThis = $singleBooking->bookingNumber;
					$displayThis .= "&nbsp;&nbsp;|&nbsp;&nbsp;".$singleBooking->Name;
					/*$displayThis .= " | Starts at ".$singleBooking->StartDate;
					$displayThis .= " ".$singleBooking->StartTime;
					$displayThis .= " | Ends at ".$singleBooking->EndDate;
					$displayThis .= " ".$singleBooking->EndTime;
					$displayThis .= " | ".$singleBooking->Location;*/
			?>				
				<h3 id="h_<?php echo $singleBooking->bookingNumber; ?>"><a href="#"><?php echo $displayThis ?></a></h3>
				<div id="<?php echo $singleBooking->bookingNumber; ?>" class="section" >
					<?php
						if(  $this->Booking_model->isBookingUpForChange( $singleBooking ) ) {
					?>
					<div style="width: 100%; border: 2px solid red; padding: 10px;  font-size: 1.2em; margin-bottom: 10px;" > 
						This booking is up for confirmation/payment. Click "View Details" to see more information.
					</div>
					<br/>
					<?php
						}else 
						if(  $this->Booking_model->isBookingRolledBack( $singleBooking ) ) {
					?>
					<div style="width: 100%; border: 2px solid red; padding: 10px;  font-size: 1.2em; margin-bottom: 10px;" > 
						The changes to this booking has been reverted because you haven't paid your dues before the deadline.
						Click here to see more information.
					</div>
					<?php 
							// since user was notified already, then clear the `Status2` table by calling this
							$this->Booking_model->markAsPaid( $singleBooking->bookingNumber );	
						} 
					?>
					<div class="bookingDetails">						
						<div class="top">		
								<input type="hidden" id="_startDate" value="<?php echo $singleBooking->StartDate ?>" />
								<input type="hidden" id="_endDate" value="<?php echo $singleBooking->EndDate ?>" />
								<input type="hidden" id="_startTime" value="<?php echo $singleBooking->StartTime ?>" />
								<input type="hidden" id="_endTime" value="<?php echo $singleBooking->EndTime ?>" />
								<div class="start">
									<span class="deed" >
										Start
									</span>
									<span class="contentproper_time" >										
										<?php 
											/*
												No need to show seconds if zero
											*/
											$splitted = explode(':', $singleBooking->StartTime);
											$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
											echo date( $timeFormat." A", strtotime($singleBooking->StartTime)); 
										?>
									</span>
									<span class="contentproper_date" >
										<?php echo date( 'Y-M-d l', strtotime($singleBooking->StartDate)); ?>										
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
											$splitted = explode(':', $singleBooking->EndTime);
											$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
											echo date( $timeFormat." A", strtotime($singleBooking->EndTime));
										?>
									</span>
									<span class="contentproper_date" >
										<?php										
											if( $singleBooking->StartDate != $singleBooking->EndDate ) 
												// if show ends past midnight (red eye), then display the next day's date.
												echo date( 'Y-M-d l', strtotime($singleBooking->EndDate));
											else
												echo '&nbsp';
										?>
									</span>
								</div>
							</div>
							<div class="bdtitle" >
								<?php echo $singleBooking->Name; ?>
							</div>
							<div class="bottom bottomspecialOnMB01">
								<?php echo $singleBooking->Location; ?>
								<br/>
								<br/>
								<?php
									$slots =  $guestCount[ $singleBooking->bookingNumber ];
								?>
								
									<p>
									<?php echo $slots; ?> Guest<?php if($slots > 1) echo 's'; ?>
									</p>
									<p>
									<?php
										echo $ticketClassesName[ $singleBooking->EventID ][ $singleBooking->TicketClassGroupID ][ $singleBooking->TicketClassUniqueID ];										
									?> 
									Class
									</p>
								
							</div>
					</div>
					<div class="containingClassTable">
						<?php
						if(  $this->Booking_model->isBookingUpForChange( $singleBooking ) ) {
						?>
							<div class="metrotile" name="viewdetails" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" /></a>																						
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_pendingchange_viewdetails'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
							<div class="metrotile" name="cancelchanges" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelchanges.png" alt="Cancel changes" /></a>																						
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_cancelchanges'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
						<?php } else { ?>
						<div class="metrotile" name="changeshowingtime" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeshowingtime.png" alt="Change showing time" /></a>																						
								<form method="post" action="<?php echo base_url().'EventCtrl/manageBooking_changeShowingTime'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
						</div>
						<div class="metrotile" name="upgradeticketclass" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-upgradeticketclass.png" alt="Upgrade Ticket Class" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_upgradeticketclass'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
						</div>					
						<div class="metrotile" name="changeseat" >
								<a href="#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeseat.png" alt="Change Seat" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/manageBooking_changeSeat'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
						</div>					
						<div class="metrotile" name="cancel" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelbooking.png" alt="Cancel Booking" /></a>								
						</div>
						<?php } ?>
						<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
						
					</div>
				</div>
			<?php
					}//foreach
				}//else				
			?>
				
			</div>
			<!-- accordion end -->
			<div id="accordion2" class="specialOnMB01" >
				<?php
					$this->load->view( 'html-generic/nobooking.inc' );
				?>
			</div>
			<div style=" clear:both;"></div>
		</div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>