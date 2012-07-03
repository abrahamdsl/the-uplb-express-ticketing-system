<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Manage Payment Modes";	
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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managebooking01.css'; ?>"/>		
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
				window.location = CI.base_url + 'useracctctrl/managepaymentmode';
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
				Manage Payment modes
			</div>
			<div style="float:right;width: 270px;; margin-top: 17px; margin-left: 80%; position: absolute; z-index: 10;">
				<p style="font-size: 2em;" ><a href="<?php echo base_url();?>useracctctrl/addpaymentmode" >Add</a></p>
			</div>
			<div style="padding-left:10px; clear: both">
				Manipulate ALL the payment modes.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >					
				<?php
					foreach( $paymentChannels as $singleChannel ){
				?>
				<h3 id="h_x<?php echo $singleChannel->UniqueID; ?>"><a href="#"><?php echo $singleChannel->Name; ?></a></h3>
				<div id="x<?php echo $singleChannel->UniqueID; ?>" class="section" >									
					<div class="bookingDetails" >						
						<div id="pc<?php echo $singleChannel->UniqueID; ?>_details" class="pChannelDetails" >
							<div id="pChannelName" class="properInfo center_purest" >
								<?php echo $singleChannel->Name; ?>
							</div>
							<?php if ( $singleChannel->Contact_Person != "" ) { ?>
							<div class="row">
								<span>Contact Person</span>
								<span><?php echo $singleChannel->Contact_Person; ?></span>
							</div>
							<?php } ?>
							<?php if ( $singleChannel->Location != "" ) { ?>
							<div class="row">
								<span>Location</span>
								<span><?php echo $singleChannel->Location;?></span>
							</div>
							<?php } ?>
							<?php if ( $singleChannel->Cellphone != "" ) { ?>
							<div class="row">
								<span>Cellphone</span>
								<span><?php echo $singleChannel->Cellphone;?></span>
							</div>
							<?php } ?>
							<?php if ( $singleChannel->Landline != "" ) { ?>
							<div class="row">
								<span>Landline</span>
								<span><?php echo $singleChannel->Landline;?></span>
							</div>
							<?php } ?>
							<?php if ( $singleChannel->Email != "" ) { ?>
							<div class="row">
								<span>Email</span>
								<span><?php echo $singleChannel->Email;?></span>
							</div>
							<?php } ?>
							<?php if ( $singleChannel->Comments != "" ) { ?>
							<div class="row">
								<span>Remarks</span>
								<span><?php echo $singleChannel->Comments;?></span>
							</div>
							<?php } ?>
							<div class="row">
								<span>Internal data type</span>
								<span><?php echo $singleChannel->internal_data_type;?></span>
							</div>
							<div class="row">
								<span>Internal data </span>
								<span>
									<?php 
										if(  $singleChannel->internal_data_type == "WIN5" ){
											if( strlen( $singleChannel->internal_data ) > 2 ){
											$internalData = explode(';', $singleChannel->internal_data);
											$internalDataPair = Array();
											foreach( $internalData as $value ){
												if( strlen( $value ) < 1 ) continue;
												$temp = explode('=', $value );
												$internalDataPair[ $temp[0] ] = $temp[1];
											}											
									?>
											<table class="center_purest schedulesCentral" >
											
									<?php
											$x = 0;
											foreach( $internalDataPair as $key => $val )
											{
									?>
													<tr <?php if( $x % 2 == 0 ) {?>class="even"<?php }else{ ?>class="odd" <?php }; ?> >
														<td><?php echo $key; ?></td>
														<td><?php echo $val; ?></td>
													</tr>
									<?php
											}
									?>
											</table>
									<?php
											}else{//if strlen
												echo 'NONE';
											}
										}else{
									?>
										<p><?php echo $singleChannel->internal_data; ?></p>
									<?php
										}
									?>
								</span>
							</div>
						</div>								
					</div>
					<div class="containingClassTable" >								
						<div class="metrotile" name="edit" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-genericedit.png" alt="Edit payment mode" /></a>
							<form method="post" action="<?php echo base_url().'useracctctrl/managepaymentmode_edit'; ?>" class="notyet" >
								<input type="hidden" name="pChannel" value="<?php echo  $singleChannel->UniqueID; ?>" />
							</form>
						</div>
						<div class="metrotile" name="delete" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-genericdelete.png" alt="Delete payment mode" /></a>
							<form method="post" action="<?php echo base_url().'useracctctrl/managepaymentmode_delete'; ?>" >								
								<input type="hidden" name="pChannel" value="<?php echo  $singleChannel->UniqueID; ?>" />
							</form>
						</div>						
					</div>																											
				</div>
				<?php
					}// foreach
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