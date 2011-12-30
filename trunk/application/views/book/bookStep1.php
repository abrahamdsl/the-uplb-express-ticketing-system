<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Book a Ticket/Post Reservation";
	$this->thisPage_menuCorrespond = "Create Event Step 6";
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
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"/></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep1.js'; ?>"/></script>				
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
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
		<?php
			$this->load->view('html-generic/userInfo-bar.inc');
		?>			
    </div>
        
    
    <div id="main_content" >    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Book a ticket | Post a Reservation
			</div>
			<div style="padding-left:10px; clear: both">
				The fun starts here.
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Select now</div>
					<div id="content">												
						<input type="hidden" id="lastFocus" value="" />
						<form method="post"  action="<?php echo base_url().'EventCtrl/book_step2' ?>" name="formLogin" id="formMain">							
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Step 1: Select an event
									</span>
									<span class="right" id="right_inner" >	
										<span class="center_purest">
											<select id="eventSelection" name="events" class="center_purest" style="width: 80%;" >
													<option value="NULL" >
														<?php
															if( count ($configuredEventsInfo ) > 0 ){
														?>
																Select Event
														<?php
															}else{
														?>
																No events found at this time
														<?php } ?>
													</option>
												<?php
													foreach(  $configuredEventsInfo as $singleEvent )
													{
												?>
													<option value="<?php echo $singleEvent->EventID?>"><?php echo $singleEvent->Name; ?></option>
												<?php
													}
												?>
											</select>
										</span>
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Step 2: Select a showing time
									</span>
									<span class="right" id="right_inner" >
										<span id="showtimeDummy" >
											Select an event first
										</span>
										<span id="showtimeCustomError" >											
										</span>
										<span id="showtimeWaiting" hidden="true"  >										
											<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" />
										</span>
										<span id="showtimeSelectionReal" hidden="true" >
											<select id="showingTimeSelection" name="showingTimes" class="center_purest" style="width: 80%;" >
											</select>
										</span>
										
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Step 3: Select the quantity
									</span>
									<span class="right"  id="right_inner" >
										<input type="text" class="commonality ayokongDefaultAngItsuraNgButton" id="slot" name="slot" value="1" /><br/>
										<input type="button" value="-" id="reduceSlots" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
										<input type="button" value="+" id="addSlots" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
									</span>
								</div>								
							</div>							
						</form>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
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