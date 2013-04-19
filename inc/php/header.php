<?php
	session_start();
	if (!$_SESSION['user_loggedin'] && $_SERVER["SCRIPT_NAME"] != "/index.php" && !$_GET["id"] ) {
		//header("Location: http://admin.freemystore.com/");
		http_redirect("http://admin.freemystore.com/");
		exit;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>FreeMyStore Admin</title>
	<link type="text/css" rel="stylesheet" href="../inc/css/styles.css">
	<script type="text/javascript">
		function addJS(jsname) {
			var th = document.getElementsByTagName('head')[0];
			var s = document.createElement('script');
			s.setAttribute('type','text/javascript');
			s.setAttribute('src',jsname);
			th.appendChild(s);
		}
		function addCSS(cssname) {
			var th = document.getElementsByTagName('head')[0];
			var l = document.createElement('link');
			l.setAttribute('type','text/css');
			l.setAttribute('rel','stylesheet');
			l.setAttribute('href',cssname);
			th.appendChild(l);
		}
	</script>
</head>

<body>
	<table class="wrapper center">
		<tr>
			<td class="topleft">&nbsp;</td>
			<td class="top">&nbsp;</td>
			<td class="topright">&nbsp;</td>
		</tr>
		<tr>
			<td class="left">&nbsp;</td>
			<td>
				<div class="page">
					
					<div class="header">
						<div class="marquee">
							<div class="title">admin.freemystore.com</div>
						</div>
						<div class="menu">
							<ul>
								<li><a href="http://admin.freemystore.com">home</a></li>
								<li><a href="http://admin.freemystore.com?login=1">login</a></li>
								<li><a href="http://admin.freemystore.com?logout=1">logout</a></li>
							</ul>
							<?php if ($_SESSION['loggedin']) { ?>
								<div class="status">logged in as <?php echo $_SESSION['user']?></div>
							<?php } ?>
						</div>
					</div>
					<div class="body">