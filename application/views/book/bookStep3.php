<?php
	$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
	$isActivityManageBooking = ( $sessionActivity[0] == "MANAGE_BOOKING" and $sessionActivity[1] == 2 );
		
	define('SLOTS', $this->clientsidedata_model->getSlotsBeingBooked() );
?>
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep3.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep3GetUserInfo.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookGuestAnchorsBelow.js'; ?>" ></script>
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
<input type="hidden" name="uplbconstituent" id="uplbcons" value="0" />
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
				For the others, if they have UPLB XT Accounts too, you can enter their username and get their
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
								$this->load->view('html-generic/eventInfoLeft_CookieBased.inc');
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
					<div class="part2 title" >Guest Details</div>					
					<form name="formMain" method="post" action="<?php echo base_url().'EventCtrl/book_step4' ?>" id="formMain">
					<div id="tabs">					
						<ul>
							<?php for( $x=0; $x< SLOTS; $x++ ) {?>
							<li><a id="g<?php echo $x+1; ?>_anchor" href="#g<?php echo $x+1; ?>">Guest <?php echo $x+1; ?></a></li>							
							<?php } ?>
						</ul>
						<?php for( $x=0; $x< SLOTS; $x++ ) {?>
						<div id="g<?php echo $x+1; ?>" class="ui-tabs-panel-Book3Special">
							<div class="left" >
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">personal</legend>
									<div style="text-align: center" >								
										<input type="button" id="g<?php echo $x+1; ?>-chooseSeat" class="ayokongDefaultAngItsuraNgButton getuserinfoBtn"  value="Get user details from profile" />
									</div>
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
												if( ( SLOTS-1 ) != $x ) 
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
								<fieldset>
									<legend class="field_grouping_bar specialOnBook3">uplb constituency</legend>
									<p style="font-size: 0.8em; padding: 3px; text-align: center;" >
										Please do not include dashes.<br/>
										These info will be used in checking of your attendance.
										Disregard if you are not a UPLB constituent.
									</p>
									<div class="row" id="g<?php echo $x+1; ?>-studentNumFld" >
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-studentNum">Student number</label>											
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-studentNum" id="id_g<?php echo $x+1; ?>-studentNum" />
											<input type="hidden" name="g<?php echo $x+1; ?>-studentNum_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-studentNumFldMsg"></span>
									</div>
									<div class="row" id="g<?php echo $x+1; ?>-empNum" >
										<div class="label" >
											<label class="label" for="id_g<?php echo $x+1; ?>-cellphone">Employee number</label>											
										</div>
										<div class="collection" >
											<input type="text" name="g<?php echo $x+1; ?>-empNum" id="id_g<?php echo $x+1; ?>-empNum" />
											<input type="hidden" name="g<?php echo $x+1; ?>-empNum_validate" value="0" />
										</div>									
									</div>
									<div class="msgContainer formErrorBookStep3Special" >	
											<div class="icon"></div>
											<span id="g<?php echo $x+1; ?>-empNumFldMsg"></span>
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