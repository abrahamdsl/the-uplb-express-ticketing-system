<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Confirmation Details - Manage Booking";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep1.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep3.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep5.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBookingFinalize_COD.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking02.css'; ?>"/>
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/manageBookConclusionDataCleanup.js'; ?>" ></script>

	<?php 
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<script type="text/javascript" >
	 $(document).ready( function(){
		$('div.pChannelDetails').show();
		$('a#buttonOK').click( function(){
			$(window).unload();
			window.location = CI.base_url;
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
    <div id="main_content" > 
		<?php 
			$bookingNumber = $bookingInfo->BOOKING_NUMBER;
			$isUpChange = $this->Booking_model->isBookingUpForChange( $bookingNumber );
		?>
    	<div id="centralContainer">	
			<div id="page_title" class="page_title_custom" >
				Confirmation
			</div>
			<div id="top_page_detail" >
				Thank you for using the application. Have fun.
				<br/>
			</div>
			
			<!-- accordion start -->
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div class="title">Event Details</div>
					<div class="content">	
						<div class="containingClassTable" style="float:left;padding-left: 25px;" >
							<div id="bookingNumber" class="properInfo center_purest" >
								<?php echo $bookingNumber; ?>
							</div>
							<div class="caption center_purest" style="margin-top: 20px;" >
								This is your booking reference number. The only thing you need when paying for your ticket(s), well,
								aside from your money.
							</div>
						</div>
						<div class="containingClassTable center_purest" >
							
							<div id="paymentDeadline" class="properInfo center_purest" >
								<input type="hidden" id="pDead_Date" value="<?php echo $this->session->userdata( 'paymentDeadline_Date' ); ?>" />
								<input type="hidden" id="pDead_Time" value="<?php echo $this->session->userdata( 'paymentDeadline_Time' ); ?>" />
								<span id="date">
									<?php echo date('Y-M-d l', strtotime($paymentDeadline['date']) ); ?>
								</span>
								<br/>
								<span id="time">
									<?php 
										/*
											No need to show seconds if zero
										*/
										$splitted = explode(':', $paymentDeadline['time']);
										$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';
										echo date( $timeFormat." A", strtotime($paymentDeadline['time'])); 
									?>
								</span>
							</div>
							<div class="center_purest caption ">
								Deadline for payment, else 
								<?php if( $isUpChange ) { ?>
									your booking will be reverted to the original state.
								<?php }else{ ?>
									your reserved slot(s) will be forfeited.
								<?php } ?>
								
								We advise you be at the collecting agency 15 minutes before the deadline.
							</div>
						</div>
					</div>
				</div>
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
						<div id="title">Details</div>
						<div id="content">
							<input type="hidden" name="bookingNumber" value="<?php echo $bookingNumber; ?>" />
								<div>
									<div class="KoreanPeninsula" >
										<span class="left"  >
											Event Name
										</span>
										<span class="rightSpecialHere" style="font-size: 1.5em;" >	
											<span class="center_purest" >
												<?php echo $singleEvent->Name; ?>
											</span>
										</span>
									</div>
									<div class="KoreanPeninsula" >
										<span class="left" >
											Quantity
										</span>
										<span class="rightSpecialHere" >
											<span class="center_purest" style="font-size: 1.2em;" >
												<?php echo $guestCount;  ?>
											</span>
											<input type="hidden" name="slot" value="<?php echo $guestCount; ?>" ><br/>
										</span>
									</div>
									<?php
										$isUpChange = $this->Booking_model->isBookingUpForChange( $this->clientsidedata_model->getBookingNumber() );
										if( $isUpChange )
										{
									?>
									<div class="KoreanPeninsula" >
										<span class="left captionCurrent" >
											Current showing time and ticket class selected
										</span>
										<span class="rightSpecialHere" >
										<?php
												$x = 2;		
												// determine if this is a red-eye show
												$redEye = FALSE;
												$dateStart = strtotime( $currentShowingTime->StartDate );
												$timeStart = strtotime( $currentShowingTime->StartTime );
												$dateEnd = strtotime( $currentShowingTime->EndDate );
												$timeEnd = strtotime( $currentShowingTime->EndTime );
												if( $timeEnd < $timeStart ) $redEye = TRUE;
											?>
											<table class="center_purest schedulesCentral">
												<thead>
													<tr>
														<td class="iNeedMostSpace" >Date</td>
														<td class="iNeedMoreSpace" >Time Start</td>
														<?php if( $redEye) { ?><td class="iNeedMoreSpace" >Date End</td><?php }  ?>
														<td class="iNeedMoreSpace" >Time End</td>
													</tr>
												</thead>
												<tbody>
												
													<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >
														<td class="BCST_date" >
															<span><?php echo date('Y-M-d l', $dateStart); ?></span>
														</td>
														<td class="BCST_time_start">
															<span><?php echo date('h:i:s A', $timeStart); ?></span>
														</td>	
													<?php if( $redEye) { ?>	
														<td class="BCST_date_end" >
															<span><?php echo date('Y-M-d l', $dateEnd); ?></span>
														</td>
													<?php }  ?>
														<td class="BCST_time_end">
															<span><?php echo date('h:i:s A', $timeEnd); ?></span>
														</td>
													</tr>
												</tbody>
											</table>
											<br/>
											<span class="center_purest" ><?php echo $oldTicketClassName; ?></span>
										</span>
									</div>
									<?php 
										}
									?>
									<div class="KoreanPeninsula" >
										<span class="left captionCurrent" >
											Your <?php if($isUpChange) echo 'new&nbsp;'?> showing time and ticket class
										</span>
										<span class="rightSpecialHere" >
										<?php
												$x = 2;
												// determine if this is a red-eye show
												$redEye = FALSE;
												$dateStart = strtotime( $newShowingTime->StartDate );
												$timeStart = strtotime( $newShowingTime->StartTime );
												$dateEnd = strtotime( $newShowingTime->EndDate );
												$timeEnd = strtotime( $newShowingTime->EndTime );
												if( $timeEnd < $timeStart ) $redEye = TRUE;
											?>
											<table class="center_purest schedulesCentral">
												<thead>
													<tr>
														<td class="iNeedMostSpace" >Date</td>
														<td class="iNeedMoreSpace" >Time Start</td>
														<?php if( $redEye) { ?><td class="iNeedMoreSpace" >Date End</td><?php }  ?>
														<td class="iNeedMoreSpace" >Time End</td>
													</tr>
												</thead>
												<tbody>
												
													<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >
														<td class="BCST_date" >
															<span><?php echo date('Y-M-d l', $dateStart); ?></span>
														</td>
														<td class="BCST_time_start">
															<span><?php echo date('h:i:s A', $timeStart); ?></span>
														</td>	
													<?php if( $redEye) { ?>	
														<td class="BCST_date_end" >
															<span><?php echo date('Y-M-d l', $dateEnd); ?></span>
														</td>
													<?php }  ?>
														<td class="BCST_time_end">
															<span><?php echo date('h:i:s A', $timeEnd); ?></span>
														</td>
													</tr>
												</tbody>
											</table>
											<br/>
											<span class="center_purest" ><?php echo $newTicketClassName; ?></span>
										</span>
									</div>
									<div class="KoreanPeninsula" >
										<span class="left captionCurrent" >
											Guests and seat assignments
										</span>
										<span class="rightSpecialHere" >
											<table class="center_purest schedulesCentral">
												<thead>
													<tr>
														<td class="iNeedMostSpace" >Name</td>
														<?php if($isUpChange){ ?>
														<td class="iNeedMoreSpace" >Former Seat</td>
														<td class="iNeedMoreSpace" >New Seat</td>
														<?php }else{ ?>
														<td class="iNeedMoreSpace" >Seat</td>
														<?php } ?>
													</tr>
												</thead>
												<tbody>
												<?php
													$x = 0;
													foreach( $guests as $singleGuest )
													{
														$allegedNewSeat =  (isset($newSeatData[ $singleGuest->UUID ][ 'visual_rep' ])) ?  $newSeatData[ $singleGuest->UUID ][ 'visual_rep' ] : "NONE";
												?>
													<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >
														<td class="guestname" >
															<?php echo $singleGuest->Lname.', '.$singleGuest->Fname.' '.$singleGuest->Mname;?>
														</td>
														<td class="oldseat">
															<?php 
																$oldSeat = (strlen($oldSeatVisuals[ $singleGuest->UUID ][ 'visual_rep' ]) > 0 ) ? $oldSeatVisuals[ $singleGuest->UUID ][ 'visual_rep' ] : "NONE";
																echo $oldSeat;
															?>
														</td>
														<?php if( $isUpChange ){ ?>
														<td class="newseat" >
															<?php 
																echo $allegedNewSeat;
															?>
														</td>
														<?php } ?>
													</tr>
												<?php
													$x++;
													}
												?>
												</tbody>
											</table>
										</span>
									</div>
								</div>
						</div>
					</div>
					<div class="accordionImitation cEvent04_container aci2_Book3Special">
						<div class="title part2">Payment Details</div>
						<div class="content paymentDetailsContent">	
								<div class="bookingDetails" style="padding-bottom: 50px;" >
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
												foreach(  $unpaidPurchasesArray as $singlePurchase ){ 
											?>
											<tr>
												<td><?php echo $singlePurchase->Quantity; ?></td>
												<td><?php echo $singlePurchase->Charge_type; ?></td>
												<td><?php echo $singlePurchase->Charge_type_Description; ?></td>
												<td>
												<?php
													$thisItemAmount = floatval($singlePurchase->Amount); 
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
									<?php
										if( is_array($paidPurchasesArray) and count($paidPurchasesArray) > 0 )
										{
									?>
									<br/>
									<span class="sectionChief" >Less Purchases</span>
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
												foreach(  $paidPurchasesArray as $singlePurchase ){ 
											?>
											<tr>
												<td><?php echo $singlePurchase->Quantity; ?></td>
												<td><?php echo $singlePurchase->Charge_type; ?></td>
												<td><?php echo $singlePurchase->Charge_type_Description; ?></td>
												<td>
												<?php
													$thisItemAmount = floatval($singlePurchase->Amount);
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
									<?php } ?>
									
									<div id="totalX" class="purchase center_purest" >
										<table id="total" class="bStep5tbl center_purest">
											<tbody>
												<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>Total Amount Due (in PHP)</td>
													<td id="value_proper" ><span class="cost" ><?php echo $amountDue; ?></span></td>
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
									<div class="row">
										<form method="post" action="<?php echo base_url();?>EventCtrl/managebooking_changepaymentmode" >
										<span>&nbsp;</span>
										<span><input type="submit" value="Change payment mode" /></span>
										<input type="hidden" name="booking_number" value="<?php echo $bookingNumber;?>" />
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>	
				</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">
				<a class="button" id="buttonMB" href="<?php echo base_url();?>EventCtrl/manageBooking" ><span class="icon">Manage Booking</span></a>
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