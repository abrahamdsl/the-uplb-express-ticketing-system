<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Create Seat Map";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/stillUnconfiguredNotice.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>			
	
	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/stillUnconfiguredNotice.js'; ?>"/></script>				
	
	
  	
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
        
    
    <div id="main_content" >    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Success
			</div>
			<div style="padding-left:10px; clear: both">	
				<br/>&nbsp;				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Message</div>
					<div id="content">						
						<input type="hidden" id="lastFocus" value="" />
						<input type="hidden" id="lastFocus_class" value="" />												
						<form method="post"  action="<?php echo base_url(); ?>seatctrl/manageseatmap" name="formLogin" id="formMain">
							<input type="hidden" id="repeat" name="repeat" value="true" />
							<div class="center_purest" id="mini-warning_text-proper" >
								This seat map can now be used in creating an event.<br/>
								We hope you enjoy using this app.
							</div>
							<div id="essentialButtonsArea">								
								<a class="button" id="buttonOK" ><span class="icon">Back to Seat Maps</span></a>																						
							</div>	
						</form>						
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			
			<div style=" clear:both;"></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>