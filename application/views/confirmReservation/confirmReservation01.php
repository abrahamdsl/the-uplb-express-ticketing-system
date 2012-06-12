<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Confirm Reservation";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css';?>" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"></script>
	<script type="text/javascript">
		 $(document).ready(function() {
			$("#accordion").accordion();
		  });
	</script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/confirmReservation01.js'; ?>"></script>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/processAJAXresponse.js'; ?>" ></script>
	<?php 
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
?>	
<div id="main_container" >
	<div id="header" >
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
		<div id="centralContainer" >
			<div id="page_title" >
				Confirm Booking
			</div>
			<div id="instruction" >
				Please enter the booking number. 
			</div>
			<!-- accordion start -->
			<div class="accordionContainer center_purest" >
				<div id="accordion" >
					<h3><a href="#" >&nbsp;</a></h3>
					<form method="post"  action="<?php echo base_url().'EventCtrl/confirm_step2' ?>" name="formLogin" id="formMain" >
						<div>
							<div class="mainWizardMainSections" >
								<span class="MWMS1" >
									Booking number
									<span class="critical" >*</span>
								</span>
								<span class="MWMS2" ><input type="text" name="bookingNumber" class="textInputSize" /></span>
								<span class="MWMShidden fieldErrorNotice NameRequired" >This is not allowed to be blank</span>
								<span class="MWMShidden fieldErrorNotice" id="ajaxind" >
									<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" alt="ajax_loader" />
								</span>	
							</div>
							<div style="width: 90%; clear: both;" id="confirm1_ajax" >

							</div>
						</div>
					</form>
				</div> <!-- accordion -->
	<?php
	$this->load->view( 'html-generic/criticalreminder.inc' );
	?>
				<div id="essentialButtonsArea" >
							<a class="button" id="buttonOK" ><span class="icon" >Next</span></a>
				</div>
				<div class="buttonfooterSeparator" ></div>
			</div>
		</div>
    </div><!--end of main content-->
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>