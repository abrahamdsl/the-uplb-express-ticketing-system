<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Home";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>

	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/homePage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/jquery-ui-custom.css'; ?>"/> <!-- needed for accordion -->
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>			
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery.min.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/jquery/jquery-ui.min.js'; ?>"/></script>		
  	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/accordionEssentials.js'; ?>"/></script>	
</head>
<body>
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
				Functions
			</div>
			<div style="padding-left:10px; clear: both">
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
					<div id="ticketSelectionDiv">
						Ticket selection here					
					</div>
					<div>
						Other functions
						<img src="<?php echo base_url().'assets/images/customer-otherfunction-temp_all_horiz.png'; ?>" usemap="#customerMap" />					
						<map name="customerMap">
							<area shape="rect" coords="4,1,186,103" title="Edit my Reservations" alt="Edit my Reservations" href="<?php echo base_url().'booking/editBooking'; ?>" />
							<area shape="rect" coords="188,1,371,103" title="View Transaction History" alt="View Transaction History" href="<?php echo base_url().'userAccountCtrl/viewTransactionHistory'; ?>" />
							<area shape="rect" coords="373,1,554,103" title="Send a message to CS" alt="Send a message to CS" href="<?php echo base_url().'client/sendMessage'; ?>" />
							<area shape="rect" coords="557,1,721,103" title="See your cash account" alt="See your cash account" href="<?php echo base_url().'client/cashAccountHome'; ?>" />
						</map>
					</div>									
					
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
					<img src="<?php echo base_url().'assets/images/event-manager_temp.png'; ?>"  usemap="#eventManagerMap"  />				
					<map name="eventManagerMap" >
						<area shape="rect" coords="6,7,176,101" title="Create Event" alt="Create Event" href="<?php echo base_url().'EventCtrl/create'; ?>" />
						<area shape="rect" coords="188,7,358,101" title="Confirm Reservation" alt="Confirm Reservation" href="" />
						<area shape="rect" coords="367,7,537,101" title="Modify Reservation" alt="Modify Reservation" href="" />
						<area shape="rect" coords="545,7,715,101" title="Modify Event" alt="Modify Event" href="" />
						<area shape="rect" coords="6,106,176,200" title="Modify Ticket Classes" alt="Modify Ticket Classes" href="" />
						<area shape="rect" coords="188,106,358,200" title="Clean Unconfirmed Slots" alt="Clean Unconfirmed Slots" href="" />
						<area shape="rect" coords="367,106,537,200" title="Customer care" alt="Customer care" href="" />
						<area shape="rect" coords="545,106,715,200" title="Grant Faculty Access" alt="Grant Faculty Access" href="" />
					</map>
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
					<p>
					Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
					Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
					ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
					lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
					</p>
					<ul>
						<li>List item one</li>
						<li>List item two</li>
						<li>List item three</li>
					</ul>
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
				<?php
					} // ender for administrator
				?>
				<?php
					if( $permissions->FACULTY)
					{
				?>
				<h3><a href="#">Terrorizing students...</a></h3>
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
				<?php
					} // ender for administrator
				?>
				<h3><a href="#">Announcements</a></h3>
				<div>					
					<p>
					Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
					ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
					amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
					odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
					</p>					
				</div>
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