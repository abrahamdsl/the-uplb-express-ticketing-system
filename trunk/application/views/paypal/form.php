<html>
	<head>
	<?php
		$this->load->view('html-generic/baseURLforJS.inc');
	?>
	<?php
		$this->load->view('html-generic/jquery-core.inc');
	?>
	<script type="text/javascript"  >
		$(document).ready( function(){
			document.forms[0].submit();
		});
	</script>
	</head>
<body>
<h1>Processing your payment...</h1>
<?php echo $paypal_form; ?>

</body>
</html>