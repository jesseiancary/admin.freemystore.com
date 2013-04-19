<?php include("../inc/php/header.php"); ?>

<!-- Try to move the following into tables.js -->
<link type="text/css" rel="stylesheet" href="../inc/css/jq_datatables.css">
<script type="text/javascript" src="../inc/js/jq/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../inc/js/jq/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="../inc/js/tables.js"></script>
<script type="text/javascript">

	$(function() {
		
		$("#btnNew").click(function() {
			$("#title div").html("New Export");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").hide();
		});
		
		$("#exports tbody tr").click(function() {
			$("#title div").html("Edit Export");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").show();
			$("#hidId").val( $(this).find("#exportid").attr("title") );
			$("#selCompany").val( $(this).find("#companyid").attr("title") );
			$("#selPartner").val( $(this).find("#partnerid").attr("title") );
			//$("#selCompany")[$(this).find("#companyid").attr("title")].attr("selected", "selected");
			//$("#selPartner")[$(this).find("#partnerid").attr("title")].attr("selected", "selected");
			$("#txtName").val( $(this).find("#name").attr("title") );
			$("#txtLogin").val( $(this).find("#login").attr("title") );
			$("#txtUrl").val( $(this).find("#url").attr("title") );
			$("#txtCustom1").val( $(this).find("#custom1").attr("title") );
			$("#txtCustom2").val( $(this).find("#custom2").attr("title") );
			$("#txtCustom3").val( $(this).find("#custom3").attr("title") );
			$("#txtCustom4").val( $(this).find("#custom4").attr("title") );
			$("#txtCustom5").val( $(this).find("#custom5").attr("title") );
			$("#txtPwd").val( $(this).find("#password").attr("title") );
			$("#chkEnabled").attr("checked", $(this).find("#exportenabled").attr("title"));
			
		});
		
	});
	
	$(document).ready(function() {
		
		$("#title div").html("Exports");
		$("#btnSave").hide();
		$("#btnNew").show();
		$("#btnCancel").hide();
		$("#editform").hide();
		$("#data").show();

		// hide table columns
		$("[name=login]").hide();
		$("[name=password]").hide();
		$("[name=url]").hide();
		$("[name=custom1]").hide();
		$("[name=custom2]").hide();
		$("[name=custom3]").hide();
		$("[name=custom4]").hide();
		$("[name=custom5]").hide();
		
		oTable = $("#exports").dataTable( {
			"sPaginationType": "scrolling"
		});
		
	});
	
</script>

<?php
	
	$dbFmsAdmin = mysql_connect('localhost', 'root', 'soochow');
	if (!$dbFmsAdmin) {
	    print_r('Could not connect to FreeMyStore database: ' . mysql_error());
	    die(include("../inc/php/footer.php"));
	} else {
		mysql_select_db('freemystore', $dbFmsAdmin);
	}
	
	if ($_POST['btnSave']) {
		
		$strQuery = sprintf("SELECT * FROM admin_companyExport WHERE exportid = %s",
			mysql_real_escape_string($_POST["hidId"]));
		
		$qryExists = mysql_query($strQuery);
		if (!$qryExists) {
		    print_r('Error selecting export: ' . mysql_error());
		    die(include("../inc/php/footer.php"));
		}
		
		if (mysql_fetch_row($qryExists)) {
			
			$strQuery = sprintf("UPDATE admin_companyExport SET companyid = %s, partnerid = %s, name = '%s', login = '%s', password = '%s', url = '%s', custom1 = '%s', custom2 = '%s', custom3 = '%s', custom4 = '%s', custom5 = '%s', enabled = %s WHERE exportid = %s",
				mysql_real_escape_string($_POST["selCompany"]),
				mysql_real_escape_string($_POST["selPartner"]),
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtLogin"]),
				mysql_real_escape_string($_POST["txtPwd"]),
				mysql_real_escape_string($_POST["txtUrl"]),
				mysql_real_escape_string($_POST["txtCustom1"]),
				mysql_real_escape_string($_POST["txtCustom2"]),
				mysql_real_escape_string($_POST["txtCustom3"]),
				mysql_real_escape_string($_POST["txtCustom4"]),
				mysql_real_escape_string($_POST["txtCustom5"]),
				mysql_real_escape_string( (isset($_POST["chkEnabled"]))?("1"):("0") ),
				mysql_real_escape_string($_POST["hidId"]));
			//echo $strQuery;
			$qryUpdate = mysql_query($strQuery);
			if (!$qryUpdate) {
			    print_r('Error updating export: ' . mysql_error());
			    die(include("../inc/php/footer.php"));
			}
			
		} else {
			
			$strQuery = sprintf("INSERT INTO admin_companyExport (companyid, partnerid, name, login, password, url, custom1, custom2, custom3, custom4, custom5) VALUES (%s, %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				mysql_real_escape_string($_POST["selCompany"]),
				mysql_real_escape_string($_POST["selPartner"]),
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtLogin"]),
				mysql_real_escape_string($_POST["txtPwd"]),
				mysql_real_escape_string($_POST["txtUrl"]),
				mysql_real_escape_string($_POST["txtCustom1"]),
				mysql_real_escape_string($_POST["txtCustom2"]),
				mysql_real_escape_string($_POST["txtCustom3"]),
				mysql_real_escape_string($_POST["txtCustom4"]),
				mysql_real_escape_string($_POST["txtCustom5"]));
			
			$qryInsert = mysql_query($strQuery);
			if (!$qryInsert) {
			    print_r('Error creating export: ' . mysql_error());
			    die(include("../inc/php/footer.php"));
			}
			
		}
		
		mysql_free_result($qryExists);
		
	}
	
	$strQuery = "SELECT * FROM admin_companyExport";
	$qryExports = mysql_query($strQuery);
	if (!$qryExports) {
	    print_r('Error selecting exports: ' . mysql_error());
	    die(include("../inc/php/footer.php"));
	}
	
	$strQuery = "SELECT * FROM admin_company";
	$qryCompanies = mysql_query($strQuery);
	if (!$qryCompanies) {
	    print_r('Error selecting companies: ' . mysql_error());
	    die(include("../inc/php/footer.php"));
	}
	
	$strQuery = "SELECT * FROM admin_partner";
	$qryPartners = mysql_query($strQuery);
	if (!$qryPartners) {
	    print_r('Error selecting parthers: ' . mysql_error());
	    die(include("../inc/php/footer.php"));
	}
	
