<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Edit Roles - Manage User";
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
	<style type="text/css" >
		div.inactive{
			background-color: rgb(145,145,145);
		}
		div.active{
			background-color: rgb(210,71,38);
		}
	</style>
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>				
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/generalChecks.js'; ?>" ></script>
	<!--<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/makeTimestampFriendly.js'; ?>" ></script>-->
	<script type="text/javascript" >
		function formSubmit()
		{			
			document.forms[0].submit();
		}//formSubmit(..)
	
		$(document).ready( function(){
			$('div.metrotile').children('input[type="hidden"]').each( function(){
				// assigns colors to the role tiles on page load	
					var thisVal = parseInt( $(this).val(), 10);					
					if( thisVal == 0 )
					{
						$(this).parent('div.metrotile').addClass('inactive');
					}else{
						$(this).parent('div.metrotile').addClass('active');
					}
				}
			);			
		
			$( 'form#formMain' ).submit( function(e){
				e.preventDefault();
				$("#buttonOK").click();
			});
		
			$('div.metrotile').click( function(e){
				e.preventDefault();
				var hiddenInputHandle = $(this).children('input[type="hidden"]').first();
				var value = hiddenInputHandle.val();
				
				if( value == 0 )
				{
					$(this).removeClass('inactive');
					$(this).addClass('active');
					hiddenInputHandle.val( 1 );
				}else{
					$(this).removeClass('active');
					$(this).addClass('inactive');
					hiddenInputHandle.val( 0 );
				}
			});
			
			$('a#buttonReset').click( function(){
				window.location = CI.base_url + 'userAccountCtrl/manageUser_step2';
			});
			
			$('a#buttonOK').click( function(){
				formSubmit();			
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
        
    
    <div id="main_content">    	
    	<div id="centralContainer" class="homepageSpecial" > 
			<div id="page_title">
				Manage User<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edit Roles
			</div>
			<div style="padding-left:10px; clear: both">
				Roles that are grayed out means it is not currently enabled for the user. Click the tile
				representing the role to enable it. When done, click Save. <br/><br/>
				The customer role cannot be removed.				
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
						<form method="post" action="<?php echo base_url().'userAccountCtrl/manageuser_editrole_save'; ?>" id="formMain" >
							<div class="metrotile" id="customer" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-user_customer.png" alt="Customer" /></a>
								<input type="hidden" name="customer" value="<?php echo $permissionObj->CUSTOMER; ?>" />
							</div>						
							<div class="metrotile" id="eventmanager" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-user_eventmanager.png" alt="Event Manager" /></a>
								<input type="hidden" name="eventmanager" value="<?php echo $permissionObj->EVENT_MANAGER; ?>" />
							</div>
							<div class="metrotile" id="receptionist" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-user_receptionist.png" alt="Receptionist" /></a>
								<input type="hidden" name="receptionist" value="<?php echo $permissionObj->RECEPTIONIST; ?>" />
							</div>
							<!--
							<div class="metrotile" id="administrator" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-user_administrator.png" alt="Administrator" /></a>
								<input type="hidden" name="administrator" value="<?php echo $permissionObj->ADMINISTRATOR; ?>" />
							</div>	-->
							<div class="metrotile" id="facultymember" >
								<a href="<?php echo base_url(); ?>#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-user_faculty.png" alt="Faculty Member" /></a>
								<input type="hidden" name="facultymember" value="<?php echo $permissionObj->FACULTY; ?>" />
							</div>	
						</form>
					</div>																											
				</div>							
			</div>
			<div id="essentialButtonsArea" >											
					<a class="button" id="buttonOK" ><span class="icon" >Save</span></a>					
					<a class="button" id="buttonReset" ><span class="icon" >Back</span></a>							
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