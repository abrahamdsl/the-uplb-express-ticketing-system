<?php
	$sessionActivity =  $this->clientsidedata_model->getSessionActivity();
	$isActivityManageBooking = ( $sessionActivity[0] == "MANAGE_BOOKING" and $sessionActivity[1] == 2 );
		
	define('SLOTS', $this->clientsidedata_model->getSlotsBeingBooked() );
?>
<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = ($isActivityManageBooking) ? "Change Ticket Class" : "Purchase Ticket";
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
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>
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
			<?php if( $isActivityManageBooking ) {?>
				Manage Booking<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>	
				Select Ticket Class
			</div>
			<div id="top_page_detail" >
				<?php if( $isActivityManageBooking ) 
				{
						$slotAvailable = $this->input->cookie( 'is_there_slot_in_same_tclass' );
						if( $slotAvailable === false or intval( $slotAvailable ) === 1  )
						{
				?>				
					The ticket class you have selected in your current booking has been automatically selected. If the same class in this showing
					time is more expensive than in your current showing time, or you have selected
					a new class, you will have to pay the amount difference.
				<?php 
						}else{
				?>
					There are no more slots for the ticket class you have selected in your current booking. Please choose another
					another one. Please note that you will have to pay the amount difference, if any.
				<?php 	} ?>
				<br/><br/>
				<?php }else{ ?>				
				Please select ticket class. There might be additional charges or even discounts at the payment page.
				<br/>				
				<?php } ?>				
			</div>			
			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Select now</div>
					<div id="content">	
						<div class="bookingDetails" >							
							<?php
								$this->load->view('html-generic/eventInfoLeft.inc');
							?>							
						</div>
						<div class="containingClassTable" >														
							<form method="post"  action="<?php echo base_url().'EventCtrl/book_step3'; ?>" id="formMain">		
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
													<?php echo ( floatval($TCD->Price) * intval( SLOTS ) ); ?>
												</td>
												<td>
													<?php if( $this->input->cookie( $TCD->UniqueID."_slot_UUIDs" ) !== false )
														  {  $selectedIndicator = "";
															 if( $isActivityManageBooking and intval($TCD->UniqueID) === intval( $bookingObj->TicketClassUniqueID ) )
															 {
																 $selectedIndicator = 'checked="checked"';
															 }
															
													?>
														<input type="radio" name="selectThisClass" value="<?php echo $TCD->UniqueID; ?>" <?php echo  $selectedIndicator; ?> />
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