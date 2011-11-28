<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Welcome UXT User!";
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
				Finished
			</div>
			<div style="padding-left:10px; clear: both">
				You have successfully signed up for the UPLB Express Ticketing System
				<br/>
				To start using the site, please click Start.
				We hope you enjoy the services we offer. <br/>
				Have a nice day! </br/>
				<br/><br/>
				By the way, your account number is:				
			</div>			
				<div class="comingSoon highlightMe center_purest"  >
					<span>
						<h1><?php echo $userData['accountNum']; ?></h1>
					</span>					
				</div>		
				<div id="essentialButtonsArea">
								<a href="<?php echo base_url().'SessionCtrl/' ?>" class="button" id="buttonOK" ><span class="icon">Start</span></a>						
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