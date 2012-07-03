<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Create Event - Step 2";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent02.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<?php
		$this->load->view('html-generic/jquery-core_choiceB.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.ui.datepicker.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/datepickerBoot.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.ui.timepicker.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/timepickerBoot.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent_002.js'; ?>" ></script>	
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
        
    
    <div id="main_content">    	
    	<div id="centralContainer">           		   
			<div id="page_title">
				Step 2: Creating Event ' <?php echo $this->input->cookie( 'eventName' ); ?> '
			</div>
			<div id="instruction" >
				Now, when are the showing times?<br/><br/>
				Please note by the way:<br/>
				<ul>
					<li> All settings will be recorded only when you click Next. </li>
					<li> Double click on an entry to delete it.</li>
				</ul>
			</div>
			<!-- accordion start -->
			<div class="accordionContainer center_purest">								
				<div class="accordionImitation">
					<div id="title">Choose date and time</div>
					<div id="content">
						<form method="post"  action="<?php echo base_url().'eventctrl/create_step3' ?>" name="formLogin" id="formMain">
							<input type="hidden" name="timeFrames_hidden" id="TF_hidden" value="" />
							<input type="hidden" name="dateFrames_hidden" id="DF_hidden" value="" />													
							<div class="center_purest" id="cEvent02_container" >
								<div class="starboardSide">
									<select size="5" multiple="multiple" disabled="disabled" id="timeSelect" class="dateAndTimeSelection center_purest" >
										<option value="NONE" id="noneTimeIndicator" class="timeFrames_proper" >Add time</option>									  									  
									</select>									
								</div>
								<div class="portSide">
									<select size="5" multiple="multiple"  disabled="disabled" id="dateSelect" class="dateAndTimeSelection center_purest" >
									  <option value="NONE" class="dateFrames_proper" >Add date</option>									 
									</select>
								</div>
								<div class="starboardSide bothUnder">
									<span id="redEye" >
										<input type="checkbox" name="redEyeIndicator" id="id_redEyeIndicator" alt="This indicates that show starts today but ends the next day, i.e. 0800PM but 1230AM which is the next day." />
										<label for="id_redEyeIndicator">Red Eye Show?</label>
									</span>
									<input type="text" id="timepicker_start" class="time"  />
									<input type="text" id="timepicker_end" class="time" />
									<input type="button" value="Add time" id="addTimeBtn"/>																		
								</div>
								<div class="portSide bothUnder">									
									<input type="text" id="datepicker"/>
									<input type="button" value="Add Date" id="addDateBtn"/>
								</div>
							</div>					
						</form>
					</div>
				</div>
				<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
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