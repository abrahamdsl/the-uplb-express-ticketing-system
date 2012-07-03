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
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>
	

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
				Create Class				
			</div>
			<div style="padding-left:10px; clear: both">
				Specify classes here and the events that you want your students to attend.
				<!--The
				Semester/Term and school year has been automatically computed according to server date and 
				time, though please check if it is correct.-->
				<br/>				
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container">
					<div id="title">Class Details</div>
					<div id="content">												
						<input type="hidden" id="lastFocus" value="" />
						<?php
							$function = (isset($classObj)) ? 'updateClass' : 'createClass_step2';
						?>
						<form method="post"  action="<?php echo base_url().'academicctrl/'.$function; ?>" name="formLogin" id="formMain">
							<?php if(isset($classObj)){ ?>
								<input type="hidden" name="classID" value="<?php echo $classObj->UUID; ?>" />
							<?php } ?>
							<div>
								<div class="KoreanPeninsula" >
									<span class="left" >
										Course Title
										<span class="critical" >*</span>
									</span>
									
									<span class="rightSpecialHere" >																			
										<input type="text" value="<?php if(isset($classObj)) echo $classObj->CourseTitle; ?>" name="title" />
									</span>
								</div>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Course Number
										<span class="critical" >*</span>
								</span>
								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($classObj)) echo $classObj->CourseNum; ?>" name="number" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Lecture Section
										<span class="critical" >*</span>
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($classObj)) echo $classObj->LectureSect; ?>" name="lectsect" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Recit/Lab Section
								</span>								
								<span class="rightSpecialHere" >																			
									<input type="text" value="<?php if(isset($classObj)) echo $classObj->RecitSect; ?>" name="recitsect" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Term
										<span class="critical" >*</span>
								</span>								
								<span class="rightSpecialHere" >																			
									<select name="term" >
										<option value="1" <?php if(isset($classObj) and $classObj->Term == 1 ) echo 'selected="selected"'; ?> >First Semester</option>
										<option value="2" <?php if(isset($classObj) and $classObj->Term == 2 ) echo 'selected="selected"'; ?> >Second Semester</option>
										<option value="3" <?php if(isset($classObj) and $classObj->Term == 3 ) echo 'selected="selected"'; ?> >Summer</option>
									</select>
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Academic Year
										<span class="critical" >*</span>
								</span>
								
								<span class="rightSpecialHere" >																			
									<input type="text" name="acadyear_1" value="<?php echo date('Y'); ?>" />
									<input type="text" name="acadyear_2" value="<?php echo date('Y', strtotime( '+1year', strtotime(date('Y-m-d') ) ) ); ?>" />
								</span>
							</div>
							<div class="KoreanPeninsula" >
								<span class="left" >
										Comments
								</span>
								<span class="rightSpecialHere" >																			
									<textarea name="comments" cols="20" rows="10" style="width:300px;" ><?php if(isset($classObj)) echo $classObj->Comments; ?></textarea>
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