<?php include("inc/php/header.php"); ?>

<?php
	if ($_POST["btnLogin"]) {
		
		$_SESSION['user_loggedin'] = false;
		$_SESSION['user_id'] = 0;
		$_SESSION['user_name'] = "";
		$_SESSION['user_admin'] = "";
		
		$dbFmsAdmin = mysql_connect('localhost', 'root', 'soochow');
		if (!$dbFmsAdmin) {
		    print_r('Could not connect to FreeMyStore database: ' . mysql_error());
		    die(include("inc/php/footer.php"));
		} else {
			mysql_select_db('freemystore', $dbFmsAdmin); 
		}
		
		$strQuery = sprintf("SELECT * FROM admin_user WHERE username='%s' AND password='%s'",
	    	mysql_real_escape_string($_POST["txtUser"]),
	    	mysql_real_escape_string($_POST["txtPass"]));
		
		$strResult = mysql_query($strQuery);
		if (!$strResult) {
		    print_r('Error executing query: ' . mysql_error());
		    die(include("inc/php/footer.php"));
		}
		
		while ($arrRow = mysql_fetch_assoc($strResult)) {
			if ($arrRow['enabled']) {
				$_SESSION['user_loggedin'] = true;
				$_SESSION['user_id'] = $arrRow['userid'];
				$_SESSION['user_name'] = $arrRow['username'];
				$_SESSION['user_admin'] = $arrRow['admin'];
				break;
			}
		}
		
		mysql_free_result($strResult);
		mysql_close($dbFmsAdmin);
		
	}
?>

<?php
	if ($_GET['logout']) {
		session_destroy();
		$_SESSION['loggedin'] = false;
		echo "<script type='text/javascript'>window.location='http://admin.freemystore.com';</script>";
		//header("Location: http://admin.freemystore.com/");
		//exit;
	}
?>

<?php if (!$_SESSION['user_loggedin'] || $_GET['login']) { ?>

	<div class="login center">
		<form method="post" name="frmLogin" action="<?php $_SERVER["PHP_SELF"]?>">
			<table>
				<tr>
					<td align="right"><label for="txtUser" id="lblUser">Username:</label></td>
					<td align="left"><input class="textbox" name="txtUser" id="txtUser" type="text" tabindex="1"></td>
				</tr>
				<tr>
					<td align="right"><label for="txtPass" id="lblPass">Password:</label></td>
					<td align="left"><input class="textbox" name="txtPass" id="txtPass" type="password" tabindex="2"></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input name="btnLogin" type="submit" value="Login" tabindex="3"></td>
				</tr>
			</table>
		</form>
	</div>

<?php } else { ?>
	
	<div class="menu center">
		<table>
			<thead>
				<tr>
					<th>freemystore tools</th>
					<?php if ($_SESSION['user_admin']) { ?>
						<th>freemystore links</th>
						<th>external links</th>
					<?php } // end if($_SESSION['user_admin']) ?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<table>
							<tr><td><a href="tools/updateinventory.php">update inventory</a></td></tr>
							<tr><td><a href="tools/exportorders.php">export orders</a></td></tr>
							<tr><td><a href="reports/">view reports</a></td></tr>
							<?php if ($_SESSION['user_admin']) { ?>
								<tr><td><a href="tools/editcompanies.php">edit companies</a></td></tr>
								<tr><td><a href="tools/editpartners.php">edit partners</a></td></tr>
								<tr><td><a href="tools/editexports.php">edit exports</a></td></tr>
								<tr><td><a href="tools/editusers.php">edit users</a></td></tr>
							<?php } // end if($_SESSION['user_admin']) ?>
						</table>
					</td>
					<?php if ($_SESSION['user_admin']) { ?>
						<td>
							<table>
									<tr><td><a href="http://php.freemystore.com" target="_blank">php myadmin</a></td></tr>
								<tr><td><a href="http://ftp.freemystore.com" target="_blank">ftp server</a></td></tr>
							</table>
						</td>
						<td>
							<table>
								<tr><td><a href="https://www.freemystore.com/cart/index.php/admin" target="_blank">log in to magento</a></td></tr>
								<tr><td><a href="http://www.google.com/analytics" target="_blank">google analytics</a></td></tr>
							</table>
						</td>
					<?php } // end if($_SESSION['user_admin']) ?>
				</tr>
			</tbody>
		</table>
	</div>
	
<?php } //end if(!$_SESSION['user_loggedin'] || $_GET['login']) ?>

<?php include("inc/php/footer.php"); ?>