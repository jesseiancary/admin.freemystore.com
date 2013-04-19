<?php include("../inc/php/header.php"); ?>

<!-- Try to move the following into tables.js -->
<link type="text/css" rel="stylesheet" href="../inc/css/jq_datatables.css">
<script type="text/javascript" src="../inc/js/jq/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../inc/js/jq/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="../inc/js/tables.js"></script>
<script type="text/javascript">

	$(function() {
		
		$("#btnNew").click(function() {
			$("#title div").html("New Company");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").hide();
		});
		
		$("#companies tbody tr").click(function() {
			$("#title div").html("Edit Company");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").show();
			$("#hidId").val( $(this).find("#companyid").attr("title") );
			$("#txtName").val( $(this).find("#companyname").attr("title") );
			$("#txtCompanyCode").val( $(this).find("#companycode").attr("title") );
			$("#txtStoreCode").val( $(this).find("#storecode").attr("title") );
			$("#txtMageDir").val( $(this).find("#magedir").attr("title") );
			$("#txtDbName").val( $(this).find("#dbname").attr("title") );
			$("#txtDbUser").val( $(this).find("#dbuser").attr("title") );
			$("#txtDbPassword").val( $(this).find("#dbpassword").attr("title") );
			$("#txtUrl").val( $(this).find("#apiurl").attr("title") );
			$("#txtUser").val( $(this).find("#apiuser").attr("title") );
			$("#txtKey").val( $(this).find("#apikey").attr("title") );
			$("#chkEnabled").attr("checked", $(this).find("#companyenabled").attr("title"));
			
		});
		
	});
	
	$(document).ready(function() {
		
		$("#title div").html("Companies");
		$("#btnSave").hide();
		$("#btnNew").show();
		$("#btnCancel").hide();
		$("#editform").hide();
		$("#data").show();

		// hide table columns
		$("[name=storecode], [name=magedir], [name=dbname], [name=dbuser], [name=dbpassword], [name=apiurl], [name=apiuser], [name=apikey]").hide();

		oTable = $("#companies").dataTable( {
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
		
		$strQuery = sprintf("SELECT * FROM admin_company WHERE companyid = %s",
			mysql_real_escape_string($_POST["hidId"]));
		
		$qryExists = mysql_query($strQuery);
		if (!$qryExists) {
		    print_r('Error selecting companie: ' . mysql_error());
		    die(include("inc/php/footer.php"));
		}
		
		if (mysql_fetch_row($qryExists)) {
			
			$strQuery = sprintf("UPDATE admin_company SET companyname = '%s', companycode = '%s', storecode = '%s', magedir = '%s', dbname = '%s', dbuser = '%s', dbpassword = '%s', apiurl = '%s', apiuser = '%s', apikey = '%s', enabled = %s WHERE companyid = %s",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtCompanyCode"]),
				mysql_real_escape_string($_POST["txtStoreCode"]),
				mysql_real_escape_string($_POST["txtMageDir"]),
				mysql_real_escape_string($_POST["txtDbName"]),
				mysql_real_escape_string($_POST["txtDbUser"]),
				mysql_real_escape_string($_POST["txtDbPassword"]),
				mysql_real_escape_string($_POST["txtUrl"]),
				mysql_real_escape_string($_POST["txtUser"]),
				mysql_real_escape_string($_POST["txtKey"]),
				mysql_real_escape_string( (isset($_POST["chkEnabled"]))?("1"):("0") ),
				mysql_real_escape_string($_POST["hidId"]));
			
			$qryUpdate = mysql_query($strQuery);
			if (!$qryUpdate) {
			    print_r('Error updating company: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		} else {
			
			$strQuery = sprintf("INSERT INTO admin_company (companyname, companycode, storecode, magedir, dbname, dbuser, dbpassword, apiurl, apiuser, apikey) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtCompanyCode"]),
				mysql_real_escape_string($_POST["txtMageDir"]),
				mysql_real_escape_string($_POST["txtStoreCode"]),
				mysql_real_escape_string($_POST["txtDbName"]),
				mysql_real_escape_string($_POST["txtDbUser"]),
				mysql_real_escape_string($_POST["txtDbPassword"]),
				mysql_real_escape_string($_POST["txtUrl"]),
				mysql_real_escape_string($_POST["txtUser"]),
				mysql_real_escape_string($_POST["txtKey"]));
			
			$qryInsert = mysql_query($strQuery);
			if (!$qryInsert) {
			    print_r('Error creating company: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		}
		
		mysql_free_result($qryExists);
		
	}
	
	$strQuery = "SELECT * FROM admin_company";
	
	$qryCompanies = mysql_query($strQuery);
	if (!$qryCompanies) {
	    print_r('Error selecting companies: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	}
	
?>

<form method="post" name="frmEditCompany" action="<?php $_SERVER["PHP_SELF"]?>">
	<table class="center collapse" width="800px">
		<tr class="buttons" id="buttons">
			<td align="left">
			</td>
			<td align="right">
				<input name="btnSave" id="btnSave" type="submit" value="save company" tabindex="1">
				<input name="btnNew" id="btnNew" type="button" value="new company" tabindex="2">
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
					<td align="left"><label for="txtName" id="lblName">company name:</label></td>
					<td align="right"><input class="textbox" name="txtName" id="txtName" type="text" value="" tabindex="101"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtCompanyCode" id="lblCode">company code:</label></td>
					<td align="right"><input class="textbox" name="txtCompanyCode" id="txtCompanyCode" type="text" value="" tabindex="102"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtStoreCode" id="lblStoreCode">store code:</label></td>
					<td align="right"><input class="textbox" name="txtStoreCode" id="txtStoreCode" type="text" value="" tabindex="103"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtMageDir" id="lblMageDir">magento directory:</label></td>
					<td align="right"><input class="textbox" name="txtMageDir" id="txtMageDir" type="text" value="" tabindex="104"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtDbName" id="lblDbName">database name:</label></td>
					<td align="right"><input class="textbox" name="txtDbName" id="txtDbName" type="text" value="" tabindex="105"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtDbUser" id="lblDbUser">database user:</label></td>
					<td align="right"><input class="textbox" name="txtDbUser" id="txtDbUser" type="text" value="" tabindex="106"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtDbPassword" id="lblDbPassword">database password:</label></td>
					<td align="right"><input class="textbox" name="txtDbPassword" id="txtDbPassword" type="password" value="" tabindex="107"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtUrl" id="lblUrl">api soap client url:</label></td>
					<td align="right"><input class="textbox" name="txtUrl" id="txtUrl" type="text" value="" tabindex="108"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtUser" id="lblUser">api user:</label></td>
					<td align="right"><input class="textbox" name="txtUser" id="txtUser" type="text" value="" tabindex="109"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtKey" id="lblKey">api key:</label></td>
					<td align="right"><input class="textbox" name="txtKey" id="txtKey" type="password" value="" tabindex="110"></td>
				</tr>
				<tr id="enabled">
					<td align="left"><label for="txtEnabled" id="lblEnabled">is enabled:</label></td>
					<td align="right"><input class="checkbox" name="chkEnabled" id="chkEnabled" type="checkbox" value="" tabindex="111"></td>
				</tr>
			</table>
		</td></tr>
		<tr class="data" id="data"><td colspan="2">
			<table class="clear collapse shade" id="companies" width="100%">
				<thead>
					<tr>
						<th class="a_left" name="companyid"><div class="label">ID</div><div class="sort_direction"></div></th>
						<th class="a_left" name="companyname"><div class="label">NAME</div><div class="sort_direction"></div></th>
						<th class="a_left" name="companycode"><div class="label">COMPANY CODE</div><div class="sort_direction"></div></th>
						<th class="a_left" name="storecode"><div class="label">STORE CODE</div><div class="sort_direction"></div></th>
						<th class="a_left" name="magedir"><div class="label">MAGENTO DIRECTORY</div><div class="sort_direction"></div></th>
						<th class="a_left" name="dbname"><div class="label">DB NAME</div><div class="sort_direction"></div></th>
						<th class="a_left" name="dbuser"><div class="label">DB USER</div><div class="sort_direction"></div></th>
						<th class="a_left" name="dbpassword"><div class="label">DB PASSWORD</div><div class="sort_direction"></div></th>
						<th class="a_left" name="apiurl"><div class="label">API URL</div><div class="sort_direction"></div></th>
						<th class="a_left" name="apiuser"><div class="label">API USER</div><div class="sort_direction"></div></th>
						<th class="a_left" name="apikey"><div class="label">API KEY</div><div class="sort_direction"></div></th>
						<th class="a_left" name="companyenabled"><div class="label">ENABLED</div><div class="sort_direction"></div></th>
						<th class="a_right" name="modified"><div class="label">MODIFIED</div><div class="sort_direction"></div></th>
					</tr>
				</thead>
				<tbody>
					<?php while ($arrRow = mysql_fetch_assoc($qryCompanies)) { ?>
						<tr class="hand">
							<td align="left" id="companyid" name="companyid" title="<?php echo $arrRow['companyid'] ?>" width="10%"><?php echo $arrRow['companyid'] ?></td>
							<td align="left" id="companyname" name="companyname" title="<?php echo $arrRow['companyname'] ?>" width="50%"><?php echo $arrRow['companyname'] ?></td>
							<td align="left" id="companycode" name="companycode" title="<?php echo $arrRow['companycode'] ?>" width="10%"><?php echo $arrRow['companycode'] ?></td>
							<td align="left" id="storecode" name="storecode" title="<?php echo $arrRow['storecode'] ?>" width="10%"><?php echo $arrRow['storecode'] ?></td>
							<td align="left" id="magedir" name="magedir" title="<?php echo $arrRow['magedir'] ?>" width="10%"><?php echo $arrRow['magedir'] ?></td>
							<td align="left" id="dbname" name="dbname" title="<?php echo $arrRow['dbname'] ?>"><?php echo $arrRow['dbname'] ?></td>
							<td align="left" id="dbuser" name="dbuser" title="<?php echo $arrRow['dbuser'] ?>"><?php echo $arrRow['dbuser'] ?></td>
							<td align="left" id="dbpassword" name="dbpassword" title="<?php echo $arrRow['dbpassword'] ?>"><?php echo $arrRow['dbpassword'] ?></td>
							<td align="left" id="apiurl" name="apiurl" title="<?php echo $arrRow['apiurl'] ?>"><?php echo $arrRow['apiurl'] ?></td>
							<td align="left" id="apiuser" name="apiuser" title="<?php echo $arrRow['apiuser'] ?>"><?php echo $arrRow['apiuser'] ?></td>
							<td align="left" id="apikey" name="apikey" title="<?php echo $arrRow['apikey'] ?>"><?php echo $arrRow['apikey'] ?></td>
							<td align="left" id="companyenabled" name="companyenabled" title="<?php echo ($arrRow['enabled'])?("true"):("") ?>" width="15%"><?php echo ($arrRow['enabled'])?("yes"):("no") ?></td>
							<td align="right" id="modified" name="modified" width="15%"><?php echo substr($arrRow['modified'], 0, 10) ?></td>
						</tr>
					<?php } // end while($arrRow = mysql_fetch_assoc($qryCompanies)) ?>
				</tbody>
			</table>
		</td></tr>
	</table>
</form>

<?php
	
	mysql_free_result($qryCompanies);
	mysql_close($dbFmsAdmin);
	
?>


<?php include("../inc/php/footer.php"); ?>