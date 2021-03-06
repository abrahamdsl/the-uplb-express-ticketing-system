<?php
/**
*	Generic Error Notice Page
* 	Created 03MAR2012-1521
*	Part of "The UPLB Express Ticketing System"
*   Special Problem of Abraham Darius Llave / 2008-37120
*	In partial fulfillment of the requirements for the degree of Bachelor fo Science in Computer Science
*	University of the Philippines Los Banos
*	------------------------------
*	
*	From the title, the purpose of this is kinda obvious. :D
   
*	By the way, here are the variables to be passed to here from the controller:

	$error - STRING - REQUIRED - What type of error. If "CUSTOM", then custom message should be displayed.
	$theMessage	- STRING - NOT_REQUIRED - As in the message you want the user to see.
	$redirect	- INT - NOT_REQUIRED - If the page should redirect or not. 
				* Value 0 don't redirect whatever happens
				* Non-presence or value 1 indicates automatic redirection to homepage.
				* Value 2 redirect to location specified by $redirectURI
	$redirectURI  - STRING (URI) - Where we should redirect.
	$defaultAction - STRING - NOT_REQUIRED - Default is "Home". Indicates what the main
				button should do when clicked. If present or not equal to "Home", the other
				button ( 'buttonReset' ) will be the one for "Home".
	
	
*/
if(  (!isset( $redirect ) or $redirect === 1) ) {
	header( 'refresh: 5;url='.base_url() );
}else
if( $redirect === 2 ){
	header( 'refresh: 5;url='.$redirectURI );
}
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "Operation Result Message";
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
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/errorNotice.js'; ?>"/></script>-->
	<script type="text/javascript" >
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
				Error
			</div>
			<!-- accordion start -->
			<div class="center_purest homePage_accordion_container" style="clear:both;" >
				<div class="accordionImitation cEvent04_container errorNotice_div_custom">
					<div id="title"></div>
					<div id="content">
						<?php if( $error == "UNAUTHORIZED_ACCESS" ){ ?>
							<p>
								You are trying to access a page which requires authentication 
								beforehand, but of course, you are denied.
							</p>
						<?php }else ?>
						<?php if( $error == "NO_DATA" ){ ?>
							<p>
								You are trying to access a page which
								submitted data beforehand, but you attempted to access it without submitting the
								data first!
								<br/><br/>
								( You will also receive this message if you try to access a functionality which requires that you accomplish first
								 an earlier step ).
							</p>
						<?php }else ?>
						<?php if( $error == "NO_PERMISSION" ){ ?>
							<p>
								You are trying to access a page which
								requires certain permissions to be granted first to your account.
							</p>
						<?php }else ?>
						<?php if( $error == "CUSTOM" ){ ?>
							<p>
								<?php echo $theMessage; ?>
							</p>
						<?php } ?>
						<p>
							<?php
								if( !isset( $redirect) or $redirect == 1 )
								{	
									echo "Redirecting you to homepage ... ";
								}else{
									if( $redirect == 2 )
									{
										echo "Redirecting you to ".$defaultAction."...";
									}
								}
							?>
						</p>
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