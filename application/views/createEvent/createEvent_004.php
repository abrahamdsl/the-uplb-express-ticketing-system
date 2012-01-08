<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Create Event";
	$this->thisPage_menuCorrespond = "Create Event Step 4";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>" /> <!-- needed for accordion -->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>" />
	<!--For overlay-->
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
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent_004.js'; ?>" ></script>			
		<!--For overlay-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/overlay_general.js'; ?>"/></script>	
</head>
<body>
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
				Step 4: Choose showing times of ' <?php echo $_COOKIE['eventName']; ?> '
			</div>
			<div style="padding-left:10px; clear: both">
				Select the showings you want to configure. <br/>
				If you have left anything unselected, you will be asked again at the end of this wizard.
				<br/>
				Those End Times that are in red color signifies that the show ends past midnight.
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Choose them</div>
					<div id="content">
						<form method="post"  action="<?php echo base_url().'EventCtrl/create_step5' ?>" name="formLogin" id="formMain">
						<div>
							<div class="starBoardSide" >
								<table class="center_purest schedulesCentral">
									<thead>
										<tr>
											<td>&nbsp;</td>
											<td class="iNeedMostSpace" >Date</td>
											<td class="iNeedMoreSpace" >Time Start</td>
											<!-- <td>&nbsp;</td> -->
											<td class="iNeedMoreSpace" >Time End</td>
										</tr>
									</thead>
									<tbody>
									<?php
										$x=0;
										foreach( $unconfiguredShowingTimes as $eachShowingTime )
										{				
											// determine if this is a red-eye show
											$redEye = FALSE;
											$timeStart = strtotime( $eachShowingTime->StartTime );
											$timeEnd = strtotime( $eachShowingTime->EndTime );
											if( $timeEnd < $timeStart ) $redEye = TRUE;										
									?>
										<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
											<td> 
												<input type="checkbox" name="<?php echo $eachShowingTime->StartDate."x".str_replace( ':', '_' , $eachShowingTime->StartTime )."-".str_replace( ':', '_' , $eachShowingTime->EndTime );  ?>" id="ch_<?php echo $eachShowingTime->StartDate."x".str_replace( ':', '_' , $eachShowingTime->StartTime )."-".str_replace( ':', '_' , $eachShowingTime->EndTime );  ?>" />
											</td>	
											<td >
												<label for="ch_<?php echo $eachShowingTime->StartDate."x".str_replace( ':', '_' , $eachShowingTime->StartTime )."-".str_replace( ':', '_' , $eachShowingTime->EndTime );  ?>" /><?php echo $eachShowingTime->StartDate; ?></label><br/>
											</td>
											<td>	
												<label for="ch_<?php echo $eachShowingTime->StartDate."x".str_replace( ':', '_' , $eachShowingTime->StartTime )."-".str_replace( ':', '_' , $eachShowingTime->EndTime );  ?>" /><?php echo $eachShowingTime->StartTime; ?></label><br/>
											</td>
											<!-- <td>
												-
											</td> -->
											<td>
												<label for="ch_<?php echo $eachShowingTime->StartDate."x".str_replace( ':', '_' , $eachShowingTime->StartTime )."-".str_replace( ':', '_' , $eachShowingTime->EndTime );  ?>" <?php if($redEye){ ?>class="redEye"<?php }; ?> /><?php echo $eachShowingTime->EndTime; ?></label><br/>
											</td>
										</tr>
									<?php
											$x++;
										}								
									?>
									<tbody>
								</table>
								<span class="buttonsAtBottom" >
									<input type="button" value="Check all" class="selectDeselectBtns" id="checkAll" />
									<input type="button" value="Uncheck all" class="selectDeselectBtns" id="UncheckAll" />
								</span>
							</div>
							<div class="portSide" >								
								<span class="emphasisNeeded" >Numbers of slots for each of<br/> the showing time selected</span><br/>
								<input type="text" value="100" name="slots" id="id_slots" /><br/>
								<input type="button" value="-" id="reduceSlots" class="adjustButtons" />								
								<input type="button" value="+" id="addSlots" class="adjustButtons" />								
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