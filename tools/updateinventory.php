<?php include("../inc/php/header.php"); ?>

<?php
	
	include("../inc/obj/FreeMyStore.php");
	$fms = new FreeMyStore();
	
	$preview = max( (int)$_GET["preview"], $_POST["btnPreview"] ? (int)$_POST["selExport"] : 0, 0 );
	$update = max( (int)$_GET["update"], $_POST["btnUpdate"] ? (int)$_POST["selExport"] : 0, 0 );
	$exportid = max($preview, $update);
	
	if ( $preview > 0 || $update > 0 ) {
		$export = $fms->getExports($exportid);
		include("../inc/obj/BlastRamp.php");
		$blastramp = new BlastRamp( $export["name"], $export["login"], $export["password"], $export["custom1"], $export["storecode"] );
	}
	
	if ($update > 0) {
		$csvInventory = $blastramp->getCsvInventory();
		
		$fms->updateInventory($update, $csvInventory);
		
		//echo "<pre>" . $csvInventory . "</pre>";
		//die(include("../inc/php/footer.php"));
		
		//$xmlInventory = $blastramp->getXMLInventory();
		//$response = @http_post_data("http://admin.freemystore.com/inc/api/xml.php", $xmlInventory);
	}
	
?>

<?php if ($preview > 0) {
	
	$arrInventory = $blastramp->getArrInventory();
	$strCompanyName = $blastramp->getCompanyName(); ?>
	
	<!-- Try to move the following into tables.js -->
	<link type="text/css" rel="stylesheet" href="../inc/css/jq_datatables.css">
	<script type="text/javascript" src="../inc/js/jq/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="../inc/js/jq/jquery.dataTables.min.js"></script>
	
	<script type="text/javascript" src="../inc/js/tables.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			/*
			$("#inventory tr").click( function() {
				if ($(this).hasClass("row_selected"))
					$(this).removeClass("row_selected");
				else
					$(this).addClass("row_selected");
			});
			*/
			/* Init the table */
			oTable = $("#inventory").dataTable( {
				"sPaginationType": "scrolling"
			});
		});
	</script>
	
<?php } ?>

<table class="center" width="800px">
	
	<form method="post" name="frmSelectExport" action="<?php $_SERVER["PHP_SELF"]?>">
		<tr class="buttons">
			<td align="left">
				<select class="select" name="selExport" tabindex="1">
					<option value="" selected>select company</option>
					<?php $qryExports = $fms->getExports(); ?>
					<?php while ($arrRow = mysql_fetch_assoc($qryExports)) { ?>
						<option value="<?php echo $arrRow['exportid'] ?>" <?php echo ( ($arrRow['exportid'] == $preview) ? 'selected="selected"' : '' ); ?>><?php echo $arrRow['name'] ?></option>
					<?php } // end while ($arrRow = mysql_fetch_assoc($qryCompanies)) ?>
				</select>
			</td>
			<td align="right">
				<input name="btnPreview" type="submit" value="preview" tabindex="2">
				<input name="btnUpdate" type="submit" value="update" tabindex="3" <?php echo ( ($preview == 0) ? 'disabled="disabled"' : '' ); ?>>
			</td>
		</tr>
	</form>
	
	<?php if ($preview > 0) { ?>
		<tr class="title"><td colspan="2">
			<?php echo $strCompanyName ?>
		</td></tr>
		<tr class="data"><td colspan="2">
			<table class="clear" id="inventory" width="100%">
				<thead>
					<tr>
						<th class="a_left"><div class="label">SKU</div><div class="sort_direction"></div></th>
						<th class="a_left"><div class="label">Description</div><div class="sort_direction"></div></th>
						<th class="a_right"><div class="label">QTY</div><div class="sort_direction"></div></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($arrInventory as $keyRow => $arrKeyedRow) { ?>
						<tr>
							<td align="left"><?php echo $arrKeyedRow["SKU"] ?></td>
							<td align="left"><?php echo $arrKeyedRow["Description"] ?></td>
							<td align="right"><?php echo $arrKeyedRow["Quantity"] ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</td></tr>
	<?php } ?>
	
</table>

<?php include("../inc/php/footer.php"); ?>