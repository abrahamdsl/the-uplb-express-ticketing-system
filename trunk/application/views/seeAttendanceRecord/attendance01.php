<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "My Classes";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->				
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking01.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/manageBooking01.js'; ?>" ></script>
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
        
    
    <div id="main_content">    	
    	<div id="centralContainer" class="homepageSpecial" > 
			<div id="page_title">
				Class Attendance
			</div>
			<div style="padding-left:10px; clear: both">
				Listed here are the records of the students who have attended the events they have paired with their classes.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >			
			 <?php foreach( $allData as $key => $value ) {?>
				<h3 id="h_<?php echo $key; ?>">
					<a href="#">
						<?php
							$singleGrandObj = $value['object'];
							$attendanceArr = $value['attendance'];
						?>
						<?php echo $singleGrandObj->CourseTitle." ".$singleGrandObj->CourseNum." "; ?>
						<?php echo $singleGrandObj->LectureSect; ?>
						<?php if( count($singleGrandObj->RecitSect) > 0 )echo "-".$singleGrandObj->RecitSect; ?>
					</a>
				</h3>
				<div >
					
					<?php 
					if( count( $attendanceArr ) > 0 )
					{
					?>					
						<table class="center_purest schedulesCentral">
					<?php
						foreach( $attendanceArr as $eachAttendance  )
						{						
					?>
						<tr>
							<td><?php echo $eachAttendance->Lname; ?></td>							
							<td><?php echo $eachAttendance->Fname." ".$eachAttendance->Mname;?></td>							
							<td style="min-width: 100px; width: auto;" >
								<?php if( strlen($eachAttendance->EntryDate) > 0 ) { 
										echo "Entry: ".$eachAttendance->EntryDate." ".$eachAttendance->EntryTime;
										echo "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Exit: ";
										if( $eachAttendance->EntryDate != $eachAttendance->ExitDate ) echo $eachAttendance->ExitDate;
										echo $eachAttendance->ExitTime;
								?>										
								<?php }else{ ?>
									<span style="color:red; font-size: 1.1em;">DID NOT ATTEND</span>
								<?php } ?>
							</td>
						</tr>					
					<?php } ?>
						</table>
					<?php 
					}else{
					?>
						<h2>No student attended this</h2>
					<?php } ?>
										
				</div>
			<?php } ?>							
			</div>
			<!-- accordion end -->
			<div id="accordion2" class="specialOnMB01" >
				<?php
					$this->load->view( 'html-generic/nobooking.inc' );
				?>
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