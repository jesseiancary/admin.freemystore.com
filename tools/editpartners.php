<?php include("../inc/php/header.php"); ?>

<!-- Try to move the following into tables.js -->
<link type="text/css" rel="stylesheet" href="../inc/css/jq_datatables.css">
<script type="text/javascript" src="../inc/js/jq/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../inc/js/jq/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="../inc/js/tables.js"></script>
<script type="text/javascript">

	$(function() {
		
		$("#btnNew").click(function() {
			$("#title div").html("New Partner");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").hide();
		});
		
		$("#partners tbody tr").click(function() {
			$("#title div").html("Edit Partner");
			$("#btnSave").show();
			$("#btnNew").hide();
			$("#btnCancel").show();
			$("#editform").show();
			$("#data").hide();
			$("#enabled").show();
			$("#hidId").val( $(this).find("#partnerid").attr("title") );
			$("#txtName").val( $(this).find("#partnername").attr("title") );
			$("#txtUrl").val( $(this).find("#apiurl").attr("title") );
			$("#chkEnabled").attr("checked", $(this).find("#partnerenabled").attr("title"));
			
		});
		
	});
	
	$(document).ready(function() {
		
		$("#title div").html("Partners");
		$("#btnSave").hide();
		$("#btnNew").show();
		$("#btnCancel").hide();
		$("#editform").hide();
		$("#data").show();
		
		oTable = $("#partners").dataTable( {
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
		
		$strQuery = sprintf("SELECT * FROM admin_partner WHERE partnerid = %s",
			mysql_real_escape_string($_POST["hidId"]));
		
		$qryExists = mysql_query($strQuery);
		if (!$qryExists) {
		    print_r('Error selecting partner: ' . mysql_error());
		    die(include("inc/php/footer.php"));
		}
		
		if (mysql_fetch_row($qryExists)) {
			
			$strQuery = sprintf("UPDATE admin_partner SET partnername = '%s', apiurl = '%s', enabled = %s WHERE partnerid = %s",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtUrl"]),
				mysql_real_escape_string( (isset($_POST["chkEnabled"]))?("1"):("0") ),
				mysql_real_escape_string($_POST["hidId"]));
			
			$qryUpdate = mysql_query($strQuery);
			if (!$qryUpdate) {
			    print_r('Error updating partner: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		} else {
			
			$strQuery = sprintf("INSERT INTO admin_partner (partnername, apiurl) VALUES ('%s', '%s')",
				mysql_real_escape_string($_POST["txtName"]),
				mysql_real_escape_string($_POST["txtUrl"]));
			
			$qryInsert = mysql_query($strQuery);
			if (!$qryInsert) {
			    print_r('Error creating partner: ' . mysql_error());
			    die(include("inc/php/footer.php"));
			}
			
		}
		
		mysql_free_result($qryExists);
		
	}
	
	$strQuery = "SELECT * FROM admin_partner";
	
	$qryPartners = mysql_query($strQuery);
	if (!$qryPartners) {
	    print_r('Error selecting parthers: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	}
	
?>

<form method="post" name="frmEditPartner" action="<?php $_SERVER["PHP_SELF"]?>">
	<table class="center collapse" width="800px">
		<tr class="buttons" id="buttons">
			<td align="left">
			</td>
			<td align="right">
				<input name="btnSave" id="btnSave" type="submit" value="save partner" tabindex="1">
				<input name="btnNew" id="btnNew" type="button" value="new partner" tabindex="2">
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
					<td align="left"><label for="txtName" id="lblName">partner name:</label></td>
					<td align="right"><input class="textbox" name="txtName" id="txtName" type="text" value="" tabindex="101"></td>
				</tr>
				<tr>
					<td align="left"><label for="txtUrl" id="lblUrl">api url:</label></td>
					<td align="right"><input class="textbox" name="txtUrl" id="txtUrl" type="text" value="" tabindex="102"></td>
				</tr>
				<tr id="enabled">
					<td align="left"><label for="txtEnabled" id="lblEnabled">is enabled:</label></td>
					<td align="right"><input class="checkbox" name="chkEnabled" id="chkEnabled" type="checkbox" value="" tabindex="103"></td>
				</tr>
			</table>
		</td></tr>
		<tr class="data" id="data"><td colspan="2">
			<table class="clear collapse shade" id="partners" width="100%">
				<thead>
					<tr>
						<th class="a_left"><div class="label">ID</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">NAME</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">API URL</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">ENABLED</div><div class="sort_direction"></div></th>
						<th class="a_right"><div class="label">MODIFIED</div><div class="sort_direction"></div></th>
					</tr>
				</thead>
				<tbody>
					<?php while ($arrRow = mysql_fetch_assoc($qryPartners)) { ?>
						<tr class="hand">
							<td align="left" id="partnerid" title="<?php echo $arrRow['partnerid'] ?>" width="10%"><?php echo $arrRow['partnerid'] ?></td>
							<td align="left" id="partnername" title="<?php echo $arrRow['partnername'] ?>" width="15%"><?php echo $arrRow['partnername'] ?></td>
							<td align="left" id="apiurl" title="<?php echo $arrRow['apiurl'] ?>" width="45%"><?php echo $arrRow['apiurl'] ?></td>
							<td align="left" id="partnerenabled" title="<?php echo ($arrRow['enabled'])?("true"):("") ?>" width="15%"><?php echo ($arrRow['enabled'])?("yes"):("no") ?></td>
							<td align="right" width="15%"><?php echo substr($arrRow['modified'], 0, 10) ?></td>
						</tr>
					<?php } // end while($arrRow = mysql_fetch_assoc($qryPartners)) ?>
				</tbody>
			</table>
		</td></tr>
	</table>
</form>

<?php
	
	mysql_free_result($qryPartners);
	mysql_close($dbFmsAdmin);
	
?>


<?php include("../inc/php/footer.php"); ?>