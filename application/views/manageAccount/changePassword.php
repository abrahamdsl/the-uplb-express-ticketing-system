<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Change Password";
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
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/processAJAXresponse.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/usersignup-actionListener.js'; ?>"/></script>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/changePassword.js'; ?>"/></script>
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
				Change Password
			</div>
			<div id="instruction" >
				Feeling not secure anymore? Then change it here now.
			</div>
			<!-- start of form -->
			<form method="post"  action="useracctctrl/changePassword_step2" name="formSignup" id="formMain">
				<div class="center_pure">
					<fieldset class="fieldsCollection">
						<div class="row" id="oldPasswordFld">
							<div class="label" >
								<label class="label" for="oldPassword">Old Password</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="password" name="oldPassword" value="" />
								<input type="hidden" name="oldPassword_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="oldPasswordFldMsg"></span>
							</div>
						</div>
						<div class="row" id="passwordFld">
							<div class="label" >
								<label class="label" for="password">New Password</label>
								<span class="critical" >*</span>
							</div>
							<div class="collection">
								<input type="password" name="password" value="" />
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
								<input type="password" name="confirmPassword" value="" />
								<input type="hidden" name="confirmPassword_validate" value="0" />
							</div>
							<div class="msgContainer">
								<div class="icon"></div>
								<span id="confirmPasswordFldMsg"></span>
							</div>
						</div>
					</fieldset>
					<?php
						$this->load->view( 'html-generic/criticalreminder.inc' );
					?>
					<div id="essentialButtonsArea">
						<a class="button" id="buttonOK_special" ><span class="icon">Save Changes</span></a>
						<a class="button" id="buttonReset_special" ><span class="icon">Reset fields</span></a>
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