<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "My Events";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/metrotile_colors_basic.css'; ?>"/>
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
	
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>		
	<script type="text/javascript" >
		$(document).ready( function(){			
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
				Manage Events
			</div>
			<div style="padding-left:10px; clear: both">
				Listed here are your events. 			
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >			
			<?php if( count($myEvents) < 1 ){ ?>
				<h3><a href="#">No events found</a></h3>
				<div>						
					<p>You don't have any events so far</p>
				</div>
			<?php }else{ 
					foreach( $myEvents as $singleEvent )
					{
			?>
				<h3>
					<a href="#"><?php echo $singleEvent->Name; ?></a>
				</h3>				
				<div>					
					
					<?php
						if( $showingTimes[ $singleEvent->EventID ] === false )
						{
					?>
						<p>This event doesn't have a showing time. Hmm, this is fishy.</p>												
					<?php
						}else{
					?>
						<table class="center_purest schedulesCentral" >
							<thead>
								<tr>
									<td>Status</td>
									<td style="min-width: 100px; width: auto;">Showing time</td>
									<td>&nbsp;</td>									
								</tr>
							</thead>
							<tbody>								
								<?php
									$x=0;
									foreach( $showingTimes[ $singleEvent->EventID ] as $showingTime )
									{
									$navigation = Array(
										
									);
									switch( $showingTime->Status )
									{
										case "CONFIGURED":
									}
									
								?>
								<tr <?php if( $x++ % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?>>
									<td><?php echo $showingTime->Status; ?></td>
									<td>
										<?php echo $this->UsefulFunctions_model->outputShowingTime_SimpleOneLine( 
												$showingTime->StartDate, 
												$showingTime->StartTime, 
												$showingTime->EndDate, 
												$showingTime->EndTime,
												true
										  );
										?>
									</td>
									<td>
										<div class="metrotile" name="viewdetails" >
												<a href="<?php echo base_url(); ?>EventCtrl2/view/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>" ><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" /></a>
										</div>
										<?php
											if( $showingTime->Status == "CONFIGURED" ){
										?>
										<div class="metrotile" name="reschedule" >
												<a href="<?php echo base_url(); ?>EventCtrl2/reschedule/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_reschedule.png" alt="Reschedule" /></a>
										</div>
										<div class="metrotile" name="manageticketclass" >
												<a href="<?php echo base_url(); ?>EventCtrl2/manage_tc/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_manageticketclass.png" alt="Manage Ticket Class" /></a>
										</div>			
										<div class="metrotile" name="manageother" >
												<a href="<?php echo base_url(); ?>EventCtrl2/manage_other/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_manageotherdetails.png" alt="Manage Other Details" /></a>
										</div>
										<div class="metrotile" name="seal" >
												<a href="<?php echo base_url(); ?>EventCtrl2/seal/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_seal.png" alt="Seal showing time" /></a>
										</div>
										<div class="metrotile" name="cancel" >
												<a href="<?php echo base_url(); ?>EventCtrl2/cancel/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_cancel.png" alt="Cancel showing time" /></a>
										</div>
										<?php
											}else
											if( $showingTime->Status == "CHECK-IN"  ){
										?>
										<div class="metrotile" name="straggle" >
												<a href="<?php echo base_url(); ?>EventCtrl2/straggle/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-straggle.png" alt="Set straggle" /></a>
										</div>
										<div class="metrotile" name="finalize" >
												<a href="<?php echo base_url(); ?>EventCtrl2/finalize/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_finalize.png" alt="Finalize showing time" /></a>
										</div>
										<?php
											}else
											if( $showingTime->Status == "STRAGGLE" ){
										?>
										<div class="metrotile" name="seal" >
												<a href="<?php echo base_url(); ?>EventCtrl2/seal/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_seal.png" alt="Seal showing time" /></a>
										</div>
										<div class="metrotile" name="finalize" >
												<a href="<?php echo base_url(); ?>EventCtrl2/finalize/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_finalize.png" alt="Finalize showing time" /></a>
										</div>
										<?php 
											}else
											if( $showingTime->Status == "FINALIZED" ){
										?>
											<div class="metrotile" name="archive" >
												<a href="<?php echo base_url(); ?>EventCtrl2/archive/<?php echo $showingTime->EventID.'/'.$showingTime->UniqueID; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-showingtime_archive.png" alt="Archive showing time" /></a>
											</div>
										<?php
											}
										?>
									</td>									
								</tr>
								<?php
									}//foreach
								?>
							</tbody>
						</table>
					<?php
						}//else
					?>
				</div>
			<?php 
			       }//foreach
				}//if else
			?>							
			</div>
			<!-- accordion end -->			
			<div style=" clear:both;"></div>
		</div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>