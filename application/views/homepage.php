<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Home";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/metrotile_colors_jumbled.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/><!-- needed for accordion -->
	<!--For modal v1-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/overlay_general.css'; ?>"/>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>" ></script>
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>" ></script>
	<!--For modal v1-->	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/nextGenModal.js'; ?>" ></script>
	<script type="text/javascript" >
		$(document).ready( function(){
			$('a.notyet').click( function(){
				$.fn.nextGenModal({
				   msgType: 'okay',
				   title: 'Not yet :-)',
				   message: 'Feature coming later'
				});
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
			$this->load->view('html-generic/menu-bar.inc');
			$this->load->view('html-generic/userInfo-bar.inc');
		?>
    </div>    
    <div id="main_content">
    	<div id="centralContainer" class="homepageSpecial" > 
			<div id="page_title">
				Functions
			</div>
			<div id="instruction" >
				Now, what should we do? :-)
				<br/>
			</div>
			<!-- accordion start -->
			<div class="center_purest homePage_accordion_container" >
			<div id="accordion" >
				<h3><a href="#">Customer</a></h3>
				<div>
					<?php
						if( $permissions->CUSTOMER )
						{
					?>
					<div class="metrotile mtile1" >
							<a href="<?php echo base_url(); ?>eventctrl/book"><img src="<?php echo base_url(); ?>assets/images/metrotiles/appbar.paper2.png" alt="Purchase ticket" /></a>
					</div>
					<div class="metrotile mtile2"  >
							<a href="<?php echo base_url()."eventctrl/managebooking"; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/managebooking.png" alt="Manage Booking" /></a>
					</div>
					<!--
					<div class="metrotile" >
							<a class="notyet" href="#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/transhistory.png" alt="Transaction History" /></a>
					</div>
					<div class="metrotile" >
							<a  class="notyet" href="#"><img src="<?php echo base_url(); ?>assets/images/metrotiles/inquiries.png" alt="Inquiries" /></a>
					</div>
					-->
					<?php
						}else{
					?>
						You are not allowed to access this feature.
					<?php
						}
					?>
				</div>
				<?php
					if( $permissions->EVENT_MANAGER )
					{
				?>
				<h3><a href="#">Event Management</a></h3>
				<div>
					<div class="metrotile mtile3"  >
							<a href="<?php echo base_url(); ?>eventctrl/create"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-createevent.png" alt="Create Event" /></a>
					</div>
					<div class="metrotile mtile5" >
							<a  href="<?php echo base_url(); ?>eventctrl2/manageEvent" ><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-modifyevent.png" alt="Modify Event" /></a>
					</div>
					<div class="metrotile mtile6"  >
							<a href="<?php echo base_url(); ?>eventctrl/confirm" ><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-confirmreservation.png" alt="Confirm Reservation" /></a>
					</div>
				</div>
				<?php
					} // ender for event management
				?>
				<?php
					if( $permissions->RECEPTIONIST )
					{
				?>
				<h3><a href="#">Checking in</a></h3>
				<div>
					<div class="metrotile mtile7"  >
						<a href="<?php echo base_url(); ?>academicctrl/check_start/1"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-receiveguests.png" alt="Receive Guests" /></a>
					</div>
					<div class="metrotile mtile3" >
						<a href="<?php echo base_url(); ?>academicctrl/check_start/2"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-checkout.png" alt="Check-out Guests" /></a>
					</div>
				</div>
				<?php
					} // ender for receptionist
				?>
				<?php
					if( $permissions->ADMINISTRATOR )
					{
				?>
				<h3><a href="#">Administration</a></h3>
				<div>
					<div class="metrotile mtile2"  >
						<a href="<?php echo base_url().'useracctctrl/system_settings'; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-systemsettings.png" alt="Edit System Settings" /></a>
					</div>
					<div class="metrotile mtile7"  >
						<a href="<?php echo base_url().'seatctrl/manageseatmap'; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-manageseatmaps.png" alt="Manage Seat Map" /></a>
					</div>
					<div class="metrotile mtile5"  >
						<a href="<?php echo base_url().'useracctctrl/manageuser'; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-manageusers.png" alt="Manage Users" /></a>
					</div>
					<div class="metrotile mtile4"  >
						<a href="<?php echo base_url().'useracctctrl/managepaymentmode'; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-managepaymentmodes.png" alt="Manage Payment Modes" /></a>
					</div>
				</div>
				<?php
					} // ender for administrator
				?>
				<?php
					if( $permissions->FACULTY)
					{
				?>
				<h3><a href="#">Faculty Lounge</a></h3>
				<div>
					<div class="metrotile mtile4"  >
							<a href="<?php echo base_url()."academicctrl/createClass"; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-createclass.png" alt="Create a new class" /></a>
					</div>
					<div class="metrotile mtile6"  >
							<a href="<?php echo base_url()."academicctrl/manageClasses"; ?>"><img src="<?php echo base_url(); ?>assets/images/metrotiles/uxt-seeattendingstudents.png" alt="See going students" /></a>
					</div>
				</div>
				<?php
					} // ender for administrator
					if( true == false){	// para lang di to ma-output sa HTML page for the meantime :D
				?>
				<!--
				<h3><a href="#">Announcements</a></h3>
				<div>
					<p>
					Cras dictum. Pellentesque habitant morbi tristique senectus et netus
					et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in
					faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia
					mauris vel est.
					</p>
					<p>
					Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus.
					Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
					inceptos himenaeos.
					</p>
				</div>
				-->
				<?php
					}//if( true==false)
				?>
			</div>
			</div>
			<!-- accordion end -->
			<div style=" clear:both;"></div>
		</div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>