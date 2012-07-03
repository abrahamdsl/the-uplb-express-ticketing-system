<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Book a Ticket - Add Classes To Booking";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep3_B.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
	
	
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
				Add classes to booking
			</div>
			<div id="instruction" >
				Associate your attendance with the classes. <br/><br/>
				One of your guests have UP Student number or Employee number thus you were redirected here.<br/><br/>
				Select all classes that apply to any of the guests under this booking. Then in the next page, you 
				will match these to the guests, or<br/>
				you can skip this step.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<form method="post"  action="<?php echo base_url().'academicctrl/associateClassToBooking_process1' ?>" name="formLogin" id="formMain">					
					<div id="tabs">
					<ul>						
						<?php
						foreach( $activeClasses as $key => $val )
							{
						?>
							<li><a href="#d-<?php echo $key; ?>"><?php echo $key?></a></li>
						<?php
							}
						?>
					</ul>
					<?php
						foreach(  $activeClasses as $key => $val )
						{
					?>
							<div id="d-<?php echo $key; ?>" style="min-height: 300px; height: auto;">
							<table class="center_purest schedulesCentral" style="text-align: center;" >
								<thead>
									<tr>
										<td>Select?</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td>Instructor</td>
									</tr>
								</thead>
								<tbody>
								<?php
									$x=0;
									foreach( $val as $xy  )
									{										
									
								?>
									<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
										<td>											
											<input type="checkbox" name="<?php echo $key; ?>_selected_<?php echo $xy->UUID; ?>"  />
										</td>
										<td><?php echo $xy->CourseTitle;?></td>
										<td><?php echo $xy->CourseNum;?></td>
										<td><?php echo $xy->LectureSect;?></td>
										<td><?php echo $xy->RecitSect;?></td>
										<td><?php echo $xy->Lname.", ".$xy->Fname." ".$xy->Mname;?></td>										
									<tr>
								<?php
									}
								?>
								</tbody>
								</table>
								<div style="position: absolute; bottom: 0; margin-bottom: 10px;">
									<input type="hidden" class="letter" value="<?php echo $key; ?>" />
									<input type="button" value="Check all" class="selectDeselectBtns" id="checkAll__d-<?php echo str_replace( array('/',' '), '_' , $key ); ?>"/>
									<input type="button" value="Uncheck all" class="selectDeselectBtns" id="UncheckAll__d-<?php echo str_replace( array('/',' '), '_' , $key ); ?>"/>
								</div>
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
			<div class="buttonfooterSeparator" ></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>