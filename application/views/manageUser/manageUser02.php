<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Manage User";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->				
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/bookStep2.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/manageBooking01.css'; ?>"/>		
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/metrotile_colors_basic.css'; ?>"/>		
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
				window.location = CI.base_url + 'userAccountCtrl/manageuser';
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
				Manage User
			</div>
			<div style="padding-left:10px; clear: both">
				Manipulate ALL the users.
				<br/>
			</div>			
			<!-- accordion start -->			
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" class="specialOnMB01" >					
				<h3 id="h_x1"><a href="#">&nbsp;</a></h3>
				<div id="x1" class="section" >									
					<div class="bookingDetails">						
						<div class="top">								
								<div class="start">
									<span class="deed" >
										&nbsp;
									</span>
									<span class="contentproper_time" >										
										<?php echo $userMainInfo->AccountNum ?>
									</span>
									<span class="contentproper_date" >
										account num									
									</span>
								</div>								
								<div class="end">
									<span class="deed" >
										&nbsp;
									</span>									
									<span class="contentproper_time" >										
										<?php echo $userMainInfo->username ?>
									</span>
									<span class="contentproper_date" >
										username
									</span>
								</div>
							</div>
							<div class="bdtitle" >								
								<?php echo $userMainInfo->Lname.", ".$userMainInfo->Fname." ".$userMainInfo->Mname ?>
							</div>
							<div class="bottom bottomspecialOnMB01">								
								<?php
									if( isset($userUPLBInfo->studentNumber) and $userUPLBInfo->studentNumber != NULL )
									{
										echo "<p>UPLB Student Number: ".$userUPLBInfo->studentNumber."</p>";
									}
									if( isset($userUPLBInfo->employeeNumber) and $userUPLBInfo->employeeNumber != NULL )
									{
										echo "<p>UPLB Employee Number: ".$userUPLBInfo->employeeNumber."</p>";
									}
								?>
								<br/>
								<br/>																								
							</div>
					</div>
					<div class="containingClassTable">								
						<div class="metrotile notyet" name="viewdetails" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-viewdetails.png" alt="View details" /></a>
							<form method="post" action="<?php echo base_url().'userAccountCtrl/manageuser_viewdetails'; ?>" class="notyet" >
								<input type="hidden" name="accountNum" value="<?php echo $userMainInfo->AccountNum; ?>"   />
							</form>
						</div>
						<div class="metrotile notyet" name="resetpasword" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-resetpassword.png" alt="Reset password" /></a>
							<form method="post" action="<?php echo base_url().'userAccountCtrl/manageuser_resetpassword'; ?>" >
								<input type="hidden" name="accountNum" value="<?php echo $userMainInfo->AccountNum; ?>" class="notyet" />
							</form>
						</div>
						<div class="metrotile" name="editroles" >
							<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-editroles.png" alt="Edit Roles" /></a>
							<form method="post" action="<?php echo base_url().'userAccountCtrl/manageuser_editroles'; ?>" >
								<input type="hidden" name="accountNum" value="<?php echo $userMainInfo->AccountNum; ?>" />
							</form>
						</div>
						<div class="metrotile notyet" name="discipline" >
							<a  href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-disciplineuser.png" alt="Discipline user" /></a>
							<form method="post" action="<?php echo base_url().'userAccountCtrl/manageuser_disciplineuser'; ?>" >
								<input type="hidden" name="accountNum" value="<?php echo $userMainInfo->AccountNum; ?>" class="notyet"  />
							</form>
						</div>						
					</div>																											
				</div>							
			</div>			
			<div id="essentialButtonsArea" >											
					<a class="button" id="buttonOK" ><span class="icon" >Another user</span></a>				
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