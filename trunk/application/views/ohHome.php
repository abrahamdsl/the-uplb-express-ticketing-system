<?php
$this->load->view('html-generic/doctype.inc');
?>
<head>
<?php
	$this->pageTitle = "UxT - Signing UP Page 1!!!";
	$this->thisPage_menuCorrespond = "HOME";
	$this->load->view('html-generic/head-title.inc');
?>
</head>
<body>
<?php
	$this->load->view('html-generic/menu-bar.inc');
?>

<a href="<?php echo site_url().'userAccountCtrl/userSignup' ?>" > Sign me up </a>

<?php
	$this->load->view('html-generic/footer.inc');
?>
</body>
<html>