<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Check-out";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css';?>" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/attend01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"></script>		
	<script type="text/javascript">
		 $(document).ready(function() {
			$("#accordion").accordion();
		  });
	</script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/attend01b.js'; ?>"></script>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
?>	
<div id="main_container" >
	<div id="header" >    	    	        
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
		<div id="centralContainer" >
			<div id="page_title" >
				Check out
			</div>
			
			<div id="instruction" >
				Key in the booking number.
			</div>				
			<!-- accordion start -->			
			<div class="accordionContainer center_purest" >
				<div id="accordion" >
					<h3><a href="#" >Basic details</a></h3>					
					<form method="post"  action="<?php echo base_url().'EventCtrl/confirm_step2' ?>" name="formLogin" id="formMain" >						
						<div>
							<h2>
								<?php echo $eventObj->Name; ?>
							</h2>
							<p>
								<?php echo date( 'Y-M-d l ', strtotime($showtimeObj->StartDate) );
									 /*
											No need to show seconds if zero
										*/
										$splitted = explode(':', $showtimeObj->StartTime);
										$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
										echo date( $timeFormat." A", strtotime($showtimeObj->StartTime));
										echo " to ";
								?>
								<?php
									if(  $showtimeObj->StartDate !=  $showtimeObj->EndDate) echo date( 'Y-M-d l', strtotime($showtimeObj->EndDate) );									
									$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
									echo date( $timeFormat." A", strtotime($showtimeObj->EndTime));									
								?>
							</p>
							<div class="mainWizardMainSections" >
								<span class="MWMS1" >
									Booking number
									<span class="critical" >*</span>
								</span>
								<span class="MWMS2" ><input type="text" name="bookingNumber" class="textInputSize" /></span>					
								<span class="MWMShidden fieldErrorNotice NameRequired" >This is not allowed to be blank</span>
								<span class="MWMShidden fieldErrorNotice" id="ajaxind" >
									<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" alt="ajax_loader" />
								</span>	
							</div>							
						</div>					
					 </form>				
				</div> <!-- accordion -->
				<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
					<div class="accordionImitation cEvent04_container aci1_Book3Special" id="bookingDetails" style="display:none;" >						
						<div id="content" >		
								<p>
									If the checkbox for a guest is disabled, it means they have
									earlier exited
								</p>
								<form method="post" action="#" id="guestdetails">
									<input type="hidden" name="bookingNumber2" value="-1" />
									<table id="maintable" class="center_purest schedulesCentral" >
										<thead>
											<tr>
												<td>Select?</td>
												<td>Name</td>
												<td>Seat</td>
											</tr>
										</thead>
										<tbody>
										</tbody>
								</table>
								</form>
							</table>
						</div>
					</div>
				</div>
				
	<?php
	$this->load->view( 'html-generic/criticalreminder.inc' );
	?>	
				<div id="essentialButtonsArea" >							
							<a class="button" id="buttonOK2" ><span class="icon" >Confirm Exit</span></a>							
							<a class="button" id="buttonOK" ><span class="icon" >Check Details</span></a>
							<a class="button" id="buttonOK3" ><span class="icon" >Clear Fields</span></a>
							<a class="button" id="buttonReset" ><span class="icon" >Change session</span></a>							
				</div>
				<div class="buttonfooterSeparator" ></div>
			</div>						
		</div>
    </div><!--end of main content-->	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>