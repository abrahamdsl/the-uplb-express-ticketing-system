<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Purchase Ticket";
	$this->thisPage_menuCorrespond = "BOOK";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStepsCommon.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/bookStep2.js'; ?>" ></script>
	
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
			<div id="page_title" class="page_title_custom" >
				Select Ticket Class
			</div>
			<div id="top_page_detail" >
				Please select ticket class. There might be additional charges or even discounts at the payment page.
				<br/>				
			</div>			
			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Select now</div>
					<div id="content">	
						<div class="bookingDetails" >							
							<div class="top">		
								<input type="hidden" id="startDate" value="<?php echo $showtimeObj->StartDate ?>" />
								<input type="hidden" id="endDate" value="<?php echo $showtimeObj->EndDate ?>" />
								<input type="hidden" id="startTime" value="<?php echo $showtimeObj->StartTime ?>" />
								<input type="hidden" id="endTime" value="<?php echo $showtimeObj->EndTime ?>" />
								<div class="start">
									<span class="deed" >
										Start
									</span>
									<span class="contentproper_time" >										
										<?php echo $showtimeObj->StartTime;?>
									</span>
									<span class="contentproper_date" >
										<?php echo $showtimeObj->StartDate; ?>										
									</span>
								</div>								
								<div class="end">
									<span class="deed" >
										End
									</span>									
									<span class="contentproper_time" >										
										<?php echo $showtimeObj->EndTime;?>
									</span>
									<span class="contentproper_date" >
										<?php
											if( $showtimeObj->StartDate != $showtimeObj->EndDate ) echo $showtimeObj->EndDate;
											else
												echo '&nbsp';
										?>
									</span>
								</div>
							</div>
							<div class="bdtitle" >
								<?php echo $eventInfo->Name; ?>
							</div>
							<div class="bottom">
								<?php echo $eventInfo->Location; ?>
								<br/>
								<br/>
								<p>
									You are booking <?php echo $slots; ?> ticket<?php if($slots > 1) echo 's'; ?>.
								</p>
							</div>
							
						</div>
						<div class="containingClassTable" >
							<form method="post"  action="<?php echo base_url().'EventCtrl/book_step3' ?>" id="formMain">							
								<table class="center_purest schedulesCentral">
									<thead>
										<tr>
											<td class="iNeedMostSpace" >Class</td>
											<td class="iNeedMostSpace" >Price <br/>per ticket</td>
											<td class="iNeedMostSpace" >Total <br/>Cost</td>										
											<td class="iNeedMoreSpace" >&nbsp;</td>																					
										</tr>
									</thead>
									<tbody>									
										<?php
											$x=0;
											foreach( $ticketClasses as $TCD )
											{				
										?>							
											<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
												<td>
													<?php echo $TCD->Name; ?>
												</td>
												<td>
													<?php echo $TCD->Price; ?>
												</td>
												<td>
													<?php echo ( intval($TCD->Price) * intval( $slots ) ); ?>
												</td>
												<td>
													<?php if( $ticketClasses_presence[$TCD->Name] === true){  ?>
														<input type="radio" name="selectThisClass" value="<?php echo $TCD->UniqueID; ?>" />
													<?php }else{ ?>
														SOLD OUT
													<?php } ?>
												</td>																							
											</tr>
										<?php
												$x++;
											}								
										?>
									</tbody>
								</table>
							</form>
						</div>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>														
							<a class="button" id="buttonReset" ><span class="icon">Cancel</span></a>
			</div>	
			<div id="misc" class="buttonfooterSeparator" ></div>
		</div>		
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>