?>

<form method="post" name="frmEditExport" action="<?php $_SERVER["PHP_SELF"]?>">
	<table class="center collapse" width="800px">
		<tr class="buttons" id="buttons">
			<td align="left">
			</td>
			<td align="right">
				<input name="btnSave" id="btnSave" type="submit" value="save export" tabindex="1">
				<input name="btnNew" id="btnNew" type="button" value="new export" tabindex="2">
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
					<td align="left"><label for="selCompany" id="lblCompany">company:</label></td>
					<td align="right">
						<select class="selectbox" name="selCompany" id="selCompany" type="select" value="" tabindex="101">
							<option value="0">select company</option>
							<?php while ($arrRow = mysql_fetch_assoc($qryCompanies)) { ?>
								<option value="<?php echo $arrRow['companyid'] ?>"><?php echo $arrRow['companyname'] ?></option>
							<?php } // end while($arrRow = mysql_fetch_assoc($qryExports)) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="left"><label for="selPartner" id="lblPartner">partner:</label></td>
					<td align="right">
						<select class="selectbox" name="selPartner" id="selPartner" type="select" value="" tabindex="102">
							<option value="0">select partner</option>
							<?php while ($arrRow = mysql_fetch_assoc($qryPartners)) { ?>
								<option value="<?php echo $arrRow['partnerid'] ?>"><?php echo $arrRow['partnername'] ?></option>
							<?php } // end while($arrRow = mysql_fetch_assoc($qryExports)) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="left"><label for="txtName" id="lblName">name:</label></td>
					<td align="right"><input class="textbox" name="txtName" id="txtName" type="text" value="" maxlength="50" tabindex="103"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtLogin" id="lblLogin">login:</label></td>
					<td align="right"><input class="textbox" name="txtLogin" id="txtLogin" type="text" value="" maxlength="20" tabindex="104"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtPwd" id="lblPwd">password:</label></td>
					<td align="right"><input class="textbox" name="txtPwd" id="txtPwd" type="text" value="" maxlength="20" tabindex="105"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtUrl" id="lblUrl">url:</label></td>
					<td align="right"><input class="textbox" name="txtUrl" id="txtUrl" type="text" value="" maxlength="100" tabindex="106"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCustom1" id="lblPwd">custom 1:</label></td>
					<td align="right"><input class="textbox" name="txtCustom1" id="txtCustom1" type="text" value="" maxlength="20" tabindex="107"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCustom2" id="lblPwd">custom 2:</label></td>
					<td align="right"><input class="textbox" name="txtCustom2" id="txtCustom2" type="text" value="" maxlength="20" tabindex="108"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCustom3" id="lblPwd">custom 3:</label></td>
					<td align="right"><input class="textbox" name="txtCustom3" id="txtCustom3" type="text" value="" maxlength="20" tabindex="109"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCustom4" id="lblPwd">custom 4:</label></td>
					<td align="right"><input class="textbox" name="txtCustom4" id="txtCustom4" type="text" value="" maxlength="50" tabindex="110"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCustom5" id="lblPwd">custom 5:</label></td>
					<td align="right"><input class="textbox" name="txtCustom5" id="txtCustom5" type="text" value="" maxlength="50" tabindex="111"></td>
				</tr>
				<tr id="enabled">
					<td align="left"><label for="chkEnabled" id="lblEnabled">is enabled:</label></td>
					<td align="right"><input class="checkbox" name="chkEnabled" id="chkEnabled" type="checkbox" value="" tabindex="112"></td>
				</tr>
			</table>
		</td></tr>
		<tr class="data" id="data"><td colspan="2">
			<table class="clear collapse shade" id="exports" width="100%">
				<thead>
					<tr>
						<th class="a_left" name="exportid"><div class="label">ID</div><div class="sort_direction"></div></th>
						<th class="a_left" name="companyid"><div class="label">COMPANY</div><div class="sort_direction"></div></th>
						<th class="a_left" name="partnerid"><div class="label">PARTNER</div><div class="sort_direction"></div></th>
						<th class="a_left" name="name"><div class="label">NAME</div><div class="sort_direction"></div></th>
						<th class="a_left" name="login"><div class="label">LOGIN</div><div class="sort_direction"></div></th>
						<th class="a_left" name="password"><div class="label">PASSWORD</div><div class="sort_direction"></div></th>
						<th class="a_left" name="url"><div class="label">URL</div><div class="sort_direction"></div></th>
						<th class="a_left" name="custom1"><div class="label">CUSTOM 1</div><div class="sort_direction"></div></th>
						<th class="a_left" name="custom2"><div class="label">CUSTOM 2</div><div class="sort_direction"></div></th>
						<th class="a_left" name="custom3"><div class="label">CUSTOM 3</div><div class="sort_direction"></div></th>
						<th class="a_left" name="custom4"><div class="label">CUSTOM 4</div><div class="sort_direction"></div></th>
						<th class="a_left" name="custom5"><div class="label">CUSTOM 5</div><div class="sort_direction"></div></th>
						<th class="a_right" name="exportenabled"><div class="label">ON</div><div class="sort_direction"></div></th>
						<th class="a_right" name="modified" width="15%"><div class="label">MODIFIED</div><div class="sort_direction"></div></th>
					</tr>
				</thead>
				<tbody>
					<?php while ($arrRow = mysql_fetch_assoc($qryExports)) { ?>
						<?php
							mysql_data_seek ($qryCompanies, 0);
							while ($arrRowCompany = mysql_fetch_assoc($qryCompanies)) {
								if ($arrRowCompany['companyid'] == $arrRow['companyid']) {
									$companyname = $arrRowCompany['companyname'];
								}
							}
							mysql_data_seek ($qryPartners, 0);
							while ($arrRowPartner = mysql_fetch_assoc($qryPartners)) {
								if ($arrRowPartner['partnerid'] == $arrRow['partnerid']) {
									$partnername = $arrRowPartner['partnername'];
								}
							}
						?>
						<tr class="hand">
							<td align="left" id="exportid" name="exportid" title="<?php echo $arrRow['exportid'] ?>"><?php echo $arrRow['exportid'] ?></td>
							<td align="left" id="companyid" name="companyid" title="<?php echo $arrRow['companyid'] ?>"><?php echo $companyname ?></td>
							<td align="left" id="partnerid" name="partnerid" title="<?php echo $arrRow['partnerid'] ?>"><?php echo $partnername ?></td>
							<td align="left" id="name" name="name" title="<?php echo $arrRow['name'] ?>"><?php echo $arrRow['name'] ?></td>
							<td align="left" id="login" name="login" title="<?php echo $arrRow['login'] ?>"><?php echo $arrRow['login'] ?></td>
							<td align="left" id="password" name="password" title="<?php echo $arrRow['password'] ?>"><?php echo $arrRow['password'] ?></td>
							<td align="left" id="url" name="url" title="<?php echo $arrRow['url'] ?>"><?php echo $arrRow['url'] ?></td>
							<td align="left" id="custom1" name="custom1" title="<?php echo $arrRow['custom1'] ?>"><?php echo $arrRow['custom1'] ?></td>
							<td align="left" id="custom2" name="custom2" title="<?php echo $arrRow['custom2'] ?>"><?php echo $arrRow['custom2'] ?></td>
							<td align="left" id="custom3" name="custom3" title="<?php echo $arrRow['custom3'] ?>"><?php echo $arrRow['custom3'] ?></td>
							<td align="left" id="custom4" name="custom4" title="<?php echo $arrRow['custom4'] ?>"><?php echo $arrRow['custom4'] ?></td>
							<td align="left" id="custom5" name="custom5" title="<?php echo $arrRow['custom5'] ?>"><?php echo $arrRow['custom5'] ?></td>
							<td align="right" id="exportenabled" name="exportenabled" title="<?php echo ($arrRow['enabled'])?("true"):("") ?>"><?php echo ($arrRow['enabled'])?("Yes"):("No") ?></td>
							<td align="right" id="modified" name="modified" width="15%"><?php echo substr($arrRow['modified'], 0, 10) ?></td>
						</tr>
					<?php } // end while($arrRow = mysql_fetch_assoc($qryExports)) ?>
				</tbody>
			</table>
		</td></tr>
	</table>
</form>

<?php
	
	mysql_free_result($qryExports);
	mysql_free_result($qryCompanies);
	mysql_free_result($qryPartners);
	mysql_close($dbFmsAdmin);
	
?>

<?php include("../inc/php/footer.php"); ?>