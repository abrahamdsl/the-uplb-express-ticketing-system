<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Attendance Report - Manage Booking";
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/userSignup.css'; ?>"/>	
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>	
	
	<?php			
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/tabsEssentials.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>	
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>		
	<script type="text/javascript" >
	 $(document).ready( function(){
		$('div.pChannelDetails').show();
		$('a#buttonOK').click( function(){
			window.history.back();
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
	
			<div id="page_title" class="page_title_custom" >
				Attendance Record
			</div>
			<br/>
			<div style="padding-left:10px; clear: both">							
			<h2>
				Class: <?php echo $singleClass->CourseTitle." ".$singleClass->CourseNum." "; ?>
						<?php echo $singleClass->LectureSect; ?>
						<?php if( strlen($singleClass->RecitSect) > 0 )echo "-".$singleClass->RecitSect; ?>
			</h2>
			<h3>
				Show: 	<?php echo $showingTime->Name;?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<?php echo date( 'Y-M-d l', strtotime($showingTime->StartDate))." "; ?>	
				<?php 
				/*
					No need to show seconds if zero
				*/
				$splitted = explode(':', $showingTime->StartTime);
				$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
				echo date( $timeFormat." A", strtotime($showingTime->StartTime)); 
				echo " to ";
				?>
				<?php										
					if( $showingTime->StartDate != $showingTime->EndDate ) 
						// if show ends past midnight (red eye), then display the next day's date.
						echo date( 'Y-M-d l', strtotime($showingTime->EndDate));
					else
						echo '&nbsp';
				?>
				<?php 
					/*
						No need to show seconds if zero
					*/
					$splitted = explode(':',  $showingTime->EndTime);
					$timeFormat = (intval($splitted[2]) === 0 ) ?  'h:i' : 'h:i:s';											
					echo date( $timeFormat." A", strtotime( $showingTime->EndTime));
				?>	
			</h3>
			<br/>				
			</div>			
			
			<p>
			
			</p>
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container bookStep2_main_div_custom" >
				<div class="accordionImitation cEvent04_container aci1_Book3Special">
					<div class="title">Attended People</div>
					<div class="content">
						<?php
							if( count($attendanceData) < 1 )
							{
						?>
							<h2>No students attended this event.</h2>
						<?php
							}else{
						?>
						<table class="center_purest schedulesCentral" >
							<thead>
								<tr>
									<td>Name</td>
									<td>Student Number</td>									
								</tr>
							</thead>
							<tbody>							
						<?php
								$x=0;
								foreach( $attendanceData as $eachAttendance )
								{
									//echo var_dump( $eachAttendance );
						?>
								<tr <?php if( $x++ % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
						<?php
						?>
									<td>
										<?php echo $eachAttendance->Lname.', '.$eachAttendance->Fname.' '.$eachAttendance->Mname; ?>
									</td>
									<td>
										<?php echo $eachAttendance->studentNumber; ?>
									</td>
								</tr>
						<?php						
								}
							}//else
						?>
							</tbody>
						</table>
					</div>
				</div>					
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" ><span class="icon">Back</span></a>
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