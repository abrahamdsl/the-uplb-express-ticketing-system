<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Home";
	$this->thisPage_menuCorrespond = "Create Event Step 3";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<!--For overlay-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>		
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent_003.js'; ?>"/></script>			
	
	<!--For overlay-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/overlay_general.js'; ?>"/></script>	
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
				Step 3: Creating Event ' <?php echo $_COOKIE['eventName']; ?> '
			</div>
			<div style="padding-left:10px; clear: both">
				Choose which showing times is for which dates.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<form method="post"  action="<?php echo base_url().'EventCtrl/create_step4' ?>" name="formLogin" id="formMain">
					<!-- <input type="hidden" name="cEvent003uplbxts" value="9024028541021312" /> -->
					<div id="tabs">
					<ul>						
						<?php
						foreach( $scheduleMatrix as $key => $val )
							{
						?>
							<li><a href="#<?php echo str_replace( array('/',' '), '_' , $key ); ?>"><?php echo $key?></a></li>
						<?php
							}
						?>
					</ul>
					<?php
						foreach(  $scheduleMatrix as $key => $val )
						{
					?>
							<div id="<?php echo str_replace( array('/',' '), '_' , $key ); ?>">
								<?php
									foreach( $val as $xy  )
									{										
								?>
									<p>
										<input type="checkbox" id="ch_<?php echo str_replace( array('/',' '), '_' , $key )."_".str_replace( array('/',' '), '_' , $xy );;?>" name="<?php echo $key."x".$xy;?>" />
										<label for="ch_<?php echo str_replace( array('/',' '), '_' , $key )."_".str_replace( array('/',' '), '_' , $xy );;?>"><?php echo $xy; ?></label><br/>
									</p>
								<?php
									}
								?>
								<input type="button" value="Check all" class="selectDeselectBtns" id="checkAll__<?php echo str_replace( array('/',' '), '_' , $key ); ?>"/>
								<input type="button" value="Uncheck all" class="selectDeselectBtns" id="UncheckAll__<?php echo str_replace( array('/',' '), '_' , $key ); ?>"/>
							</div>
					<?php
						}
					?>					
					</div>	
				</form>
			
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
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