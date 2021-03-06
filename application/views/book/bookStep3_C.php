<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Assign classes";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/seatV2/seatV2.css'; ?>"/>	 <!--For seat map v2 --> 
	
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep3_C.js'; ?>" ></script>	
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
			<div id="page_title">
				Add classes to booking
			</div>
			<div id="instruction" >
				Associate your attendance with the classes. <br/><br/>
				Now, match the classes to the guests.
				you can skip this step.
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
							Remaining time here?<br/><br/>
							Or the "get-from-profile" feature.
						</div>
					</div>
				</div>
				<div class="accordionImitation aci2_Book3Special" >
					<div class="title part2" >Guest Details</div>
					<?php
						$slots = $this->input->cookie( 'slots_being_booked' );					
					?>
					<input type="hidden" id="managebookingChooseSeat" value="0"/>					
					<form name="formMain" method="post" action="<?php echo base_url().'academicctrl/associateClassToBooking_process2'; ?>" id="formMain">					
					
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
									<legend class="field_grouping_bar specialOnBook3">classes</legend>										
										<?php if( strlen($singleGuest->studentNumber) == 9 or  strlen($singleGuest->employeeNumber) >= 9 )
											{
										?>
											<table class="center_purest schedulesCentral" style="text-align: center;" >
												<thead>
													<tr>
														<td>Select?</td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td>Instructor</td>
													</tr>
												</thead>
												<tbody>
												<?php
													$xz=0;
													foreach( $val as $xy  )
													{										
													
												?>
													<tr <?php if( $xz % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
														<td>
															<input type="checkbox" name="<?php echo $singleGuest->UUID; ?>_<?php echo $xy->UUID; ?>"  />
														</td>
														<td><?php echo $xy->CourseTitle;?></td>
														<td><?php echo $xy->CourseNum;?></td>
														<td><?php echo $xy->LectureSect;?></td>
														<td><?php echo $xy->RecitSect;?></td>
														<td><?php echo $xy->Lname.", ".$xy->Fname." ".$xy->Mname;?></td>										
													<tr>
												<?php
													}
												?>
												</tbody>
											</table>
											<div style=" margin-bottom: 10px;">
												<input type="hidden" value="<?php echo $singleGuest->UUID; ?>" class="guestUUID " />
												<input type="button" value="Check all" class="selectDeselectBtns" id="checkAll__d"/>
												<input type="button" value="Uncheck all" class="selectDeselectBtns" id="UncheckAll__d"/>
											</div>
											<?php }else{
											?>
												<p>You did not specify UPLB constituency data for this guest so selection is not available.</p>
											<?php
												}
											?>
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
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
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