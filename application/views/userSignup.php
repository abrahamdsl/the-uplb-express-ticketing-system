<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Sign up";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup-rev7.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup-actionListener.js'; ?>"/></script>
	
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
		<div id="graynavbar" >					
		</div>
        
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Sign up for the UPLB Express Ticketing System | Step 1
			</div>
			<div style="padding-left:10px; clear: both">
				Please complete the following information. Fields with an asterisk indicate that
				it is required.
			</div>				
			<!-- start of form -->			
			<form method="post"  action="<?php echo base_url().'useracctctrl/userSignup_step2' ?>" name="formSignup" id="formMain">

				<div class="center_pure">
					<fieldset class="fieldsCollection">						
						<legend class="field_grouping_bar">login credentials</legend>
						<div class="row" id="usernameFld">							
							<div class="label" >
								<label class="label" for="username">Username</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="text" name="username" />
								<input type="hidden" name="username_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="usernameFldMsg"></span>
							</div>
						</div>
						<div class="row" id="passwordFld">							
							<div class="label" >
								<label class="label" for="password">Password</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="password" name="password" />
								<input type="hidden" name="password_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="passwordFldMsg"></span>
							</div>
						</div>
						<div class="row" id="confirmPasswordFld">							
							<div class="label" >
								<label class="label" for="confirmPassword">Confirm Password</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="password" name="confirmPassword" />
								<input type="hidden" name="confirmPassword_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="confirmPasswordFldMsg"></span>
							</div>
						</div>
					</fieldset>
					<div class="center_pure">
					<fieldset class="fieldsCollection">						
						<legend class="field_grouping_bar">personal</legend>
						<div class="row" id="firstNameFld">														
							<div class="label" >
								<label class="label" for="firstName">First name</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="text" name="firstName" />
								<input type="hidden" name="firstName_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="firstNameFldMsg"></span>
							</div>
						</div>
						<div class="row" id="middleNameFld">																				
							<div class="label" >
								<label class="label" for="middleName">Middle name</label>
							</div>
							<div class="collection">
								<input type="text" name="middleName" />
								<input type="hidden" name="middleName_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="middleNameFldMsg"></span>
							</div>
						</div>
						<div class="row" id="lastNameFld">														
							<div class="label" >
								<label class="label" for="lastName">Last name</label>
								<span class="critical" >*</span>
							</div>							
							<div class="collection">
								<input type="text" name="lastName" />
								<input type="hidden" name="lastName_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="lastNameFldMsg"></span>
							</div>
						</div>
						<div class="row" id="genderFld">														
							<div class="label" >
								<label class="label" for="gender">Gender</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="radio" name="gender" value="MALE" />Male																				
								<input type="radio" name="gender" value="FEMALE" />Female
								<input type="hidden" name="gender_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="genderFldMsg">Please select gender</span>
							</div>
						</div>
					</fieldset>
					<fieldset class="fieldsCollection">						
						<legend class="field_grouping_bar">electronic contact</legend>
						<div class="row" id="cellPhoneFld">														
							<div class="label" >
								<label class="label" for="cellPhone">Cellphone</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="text" name="cellPhone" />
								<input type="hidden" name="cellPhone_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="cellPhoneFldMsg"></span>
							</div>
						</div>
						<div class="row" id="landlineFld">														
							<div class="label" >
								<label class="label" for="landline">Landline Phone</label>
							</div>
							<div class="collection">
								<input type="text" name="landline" />
								<input type="hidden" name="landline_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="landlineFldMsg"></span>
							</div>
						</div>
						<div class="row" id="email_01_Fld">														
							<div class="label" >
								<label class="label" for="email_01_">E-mail address</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="text" name="email_01_" />
								<input type="hidden" name="email_01__validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="email_01_FldMsg"></span>
							</div>
						</div>						
					</fieldset>
					<fieldset class="fieldsCollection">						
						<legend class="field_grouping_bar">address</legend>
						<div class="row" id="homeAndStreet_addrFld">														
							<div class="label" >
								<label class="label" for="homeAndStreet_addr">Home &amp; Street</label>
							</div>
							<div class="collection">
								<input type="text" name="homeAndStreet_addr" />
								<input type="hidden" name="homeAndStreet_addr_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="homeAndStreet_addrFldMsg"></span>
							</div>
						</div>
						<div class="row" id="barangay_addrFld">														
							<div class="label" >
								<label class="label" for="barangay_addr">Barangay</label>
							</div>							
							<div class="collection">
								<input type="text" name="barangay_addr" />
								<input type="hidden" name="barangay_addr_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="barangay_addrFldMsg"></span>
							</div>
						</div>
						<div class="row" id="cityOrMun_addrFld">														
							<div class="label" >
								<label class="label" for="cityOrMun_addr">City/Municipality</label>
							</div>
							<div class="collection">
								<input type="text" name="cityOrMun_addr" />
								<input type="hidden" name="cityOrMun_addr_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="cityOrMun_addrFldMsg"></span>
							</div>
						</div>	
						<div class="row" id="province_addrFld">														
							<div class="label" >
								<label class="label" for="province_addr">Province</label>
							</div>
							<div class="collection">
								<input type="text" name="province_addr" />
								<input type="hidden" name="province_addr_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="province_addrFldMsg"></span>
							</div>
						</div>							
					</fieldset>
					<fieldset class="fieldsCollection uplb">						
						<legend class="field_grouping_bar">uplb identification</legend>
						<div id="uplb_ident_explain">
						<span class="field_explain_logo">
							<img src="<?php echo base_url().'images/appbar.basecircle.rest.png'; ?>" alt="" title="" style="border:0" />
						</span>
						<span>
							We need this information for reporting your attendance in the events
							you are attending. You must fill out at least one if you choose the Yes option. <br/><br/>
							Please enter the details without dashes.
						</span>
					</div>
						<div class="row" id="uplbConstituentBooleanFld">							
							<div class="label" >
								<label class="label" for="uplbConstituentBoolean">Are you a UPLB student/employee?</label>
							</div>
							<div class="collection">
								<input type="checkbox" name="uplbConstituentBoolean" value="1" />Yes																											
							</div>
							<br/>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="uplbConstituentBooleanFldMsg"></span>
							</div>
						</div>
						<div class="row" id="studentNumberFld">														
							<div class="label" >
								<label class="label" for="studentNumber">Student number</label>
							</div>
							<div class="collection">
								<input type="text" name="studentNumber" disabled="disabled" maxlength="9" value="disabled" />
								<input type="hidden" name="studentNumber_validate" value="-1" />
							</div>
							<br/>
							<div class="msgContainer">
								<div class="icon"  ></div>
								<span id="studentNumberFldMsg" ></span>
							</div>
						</div>
						<div class="row" id="employeeNumberFld">														
							<div class="label" >
								<label class="label" for="employeeNumber">Employee number</label>
							</div>
							<div class="collection">
								<input type="text" name="employeeNumber" disabled="disabled" maxlength="10" value="disabled" />
								<input type="hidden" name="employeeNumber_validate" value="-1" />
							</div>
							<br/>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="employeeNumberFldMsg"></span>
							</div>
						</div>										
					</fieldset>																	
					
					<div id="essentialButtonsArea">
						<a class="button" id="buttonOK" ><span class="icon">Sign me up</span></a>
						<a class="button" id="buttonReset" ><span class="icon">Reset fields</span></a>
					</div>
					
				</div>
				
			</div>
			</form> <!--end of form -->
		<div style=" clear:both;"></div>
		</div><!--end of centralContainer-->
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</body>
</div>
</html>