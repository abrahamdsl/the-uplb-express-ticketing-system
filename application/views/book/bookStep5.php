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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookPaymentChannelSelection.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep5.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>	
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
				Payment
			</div>
			<div id="top_page_detail" >
				We sure do have a lot of payment channels for your convenience. Have fun.
				<br/>				
			</div>			
			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div id="title">Event Details</div>
					<div id="content">	
						<div class="bookingDetails" >
							<?php
								$slots = $this->input->cookie( 'slots_being_booked' ); $this->input->cookie( '' );
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
									You are booking <?php echo $slots; ?> ticket<?php if($slots > 1) echo 's'; ?>.
								</p>
							</div>														
						</div>
						<div class="containingClassTable" >
							<!--
								Remaining time here?<br/><br/>
							-->
						</div>
					</div>
				</div>
				<div class="accordionImitation aci2_Book3Special" >
					<div class="title part2" >Payment details</div>
					<div class="content paymentDetailsContent">	
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
												<td><span class="cost"></span><span class="cost" ><?php echo $this->session->userdata( "totalCharges" ); ?></span></td>
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
								<form name="formMain" method="post" action="<?php echo base_url().'EventCtrl/book_step6' ?>" id="formMain">
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
								</form>
								<br/>
								<br/>
							
								<?php 
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
								<?php } ?>
							</div>
					</div>
				</div>
				<div class="accordionImitation" >
					<div class="title" >
						Guest Details
						<a class="toggleGuestInfo" >
							<span id="toggleGuestDetails" >(Show)</span>
						</a>
					</div>
					<?php
						$slots = $this->input->cookie( 'slots_being_booked' );
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
							<a class="button" id="buttonOK" ><span class="icon">Pay</span></a>														
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
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