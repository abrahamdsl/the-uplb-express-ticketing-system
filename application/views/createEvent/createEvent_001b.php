<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Create Event!";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->

	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent_001b.js'; ?>"/></script>	
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
		<?php
			$this->load->view('html-generic/userInfo-bar.inc');
		?>			
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Create Event 
			</div>
			<div style="padding-left:10px; clear: both">
				Please fill out the following fields.
			</div>				
			<!-- accordion start -->
			<!--  -->
			<div class="accordionContainer center_purest">
				<div id="accordion" >
					<h3><a href="#">Basic details</a></h3>					
					<form method="post"  action="<?php echo base_url().'EventCtrl/create_step2' ?>" name="formLogin" id="formMain">
						<input type="hidden" id="allIsWell" value="0" />
						<div>
							<div class="mainWizardMainSections">
								<span class="MWMS1" >Name (Required)</span>
								<span class="MWMS2"><input type="text" name="eventName" class="textInputSize" id="id_eventName" /></span>					
								<span class="MWMShidden fieldErrorNotice" id="NameRequired" hidden="true" >This is not allowed to be blank</span>								
								<span class="MWMShidden fieldErrorNotice" id="EventExists" hidden="true" ><p>An event with the same name already exists</p></span>
							</div>
							<div class="mainWizardMainSections">
								<span class="MWMS1" >Facebook RSVP Page</span>
								<span class="MWMS2"><input type="text" name="eventFBRSVP" class="textInputSize" /></span>					
							</div>
						</div>					
					</form>
				</div> <!-- accordion -->
				<div id="essentialButtonsArea">
							<a onClick="document.pressed=this.value" class="button" id="buttonOK" ><span class="icon">Next</span></a>							
				</div>	
			</div>
			
			<div style=" clear:both;"></div>
		</div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>