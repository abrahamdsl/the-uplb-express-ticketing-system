<?php
$this->load->view('html-generic/doctype.inc');
// the tag '<html>' is already in the doctype.inc
?>
<head>
<?php
	$this->pageTitle = "Title";
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

</head>
<body>
<!-- <div id="main_container"> -->
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
				Title
			</div>
			<div style="padding-left:10px; clear: both">
				Konting pasakalye
			</div>				
			<div class="mainWizardBox">
				<div class="mainWizardTop">
					<span>Create Event</span>
					</span>basic details</span>
				</div>
				<div class="mainWizardMainSections">
					<span>Name</span>
					</span><input type="text" name="eventName" /></span>					
				</div>
				<div class="mainWizardMainSections">
					<span>Facebook RSVP Page</span>
					</span><input type="text" name="eventFBRSVP" /></span>					
				</div>
				<div id="essentialButtonsArea">
						<a onClick="document.pressed=this.value" class="button" id="buttonOK" ><span class="icon">Sign me up</span></a>
						<a class="button" id="buttonReset" ><span class="icon">Reset fields</span></a>
					</div>
			</div>							
		</div><!--end of centralContainer-->			
		<div style=" clear:both;"></div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
<!-- </div>--><!-- main_container -->
</body>
</html>