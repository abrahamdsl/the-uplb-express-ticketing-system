<?php
/**
*	Generic Confirmation Notice Page
* 	Created 04MAR2012-1534
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D
   
*	By the way, here are the variables to be passed to here from the controller:

	$title 	- STRING - NOT_REQUIRED - Obviously.
	$theMessage	- STRING - REQUIRED - As in the message you want the user to see.
	$yesAction - STRING - NOT_REQUIRED - Caption for the yes button. Non-presence would just mean "Yes"
	$noAction - STRING - NOT_REQUIRED -  Caption for the no button. Non-presence would just mean "No"
	$yesURI - STRING (URI) - REQUIRED - Where we should redirect if user clicked yes. Actually form action.
	$noURI  - STRING (URI) - REQUIRED - Where we should redirect if user clicked no.	
	$formInputs  - ARRAY - REQUIRED - Input elements to be submitted to the $yesURI.
*/
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Question";
	$this->thisPage_menuCorrespond = "BOOK";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<!--For overlay-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php			
		$this->load->view('html-generic/jquery-core_choiceB.inc');	
	?>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"/></script>	
	<script type="text/javascript" />
			var yesURI = '<?php echo $yesURI; ?>';
			var noURI = '<?php echo $noURI; ?>';
			
			$(document).ready( function(){
				$('#buttonReset').click( function(){				
					$('form#formMain').attr('action',noURI);
					document.forms[0].submit();
				});

				$('#buttonOK').click( function(){
					$('form#formMain').attr('action',yesURI);
					document.forms[0].submit();
				});		
		});
	</script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<!--For overlay-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/overlay_general.js'; ?>"/></script>	
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
				<?php if( isset( $title ) ) echo $title;
					  else
						echo  "Wondering"; 
				?>
			</div>						
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container errorNotice_div_custom">
					<div id="title"></div>
					<div id="content">						
						<p>
							<?php echo $theMessage; ?>
						</p>												
						<form method="post" action="" name="formMain" id="formMain" >
							<?php foreach( $formInputs as $key => $value ){?>
								<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
							<?php } ?>
						</input>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" >
								<span class="icon">
									<?php if( !isset( $yesAction ) ){ ?>
										Yes
									<?php 
										}else{
											echo $yesAction;
										}
									?>																			
								</span>
							</a>
							<a class="button" id="buttonReset" >
								<span class="icon">
									<?php if( !isset( $NoAction ) ){ ?>
										No
									<?php 
										}else{
											echo $noAction;
										}
									?>																			
								</span>
							</a>							
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