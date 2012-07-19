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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managebooking01.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/managebooking01.js'; ?>" ></script>
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
			<div style="padding-left:10px; clear: both; width: 90%; ">
				Please select the booking you want to modify and click the tile specifying
				what operation you want to do. 
				Click <a href="#">here</a> to see your archived bookings.
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
						$x = -1;
						foreach( $bookings as $singleBooking )
						{ 
							$x++;
							$isExpired   	 = $this->booking_model->isBookingExpired( $singleBooking );
							$isBeingBooked   = $this->booking_model->isBookingBeingBooked( $singleBooking );
							$isExpired_State = ( $isExpired ) ? $singleBooking->Status2 : NULL;
							$isUpChange  	 = $this->booking_model->isBookingUpForChange( $singleBooking );
							$isUpPayment 	 = $this->booking_model->isBookingUpForPayment( $singleBooking );
							$isPendingChange = ( $isUpChange  or $isUpPayment );
							$isRolledBack    = $this->booking_model->isBookingRolledBack( $singleBooking );

							$displayThis = $singleBooking->bookingNumber;
							$displayThis .= "&nbsp;&nbsp;|&nbsp;&nbsp;".$singleBooking->Name;
					?>
						<h3 id="h_<?php echo $singleBooking->bookingNumber; ?>"><a href="#"><?php echo $displayThis ?></a></h3>
						<div id="proper_<?php echo $singleBooking->bookingNumber; ?>" class="section" >
				<?php
						/* <area id="warnings" > */{
				?>
						<?php
							if( $isExpired ) 
							{
								if( $isExpired_State == BOOKDETAIL_STAT2_FOR_DELETION ){
									$argumentArray = Array( 'bool' => true, 'Status2' => BOOKDETAIL_STAT2_FOR_DELETION );
									$this->bookingmaintenance->deleteBookingTotally_andCleanup( $singleBooking->bookingNumber, $argumentArray );
								}else
									$this->booking_model-> markAsExpired_ForDeletion( $singleBooking->bookingNumber );
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
								$this->booking_model->markAsPaid( $singleBooking->bookingNumber );	
							} 
						?>
				<?php 
						}
						// </area id="warnings" >
				?>
							<div class="bookingDetails">
								<?php 
									$data['showtimeObj']   			 = $data['eventInfo'] = $singleBooking;
									$data['existingTCName'] 		 = $ticketClassesName[ $singleBooking->EventID ][ $singleBooking->TicketClassGroupID ][ $singleBooking->TicketClassUniqueID ];
									$data['isActivityManageBooking'] = TRUE;
									$data['bottomOtherClass']		 = "bottomspecialOnMB01";
									$data['slots'] 					 = count( $this->Guest_model->getGuestDetails( $singleBooking->bookingNumber ) );
									$data['output_datetime']		 = FALSE;
									$this->load->view('html-generic/eventInfoLeft_phpall.inc', $data);
								?>
							</div>
							<div class="containingClassTable">
								<?php 
									if( $isBeingBooked )
									{
								?>
								<div class="metrotile" id="resumebooking_<?php echo $x; ?>" >
									<a href="<?php echo base_url().'eventctrl/mb_prep/8/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-resumebooking.png" alt="Resume Booking" />
									</a>
								</div>
								<?php 
									}else
									if( $isPendingChange or $isExpired ) {
								?>
										<div class="metrotile" id="viewdetails_<?php echo $x; ?>" >
											<a href="<?php echo base_url().'eventctrl/mb_prep/6/'.$singleBooking->bookingNumber; ?>">
												<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View pending details" />
											</a>
										</div>
										<?php if( $isUpChange ) { ?>									
											<div class="metrotile" id="cancelchanges_<?php echo $x; ?>" >
												<a href="<?php echo base_url().'eventctrl/mb_prep/7/'.$singleBooking->bookingNumber; ?>">
													<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelchanges.png" alt="Cancel changes" />
												</a>
											</div>											
										<?php }else{ ?>
											<div class="metrotile" id="cancelbooking_<?php echo $x; ?>" >
												<?php /*Does not need to call eventctrl/mb_prep - AJAX handles this directly in the page.*/ ?>
												<a href="<?php echo base_url().'eventctrl/#' ?>">
													<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelbooking.png" alt="Cancel Booking" />
												</a>									
											</div>
										<?php } ?>
								<?php } else { ?>
								<!--
								<div class="metrotile" id="viewdetails_<?php echo $x; ?>" >
									<a href="<?php echo base_url().'eventctrl/mb_prep/5/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" />
									</a>
								</div>-->
								<div class="metrotile" id="changeshowingtime_<?php echo $x; ?>" >
									<a href="<?php echo base_url().'eventctrl/mb_prep/1/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeshowingtime.png" alt="Change showing time" />
									</a>
								</div>
								<div class="metrotile" id="upgradeticketclass_<?php echo $x; ?>" >
									<a href="<?php echo base_url().'eventctrl/mb_prep/2/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-upgradeticketclass.png" alt="Upgrade Ticket Class" />
									</a>
								</div>
								<!--
								<div>
									<a href="<?php echo base_url().'eventctrl/mb_prep/4/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-customer-manageclass.png" alt="Manage Associated Classes" />
									</a>						
								</div>
								-->						
								<div class="metrotile" id="changeseat_<?php echo $x; ?>" >
									<a href="<?php echo base_url().'eventctrl/mb_prep/3/'.$singleBooking->bookingNumber; ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-changeseat.png" alt="Change Seat" />
									</a>
								</div>
								<div class="metrotile" id="cancelbooking_<?php echo $x; ?>" >
									<?php /*Does not need to call eventctrl/mb_prep - AJAX handles this directly in the page.*/ ?>
									<a href="<?php echo base_url().'eventctrl/#' ?>">
										<img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-cancelbooking.png" alt="Cancel Booking" />
									</a>									
								</div>
								<?php } ?>
								<input type="hidden" name="bookingNumber" value="<?php echo $singleBooking->bookingNumber; ?>" />	
							</div><?php // end of containing class table ?>
						</div> <?php // end of each accordion entry ?>
				<?php
						}//foreach booking
					}//else
				?>
				<?php
						$this->load->view( 'html-generic/nobooking.inc' );
				?>
				</div> <?php // the ultimate accordion div ?>
			</div> <?php // the div holding the  accordion-ized div ?>
			<!-- accordion end -->
		</div>
    </div><!--end of main content-->
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>