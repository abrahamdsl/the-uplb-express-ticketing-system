<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Create Class Association";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep1.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking02.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		//$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>
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
			<div id="page_title">
				Create Class Step 2			
			</div>
			<div style="padding-left:10px; clear: both">
				Now, among these events, select the events and/or showing times you want your students
				to attend.
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<form method="post"  action="<?php echo base_url().'AcademicCtrl/createClass_step3' ?>" name="formLogin" id="formMain">
			<div id="accordion" >
				<?php
					foreach( $configuredShowingTimes as $eventID => $showingTimes )
					{
				?>
				<h3><a href="#"><?php echo  $configuredEventsInfo[$eventID]->Name; ?></a></h3>
				<div>
					<?php
						if( !is_array( $showingTimes ) )
						{
							echo "No showing time for this yet!";
							echo "</div>";
							continue;
						}
					?>
					<table class="center_purest schedulesCentral">
					<thead>
						<tr>
							<td></td>
							<td>Start Info</td>
							<td>End Info</td>
						</tr>
					</thead>
					
					<?php						
						$x = -1;				
						foreach( $showingTimes as $eachST )						
						{
						++$x;
					?>
						<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
					<?php
						$elementName = 'st-'.$eventID.'-'.$eachST->UniqueID;
					?>
							<td>
								<input type="checkbox" name="<?php echo $elementName; ?>" />
							</td>
							<td>
								<label for name="<?php echo $elementName; ?>">
								<?php echo date( 'Y-M-d l', strtotime($eachST->StartDate)); ?>&nbsp;
								<?php 
									/*
										Time start... No need to show seconds if zero
									*/
									$splitted = explode(':', $eachST->StartTime);
									$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
									echo date( $timeFormat." A", strtotime($eachST->StartTime)); 
								?>
								</label>
							</td>
							<td>
								<label for name="<?php echo $elementName; ?>">	
									<?php										
										if( $eachST->StartDate != $eachST->EndDate ) 
											// if show ends past midnight (red eye), then display the next day's date.
											echo date( 'Y-M-d l', strtotime($eachST->EndDate));
										else
											echo '&nbsp';
									?>
									<?php 
										/*
											No need to show seconds if zero
										*/
										$splitted = explode(':', $eachST->EndTime);
										$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
										echo date( $timeFormat." A", strtotime($eachST->EndTime));
									?>
								</label>
							</td>															
						</tr>
					<?php					
						}
					?>
					
					</table>
				</div>												
				<?php
					}
				?>
			</div>
			</form>
			<!-- accordion end -->			
			</div>
			<div id="essentialButtonsArea" class="dropOnTheFloor" >							
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