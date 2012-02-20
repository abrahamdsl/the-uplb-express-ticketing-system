<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Create Event - Step 5";
	$this->thisPage_menuCorrespond = "Create Event Step 5";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>	 <!--For modal v1-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlayv2_general.css'; ?>"/> <!--For overlay v2 -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/seatV2/seatV2.css'; ?>"/>	 <!--For seat map v2 --> 
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>		
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/createEvent_005.js'; ?>"></script>				
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<!-- For overlay v2-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/modal2/jquery.simplemodal.js'; ?>" ></script>
	<?php
		$this->load->view('html-generic/seatV2_Essentials_Scripts.inc');
	?>		
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
		$this->load->view('html-generic/overlayv2_freeform.inc');
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
				Step 5: Configure ticket classes for ' <?php echo $_COOKIE['eventName']; ?> '
			</div>
			<div id="instruction" >
				Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do 
				<br/>
				labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >				
				<div class="accordionImitation cEvent04_container">
					<div id="title">Choose them</div>
					<div id="content">
						<input type="hidden" id="lastFocus" value="" />
						<input type="hidden" id="maxSlot" value="<?php echo $maxSlots; ?>" />
						<input type="hidden" id="allIsWell" value="0" />						
						<?php										
							foreach( $ticketClasses_default as $TCD )
							{				
						?>	
						<input type="hidden" id="seatAssigned_<?php echo $TCD->Name;?>" value="0" />						
						<?php
							}
						?>
						<form method="post"  action="<?php echo base_url().'EventCtrl/create_step6' ?>" name="formLogin" id="formMain">
						<div>
							
							<div id="containingSeatMapSelection" >
								Choose seat map: 
									<select id="seatMapPullDown" name="seatMapPullDown" >								
										<option value="null" selected="selected" ></option>									
										<?php foreach( $seatMaps as $singleSeatMap ){ ?>
											<option value="<?php echo $singleSeatMap->UniqueID; ?>" ><?php echo $singleSeatMap->Name; ?></option>
										<?php }?>
									</select>			
							</div>
							<div id="containingFeaturedTable" >
								<table class="center_purest schedulesCentral">
									<thead>
										<tr>
											<td class="iNeedMostSpace" >Class</td>
											<td class="iNeedMostSpace" >Price</td>
											<td class="iNeedMostSpace" >Distribution<p class="titleDescriptor" >(Total max of <?php echo $maxSlots; ?>)</p></td>
											<td class="iNeedMoreSpace" >Holding Time<p class="titleDescriptor" >(in minutes, max 59)</p></td>											
											<td class="iNeedMoreSpace" >&nbsp;</td>											
											<td class="iNeedMoreSpace" >&nbsp;</td>
										</tr>
									</thead>
									<tbody>
									<?php
										$x=0;
										foreach( $ticketClasses_default as $TCD )
										{				
									?>							
										<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
											<td>
												<?php echo $TCD->Name; ?>
											</td>
											<td>
												<input type="text" class="commonality ayokongDefaultAngItsuraNgButton <?php if( $x % 2 == 0 ) {?>even<?php }else{ ?> odd<?php }; ?>" id="id_price_<?php echo $TCD->Name; ?>" name="price_<?php echo $TCD->Name; ?>" value="<?php echo $TCD->Price; ?>" /><br/>
												<input type="button" value="-" id="reducePrice_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
												<input type="button" value="+" id="addPrice_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
											</td>
											<td>
												<input type="text" class="commonality ayokongDefaultAngItsuraNgButton <?php if( $x % 2 == 0 ) {?>even<?php }else{ ?> odd<?php }; ?>" id="id_slot_<?php echo $TCD->Name; ?>" name="slot_<?php echo $TCD->Name; ?>" value="<?php echo $TCD->Slots; ?>" /><br/>
												<input type="button" value="-" id="reduceSlots_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
												<input type="button" value="+" id="addSlots_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
											</td>												
											<td>
												<input type="text" class="commonality ayokongDefaultAngItsuraNgButton <?php if( $x % 2 == 0 ) {?>even<?php }else{ ?> odd<?php }; ?>" id="id_HoldingTime_<?php echo $TCD->Name; ?>" name="holdingTime_<?php echo $TCD->Name; ?>" value="20" /><br/>
												<input type="button" value="-" id="reduceHoldingTime_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
												<input type="button" value="+" id="addHoldingTime_<?php echo $TCD->Name; ?>" class="adjustButtons ayokongDefaultAngItsuraNgButton" />								
											</td>
											<td>
												<input type="button" value="Choose seats" class="ayokongDefaultAngItsuraNgButton" id="id_seat_<?php echo $TCD->Name; ?>" name="seatTrigger_<?php echo $TCD->Name; ?>" />
											</td>
											<td>
												<input type="button" value="Edit Privileges/Restrictions" class="ayokongDefaultAngItsuraNgButton"  id="id_privilege_<?php echo $TCD->Name; ?>" />
											</td>
										</tr>
									<?php
											$x++;
										}								
									?>
										<tr>
											<td class="iNeedMostSpace" >&nbsp;</td>
											<td class="iNeedMostSpace" >&nbsp;</td>
											<td class="iNeedMostSpace" ><input type="text" class="commonality ayokongDefaultAngItsuraNgButton <?php if( $x % 2 == 0 ) {?>even<?php }else{ ?> odd<?php }; ?>" id="totalSlotsChosen" value="0" /><br/></td>
											<td class="iNeedMoreSpace" >&nbsp;</td>											
											<td class="iNeedMoreSpace" >&nbsp;</td>											
											<td class="iNeedMoreSpace" >&nbsp;</td>
										</tr>
									</tbody>
								</table>								
							</div>							
						</div>
						</form>
					</div>
				</div>												
			</div>
			<!-- to load a new page -->
			<form id="id_forwarder" name="forwarder" method="post"  action="<?php echo base_url().'EventCtrl/create_step6_forward'; ?>" >
				<input type="hidden" name="uuid" id="eligibility" value="0" <?php echo '/'; ?>>
			</form>
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