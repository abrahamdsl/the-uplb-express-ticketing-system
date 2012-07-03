<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Reschedule Showing Time";
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
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>				
	<!-- <script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep1.js'; ?>" ></script>	-->
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
  	<script type="text/javascript" >
		$(document).ready( function(){
			$('a#buttonOK').click( function(){
				var dateStart =  $('input#datepicker').val();
				var timeStart = $('input#timepicker_start').val();
				var dateEnd = $('input#datepicker2').val();
				var timeEnd =  $('input#timepicker_end').val();
				
				if( !isTimeValid( timeStart ) )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'error',
					   message: 'Invalid time start' 
					});
					return false;
				}
				if( !isTimeValid( timeEnd ) )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'error',
					   message: 'Invalid time end' 
					});
					return false;
				}
				if( !isTimestampGreater( dateStart, timeStart, dateEnd, timeEnd, false) )
				{
					$.fn.nextGenModal({
					   msgType: 'error',
					   title: 'error',
					   message: 'End of show timestamp should be later than start of show.' 
					});
					return false;
				}
				document.forms[0].submit();
			});
		});
	</script>
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
				Manage Event<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reschedule Showing Time
			</div>
			<div style="padding-left:10px; clear: both">
				Please use fill-out the information required.
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Select now</div>
					<div id="content">												
						<input type="hidden" id="lastFocus" value="" />
						<input type="hidden" id="slotEnabledClass" value="commonality enabled" />
						<input type="hidden" id="slotDisabledClass" value="commonality disabled" />
						<input type="hidden" id="adjustEnabledClass" value="adjustButtons enabled" />
						<input type="hidden" id="adjustDisabledClass" value="adjustButtons disabled" />
						
						<form method="post"  action="<?php echo base_url().'eventctrl2/reschedule_process' ?>" name="formLogin" id="formMain">							
							<input type="hidden" name="eventID" value="<?php echo $eventObj->EventID; ?>" />
							<input type="hidden" name="showtimeID" value="<?php echo $eventObj->UniqueID; ?>" />
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Start Date
									</span>
									<span class="rightSpecialHere" >									
										<span class="center_purest" >
											<input type="text" id="datepicker" name="startDate" value="<?php echo str_replace( '-', '/',$eventObj->StartDate); ?>" >
										</span>
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Start Time
									</span>
									<span class="rightSpecialHere" >									
										<span class="center_purest" >
											<input type="text" id="timepicker_start" name="startTime" value="<?php echo $eventObj->StartTime; ?>" >
										</span>
									</span>
								</div>								
								<div class="KoreanPeninsula" >
									<span class="left" >
										End Date
									</span>
									<span class="rightSpecialHere" >									
										<span class="center_purest" >
											<input type="text" id="datepicker2" name="endDate" value="<?php echo str_replace( '-', '/',$eventObj->EndDate); ?>" >
										</span>
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										End Time
									</span>
									<span class="rightSpecialHere" >									
										<span class="center_purest" >
											<input type="text" id="timepicker_end" name="endTime" value="<?php echo $eventObj->EndTime; ?>">
										</span>
									</span>
								</div>
								<input type="checkbox" name="redEyeIndicator" id="id_redEyeIndicator" style="display:none;" alt="This indicates that show starts today but ends the next day, i.e. 0800PM but 1230AM which is the next day." />
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