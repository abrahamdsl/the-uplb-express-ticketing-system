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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/metrotile_colors_basic.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking01.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>	
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
				{ // nothing to do
			?>
			<?php
				}else{
					foreach( $bookings as $singleBooking )
					{ 
						$isExpired   	 = $this->Booking_model->isBookingExpired( $singleBooking );
						$isBeingBooked   = $this->Booking_model->isBookingBeingBooked( $singleBooking );
						$isExpired_State = ( $isExpired ) ? $singleBooking->Status2 : NULL;
						$isUpChange  	 = $this->Booking_model->isBookingUpForChange( $singleBooking );
						$isUpPayment 	 = $this->Booking_model->isBookingUpForPayment( $singleBooking );
						$isPendingChange = ( $isUpChange  or $isUpPayment );
						$isRolledBack    = $this->Booking_model->isBookingRolledBack( $singleBooking );

						$displayThis = $singleBooking->bookingNumber;
						$displayThis .= "&nbsp;&nbsp;|&nbsp;&nbsp;".$singleBooking->Name;
			?>
				<h3 id="h_<?php echo $singleBooking->bookingNumber; ?>"><a href="#"><?php echo $displayThis ?></a></h3>
				<div id="<?php echo $singleBooking->bookingNumber; ?>" class="section" >
					<?php
						if( $isExpired ) 
						{
							if( $isExpired_State == 'FOR-DELETION'){
								$argumentArray = Array( 'bool' => true, 'Status2' => "FOR-DELETION" );
								$this->bookingmaintenance->deleteBookingTotally_andCleanup( $singleBooking->bookingNumber, $argumentArray );
							}else
								$this->Booking_model-> markAsExpired_ForDeletion( $singleBooking->bookingNumber );
					?>
					<div class="warning" > 
						You were not able to pay for this booking on the deadline. Your slots and seats if any have been forfeited. All data
						regarding this will be erased upon page refresh or the next time you visit this page.
					</div>
					<?php
						}else
						if( $isBeingBooked ){
					?>
					<div class="warning" > 
						You are currently making this booking! If you have accidentally closed the window/tab you
						where you are making this booking, click Resume Booking. 
					</div>
					<?php
						}else
						if( $isPendingChange ) 
						{
					?>
					<div class="warning" > 
						This booking is up for confirmation/payment. Click "View Details" to see more information.
					</div>
					<br/>
					<?php
						}else 
						if( $isRolledBack ) {
					?>
					<div class="warning" > 
						The changes to this booking have been reverted because you haven't paid your dues before the deadline.
						Click here to see more information.
					</div>
					<?php 
							// since user was notified already, then clear the `Status2` column by calling this
							$this->Booking_model->markAsPaid( $singleBooking->bookingNumber );	
						} 
					?>
					<div class="bookingDetails">
						<?php 
							$data['showtimeObj']   			 = $data['eventInfo'] = $singleBooking;
							$data['existingTCName'] 		 = $ticketClassesName[ $singleBooking->EventID ][ $singleBooking->TicketClassGroupID ][ $singleBooking->TicketClassUniqueID ];
							$data['isActivityManageBooking'] = TRUE;
							$data['bottomOtherClass']		 = "bottomspecialOnMB01";
							$data['slots'] 					 = count( $this->Guest_model->getGuestDetails( $singleBooking->bookingNumber ) );
							$this->load->view('html-generic/eventInfoLeft_phpall.inc', $data);
						?>
					</div>
					<div class="containingClassTable">
						<?php 
						if( $isBeingBooked )
						{
						?>
							<div class="metrotile" name="resumebooking" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-resumebooking.png" alt="Resume Booking" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/resume_booking'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
						<?php 
						}else
						if( $isPendingChange or $isExpired ) {
						?>
							<div class="metrotile" name="viewdetails" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_pendingchange_viewdetails'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
							<?php if( $isUpChange ) { ?>
							<div class="metrotile" name="cancelchanges" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelchanges.png" alt="Cancel changes" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_cancelchanges'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
							<?php }else{ ?>
							<div class="metrotile" name="cancel" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelbooking.png" alt="Cancel Booking" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_cancelchanges'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
							</div>
							<?php } ?>
						<?php } else { ?>
						<!--
						<div class="metrotile" name="viewdetails" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" /></a>
							<form method="post" action="<?php echo base_url().'EventCtrl/viewdetails'; ?>" >
								<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
							</form>
						</div>-->
						<div class="metrotile" name="changeshowingtime" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeshowingtime.png" alt="Change showing time" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/mb_prep'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
									<input type="hidden" name="next" value="managebooking_changeshowingtime"   />
								</form>
						</div>
						<div class="metrotile" name="upgradeticketclass" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-upgradeticketclass.png" alt="Upgrade Ticket Class" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_upgradeticketclass'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
						</div>--><!--
						<div class="metrotile" name="customer-manage-class" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-customer-manageclass.png" alt="Change showing time" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/manageBooking_manageclasses'; ?>" >
									<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>"   />
								</form>
						</div>-->
						<div class="metrotile" name="changeseat" >
								<a href="#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeseat.png" alt="Change Seat" /></a>
								<form method="post" action="<?php echo base_url().'EventCtrl/managebooking_changeseat'; ?>" >
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