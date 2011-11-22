<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Sign up";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/head-title.inc');
	$this->load->view('css/body_all.css');	
	$this->load->view('css/buttonOK.css');
	$this->load->view('javascript/jquery-core.inc');
	$this->load->view('javascript/proceedbutton_jquery.inc');
	$this->load->view('javascript/resetbutton_jquery.inc');	
?>
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
		
		<div id="graynavbar" >		
			<ul>
				<li>a</li>
				<li>b</li>
				<li class="last">
					<a href='login/logout' class='underline'>Log out</a>
				</li>
			</ul>			
		</div>
        
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Sign up for the UPLB Express Ticketing System | Step 2
			</div>
			<div style="padding-left:10px; clear: both">
				Would you like to connect your social networking accounts? If so, click the corresponding image for that
				account. You can also click next immediately if you want to skip this.
			</div>
			<form name="socialNetworkForm" action="<?php echo base_url()."userAccountCtrl/newUserWelcome"; ?>" method="post" >
				<input type="hidden" name="formValidityIndicator" value="*888" /> <!-- *888 some arbitrary value -->
				<div style="text-align: center" >
					<h1>This feature is coming soon.</h1>
				</div>		
				<div id="essentialButtonsArea">
								<a onClick="javascript: document.socialNetworkForm.submit()" class="button" id="buttonOK" ><span class="icon">Next</span></a>						
				</div>
			</form>
			<div style=" clear:both;"></div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</body>
<html>