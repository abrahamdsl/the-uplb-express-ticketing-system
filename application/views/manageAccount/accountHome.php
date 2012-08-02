<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Account settings";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup-rev7.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageAccount01.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/processAJAXresponse.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup-actionListener.js'; ?>"/></script>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/airtraffic_v2.js'; ?>" ></script>
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
?>	
<input type="hidden" id="isCurrentPageUserAccount" value="1" /><!-- needed for javascript -->
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
    <div id="main_content">
    	<div id="centralContainer">
			<div id="page_title">
				Profile and Account Settings
			</div>
			<div id="instruction">
				Change your basic account and system settings.
			</div>
			<!-- start of form -->
			<form method="post"  action="useracctctrl/manageAccountSave" name="formSignup" id="formMain">
				<div class="center_pure">
					<fieldset class="fieldsCollection">
						<legend class="field_grouping_bar">login credentials</legend>
						<div class="row" id="usernameFld">
							<div class="label" >
								<label class="label" for="username">Username</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="text" name="username" value="<?php echo $userObj->username; ?>" />
								<input type="hidden" name="username_validate" value="1" />
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
								<a href="<?php echo base_url(); ?>useracctctrl/changePassword_step1">Change Password</a>
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="passwordFldMsg"></span>
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
								<input type="text" name="firstName" value="<?php echo $userObj->Fname; ?>" />
								<input type="hidden" name="firstName_validate" value="1" />
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
								<input type="text" name="middleName" value="<?php echo $userObj->Mname; ?>" />
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
								<input type="text" name="lastName" value="<?php echo $userObj->Lname; ?>" />
								<input type="hidden" name="lastName_validate" value="1" />
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
								<input type="radio" name="gender" value="MALE" <?php if($userObj->Gender == "MALE" ){ ?> checked="checked"  <?php } ?> />Male
								<input type="radio" name="gender" value="FEMALE" <?php if($userObj->Gender == "FEMALE" ){ ?> checked="checked"  <?php } ?> />Female
								<input type="hidden" name="gender_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="genderFldMsg"></span>
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
								<input type="text" name="cellPhone" value="<?php echo $userObj->Cellphone; ?>" />
								<input type="hidden" name="cellPhone_validate" value="1" />
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
								<input type="text" name="landline" value="<?php if( $userObj->Landline != "0" ){ echo $userObj->Landline;  } else { echo ""; } ?>" />
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
								<input type="text" name="email_01_"  value="<?php echo $userObj->Email; ?>" />
								<input type="hidden" name="email_01__validate" value="1" />
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
								<input type="text" name="homeAndStreet_addr" value="<?php echo $userObj->addr_homestreet; ?>" />
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
								<input type="text" name="barangay_addr" value="<?php echo $userObj->addr_barangay; ?>" />
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
								<input type="text" name="cityOrMun_addr" value="<?php echo $userObj->addr_cityMunicipality; ?>" />
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
								<input type="text" name="province_addr" value="<?php echo $userObj->addr_province; ?>" />
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
						<span>
							Please enter the details without dashes.
						</span>
						<?php
							$lbCons = ( $uplbConstituencyObj !== false );
						?>
					</div>
						<div class="row" id="uplbConstituentBooleanFld">
							<div class="label" >
								<label class="label" for="uplbConstituentBoolean">Are you a UPLB student/employee?</label>
							</div>
							<div class="collection">
								<input type="checkbox" name="uplbConstituentBoolean" value="1" <?php if($lbCons){ ?> checked="checked"  <?php } ?> />Yes
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
								<input type="text" name="studentNumber" <?php if(!$lbCons){ ?> disabled="disabled"<?php } ?> maxlength="9" value="<?php if($lbCons) { echo $uplbConstituencyObj->studentNumber; } else { echo ' ';}?>" />
								<input type="hidden" name="studentNumber_validate" value="<?php if($lbCons AND isset($uplbConstituencyObj->studentNumber)){ ?>1<?php }else{?>-2<?php }?>" />
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
								<input type="text" name="employeeNumber" <?php if(!$lbCons){ ?> disabled="disabled"<?php } ?>  maxlength="10" value="<?php if($lbCons) {echo $uplbConstituencyObj->employeeNumber;} else { echo ' ';}  ?>" />
								<input type="hidden" name="employeeNumber_validate" value="<?php if($lbCons AND isset($uplbConstituencyObj->employeeNumber)){?>1<?php }else{?>-2<?php }?>" />
							</div>
							<br/>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="employeeNumberFldMsg"></span>
							</div>
						</div>
					</fieldset>
					<fieldset class="fieldsCollection">
						<legend class="field_grouping_bar">booking settings</legend>
						<div class="row" id="allowfriendsFld">
							<div class="label" >
								<label class="label" for="allowfriends">Allow friends to book me</label>
							</div>
							<div class="collection">
								<input type="checkbox" name="allowfriends" <?php if( intval($userObj->BookableByFriend) === 1){ ?> checked="checked"  <?php } ?> />
								<input type="hidden" name="allowfriends_validate" value="1" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="allowfriendsFldMsg"></span>
							</div>
						</div>
					</fieldset>
					<fieldset class="fieldsCollection roles">
						<legend class="field_grouping_bar">roles</legend>
						<p>
							This feature is under development. Meanwhile, the Administrator of this system
							can edit your access permissions, so you seek him/her. 
							
							:P
						</p>
						<?php
							$noAccess  = "Request for this role";
							$yesAccess = "Remove this role";
							$adminRoleCaption = "" ;
							$eventMgrRoleCaption = "" ;
							$receptionistRoleCaption = "" ;
							$facultyRoleCaption = "" ;
						?>
						<table class="center_purest schedulesCentral" style="text-align: center;" >
							<thead>
								<tr>
									<td>Role</td>
									<td>Granted?</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
							</thead>
							<tbody>
								<tr id="customer" class="even" >
									<td>Customer</td>
									<td>YES</td>
									<td>This role cannot be removed</td>
									<td></td>
								</tr>
								<tr id="eventmanager" class="odd" >
									<td>Event Manager</td>
									<td>
										<?php
										if( intval($permissionsObj->EVENT_MANAGER) === 1 ){
											$eventMgrRoleCaption = $yesAccess;
										?>
											YES
										<?php }else{ 
												$eventMgrRoleCaption = $noAccess;
										?>
											NO
										<?php } ?>
									</td>
									<td><input type="button" name="togglePermission_eventMgr" value="<?php echo $eventMgrRoleCaption; ?>" /></td>
									<td><input type="button" name="seeDo_eventMgr" value="What can you do" /></td>
								</tr>
								<tr id="receptionist" class="even" >
									<td>Receptionist</td>
									<td>
										<?php
										if( intval($permissionsObj->RECEPTIONIST) === 1 ){
											$receptionistRoleCaption = $yesAccess;
										?>
											YES
										<?php }else{ 
												$receptionistRoleCaption = $noAccess;
										?>
											NO
										<?php } ?>
									</td>
									<td><input type="button" name="togglePermission_receptionist" value="<?php echo $receptionistRoleCaption; ?>" /></td>
									<td><input type="button" name="seeDo_receptionist" value="What can you do" /></td>
								</tr>
								<tr id="faculty" class="odd"  >
									<td>Faculty Member</td>
									<td>
										<?php
										if( intval($permissionsObj->FACULTY) === 1 ){
											$facultyRoleCaption = $yesAccess;
									?>
										YES
									<?php }else{ 
											$facultyRoleCaption = $noAccess;
									?>
										NO
									<?php } ?>
									</td>
									<td><input type="button" name="togglePermission_faculty" value="<?php echo $facultyRoleCaption; ?>" /></td>
									<td>
										<input type="button" name="seeDo_faculty" value="What can you do" />
									</td>
								</tr>
								<tr id="administrator" class="even" >
									<td>Administrator</td>
									<td>
										<?php
										if( intval($permissionsObj->ADMINISTRATOR) === 1 ){
											$adminRoleCaption = $yesAccess;
										?>
											YES
										<?php }else{ 
												$adminRoleCaption = $noAccess;
										?>
											NO
										<?php } ?>
									</td>
									<td><input type="button" name="togglePermission_admin"  value=<?php echo $adminRoleCaption; ?>" /></td>
									<td><input type="button" name="seeDo_admin" value="What can you do" /></td>
								</tr>
							</tbody>
						</table>
					</fieldset>
					<div id="essentialButtonsArea">
						<a class="button" id="buttonOK" ><span class="icon">Save Changes</span></a>
						<a class="button" id="buttonReset" ><span class="icon">Reset fields</span></a>
						<a class="button" id="buttonCancel" ><span class="icon">Cancel</span></a>
					</div>
				</div>
			</div>
			</form>
		<div style=" clear:both;"></div>
		</div><!--end of centralContainer-->
    </div><!--end of main content-->
<?php
	$this->load->view('html-generic/footer.inc');
?>
</body>
</div>
</html>