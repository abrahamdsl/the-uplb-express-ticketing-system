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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep3.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup.js'; ?>" ></script>
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
					<div class="part2 title" >Guest Details</div>
					<?php
						$slots = $this->input->cookie( 'slots_being_booked' );
					?>
					<form name="formMain" method="post" action="<?php echo base_url().'EventCtrl/book_step4' ?>" id="formMain">
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
											<label class="label" for="id_g<?php echo $x+1; ?>-firstName" >First name</label>							
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-firstName" id="id_g<?php echo $x+1; ?>-firstName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-firstName_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-firstNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-middleNameFld">							
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-middleName">Middle name</label>											
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-middleName" id="id_g<?php echo $x+1; ?>-middleName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-middleName_validate" id="id_g<?php echo $x+1; ?>-middleName_validate" value="1" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-middleNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-lastNameFld">							
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-lastName">Last name</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-lastName" id="id_g<?php echo $x+1; ?>-lastName" />
											<input type="hidden" name="g<?php echo $x+1; ?>-lastName_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-lastNameFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-genderFld">					
										<div class="label" >
											<span>Gender</span>
											<span class="critical" >*</span>
										</div>
						 				<div class="collection gender"  >
											<input type="radio" name="g<?php echo $x+1; ?>-gender" value="MALE" />Male																				
											<input type="radio" name="g<?php echo $x+1; ?>-gender" value="FEMALE" />Female
											<input type="hidden" name="g<?php echo $x+1; ?>-gender_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-genderFldMsg">Please select gender</span>
									</div>
									<input type="hidden" name="g<?php echo $x+1; ?>-accountNum" value="0" />
								</fieldset>
							</div>
							<div class="right" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">electronic contact</legend>								
									<div class="row" id="g<?php echo $x+1; ?>-cellPhoneFld" >
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-cellphone">Cellphone</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-cellphone" id="id_g<?php echo $x+1; ?>-cellphone" />
											<input type="hidden" name="g<?php echo $x+1; ?>-cellphone_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-cellphoneFldMsg"></span>
									</div>
																	
									<div class="row" id="g<?php echo $x+1; ?>-landlineFld" >
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-landline">Landline</label>							
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-landline" id="id_g<?php echo $x+1; ?>-landline" />
											<input type="hidden" name="g<?php echo $x+1; ?>-landline_validate" value="1" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-landlineFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-email_01Fld" >
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-email_01">Email</label>
											<span class="critical" >*</span>
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-email_01" id="id_g<?php echo $x+1; ?>-email_01" />
											<input type="hidden" name="g<?php echo $x+1; ?>-email_01_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-email_01FldMsg"></span>
									</div>
								</fieldset>
							</div>
						</div>		
						<?php } ?>
					</div>
					</form>
<?php
	$this->load->view( 'html-generic/criticalreminder.inc' );
?>					
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