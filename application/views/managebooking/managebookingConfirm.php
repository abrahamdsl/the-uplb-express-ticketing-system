<?php
/*
	Copied from managebooking02_selectShowingTime.php
*/

$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Confirm - Manage Booking";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep1.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep3.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep5.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managebooking02.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/managebooking02.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookPaymentChannelSelection.js'; ?>" ></script>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
  	
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
				Manage Booking<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;			
				Changes Info
			</div>
			<div id="top_page_detail" >
				The following are the information regarding the changes in your booking.
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" style="clear:both;" >
				<input type="hidden" id="doNotProcessTime" value="1" />
				<form method="post"  action="<?php echo base_url().'eventctrl/managebooking_finalize' ?>" name="formLogin" id="formMain">					
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
											<!--<input type="text" class="commonality disabled" id="slot" name="slot_visual" value="<?php echo $guestCount; ?>" style="background-color: white; color: black;" disabled="disabled" /><br/>-->
											<span class="center_purest" style="font-size: 1.2em;" >
												<?php echo $guestCount;  ?>
											</span>
											<input type="hidden" name="slot" value="<?php echo $guestCount; ?>" ><br/>									
										</span>
									</div>
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
									<div class="KoreanPeninsula" >
										<span class="left captionCurrent" >
											Your new showing time and ticket class
										</span>
										<span class="rightSpecialHere" >
										<?php
												$x = 2;		
												// determine if this is a red-eye show
												$redEye = FALSE;													
												$dateStart = strtotime( $newShowingTime->StartDate );
												$timeStart = strtotime( $newShowingTime->StartTime );
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
														<td class="iNeedMoreSpace" >Former Seat</td>
														<td class="iNeedMoreSpace" >New Seat</td>
														<!--<td class="iNeedMoreSpace" >Status</td>-->
													</tr>
												</thead>
												<tbody>
												<?php
													$x = 0;
													foreach( $guests as $singleGuest )
													{														
														if( isset($newSeatData[ $singleGuest->UUID ][ 'visual_rep' ]) )
														{
															$allegedNewSeat = $newSeatData[ $singleGuest->UUID ][ 'visual_rep' ];
														}else{
															if( $isShowtimeChanged and $isTicketClassTheSame ){
																$allegedNewSeat = $oldSeatVisuals[ $singleGuest->UUID ][ 'visual_rep' ];
															}else{
																$allegedNewSeat = "NONE";
															}
														}
												?>
													<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >																						
														<td class="guestname" >
															<?php echo $singleGuest->Lname.', '.$singleGuest->Fname.' '.$singleGuest->Mname;?>													
														</td>
														<td class="oldseat">	
															<?php 
																echo $oldSeatVisuals[ $singleGuest->UUID ][ 'visual_rep' ];
															?>
														</td>													
														<td class="newseat" >
															<?php 
																echo $allegedNewSeat;
															?>
														</td>												
													<!--	<td class="status">
															<?php
															if( $allegedNewSeat == "NONE" )
															{
															?>
																RETAINED
															<?php
															}else{
															?>
																WILL BE CHANGED
															<?php } ?>
														</td>-->
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
										if( count($paidPurchasesArray) > 0 )
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
									<br/>
									<br/>
									<input type="hidden" id="lastPChannel" value="0" />								
									<?php if ( $amountDue > 0 ) { ?>
										<select name="paymentChannel" class="pChannel"  >
												<option value="NULL" >Select payment option</option>										
											<?php
												$x=0;
												foreach( $paymentChannels as $singleChannel )
												{										
											?>		
												<option value="<?php echo $singleChannel->UniqueID; ?>" ><?php echo $singleChannel->Name; ?></option>										
											<?php									
												}
											?>
										</select>
									<?php }else{ 
										/*
											Factory Default: Payment_mode which means automatically confirm since no charge is UniqueID 0.
										*/
									?>	
										<p class="center_purest" style="padding: 10px;" >
											Your changes will be saved immediately upon clicking Confirm
											because you do not have outstanding balance.
										</p>
										<input type="hidden" name="paymentChannel" value="0" />
									<?php } ?>								
									<br/>
									<br/>
								
									<?php 
										if ( $amountDue > 0 ) {
										foreach( $paymentChannels as $singleChannel )
										{	
									?>
									<div id="pc<?php echo $singleChannel->UniqueID; ?>_details" class="pChannelDetails" >
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
									<?php }
										}//if
									?>
								</div>
						</div>
					</div>	
				</form>
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">
							<a class="button" id="buttonOK" ><span class="icon">Confirm</span></a>
							<a class="button" id="buttonReset<?php if( $this->functionaccess->isChangingPaymentMode() ) echo 'p'; ?>" ><span class="icon">Cancel</span></a>
			</div>	
			<div id="misc" style=" clear:both;"></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>