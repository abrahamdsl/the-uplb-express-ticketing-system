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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>

	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>	

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
				Sign up for the UPLB Express Ticketing System | Step 2
			</div>
			<div style="padding-left:10px; clear: both">
				Would you like to connect your social networking accounts? If so, click the corresponding image for that
				account. <br/> You can also click next immediately if you want to skip this.
			</div>
			<form name="socialNetworkForm" action="<?php echo base_url()."useracctctrl/newUserWelcome"; ?>" method="post" >
				<input type="hidden" name="formValidityIndicator" value="*888" /> <!-- *888 some arbitrary value -->
				<div class="comingSoon" >
					<h1>This feature is coming soon.</h1>
				</div>		
				<div id="essentialButtonsArea">
								<a onClick="javascript: document.socialNetworkForm.submit()" class="button" id="buttonOK" ><span class="icon">Next</span></a>						
				</div>
			</form>
			<div style=" clear:both;"></div>
		</div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>