<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
$this->load->view('html-generic/metadata.inc');
?>
<?php
	$this->pageTitle = "Login";
	$this->thisPage_menuCorrespond = "HOME";	
	$this->load->view('html-generic/segoefont_loader.inc');	
	$this->load->view('html-generic/head-title.inc');
?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/body_all.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/loginPage.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/buttonOK.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent04.css'; ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/css/createEvent05.css'; ?>"/>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>	
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/resetbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/proceedbutton_jquery.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/form-validation/userLogin.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/submitKeypressHandler.js'; ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url().'assets/javascript/anti-ie.js'; ?>" ></script>
	<style type="text/css">
		div.questionableBrowser{
			height: auto;
			width: 100%;
			margin: 5px; 
			clear:right;
			background-color:rgb(255,174,201);
			padding: 10px;
		}
		div.questionableBrowserInner{
			border: 2px solid rgb(255,45,45);
			font-size: 1.6em;
			padding: 5px;
			width: 95%;
			height: 10%;
		}
	</style>
</head>
<body>
<?php if( isset($UA_CHECK ) and $UA_CHECK != BR_ALLOWED ) { ?> 
<div class="questionableBrowser" >
	<div class="center_purest questionableBrowserInner" >
		<?php switch( $UA_CHECK ) { 
			case BR_UNKNOWN_BUT_PERMIT_STILL: 
					$this->telemetry_model->add( BR_UNKNOWN_BUT_PERMIT_STILL, $uuid_new_ident, $_client_iPv4, "REF_".$uuid, "" );
		?>
				We do not know exactly what your browser is but decided to allow it.
				As a result, some functionalities might be buggy or not available.
		<?php 
				break;
			case BR_NOT_TESTED_BUT_PERMIT_STILL:
				$this->telemetry_model->add( BR_NOT_TESTED_BUT_PERMIT_STILL,  $uuid_new_ident, $_client_iPv4, "REF_".$uuid, "" );
		?>
				We have tested a recent version of your browser but not this particular
				one you are using. Therefore, we can't be sure if all functionalities would work
				as they should.
		<?php 
				break;
			default: break;
		?>
		<?php } ?>
	</div>
</div>
<?php } ?>
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
				Login to access services.
			</div>
			
			<div id="left_content">	
				<div class="text_box">
					<form method="post"  action="<?php echo base_url().'SessionCtrl/login' ?>" name="formLogin" id="formMain">
						<div class="login_form_row">
							<label class="login_label">Username:</label>
							<input type="text" name="username" class="login_input" value="" /><br/>
							<input type="hidden" name="username_validate" value="0" />
						</div>
						<div class="login_form_row">
							<label class="login_label">Password:</label>
							<input type="password" name="password" class="login_input" value="" /><br/>
							<input type="hidden" name="password_validate" value="0" />
						</div>
						<div id="essentialButtonsArea">
							<a class="button button2" id="buttonOK" ><span class="icon">Log me in</span></a>
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
							echo "&nbsp;";
						}
					?>
				</div>
				<div class="signup_div">
					<h3 style="float:left;">No account yet?</h3>
					<a href="<?php echo base_url().'/userAccountCtrl/userSignup'; ?>" class="button" id="buttonOK2" ><span class="icon">Sign up</span></a>
				</div>
				
			</div><!-- left content -->
			<div style="width: 50%; float: right; border-left: 2px solid orange; margin-right: 10px; padding: 10px 10px 10px 30px; position: relative; top: -60px;" >
				<h3>Hi!</h3>
				<p>
					Thank you for testing this web application. You may use the following accounts in accessing the application if you don't want to try the sign-up process
					now.
				</p>
				<table class="center_purest" style="padding: 10px;">
					<thead style="font-size: 1.2em;" >
						<tr>
							<td>Username</td>
							<td>&nbsp;</td>
							<td>Password</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>sampleuser01</td>
							<td> | </td>
							<td>southkorea</td>
						</tr>
						<tr>
							<td>sampleuser02</td>
							<td> | </td>
							<td>northkorea</td>
						</tr>
						<tr>
							<td>sampleuser03</td>
							<td> | </td>
							<td>tokyojapan</td>
						</tr>
						<tr>
							<td>sampleuser04</td>
							<td> | </td>
							<td>washingtonusa</td>
						</tr>
					</tbody>
				</table>
				<p>
					Booking tutorial <a href="http://abrahamdslx.blogspot.com/2012/02/booking-event-in-uxt.html">here.</a>
				</p>
			</div>
		</div><!--end of centralContainer-->
		<div style=" clear:both;"></div>
    </div><!--end of main content-->
	
<?php
	$this->load->view('html-generic/footer.inc');
?>
</div>
</body>
</html>