<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UXT - Login";
	$this->thisPage_menuCorrespond = "HOME";	
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/loginPage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>"/></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/userLogin.js'; ?>"/></script>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/submitKeypressHandler.js'; ?>"/></script>	
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
		<!-- userInfo bar not needed here -->
    </div>
        
    
    <div id="main_content">    	
    	<div id="centralContainer">			
			<div id="page_title">
				Login
			</div>
			<div style="padding-left:10px; clear: both">
				Login to start enjoying the UPLB experience ... Acho cho cho chooo choo choooo... lorem ipsum bla bla bla
			</div>				
			<div id="left_content">					
				<div class="text_box">
					<form method="post"  action="<?php echo base_url().'SessionCtrl/login' ?>" name="formLogin" id="formMain">
						<div class="login_form_row">
							<label class="login_label">Username:</label>
							<input type="text" name="username" class="login_input" /><br/>							
							<input type="hidden" name="username_validate" value="0" />
						</div>
						
						<div class="login_form_row">
							<label class="login_label">Password:</label>
							<input type="password" name="password" class="login_input" /><br/>							
							<input type="hidden" name="password_validate" value="0" />
						</div>                                     
						<div id="essentialButtonsArea">
							<a onClick="document.pressed=this.value" class="button button2" id="buttonOK" ><span class="icon">Log me in</span></a>																										
						</div>												
					</form>
				</div><!--text_box-->
				<div class="errorNotice">
					<span class="FldMsg" id="usernameFldMsg"></span>
					<span class="FldMsg" id="passwordFldMsg"></span>
					<?php
						if( isset($incorrect_credentials) or
							$this->session->userdata('LOGIN_WARNING') != FALSE 
						){
					?>
						<ul class="loginWarning">							
						<?php	
							foreach($this->session->userdata('LOGIN_WARNING') as $error)
							{
								echo "<li>";
								echo $error;
								echo "</li>";
							}
							$data['LOGIN_WARNING'] = FALSE;
							$this->session->set_userdata($data);
						?>
						</ul>
					<?php								
						}else{
							echo "&nbsp";
						}
					?>
				</div>				
				<div class="signup_div">
					<h3 style="float:left">No account yet?</h3>
					<a href="<?php echo base_url().'/userAccountCtrl/userSignup'; ?>" class="button" id="buttonOK" ><span class="icon">Sign up</span></a>						
				</div>
				
			</div><!-- left content -->
		</div><!--end of centralContainer-->			
		<div style=" clear:both;"></div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>