<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Sign up";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup-actionListener.js'; ?>"/></script>
</head>
<body>
<div id="main_container">
	<div id="header">    	    	        
		<?php
			$this->load->view('html-generic/headerimage.inc');
		?>
        <?php
			$this->load->view('html-generic/menu-bar.inc');
		?>		
		<div id="graynavbar" >		
			<!-- <ul>
				<li>a</li>
				<li>b</li>
				<li class="last">
					<a href='login/logout' class='underline'>Log out</a>
				</li>
			</ul>
			-->			
		</div>
        
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Sign up for the UPLB Express Ticketing System | Step 1
			</div>
			<div style="padding-left:10px; clear: both">
				Please complete the following information:
			</div>				
			<!-- start of form -->
			<!--onSubmit="return OnSubmitForm5();"-->
			<form method="post"  action="<?php echo base_url().'userAccountCtrl/userSignup_step2' ?>" name="formSignup" id="formMain">

				<div class="center_pure">
					<fieldset class="fieldsCollection">						
						<legend class="field_grouping_bar">login credentials</legend>
						<div class="row" id="usernameFld">							
							<label class="label" for="username">Username</label>							
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
							<label class="label" for="password">Password</label>
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
							<label class="label" for="confirmPassword">Confirm Password</label>
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
							<label class="label" for="firstName">First name</label>							
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
							<label class="label" for="middleName">Middle name</label>							
							<div class="collection">
								<input type="text" name="middleName" />
								<input type="hidden" name="middleName_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="middleNameFldMsg"></span>
							</div>
						</div>
						<div class="row" id="lastNameFld">							
							<label class="label" for="lastName">Last name</label>							
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
							<label class="label" for="gender">Gender</label>							
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
							<label class="label" for="cellPhone">Cellphone</label>							
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
							<label class="label" for="landline">Landline Phone</label>							
							<div class="collection">
								<input type="text" name="landline" />
								<input type="hidden" name="landline_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="landlineFldMsg"></span>
							</div>
						</div>
						<div class="row" id="email_01_Fld">							
							<label class="label" for="email_01_">E-mail address</label>							
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
							<label class="label" for="homeAndStreet_addr">Home & Street</label>							
							<div class="collection">
								<input type="text" name="homeAndStreet_addr" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="homeAndStreet_addrFldMsg"></span>
							</div>
						</div>
						<div class="row" id="barangay_addrFld">							
							<label class="label" for="barangay_addr">Barangay</label>							
							<div class="collection">
								<input type="text" name="barangay_addr" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="barangay_addrFldMsg"></span>
							</div>
						</div>
						<div class="row" id="cityOrMun_addrFld">							
							<label class="label" for="cityOrMun_addr">City/Municipality</label>							
							<div class="collection">
								<input type="text" name="cityOrMun_addr" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="cityOrMun_addrFldMsg"></span>
							</div>
						</div>	
						<div class="row" id="province_addrFld">							
							<label class="label" for="province_addr">Province</label>							
							<div class="collection">
								<input type="text" name="province_addr" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="province_addrFldMsg"></span>
							</div>
						</div>							
					</fieldset>
					<fieldset class="fieldsCollection">						
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
							<label class="label" for="uplbConstituentBoolean">Are you a UPLB student/employee?</label>							
							<div class="collection">
								<input type="checkbox" name="uplbConstituentBoolean" value="1" />Yes																											
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="uplbConstituentBooleanFldMsg"></span>
							</div>
						</div>
						<div class="row" id="studentNumberFld">							
							<label class="label" for="studentNumber">Student number</label>							
							<div class="collection">
								<input type="text" name="studentNumber" disabled="disabled" maxlength="9" value="disabled" />
								<input type="hidden" name="studentNumber_validate" value="-1" />
							</div>
							<div class="msgContainer">
								<div class="icon" id="puke" ></div>
								<span id="studentNumberFldMsg" ></span>
							</div>
						</div>
						<div class="row" id="employeeNumberFld">							
							<label class="label" for="employeeNumber">Employee number</label>							
							<div class="collection">
								<input type="text" name="employeeNumber" disabled="disabled" maxlength="10" value="disabled" />
								<input type="hidden" name="employeeNumber_validate" value="-1" />
							</div>
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