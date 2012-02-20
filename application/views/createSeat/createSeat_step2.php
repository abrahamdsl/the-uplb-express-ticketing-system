<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<!--
**** WARNING 18JAN2012-2037
*
*	Element names, IDs and classes are frequently used in the JavaScript/DOM manipulation. Consult the JavaScript files
*     when doing changes to such.
*
**** WARNING
-->
<?php
	$this->pageTitle = "Create Seat Map - Step 2";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createSeat01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<!--For overlay-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>		
	<?php
		$this->load->view('html-generic/seatEssentials.inc');
	?>
	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/seatsScript.js'; ?>" ></script>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/seatsScript2.js'; ?>" ></script>			
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
    	<div id="centralContainer">           		   
			<div id="page_title">
				Create Seat Map ' <?php echo $this->input->post( 'name' );?> '
			</div>
			<div style="padding-left:10px; clear: both">
				Please fill out the following fields.
			</div>				
			<!-- accordion start -->
			<!--  -->
			<form method="post"  action="<?php echo base_url().'SeatCtrl/create_step3' ?>" name="formLogin" id="formMain">						
						<input type="hidden" id="rows" value="<?php echo $rows; ?>" />
						<input type="hidden" id="cols" value="<?php echo $cols; ?>" />
						<input type="hidden" id="rows_touchable" value="<?php echo $rows; ?>" />
						<input type="hidden" id="cols_touchable" value="<?php echo $cols; ?>" />
						<div class="holder center_purest" id="holder" >
							<table>
								<thead>
									<tr>
										<td></td>
										<?php
											for( $x = 0; $x < $cols; $x++ )
											{
										?>
												<td class="legend">													
													<input type="text" name="label_up_number" disabled="disabled" value="<?php echo $x+1; ?>" />
													<?php //echo $x+1; ?>
												</td>
										<?php
											}
										?>
										<td></td>
									</tr>
								</thead>
								<tbody>
									<?php $indicator = 65; ?>
									<?php for( $x = 0; $x < $rows; $x++, $indicator++){ ?>
									<tr>
										<td class="legend" >
											<!--<input type="hidden" name="label_real_left_letter" value="<?php echo chr($indicator); ?>" />
												<input type="type" name="label_pesentation_left_y" disabled="disabled" value="<?php echo chr($indicator); ?>" />-->
												<input type="text" name="label_letter" disabled="disabled" value="<?php echo chr($indicator); ?>" />											
										</td>
										<?php for( $y = 0; $y < $cols; $y++ ){ ?>
											<td>
												<div class="drop" >
													<span><?php echo chr($indicator); ?>-<?php  echo $y+1; ?></span>
													<input type="hidden" class="seatInfo" name="seatLocatedAt_<?php  echo $x; ?>_<?php  echo $y; ?>_presentation" value="<?php echo chr($indicator); ?>_<?php  echo $y+1; ?>" />																																																															
													<input type="hidden" class="seatInfo" name="seatLocatedAt_<?php  echo $x; ?>_<?php  echo $y; ?>_status" value="0" />																																																															
												</div>								
											</td>
										<?php } ?>
										<td class="legend" >
											<!--<input type="hidden" name="real_label_right" value="<?php echo chr($indicator); ?>" /> -->
											<input type="text" name="label_letter" disabled="disabled" value="<?php echo chr($indicator); ?>" />											
										</td>
									</tr>
									<?php } ?>	
									<tr>
										<td></td>
										<?php
											for( $x = 0; $x < $cols; $x++ )
											{
										?>
												<td class="legend">
													<!--<input type="hidden" name="label_real_down_x" value="<?php echo $x+1; ?>" />-->
													<input type="text" name="label_down_number" disabled="disabled" value="<?php echo $x+1; ?>" />
													<?php //echo $x+1; ?>
												</td>
										<?php
											}
										?>
										<td></td>
									</tr>
								</tbody>
							</table>
						</div>					
					</form>										
			<div class="accordionContainer center_purest">				
				<div id="seatIndicatorLegend">
					Area for seat legend here. Later
				</div>
				<div id="essentialButtonsArea">
							<a class="button" id="buttonOK" ><span class="icon">Next</span></a>							
				</div>	
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