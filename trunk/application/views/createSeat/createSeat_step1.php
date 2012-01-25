<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Create Seat Map - Step 1";
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createSeat_01.js'; ?>"/></script>	
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
				Create Seat Map
			</div>
			<div style="padding-left:10px; clear: both">
				Please fill out the following fields.
			</div>				
			<!-- accordion start -->
			<!--  -->
			<div class="accordionContainer center_purest">
				<div id="accordion" >
					<h3><a href="#">Dimensions</a></h3>					
					<form method="post"  action="<?php echo base_url().'SeatCtrl/create_step2' ?>" name="formLogin" id="formMain">						
						<div>
							<div class="mainWizardMainSections">
								<span class="MWMS1" >Name</span>
								<span class="MWMS2"><input type="text" name="name" class="textInputSize" id="rows" /></span>					
								<span class="MWMShidden fieldErrorNotice" id="info_req_name" hidden="true" >This is not allowed to be blank</span>																
							</div>
							<div class="mainWizardMainSections">
								<span class="MWMS1" >Rows</span>
								<span class="MWMS2"><input type="text" name="rows" class="textInputSize" id="rows" /></span>					
								<span class="MWMShidden fieldErrorNotice" id="info_req_rows" hidden="true" >This is not allowed to be blank</span>																
							</div>
							<div class="mainWizardMainSections">
								<span class="MWMS1" >Columns</span>
								<span class="MWMS2"><input type="text" name="cols" class="textInputSize" id="cols" /></span>					
								<span class="MWMShidden fieldErrorNotice" id="info_req_cols" hidden="true" >This is not allowed to be blank</span>																
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