	<!-- /*the CSS responsible for this UL should have been declared earlier.*/  -->
		<div id="menu_bar">
			<ul>
				<li <?php if(isset( $this->thisPage_menuCorrespond) and $this->thisPage_menuCorrespond == "HOME") echo 'id="menu_bar_current"';  ?> ><a href="<?php echo base_url(); ?>">Home</a></li>
				<li <?php if(isset( $this->thisPage_menuCorrespond) and $this->thisPage_menuCorrespond == "BOOK") echo 'id="menu_bar_current"';  ?> ><a href="<?php echo base_url()."EventCtrl/book"; ?>">Book</a></li>
				<li <?php if(isset( $this->thisPage_menuCorrespond) and $this->thisPage_menuCorrespond == "ACCOUNT") echo 'id="menu_bar_current"';  ?> ><a href="<?php echo base_url()."userAccountCtrl/myAccount"; ?>">My Account</a></li>
				<li <?php if(isset( $this->thisPage_menuCorrespond) and $this->thisPage_menuCorrespond == "CUST_SVC") echo 'id="menu_bar_current"';  ?> ><a href="#">Customer Service</a></li>
				<li <?php if(isset( $this->thisPage_menuCorrespond) and $this->thisPage_menuCorrespond == "HELP") echo 'id="menu_bar_current"';  ?> ><a href="#">Help</a></li>				
			</ul>
		</div>
