<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Manage Payment Mode";
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
	<style type="text/css" >
		input[type="text"]{
			width: 300px;
		}
	</style>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep1.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/managebooking02.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookProgressIndicator.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent06.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>				
	<script type="text/javascript" >
		$(document).ready( function(){
			$('a#buttonOK').click( function(){
				document.forms[0].submit();	
			});
		});
	</script>				
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
				Manage Payment Mode			
			</div>
			<div style="padding-left:10px; clear: both">
				Input your details						
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Payment mode details</div>
					<div id="content">												
						<input type="hidden" id="lastFocus" value="" />
						<?php
							$function = (isset($singleChannel)) ? 'managepaymentmode_save' : 'addpaymentmode_step2';
						?>
						<form method="post"  action="<?php echo base_url().'useracctctrl/'.$function; ?>" name="formLogin" id="formMain">
							<input type="hidden" name="mode" value="<?php echo $mode; ?>" />
							<?php if(isset($singleChannel)){ ?>
								<input type="hidden" name="uniqueID" value="<?php echo $singleChannel->UniqueID; ?>" />
							<?php } ?>
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Type
										<span class="critical" >*</span>
									</span>									
									<span class="rightSpecialHere" >																			
										<select name="ptype">
											<option value="COD" <?php if( isset($singleChannel) and $singleChannel->Type=="COD") echo 'selected="selected"';?> >COD</option>
											<option value="ONLINE" <?php if( isset($singleChannel) and $singleChannel->Type=="ONLINE") echo 'selected="selected"';?> >ONLINE</option>
											<option value="OTHER" <?php if( isset($singleChannel) and $singleChannel->Type=="OTHER") echo 'selected="selected"';?> >Other</option>
										</select>
									</span>
								</div>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Name
										<span class="critical" >*</span>
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Name; ?>" name="name" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Contact Person
										<span class="critical" >*</span>										
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Contact_Person; ?>" name="person" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Location
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Location; ?>" name="location" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Cellphone
										<span class="critical" >*</span>
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Cellphone; ?>" name="cellphone" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Landline										
								</span>
								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Landline; ?>" name="landline" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Email
								</span>
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($singleChannel)) echo $singleChannel->Email; ?>" name="email" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Comments
								</span>
								<span class="rightSpecialHere" >																			
									<textarea name="comments" cols="20" rows="10" style="width:300px;" ><?php if(isset($singleChannel)) echo $singleChannel->Comments; ?></textarea>
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Internal data type
								</span>
								<span class="rightSpecialHere" >																			
									<select name="internal_data_type">
										<option value="WIN5" <?php if( isset($singleChannel) and $singleChannel->internal_data_type=="WIN5") echo 'selected="selected"';?> >WIN5</option>
										<option value="XML" <?php if( isset($singleChannel) and $singleChannel->internal_data_type=="XML") echo 'selected="selected"';?> >XML</option>										
									</select>
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Internal data
								</span>
								<span class="rightSpecialHere" >																			
									<textarea name="internal_data" cols="20" rows="10" style="width:300px;" ><?php if(isset($singleChannel)) echo$singleChannel->internal_data; ?></textarea>
								</span>
							</div>
						</form>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<?php
				$this->load->view( 'html-generic/criticalreminder.inc' );
			?>	
			<div id="essentialButtonsArea">							
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