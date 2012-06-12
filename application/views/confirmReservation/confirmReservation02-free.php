<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Purchase Ticket";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep5.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep6.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>
	<!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookConclusionOnloadRitual.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookConclusionDataCleanup.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep6_FreeConclusion.js'; ?>" ></script>
	<?php 
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<!--For modal v1-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
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
    <div id="main_content" >
    	<div id="centralContainer">
<?php 
			$this->load->view( 'html-generic/bookProgressIndicator.inc');
?>
			<div id="page_title" class="page_title_custom" >
				Confirmation
			</div>
			<div id="top_page_detail" >
				Congratulations. You now have an e-ticket to the event.
				<br/>
			</div>

			<!-- accordion start -->
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div class="title">Event Details</div>
					<div class="content">	
						<div class="bookingDetails" >
							<?php
								$this->load->view('html-generic/eventInfoLeft_ndx.inc');
							?>
						</div>
						<div class="containingClassTable center_purest" >
							<div id="bookingNumber" class="properInfo center_purest" >
								<?php echo $bookingInfo->BOOKING_NUMBER; ?>
							</div>
							<div class="caption center_purest">
								Booking number. The only thing you need to be with you in
								the event aside from your ID(s).
							</div>
							<div id="paymentDeadline" class="properInfo center_purest" >
								CONFIRMED
							</div>
							<div class="center_purest caption" id="deadlineCaption" >
								Congratulations. Enjoy the show.
							</div>
							
						</div>
					</div>
				</div>
				<div class="accordionImitation aci2_Book3Special" >
					<div class="title part2" >Payment details</div>
					<div class="content paymentDetailsContent" >
							<div class="bookingDetails" >
								<span class="sectionChief" >Billing Summary</span>
								<table id="billingSummary" class="bStep5tbl center_purest" >
									<thead>
										<tr>
											<td > Quantity</td>
											<td > Item</td>
											<td > Description</td>
											<td > Cost</td>
										</tr>
									</thead>
									<tbody>
										<?php
											$totalCharges = 0;
											foreach( $purchases as $singlePurchase ){ 
										?>
										<tr>
											<td><?php echo $singlePurchase->Quantity; ?></td>
											<td><?php echo $singlePurchase->Charge_type; ?></td>
											<td><?php echo $singlePurchase->Charge_type_Description; ?></td>
											<td>
											<?php
												$thisItemAmount = floatval($singlePurchase->Amount); 
												$totalCharges += $thisItemAmount;
												if ( $thisItemAmount < 0 )
													echo '('.$thisItemAmount.')';
												else
													echo $thisItemAmount;
											?>
											</td>
										</tr>
										<?php }	
										?>
									</tbody>
								</table>
								
								<div id="totalX" class="purchase center_purest" >
									<table id="total" class="bStep5tbl center_purest">
										<tbody>
											<tr>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td>Total (in PHP)</td>
												<td id="value_proper"><span class="cost" ><?php echo $this->session->userdata( "totalCharges" ); ?></span></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="containingClassTable" >
								<span class="sectionChief" >Payment mode</span>
								<div id="pc<?php echo $singleChannel->UniqueID; ?>_details" class="pChannelDetails" >
									<div id="pChannelName" class="properInfo center_purest" >
										<?php echo $singleChannel->Name; ?>
									</div>
									<?php if ( $singleChannel->Contact_Person != "" ) { ?>
									<div class="row">
										<span>Contact Person</span>
										<span><?php echo $singleChannel->Contact_Person; ?></span>
									</div>
									<?php } ?>
									<?php if ( $singleChannel->Location != "" ) { ?>
									<div class="row">
										<span>Location</span>
										<span><?php echo $singleChannel->Location;?></span>
									</div>
									<?php } ?>
									<?php if ( $singleChannel->Cellphone != "" ) { ?>
									<div class="row">
										<span>Cellphone</span>
										<span><?php echo $singleChannel->Cellphone;?></span>
									</div>
									<?php } ?>
									<?php if ( $singleChannel->Landline != "" ) { ?>
									<div class="row">
										<span>Landline</span>
										<span><?php echo $singleChannel->Landline;?></span>
									</div>
									<?php } ?>
									<?php if ( $singleChannel->Email != "" ) { ?>
									<div class="row">
										<span>Email</span>
										<span><?php echo $singleChannel->Email;?></span>
									</div>
									<?php } ?>
									<?php if ( $singleChannel->Comments != "" ) { ?>
									<div class="row">
										<span>Remarks</span>
										<span><?php echo $singleChannel->Comments;?></span>
									</div>
									<?php } ?>
								</div>
							</div>
					</div>
				</div>
				<div class="accordionImitation" >
					<div class="title" >Guest Details </div>
					<?php
						$slots = $bookingInfo->SLOT_QUANTITY;
					?>
					<div id="tabs">
						<ul>
							<?php 
								 for( $x=0, $y = count( $guests); $x< $y; $x++ ){
							?>
							<li><a id="g<?php echo $x+1; ?>_anchor" href="#g<?php echo $x+1; ?>">Guest <?php echo $x+1; ?></a></li>
							<?php } ?>
						</ul>
						<?php $x=0;
							foreach( $guests as $singleGuest) {
						?>
						<div id="g<?php echo $x+1; ?>" class="ui-tabs-panel-Book3Special">
							<div class="left" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">personal</legend>
									<div class="row" id="g<?php echo $x+1; ?>-firstNameFld" >
										<?php echo $singleGuest->Fname; ?>
									</div>
									<?php if( $singleGuest->Mname != "" ){ ?>
									<div class="row" id="g<?php echo $x+1; ?>-middleNameFld">
										<?php echo $singleGuest->Mname; ?>
									</div>
									<?php } ?>
									<div class="row" id="g<?php echo $x+1; ?>-lastNameFld">
										<?php echo $singleGuest->Lname; ?>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-genderFld">
										<?php echo $singleGuest->Gender; ?>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-cellPhoneFld" >
										<?php echo $singleGuest->Cellphone; ?>
									</div>
									<?php if( $singleGuest->Landline != "" ){ ?>
									<div class="row" id="g<?php echo $x+1; ?>-landlineFld" >
										<?php echo $singleGuest->Landline; ?>
									</div>
									<?php } ?>
									<div class="row" id="g<?php echo $x+1; ?>-email_01Fld" >
										<?php echo $singleGuest->Email; ?>
									</div>
								</fieldset>
							</div>
							<div class="right" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">seat</legend>
									<input type="text" class="seatText" name="g<?php echo $x+1; ?>_seatVisual" value="<?php echo $seatVisuals[ $singleGuest->UUID ]; ?>" disabled="disabled"   />
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
				</div>
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">
							<a class="button" id="buttonOK" ><span class="icon">Home</span></a>
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