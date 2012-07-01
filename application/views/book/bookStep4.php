<?php 
	$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
?>
<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>

<?php
	$this->pageTitle = ( $isActivityManageBooking ) ? "Change seat - Manage Booking " : "Choose seat - Purchase Ticket";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep3.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep4.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlayv2_general.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/seatV2/seatV2.css'; ?>"/> <!--For seat map v2 --> 
	
	<?php 
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep4.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>
	<?php 
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<!-- For overlay v2-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/modal2/jquery.simplemodal.js'; ?>" ></script>
	<!-- seat manipulations -->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/seatV2/jquery.drag_drop_multi_select_alpha.js'; ?>"></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/seatV2/seatManipulation.js'; ?>"></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/processAJAXresponse.js'; ?>"></script>
	
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
		$this->load->view('html-generic/seatModal-client.inc');
		
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
    <div id="main_content" >
    	<div id="centralContainer">
<?php 
			 if( !$isActivityManageBooking )$this->load->view( 'html-generic/bookProgressIndicator.inc');
?>		
			<div id="page_title" class="page_title_custom" >
			<?php if( $isActivityManageBooking ) { ?>
				Manage Booking<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;Change Seat
			<?php }else { ?>
				Pick seat
			<?php } ?>
			</div>
			<div id="top_page_detail" >
			<?php
				$still = "Your former seats are still reserved until you confirm/cancel the changes to this booking.";
				if( $isSeatSelectionRequired ){
			?>
				You have selected an event or its showing time that requires seats to be chosen during the booking process.
			<?php	if( $isActivityManageBooking ) { echo $still; } ?>
			<?php }else{
			?>
					<?php if( $isActivityManageBooking ) {?>
							<?php if( $isTicketClassChanged )
								{
							?>
								You have selected a different ticket class and is given the option to whether choose new seats or not.
							<?php 
								}
							?>
							
					<?php 
						echo $still;
						}else{
					?>
						Now, there's no more racing and haggling in line outside the auditorium to race for seats.
					<?php 
						}
					?>
			<?php 
				}
			?>
			<br/>
			</div>
			<!-- accordion start -->
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div id="title">Event Details</div>
					<div id="content">
						<div class="bookingDetails" >
							<?php
								$this->load->view('html-generic/eventInfoLeft_ndx.inc');
							?>
						</div>
						<div class="containingClassTable" >
							<!--
								Remaining time here?<br/><br/>
							-->
						</div>
					</div>
				</div>
				<div class="accordionImitation aci2_Book3Special" >
					<div class="title part2" >Guest Details</div>
					<?php
						$slots = $bookingInfo->SLOT_QUANTITY;
					?>
					<input type="hidden" id="manageBookingChooseSeat" value="<?php echo intval($isActivityManageBooking); ?>"/>
					<input type="hidden" id="_js_use_slots" value="<?php echo $slots ?>" />
					<input type="hidden" id="_js_use_ticketClassUniqueID" value="<?php echo $bookingInfo->TICKET_CLASS_UNIQUE_ID ?>" />
					<form name="formMain" method="post" action="<?php echo base_url().'EventCtrl/book_step5'; ?>" id="formMain">					
					
					<div id="tabs">					
						<ul>
							<?php 								
								 for( $x=0, $y = count( $guests); $x< $y; $x++ ){
							?>
							<li><a id="g<?php echo $x+1; ?>_anchor" href="#g<?php echo $x+1; ?>">Guest <?php echo $x+1; ?></a></li>							
							<?php } ?>
						</ul>
						<?php 							
							$x=0;
							foreach( $guests as $singleGuest) {
						?>
						<div id="g<?php echo $x+1; ?>" class="ui-tabs-panel-Book3Special">
							<div class="left" >
								<?php
									$this->load->view( "html-generic/customer_proper_info.inc", Array(
											'guestnum' =>  $x,
											'singleGuest' => $singleGuest			
										)
									);
								?>
							</div>
							<div class="right" >
								<fieldset>								
									<legend class="field_grouping_bar specialOnBook3">seat</legend>	
									<input type="text" class="seatText" name="g<?php echo $x+1; ?>_seatVisual" value="0" disabled="disabled" />
									<?php 
										$seatMatrixVal = "0";
										if( $isActivityManageBooking )
										{
											 $guestSeatObj = $guestSeatDetails[$singleGuest->UUID];
											 $seatMatrixVal = ($guestSeatObj['visual_rep'] == "NONE" ) ? "0" : $guestSeatObj['matrix_x'].'_'.$guestSeatObj['matrix_y'];
											 $seatMatrixValOld = $seatMatrixVal;
									?>									
									<input type="hidden" name="g<?php echo $x+1; ?>_seatMatrix_old" value="<?php echo $seatMatrixValOld;?>" />
									<?php
										}
									?>
									<input type="hidden" name="g<?php echo $x+1; ?>_seatMatrix" value="<?php echo $seatMatrixVal;?>" />
									<input type="button" id="g<?php echo $x+1; ?>_chooseSeat" class="seatChooser" value="Choose seat" />
									<div class="row anchorBelow" id="g<?php echo $x+1; ?>-navigation" >																			
											<?php
												if( ( $x+1 ) != 1 )
												{
											?>
												<div class="leftInr" >
													<input type="button" class="anchor_below" id="g<?php echo $x; ?>_anchor-below" value="&lt; Guest <?php echo $x ?>" />
												</div>
											<?php
												}													
												if( ( $slots-1 ) != $x ) 
												{
											?>
												<div class="rightInr">
													<input type="button" class="anchor_below" id="g<?php echo $x+2; ?>_anchor-below" value="Guest <?php echo $x+2 ?> &gt;" />
												</div>
											<?php
												}
											?>										
									</div>	
								</fieldset>
							</div>
						</div>		
						<?php $x++; } ?>
					</div>
					</form>				
				</div>
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<a class="button" id="buttonReset<?php if($isActivityManageBooking) echo "2"; ?>" ><span class="icon">Cancel</span></a>
			</div>	
			<div class="buttonfooterSeparator" ></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>