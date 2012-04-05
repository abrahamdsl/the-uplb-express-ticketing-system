<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Manage Seat Maps";	
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->				
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/partitionermain.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking01.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/metrotile_colors_basic.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managePaymentModes.css'; ?>"/>		
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>-->
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/metrotile_action_default.js'; ?>" ></script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<script type="text/javascript" >		
		$(document).ready( function(){
			$('a.notyet').click( function(e){
				e.preventDefault();
				
			});
			
			$('a#buttonOK').click( function(e){
				window.location = CI.base_url ;
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
				Manage Seat Maps
			</div>
			<div style="float:right;width: 270px;; margin-top: 17px; margin-left: 80%; position: absolute; z-index: 10;">
				<p style="font-size: 2em;" ><a href="<?php echo base_url();?>SeatCtrl/create" >Add</a></p>
			</div>
			<div style="padding-left:10px; clear: both">
				Manipulate ALL the seat maps.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >					
				<?php if( $seatmaps === false){ ?>
					<p style="padding: 20px;" >You have no seat maps yet.</p>
				<?php
				     }else{
					foreach( $seatmaps as $singleSeatmap ){
				?>
				<h3 id="h_x<?php echo $singleSeatmap->UniqueID; ?>"><a href="#"><?php echo $singleSeatmap->Name; ?></a></h3>
				<div id="x<?php echo $singleSeatmap->UniqueID; ?>" class="section" >									
					<div class="bookingDetails" >						
						<div id="pc<?php echo $singleSeatmap->UniqueID; ?>_details" class="pChannelDetails" >
							<div id="pChannelName" class="properInfo center_purest" >
								<?php echo $singleSeatmap->Name; ?>
							</div>							
							<div class="row">
								<span>Rows</span>
								<span><?php echo $singleSeatmap->Rows; ?></span>
							</div>							
							<div class="row">
								<span>Columns</span>
								<span><?php echo $singleSeatmap->Cols;?></span>
							</div>							
							<div class="row">
								<span>Location</span>
								<span><?php echo $singleSeatmap->Location;?></span>
							</div>							
							<div class="row">
								<span>Status</span>
								<span><?php echo $singleSeatmap->Status;?></span>
							</div>							
							<div class="row">
								<span>Usable Capacity</span>
								<span><?php echo $singleSeatmap->UsableCapacity;?></span>
							</div>							
						</div>								
					</div>
					<div class="containingClassTable" >								
						<div class="metrotile" name="edit" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-genericedit.png" alt="Edit payment mode" /></a>
							<form method="post" action="<?php echo base_url().'SeatCtrl/editseatmap'; ?>" class="notyet" >
								<input type="hidden" name="uniqueID" value="<?php echo  $singleSeatmap->UniqueID; ?>" />
							</form>
						</div>
						<div class="metrotile" name="delete" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-genericdelete.png" alt="Delete payment mode" /></a>
							<form method="post" action="<?php echo base_url().'SeatCtrl/deleteseatmap'; ?>" >								
								<input type="hidden" name="uniqueID" value="<?php echo  $singleSeatmap->UniqueID; ?>" />
							</form>
						</div>						
					</div>																											
				</div>
				<?php
					}// foreach
					}//else
				?>
			</div>			
			<div id="essentialButtonsArea" >											
					<a class="button" id="buttonOK" ><span class="icon" >Back</span></a>				
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