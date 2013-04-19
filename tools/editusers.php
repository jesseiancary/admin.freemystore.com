<?php include("../inc/php/header.php"); ?>

<!-- Try to move the following into tables.js -->
<link type="text/css" rel="stylesheet" href="../inc/css/jq_datatables.css">
<script type="text/javascript" src="../inc/js/jq/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../inc/js/jq/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="../inc/js/tables.js"></script>
<script type="text/javascript">

	$(function() {
		
		$("#btnNew").click(function() {
			$("#title div").html("New User");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").hide();
		});
		
		$("#users tbody tr").click(function() {
			$("#title div").html("Edit User");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").show();
			$("#hidId").val( $(this).find("#userid").attr("title") );
			$("#txtName").val( $(this).find("#username").attr("title") );
			$("#txtPwd").val( $(this).find("#password").attr("title") );
			$("#chkAdmin").attr("checked", $(this).find("#useradmin").attr("title"));
			$("#chkEnabled").attr("checked", $(this).find("#userenabled").attr("title"));
			
		});
		
	});
	
	$(document).ready(function() {
		
		$("#title div").html("Users");
		$("#btnSave").hide();
		$("#btnNew").show();
		$("#btnCancel").hide();
		$("#editform").hide();
		$("#data").show();
		
		oTable = $("#users").dataTable( {
			"sPaginationType": "scrolling"
		});
		
	});
	
</script>

<?php
	
	$dbFmsAdmin = mysql_connect('localhost', 'root', 'soochow');
	if (!$dbFmsAdmin) {
	    print_r('Could not connect to FreeMyStore database: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	} else {
		mysql_select_db('freemystore', $dbFmsAdmin);
	}
	
	if ($_POST['btnSave']) {
		
		$strQuery = sprintf("SELECT * FROM admin_user WHERE userid = %s",
			mysql_real_escape_string($_POST["hidId"]));
		
		$qryExists = mysql_query($strQuery);
		if (!$qryExists) {
		    print_r('Error selecting user: ' . mysql_error());
		    die(include("inc/php/footer.php"));
		}
		
		if (mysql_fetch_row($qryExists)) {
			
			$strQuery = sprintf("UPDATE admin_user SET username = '%s', password = '%s', admin = %s, enabled = %s WHERE userid = %s",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtPwd"]),
				mysql_real_escape_string( (isset($_POST["chkAdmin"]))?("1"):("0") ),
				mysql_real_escape_string( (isset($_POST["chkEnabled"]))?("1"):("0") ),
				mysql_real_escape_string($_POST["hidId"]));
			
			$qryUpdate = mysql_query($strQuery);
			if (!$qryUpdate) {
			    print_r('Error updating user: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		} else {
			
			$strQuery = sprintf("INSERT INTO admin_user (username, password, admin) VALUES ('%s', '%s', %s)",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtPwd"]),
				mysql_real_escape_string( (isset($_POST["chkAdmin"]))?("1"):("0") ));
			
			$qryInsert = mysql_query($strQuery);
			if (!$qryInsert) {
			    print_r('Error creating user: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		}
		
		mysql_free_result($qryExists);
		
	}
	
	$strQuery = "SELECT * FROM admin_user";
	
	$qryUsers = mysql_query($strQuery);
	if (!$qryUsers) {
	    print_r('Error selecting users: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	}
	
?>

<form method="post" name="frmEditUser" action="<?php $_SERVER["PHP_SELF"]?>">
	<table class="center collapse" width="800px">
		<tr class="buttons" id="buttons">
			<td align="left">
			</td>
			<td align="right">
				<input name="btnSave" id="btnSave" type="submit" value="save user" tabindex="1">
				<input name="btnNew" id="btnNew" type="button" value="new user" tabindex="2">
				<input name="btnCancel" id="btnCancel" type="submit" value="cancel" tabindex="3">
			</td>
		</tr>
		<tr class="title" id="title"><td colspan="2">
			<div></div>
		</td></tr>
		<tr class="editform" id="editform"><td colspan="2">
			<table class="clear collapse" width="100%">
				<input type="hidden" name="hidId" id="hidId" value="0">
				<tr>
					<td align="left"><label for="txtName" id="lblName">user name:</label></td>
					<td align="right"><input class="textbox" name="txtName" id="txtName" type="text" value="" tabindex="101"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtPwd" id="lblPwd">password:</label></td>
					<td align="right"><input class="textbox" name="txtPwd" id="txtPwd" type="password" value="" tabindex="102"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtAdmin" id="lblAdmin">is admin:</label></td>
					<td align="right"><input class="checkbox" name="chkAdmin" id="chkAdmin" type="checkbox" value="" tabindex="103"></td>
				</tr>
				<tr id="enabled">
					<td align="left"><label for="txtEnabled" id="lblEnabled">is enabled:</label></td>
					<td align="right"><input class="checkbox" name="chkEnabled" id="chkEnabled" type="checkbox" value="" tabindex="103"></td>
				</tr>
			</table>
		</td></tr>
		<tr class="data" id="data"><td colspan="2">
			<table class="clear collapse shade" id="users" width="100%">
				<thead>
					<tr>
						<th class="a_left"><div class="label">ID</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">NAME</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">PASSWORD</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">ADMIN</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">ENABLED</div><div class="sort_direction"></div></th>
						<th class="a_right"><div class="label">MODIFIED</div><div class="sort_direction"></div></th>
					</tr>
				</thead>
				<tbody>
					<?php while ($arrRow = mysql_fetch_assoc($qryUsers)) { ?>
						<tr class="hand">
							<td align="left" id="userid" title="<?php echo $arrRow['userid'] ?>" width="10%"><?php echo $arrRow['userid'] ?></td>
							<td align="left" id="username" title="<?php echo $arrRow['username'] ?>" width="20%"><?php echo $arrRow['username'] ?></td>
							<td align="left" id="password" title="<?php echo $arrRow['password'] ?>" width="25%"><?php preg_replace("/\S/", "*", $arrRow['password']) ?></td>
							<td align="left" id="useradmin" title="<?php echo ($arrRow['admin'])?("true"):("") ?>" width="15%"><?php echo ($arrRow['admin'])?("yes"):("no") ?></td>
							<td align="left" id="userenabled" title="<?php echo ($arrRow['enabled'])?("true"):("") ?>" width="15%"><?php echo ($arrRow['enabled'])?("yes"):("no") ?></td>
							<td align="right" width="15%"><?php echo substr($arrRow['modified'], 0, 10) ?></td>
						</tr>
					<?php } // end while($arrRow = mysql_fetch_assoc($qryUsers)) ?>
				</tbody>
			</table>
		</td></tr>
	</table>
</form>

<?php
	
	mysql_free_result($qryUsers);
	mysql_close($dbFmsAdmin);
	
?>


<?php include("../inc/php/footer.php"); ?>