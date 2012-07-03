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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep1.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/confirmReservation01.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/confirmReservation01.js'; ?>"></script>	
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
		<?php
			$this->load->view('html-generic/userInfo-bar.inc');
		?>			
    </div>
        
    
    <div id="main_content" >    	
    	<div id="centralContainer">	
			<div id="page_title">
				Confirm Reservation
			</div>
			<div id="instruction" >
				Please fill out the following fields.
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">&nbsp;</div>
					<div id="content">												
						<input type="hidden" id="lastFocus" value="" />
						<input type="hidden" id="slotEnabledClass" value="commonality ayokongDefaultAngItsuraNgButton enabled" />
						<input type="hidden" id="slotDisabledClass" value="commonality ayokongDefaultAngItsuraNgButton disabled" />
						<input type="hidden" id="adjustEnabledClass" value="adjustButtons ayokongDefaultAngItsuraNgButton enabled" />
						<input type="hidden" id="adjustDisabledClass" value="adjustButtons ayokongDefaultAngItsuraNgButton disabled" />
						
						<form method="post"  action="<?php echo base_url().'eventctrl/confirm_step' ?>" name="formLogin" id="formMain">							
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Booking number
									</span>
									<span class="rightSpecialHere" >
										<input type="text" name="bookingNumber" class="textInputSize" />
									</span>
								</div>
								<div class="KoreanPeninsula" style="text-align: center;" >
									<div id="confirm1_ajax-b" >
										<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" alt="ajax_loader" />
									</div>
								
								</div>														
							</div>							
						</form>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<!--<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a> -->
			</div>	
			<div id="misc" style=" clear:both;"></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>