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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managebooking01.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/managebooking01.js'; ?>" ></script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>		
	<script type="text/javascript" >
		$(document).ready( function(){
			//$('input[type="button"][name="viewStudentsBtn"]').click( function(){
			$('input[type="button"]').click( function(){
				$(this).siblings('form').submit();
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
        
    
    <div id="main_content">    	
    	<div id="centralContainer" class="homepageSpecial" > 
			<div id="page_title">
				Manage Classes
			</div>
			<div style="padding-left:10px; clear: both">
				Listed here are your classes. Click "See Attending Students" to see students who have attended the events attached to your
				class.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >			
			<?php if( count($myClasses) < 1 ){ ?>
				<h3><a href="#">No class found</a></h3>
				<div>						
					<p>You don't have any classes so far</p>
				</div>
			<?php }else{ 
					foreach( $myClasses as $singleClass )
					{
			?>
				<h3>
					<a href="#">
						<?php echo $singleClass->CourseTitle." ".$singleClass->CourseNum." "; ?>
						<?php echo $singleClass->LectureSect; ?>
						<?php if( strlen($singleClass->RecitSect) > 0 )echo "-".$singleClass->RecitSect; ?>
						&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
						<?php 
							switch( intval($singleClass->Term) )
							{
								case 1: echo "First Semester"; break;
								case 2: echo "Second Semester"; break;
								case 3: echo "Summer"; break;
							}
						?>
						&nbsp;
						<?php echo $singleClass->AcadYear1.'&nbsp;-&nbsp;'; ?>
						<?php echo $singleClass->AcadYear2; ?>
					</a>
				</h3>				
				<div>
					<span class="choice" ><a href="<?php echo base_url();?>academicctrl/modifyClass/<?php echo $singleClass->UUID; ?>" >Modify</a></span>
					<span class="choice" ><a href="<?php echo base_url();?>academicctrl/deleteClass/<?php echo $singleClass->UUID; ?>" >Delete</a></span>
					<span class="choice"><a href="<?php echo base_url();?>academicctrl/createClass" >Create another</a></span>
					<br/>
					<p class="comment">
						<?php echo $singleClass->Comments; ?>
					</p>
					<?php
						if( $eventClassPair[ $singleClass->UUID ] === false )
						{
					?>
						<p>You did not associate any event for this.</p>						
						<a href="<?php echo base_url();?>academicctrl/addEventToClass/<?php echo $singleClass->UUID; ?>" >Add one now</a>
					<?php
						}else{
					?>
						<table class="center_purest schedulesCentral" >
						<thead>
							<tr>
								<td>Name</td>
								<td>Showing time</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
						</thead>
						<tbody>
					<?php
							$x=0;
							foreach( $eventClassPair[ $singleClass->UUID ] as $showingTime )
							{
					?>
							<tr <?php if( $x++ % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>								
								<td>
									<?php echo $showingTime->Name;?>
								</td>								
								<td>															
									<?php echo $this->usefulfunctions_model->outputShowingTime_SimpleOneLine( 
												$showingTime->StartDate, 
												$showingTime->StartTime, 
												$showingTime->EndDate, 
												$showingTime->EndTime
										  );
									?>
								</td>
								<td>
									<input type="button" value="Delete" name="deleteBtn" />
									<form method="post" action="<?php echo base_url();?>academicctrl/deleteClassEventAssociation">
										<input type="hidden" name="ec_uniqueid" value="<?php echo $showingTime->EC_UniqueID; ?>" />
									</form>
								</td>
								<td>
									<input type="button" value="View Attending Students" name="viewStudentsBtn" />
									<form method="post" action="<?php echo base_url();?>academicctrl/seeAttendingStudents">
										<input type="hidden" name="ec_uniqueid" value="<?php echo $showingTime->EC_UniqueID; ?>" />
									</form>
								</td>								
							</tr>
					<?php
							}
					?>
						</tbody>
						</table>
					<?php
						}
					?>				
				</div>
			<?php 
			       }//foreach
				}//if else
			?>							
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