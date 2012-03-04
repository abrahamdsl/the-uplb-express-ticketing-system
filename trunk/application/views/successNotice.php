<?php
/**
*	Generic Success Notice Page
* 	Created 03MAR2012-1521
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D
   
*	By the way, here are the variables to be passed to here from the controller:

	$theMessage	- STRING - REQUIRED - As in the message you want the user to see.
	$redirect	- BOOLEAN - NOT_REQUIRED - If the page should redirect or not. 
				*Non-presence and value TRUE indicates automatic redirection to homepage.	
	$redirectURI  - STRING (URI) - Where we should redirect.
	$defaultAction - STRING - NOT_REQUIRED - Default is "Home". Indicates what the main
				button should do when clicked. If present or not equal to "Home", the other
				button ( 'buttonReset' ) will be the one for "Home".
	
	
*/
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Success";
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
			$(document).ready( function(){
				$('#buttonReset').click( function(){
					
					<?php if(( !isset( $defaultAction ) or strtolower($defaultAction) === "home" ) === FALSE ){ ?>
						window.location = CI.base_url;
					<?php 
						}else{						
					?>
						window.history.back();
					<?php } ?>
				});

				$('#buttonOK').click( function(){
					<?php if( !isset( $defaultAction ) or strtolower($defaultAction) === "home" ){ ?>
						window.location = CI.base_url;
					<?php 
						}else{
							echo "window.location = '".$redirectURI."';";
						}
					?>
					
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
				Success
			</div>						
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
				<div class="accordionImitation cEvent04_container errorNotice_div_custom">
					<div id="title"></div>
					<div id="content">						
						<p>
							<?php echo $theMessage; ?>
						</p>						
						<?php if( !isset( $redirect) or $redirect === true ){ ?>
						<p>
							Redirecting you to the homepage....
						</p>
						<?php } ?>
					</div>
				</div>												
			</div>
			<!-- accordion end -->
			<div id="essentialButtonsArea">							
							<a class="button" id="buttonOK" >
								<span class="icon">
									<?php if( !isset( $defaultAction ) or strtolower($defaultAction) === "home" ){ ?>
										Home
									<?php 
										}else{
											echo $defaultAction;
										}
									?>										
								</span>
							</a>
							<?php 
								if( (!isset( $defaultAction ) or strtolower($defaultAction) === "home")
									=== FALSE)
								{ ?>
								<a class="button" id="buttonReset" ><span class="icon">Home</span></a>
							<?php 
								}
							?>
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