<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Enter identifier - Manage User";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css';?>" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent01.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/attend01.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/accordionImitate.css'; ?>"/>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"></script>		
	<script type="text/javascript">
		 $(document).ready(function() {
			$("#accordion").accordion();
		  });
	</script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>"></script>	
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/processAJAXresponse.js'; ?>"/></script>
	<?php			
		$this->load->view('html-generic/baseURLforJS.inc');	
	?>	
	<script type="text/javascript" >
		function formSubmit()
		{
			var x = $.ajax({	
					type: 'POST',
					url: CI.base_url + 'userAccountCtrl/isUserExisting2',
					timeout: 10000,
					data: $('form').first().serialize(),
					beforeSend: function(){
						$.fn.nextGenModal({
						   msgType: 'ajax',
						   title: 'processing',
						   message: 'Contacting server for your request...'
						});
					},
					success: function(data){
						if( $(data).find('resultstring').text() == 'USERNAME_EXISTS' )
						{
							window.location = $('input#ok_proceed').val();
						}else{
							if( $(data).find('type').text() != 'okay' ) $.fn.makeOverlayForResponse( data );
						}
					}
				});
		}//formSubmit(..)
		
		
		$(document).ready( function(){
			
			// when filling out 
			$('input[name="useridentifier"]').change( function() {		
				$(this).parent().siblings('span.NameRequired').hide();
			});
			
			$( 'form#formMain' ).submit( function(e){
				e.preventDefault();
				$("#buttonOK").click();
			});
			
			$("#buttonOK").click( function(e) {
				var bNumberHandle = $('input[name="useridentifier"]');			
				var allOK = 0;
				
				e.preventDefault();
				if( bNumberHandle.val() == "" )
				{				
					bNumberHandle.parent().siblings("span.NameRequired").show();
					allOK++;				
				}					
				if( allOK < 1 ) formSubmit();			
			});
		});
	</script>
</head>
<body>
<?php
		$this->load->view('html-generic/overlay_general.inc');
?>	
<div id="main_container" >
	<div id="header" >    	    	        
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
		<div id="centralContainer" >
			<div id="page_title" >
				Manage User
			</div>			
			<div id="instruction" >
				Please key in ...
			</div>				
			<!-- accordion start -->			
			<div class="accordionContainer center_purest" >
				<div id="accordion" >
					<h3><a href="#" >Key it in</a></h3>					
					<div>
					<form method="post" action="#" name="formLogin" id="formMain" >						
						<input type="hidden" id="ok_proceed" value="<?php echo base_url().'userAccountCtrl/manageUser_step2' ?>" />
						<div class="mainWizardMainSections" >
							<span class="MWMS1" >
								Username or account number
								<span class="critical" >*</span>
							</span>
							<span class="MWMS2" ><input type="text" name="useridentifier" class="textInputSize" /></span>					
							<span class="MWMShidden fieldErrorNotice NameRequired" >This is not allowed to be blank</span>
							<span class="MWMShidden fieldErrorNotice" id="ajaxind" >
								<img title="ajaxloader" src="<?php echo base_url().'assets/images/ajax-horiz.gif'; ?>" alt="ajax_loader" />
							</span>	
						</div>
					</form>
					</div>
				</div>
	<?php
	$this->load->view( 'html-generic/criticalreminder.inc' );
	?>	
				<div id="essentialButtonsArea" >														
							<a class="button" id="buttonOK" ><span class="icon" >Go</span></a>
							<a class="button" id="buttonReset" ><span class="icon" >Back</span></a>							
				</div>
				<div class="buttonfooterSeparator" ></div>
			</div>						
		</div>
    </div><!--end of main content-->	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>