<!-- /*the CSS responsible for this UL should have been declared earlier.*/  -->
 <div id="menu_bar">
 <ul>
	<li <?php if($this->thisPage_menuCorrespond == "HOME") echo 'id="menu_bar_current"';  ?> ><a href="#">Home</a></li>
	<li <?php if($this->thisPage_menuCorrespond == "BOOK") echo 'id="menu_bar_current"';  ?> ><a href="#">Book</a></li>
	<li <?php if($this->thisPage_menuCorrespond == "RESERVATIONS") echo 'id="menu_bar_current"';  ?> ><a href="#">My Reservations</a></li>
	<li <?php if($this->thisPage_menuCorrespond == "CUST_SVC") echo 'id="menu_bar_current"';  ?> ><a href="#">Customer Service</a></li>
    <li <?php if($this->thisPage_menuCorrespond == "HELP") echo 'id="menu_bar_current"';  ?> ><a href="#">Help</a></li>				
 </ul>
 </div>