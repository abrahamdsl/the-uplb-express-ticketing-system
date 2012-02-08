<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Create Event";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<!--For overlay-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.ui.datepicker.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.ui.timepicker.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent006_pickerBoot.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/timepickerBoot.js'; ?>"/></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent006.js'; ?>"/></script>				
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
				Step 6: Configure some other options for ' <?php echo $_COOKIE['eventName']; ?> '
			</div>
			<div style="padding-left:10px; clear: both">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do 
				<br/>
				labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Particulars</div>
					<div id="content">						
						<input type="hidden" id="lastFocus" value="" />
						<input type="hidden" id="lastFocus_class" value="" />
						<input type="hidden" id="fixedTime_caption" value="Put time here" />
						<input type="hidden" id="numOfDays_caption" value="Number of days after" />						
						<!--the ff 4: id here are names  in their fields -->
						<input type="hidden" id="selling_dateStart_caption" value="Start Date" />						
						<input type="hidden" id="selling_timeStart_caption" value="Start Time" />						
						<input type="hidden" id="selling_dateEnd_caption" value="End Date" />						
						<input type="hidden" id="selling_timeEnd_caption" value="End Time" />						
						<!-- <input type="hidden" id="maxSlot" value="<?php //echo $maxSlots; ?>" /> -->
						<input type="hidden" id="allIsWell" value="0" />																		
						<form method="post"  action="<?php echo base_url().'EventCtrl/create_step7' ?>" name="formLogin" id="formMain">
							<input type="hidden" id="deadlineSelectionVal" value="1" />
							<!--
								08JAN2012-1419: Since we have to represent the visible dates in a user friendly form,
								i.e., 2012/01/02 as 02JAN2012, the field that will be considered when passed into the server
								are these, instead of the visible ones
							-->						
							<input type="hidden" name="hidden_selling_dateStart" value="" />
							<input type="hidden" name="hidden_selling_timeStart" value="" />
							<input type="hidden" name="hidden_selling_dateEnd" value="" />
							<input type="hidden" name="hidden_selling_timeEnd" value="" />
							<!-- end-->							
							
								<table class="center_purest schedulesCentral">
									<thead>
										<tr>											
											<td class="iNeedMostSpace" >Date</td>
											<td class="iNeedMoreSpace" >Time Start</td>
											<!-- <td>&nbsp;</td> -->
											<td class="iNeedMoreSpace" >Time End</td>
										</tr>
									</thead>
									<tbody>
									<?php
										$x=0;
										foreach( $beingConfiguredShowingTimes as $eachShowingTime )
										{				
											// determine if this is a red-eye show
											$redEye = FALSE;
											$timeStart = strtotime( $eachShowingTime->StartTime );
											$timeEnd = strtotime( $eachShowingTime->EndTime );
											if( $timeEnd < $timeStart ) $redEye = TRUE;										
									?>
										<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >																						
											<td class="BCST_date" >
												<span><?php echo $eachShowingTime->StartDate; ?></span>
												<input type="hidden" class="value" value="<?php echo $eachShowingTime->StartDate; ?>" />
											</td>
											<td class="BCST_time_start">	
												<span><?php echo $eachShowingTime->StartTime; ?></span>
												<input type="hidden" class="value" value="<?php echo $eachShowingTime->StartTime; ?>" />
											</td>											
											<td class="BCST_time_end">
												<span><?php echo $eachShowingTime->EndTime; ?></span>
												<input type="hidden" class="value" value="<?php echo $eachShowingTime->EndTime; ?>" />
											</td>
										</tr>
									<?php
											$x++;
										}								
									?>
									<tbody>
								</table>								
							
							
							
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Online selling availability
									</span>
									<span class="right" id="special" >										
										<input type="text" id="datepicker" class="textInputSize grayGuide" name="selling_dateStart" value="Start Date" />
										<input type="text" id="timepicker_start" class="textInputSize grayGuide" name="selling_timeStart" value="Start Time" />
										<input type="text" id="datepicker2" class="textInputSize grayGuide" name="selling_dateEnd" value="End Date" />
										<input type="text" id="timepicker_end_006" class="textInputSize grayGuide" name="selling_timeEnd" value="End Time" />
										<br/>
										<!--this red eye indicator is used by some function in createEvent_002.js that is being called from here,
											so we include it here so that such JS file won't malfunction because of this single element's absence
										-->
										<input type="hidden" name="redEyeIndicator" id="id_redEyeIndicator" alt="This indicates that show starts today but ends the next day, i.e. 0800PM but 1230AM which is the next day.">
										<!--<label for="redEyeIndicator">Red Eye Deadline?</label> -->
									</span>


								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Deadline for payment of slots (if not paid immediately)
									</span>
									<span class="right " id="right_inner" >
										<span class="center_purest">
											<select id="deadlineChoose" name="deadlineChoose" class="deadlineSelection" >
												<option value="1" >Fixed time in the day of booking</option>
												<option value="2" >Fixed time after 1 or more days of booking day</option>
												<option value="3" >After X days and hours of booking time</option>											
											</select>
										</span>
										<br/>
										<span id="right_inner_RelativeAfterBookingDay" class="innerRightChanging" hidden="true" >											
												<input type="text" class="textInputSize grayGuide" id="relative_days" name="numOfDays_relative" value="Days" />												
										</span>		
										<span id="right_inner_fixedSameDay" class="innerRightChanging" >
											<input type="text" id="fixedTime"  class="textInputSize_Larger grayGuide" name="bookCompletionTime" value="Put time here" />
										</span>
										<span id="right_inner_fixedAfterBookingDay" class="innerRightChanging"  hidden="true" >
											<input type="text" class="textInputSize_Larger grayGuide" id="numOfDays" name="numOfDays_fixed" value="Number of days after" />
										</span>																			
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										In case of no more seats, still permit selling?
									</span>
									<span class="right" >
										<input type="radio" id="id_seatNone_StillSell_YES" name="seatNone_StillSell" value="YES"  checked="true" />
										<label for="id_seatNone_StillSell_YES">YES</label>
										<input type="radio" id="id_seatNone_StillSell_NO" name="seatNone_StillSell" value="NO" />
										<label for="id_seatNone_StillSell_NO">NO</label>
									</span>
								</div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Seat required during confirmation?
									</span>
									<span class="right" >
										<input type="radio" id="id_confirmationSeatReqd_YES" name="confirmationSeatReqd" value="YES" checked="true" />
										<label for="id_confirmationSeatReqd_YES">YES</label>
										<input type="radio" id="id_confirmationSeatReqd_NO" name="confirmationSeatReqd" value="NO" />
										<label for="id_confirmationSeatReqd_NO">NO</label>
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
			<div style=" clear:both;"></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>