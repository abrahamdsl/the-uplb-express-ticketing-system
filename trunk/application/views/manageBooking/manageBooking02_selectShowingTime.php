<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Change Showing Time - Manage Booking";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking02.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/manageBooking02.js'; ?>" ></script>				
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
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
<?php			
			$this->load->view( 'html-generic/bookProgressIndicator.inc');
?>		
			<div id="page_title">
				Manage Booking<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;			
				Change showing time
			</div>
			<div style="padding-left:10px; clear: both">
				Please select a new showing time
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
						<input type="hidden" id="excludeShowingTime" value="<?php echo $existingShowtimeID; ?>" />
						
						<form method="post"  action="<?php echo base_url().'EventCtrl/manageBooking_changeShowingTime_process' ?>" name="formLogin" id="formMain">							
						<input type="hidden" name="bookingNumber" value="<?php echo $bookingNumber; ?>" />
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Event Name
									</span>
									<span class="rightSpecialHere" >																			
										<span class="center_purest" >
											<select id="eventSelection" name="events" class="center_purest"  >												
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
									<span class="left captionCurrent" >
										Current showing time selected
									</span>
									<span class="rightSpecialHere" >
										<?php
											$x = 2;		
											// determine if this is a red-eye show
											$redEye = FALSE;													
											$dateStart = strtotime( $currentShowingTime->StartDate );
											$timeStart = strtotime( $currentShowingTime->StartTime );
											$dateEnd = strtotime( $currentShowingTime->EndDate );																										
											$timeEnd = strtotime( $currentShowingTime->EndTime );																										
											if( $timeEnd < $timeStart ) $redEye = TRUE;										
										?>
										<table class="center_purest schedulesCentral">
											<thead>
												<tr>											
													<td class="iNeedMostSpace" >Date</td>
													<td class="iNeedMoreSpace" >Time Start</td>
													<?php if( $redEye) { ?><td class="iNeedMoreSpace" >Date End</td><?php }  ?>
													<td class="iNeedMoreSpace" >Time End</td>
												</tr>
											</thead>
											<tbody>
											
												<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >																						
													<td class="BCST_date" >
														<span><?php echo date('Y-M-d l', $dateStart); ?></span>														
													</td>
													<td class="BCST_time_start">	
														<span><?php echo date('h:i:s A', $timeStart); ?></span>														
													</td>	
												<?php if( $redEye) { ?>	
													<td class="BCST_date_end" >
														<span><?php echo date('Y-M-d l', $dateEnd); ?></span>														
													</td>
												<?php }  ?>
													<td class="BCST_time_end">
														<span><?php echo date('h:i:s A', $timeEnd); ?></span>														
													</td>
												</tr>
											</tbody>
										</table>
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Your new showing time
									</span>
									<span class="rightSpecialHere" >
										<span id="showtimeDummy" class="center_purest" >										
											<input type="text" class="commonality disabled" id="messenger" name="messenger" value="Please refresh the page" disabled="disabled" style="width: 80%;"  /><br/>										
										</span>
										<span id="showtimeCustomError" class="center_purest" >											
										</span>
										<span id="showtimeWaiting" class="center_purest"  >										
											<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" alt="ajax_loader" />
										</span>
										<span id="showtimeSelectionReal" class="center_purest" >
											<select id="showingTimeSelection" name="showingTimes" class="center_purest" >
												<option value="NULL" >Dummy content</option>
											</select>
										</span>
										
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Quantity
									</span>
									<span class="rightSpecialHere" >
										<input type="text" class="commonality disabled" id="slot" name="slot_visual" value="<?php echo $guestCount; ?>" style="background-color: white; color: black;" disabled="disabled" /><br/>									
										<input type="hidden" name="slot" value="<?php echo $guestCount; ?>" ><br/>									
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