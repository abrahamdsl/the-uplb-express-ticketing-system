<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Home";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/manageEventMain.js'; ?>"/></script>				
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
				Manage Events
			</div>
			<div style="padding-left:10px; clear: both">
				Now, what should we do? :-)
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >			
			<div id="accordion" >
				<?php
					if( count ( $events ) != 0 )
				{
					foreach( $events as $eachEvent )
					{
				?>
				<h3><a href="#"><?php echo $eachEvent->EventID." | ".$eachEvent->Name; ?></a></h3>
				<div>			
					<form method="post"  action="<?php echo base_url().'EventCtrl/deleteEventCompletely' ?>" name="formDelete__<?php echo $eachEvent->EventID; ?>" id="form_<?php echo $eachEvent->EventID; ?>">
						<input type="hidden" name="eventID" value="<?php echo $eachEvent->EventID; ?>" />
						<input type="submit" id="deleteAll_<?php echo $eachEvent->EventID; ?>" value="Delete all" />
					</form>
				</div>				
				<?php
					}//foreach
				}//if
				else{
					echo "No event found";
				}
				?>
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