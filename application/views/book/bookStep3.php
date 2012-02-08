<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Book a Ticket/Post Reservation";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	<!--For overlay-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep2.js'; ?>"/></script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For overlay-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/overlay_general.js'; ?>"/></script>	
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
				Enter guest details
			</div>
			<div id="top_page_detail" >
				Enter your details promptly. For one slot, you can opt to get the information from your profile.
				For the others, if they have UPLB UXT Accounts too, you can enter their username and get their
				info if they have this setting enabled.
				<br/>				
			</div>			
			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div id="title">Event Details</div>
					<div id="content">	
						<div id="bookingDetails" >
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
						<div id="containingClassTable" >
							Remaining time here?<br/><br/>
							Or the "get-from-profile" feature.
						</div>
					</div>
				</div>
				<div class="accordionImitation aci2_Book3Special" >
					<div id="title" class="part2" >Guest Details</div>
					<?php
						$slots = $this->input->cookie( 'slots_being_booked' );
					?>
					<div id="tabs">					
						<ul>
							<?php for( $x=0; $x< $slots; $x++ ) {?>
							<li><a href="#g<?php echo $x+1; ?>">Guest <?php echo $x+1; ?></a></li>							
							<?php } ?>
						</ul>
						<?php for( $x=0; $x< $slots; $x++ ) {?>
						<div id="g<?php echo $x+1; ?>" class="ui-tabs-panel-Book3Special">
							<div class="left" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">personal</legend>								
									<div class="row" id="g<?php echo $x+1; ?>-firstNameFld" >
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-firstName">First name</label>							
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-firstName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-firstName_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-firstNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-middleNameFld">							
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-middleName">Middle name</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-middleName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-middleName_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-middleNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-lastNameFld">							
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-lastName">Last name</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-middleName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-middleName_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-lastNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-genderFld">					
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-gender">Gender</label>							
											<span class="critical" >*</span>
										</div>
						 				<div class="collection" >
											<input type="radio" name="g<?php echo $x+1; ?>-gender" value="MALE" />Male																				
											<input type="radio" name="g<?php echo $x+1; ?>-gender" value="FEMALE" />Female
											<input type="hidden" name="g<?php echo $x+1; ?>-gender_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-genderFldMsg">Please select gender</span>
									</div>
								</fieldset>
							</div>
							<div class="right" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">electronic contact</legend>								
									<div class="row" id="g<?php echo $x+1; ?>-cellPhoneFld" >
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-cellphone">Cellphone</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-cellphone" />
											<input type="hidden" name="g<?php echo $x+1; ?>-cellphone_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-cellphoneFldMsg"></span>
									</div>
																	
									<div class="row" id="g<?php echo $x+1; ?>-landlineFld" >
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-landline">Landline</label>							
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-landline" />
											<input type="hidden" name="g<?php echo $x+1; ?>-landline_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-landlineFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-email_01_Fld" >
										<div class="label" >
											<label class="label" for="g<?php echo $x+1; ?>-email_01_">Email</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-email_01_" />
											<input type="hidden" name="g<?php echo $x+1; ?>-_email_01_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-email_01_FldMsg"></span>
									</div>
							</div>
						</div>		
						<?php } ?>
					</div>
					<p class="criticalityIndicator" >
						Fields with a red asterisk means they are needed.
					</p>
				</div>
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
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