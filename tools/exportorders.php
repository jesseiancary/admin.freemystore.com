<?php include("../inc/php/header.php"); ?>

<?php
	
	function formatdate($strDate) {
		return substr($strDate, 5, 2) . "/" . substr($strDate, 8, 2) . "/" . substr($strDate, 0, 4);
	}
	
	function getStateAbbr($state) {
		$states = array(
			'AL'=>'ALABAMA',
			'AK'=>'ALASKA',
			'AS'=>'AMERICAN SAMOA',
			'AZ'=>'ARIZONA',
			'AR'=>'ARKANSAS',
			'CA'=>'CALIFORNIA',
			'CO'=>'COLORADO',
			'CT'=>'CONNECTICUT',
			'DE'=>'DELAWARE',
			'DC'=>'DISTRICT OF COLUMBIA',
			'FM'=>'FEDERATED STATES OF MICRONESIA',
			'FL'=>'FLORIDA',
			'GA'=>'GEORGIA',
			'GU'=>'GUAM GU',
			'HI'=>'HAWAII',
			'ID'=>'IDAHO',
			'IL'=>'ILLINOIS',
			'IN'=>'INDIANA',
			'IA'=>'IOWA',
			'KS'=>'KANSAS',
			'KY'=>'KENTUCKY',
			'LA'=>'LOUISIANA',
			'ME'=>'MAINE',
			'MH'=>'MARSHALL ISLANDS',
			'MD'=>'MARYLAND',
			'MA'=>'MASSACHUSETTS',
			'MI'=>'MICHIGAN',
			'MN'=>'MINNESOTA',
			'MS'=>'MISSISSIPPI',
			'MO'=>'MISSOURI',
			'MT'=>'MONTANA',
			'NE'=>'NEBRASKA',
			'NV'=>'NEVADA',
			'NH'=>'NEW HAMPSHIRE',
			'NJ'=>'NEW JERSEY',
			'NM'=>'NEW MEXICO',
			'NY'=>'NEW YORK',
			'NC'=>'NORTH CAROLINA',
			'ND'=>'NORTH DAKOTA',
			'MP'=>'NORTHERN MARIANA ISLANDS',
			'OH'=>'OHIO',
			'OK'=>'OKLAHOMA',
			'OR'=>'OREGON',
			'PW'=>'PALAU',
			'PA'=>'PENNSYLVANIA',
			'PR'=>'PUERTO RICO',
			'RI'=>'RHODE ISLAND',
			'SC'=>'SOUTH CAROLINA',
			'SD'=>'SOUTH DAKOTA',
			'TN'=>'TENNESSEE',
			'TX'=>'TEXAS',
			'UT'=>'UTAH',
			'VT'=>'VERMONT',
			'VI'=>'VIRGIN ISLANDS',
			'VA'=>'VIRGINIA',
			'WA'=>'WASHINGTON',
			'WV'=>'WEST VIRGINIA',
			'WI'=>'WISCONSIN',
			'WY'=>'WYOMING',
			'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
			'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
			'AP'=>'ARMED FORCES PACIFIC'
		);
		$abbr = array_keys($states, strtoupper($state)) or $abbr = array(0 => $state);
		return( $abbr[0] );
	}
	
	$intExportId = $_POST["selExport"] ? $_POST["selExport"] : "";
	
	$preview = ( $_POST["btnPreview"] && strlen($intExportId) ) ? true : false;
	$update = ( $_POST["btnUpdate"] && strlen($intExportId) ) ? true : false;
	$log = "";
	
	if ( $_GET["id"] ) {
		
		$intExportId = $_GET["id"];
		$preview = $update = true;
		
	}
	
	$dbFmsAdmin = mysql_connect('localhost', 'root', 'soochow');
	if (!$dbFmsAdmin) {
	    print_r('Could not connect to FreeMyStore database: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	} else {
		mysql_select_db('freemystore', $dbFmsAdmin);
	}
	
	$strQuery = "SELECT * FROM admin_companyExport e INNER JOIN admin_companyMage cm ON cm.companyid = e.companyid INNER JOIN admin_company c ON c.companyId = e.companyId WHERE e.enabled = 1";
	$qryExports = mysql_query($strQuery);
	if (!$qryExports) {
	    print_r('Error selecting exports: ' . mysql_error());
	    die(include("inc/php/footer.php"));
	}
	
	if ( $preview == true ) {
		
		$intMageId = -1;
		$strExportName = "";
		
		while ($arrRow = mysql_fetch_assoc($qryExports)) {
			if ($arrRow["exportid"] == $intExportId) {
				//$intExportId = $arrRow["exportid"];
				$intMageId = $arrRow["mageid"];
				$strExportName = $arrRow["name"];
				$apiurl = $arrRow["apiurl"];
				$apiuser = $arrRow["apiuser"];
				$apikey = $arrRow["apikey"];
			}
		}
		
		$fmsProxy = new SoapClient($apiurl);
		$sessionId = $fmsProxy->login($apiuser, $apikey);
		
		$filters = array (
	        "complex_filter" => array (
	            array (
	                "key" => "status",
	                "value" => array (
	                    "key" => "eq",
	                    "value" => "processing"
	                )
	            )/*,
	            array (
	                "key" => "store_id",
	                "value" => array (
	                    "key" => "eq",
	                    "value" => $intMageId
	                )
	            )*/
	        )
	    );
		
		$_SESSION["arrOrders"] = $fmsProxy->salesOrderList($sessionId, $filters);
		//print_r($_SESSION["arrOrders"]);
		//die(include("../inc/php/footer.php"));
		
	}
	
	if ( $update == true ) {
		
		mysql_data_seek( $qryExports, 0 );
		while ($arrRow = mysql_fetch_assoc($qryExports)) {
			if ($arrRow["exportid"] == $intExportId) {
				$postUrl = $arrRow["url"];
				$vendorCode = $arrRow["login"];
				$vendorAccessKey = $arrRow["password"];
				//$orderId = "TBD";
				$companyCode = $arrRow["companycode"];
				$apiurl = $arrRow["apiurl"];
				$apiuser = $arrRow["apiuser"];
				$apikey = $arrRow["apikey"];
				$warehouseId = $arrRow["custom1"];
				$currencyCode = $arrRow["custom2"];
				$userId = $arrRow["custom3"];
			}
		}
		
		$fmsProxy = new SoapClient($apiurl);
		$sessionId = $fmsProxy->login($apiuser, $apikey);
		
		foreach ($_SESSION["arrOrders"] as $order) {
			
			$xml = "";
			$xml = $xml . '<OrderUpload xmlns="http://chrome52/webservices/">' . "\n";
			$xml = $xml . '    <VendorCode>' . $vendorCode . '</VendorCode>' . "\n";
			$xml = $xml . '    <VendorAccessKey>' . $vendorAccessKey . '</VendorAccessKey>' . "\n";
			$xml = $xml . '    <Batch>' . "\n";
			
			$objOrderInfo = $fmsProxy->salesOrderInfo($sessionId, $order->increment_id);
			
			//print_r($objOrderInfo);
			//die(include("../inc/php/footer.php"));
			
			$tax_rate = round(($objOrderInfo->grand_total / ($objOrderInfo->grand_total - $objOrderInfo->tax_amount)) - 1, 4);
			
			$xml = $xml . '        <Order>' . "\n";
			//$xml = $xml . '            <orderid>' . $orderId . '</orderid>' . "\n";
			$xml = $xml . '            <orderid>' . substr($companyCode . $objOrderInfo->increment_id, 0, 60) . '</orderid>' . "\n";
			$xml = $xml . '            <po>' . substr($companyCode . $objOrderInfo->increment_id, 0, 60) . '</po>' . "\n";
			$xml = $xml . '            <orderDate>' . substr(formatdate($objOrderInfo->created_at), 0, 10) . '</orderDate>' . "\n";
			$xml = $xml . '            <startDate>' . substr(formatdate($objOrderInfo->created_at), 0, 10) . '</startDate>' . "\n";
			$xml = $xml . '            <expiryDate>' . substr(formatdate($objOrderInfo->created_at), 0, 10) . '</expiryDate>' . "\n";
			$xml = $xml . '            <numOfCartons />' . "\n";
			$xml = $xml . '            <weight>' . round($objOrderInfo->weight) . '</weight>' . "\n";
			$xml = $xml . '            <shipMethod>' . substr($objOrderInfo->shipping_description, 0, 200) . '</shipMethod>' . "\n";
			$xml = $xml . '            <orderTerms>WEB STORE</orderTerms>' . "\n";
			$xml = $xml . '            <orderType>P</orderType>' . "\n";
			$xml = $xml . '            <orderCurrency>' . $currencyCode . '</orderCurrency>' . "\n";
			$xml = $xml . '            <warehouseCurrency>' . $currencyCode . '</warehouseCurrency>' . "\n";
			//$xml = $xml . '            <orderTax>' . $tax_rate . "</orderTax>" . "\n";
			//$xml = $xml . '            <freightTax>' . $tax_rate . "</freightTax>" . "\n";
			$xml = $xml . '            <orderTax>0</orderTax>' . "\n";
			$xml = $xml . '            <freightTax>0</freightTax>' . "\n";
			$xml = $xml . '            <warehouseid>' . $warehouseId . '</warehouseid>' . "\n";
			$xml = $xml . '            <note />' . "\n";
			$xml = $xml . '            <season />' . "\n";
			$xml = $xml . '            <userid>' . $userId . '</userid>' . "\n";
			$xml = $xml . '            <repcode />' . "\n";
			//$xml = $xml . '            <custom1>' . number_format($objOrderInfo->shipping_amount, 2) . "</custom1>" . "\n";
			//$xml = $xml . '            <custom2>' . number_format($objOrderInfo->discount_amount, 2) . "</custom2>" . "\n";
			$xml = $xml . '            <custom1 />' . "\n";
			$xml = $xml . '            <custom2 />' . "\n";
			$xml = $xml . '            <custom3 />' . "\n";
			$xml = $xml . '            <custom4 />' . "\n";
			$xml = $xml . '            <custom5 />' . "\n";
			$xml = $xml . '            <shipAddress>' . "\n";
			$xml = $xml . '                <name>' . substr($objOrderInfo->shipping_address->firstname . ' ' . $objOrderInfo->shipping_address->lastname, 0, 200) . '</name>' . "\n";
			$xml = $xml . '                <line1>' . substr(str_replace("&", "&amp;", str_replace("\n", " - ", $objOrderInfo->shipping_address->street)), 0, 200) . '</line1>' . "\n";
			$xml = $xml . '                <line2 />' . "\n";
			$xml = $xml . '                <line3 />' . "\n";
			$xml = $xml . '                <city>' . substr($objOrderInfo->shipping_address->city, 0, 150) . '</city>' . "\n";
			if (strlen($objOrderInfo->shipping_address->region)) {
				$xml = $xml . '                <provstate>' . substr(getStateAbbr($objOrderInfo->shipping_address->region), 0, 10) . '</provstate>' . "\n";
			} else {
				$xml = $xml . '                <provstate>--</provstate>' . "\n";
			}
			$xml = $xml . '                <postalcode>' . substr($objOrderInfo->shipping_address->postcode, 0, 10) . '</postalcode>' . "\n";
			$xml = $xml . '                <country>' . substr($objOrderInfo->shipping_address->country_id, 0, 50) . '</country>' . "\n";
			$xml = $xml . '                <phone>' . substr($objOrderInfo->shipping_address->telephone, 0, 50) . '</phone>' . "\n";
			$xml = $xml . '                <fax>--</fax>' . "\n";
			$xml = $xml . '                <email>' . substr($objOrderInfo->customer_email, 0, 50) . '</email>' . "\n";
			$xml = $xml . '                <web>--</web>' . "\n";
			$xml = $xml . '                <contact>' . substr($objOrderInfo->shipping_address->firstname . ' ' . $objOrderInfo->shipping_address->lastname, 0, 50) . '</contact>' . "\n";
			$xml = $xml . '            </shipAddress>' . "\n";
			$xml = $xml . '            <billAddress>' . "\n";
			$xml = $xml . '                <name>' . substr($objOrderInfo->billing_address->firstname . ' ' . $objOrderInfo->billing_address->lastname, 0, 200) . '</name>' . "\n";
			$xml = $xml . '                <line1>' . substr(str_replace("&", "&amp;", str_replace("\n", " ", $objOrderInfo->billing_address->street)), 0, 200) . '</line1>' . "\n";
			$xml = $xml . '                <line2 />' . "\n";
			$xml = $xml . '                <line3 />' . "\n";
			$xml = $xml . '                <city>' . substr($objOrderInfo->billing_address->city, 0, 150) . '</city>' . "\n";
			$xml = $xml . '                <provstate>' . substr(getStateAbbr($objOrderInfo->billing_address->region), 0, 10) . '</provstate>' . "\n";
			$xml = $xml . '                <postalcode>' . substr($objOrderInfo->billing_address->postcode, 0, 10) . '</postalcode>' . "\n";
			$xml = $xml . '                <country>' . substr($objOrderInfo->billing_address->country_id, 0, 50) . '</country>' . "\n";
			$xml = $xml . '                <phone>' . substr($objOrderInfo->billing_address->telephone, 0, 50) . '</phone>' . "\n";
			$xml = $xml . '                <fax>' . substr($objOrderInfo->billing_address->fax, 0, 50) . '</fax>' . "\n";
			$xml = $xml . '                <email>' . substr($objOrderInfo->customer_email, 0, 50) . '</email>' . "\n";
			$xml = $xml . '                <web />' . "\n";
			$xml = $xml . '                <contact>' . substr($objOrderInfo->billing_address->firstname . ' ' . $objOrderInfo->billing_address->lastname, 0, 50) . '</contact>' . "\n";
			$xml = $xml . '            </billAddress>' . "\n";
			$xml = $xml . '            <orderItems>' . "\n";
			foreach ($objOrderInfo->items as $item) {
				if ($item->product_type == "configurable") {
					$itemValue = $item->price;
				}
				if ($item->product_type == "simple") {
					$xml = $xml . '                <OrderItem>' . "\n";
					//$xml = $xml . '                    <orderID>' . $orderId . '</orderID>' . "\n";
					$xml = $xml . '                    <orderID>' . substr($companyCode . $objOrderInfo->increment_id, 0, 60) . '</orderID>' . "\n";
					$xml = $xml . '                    <sku>' . substr($item->sku, 0, 50) . '</sku>' . "\n";
					$xml = $xml . '                    <quantity>' . round($item->qty_ordered) . '</quantity>' . "\n";
					//$xml = $xml . '                    <itemValue>' . money_format("%i", $itemValue) . '</itemValue>' . "\n";
					$xml = $xml . '                    <itemValue>0</itemValue>' . "\n";
					$xml = $xml . '                    <lineNote />' . "\n";
					$xml = $xml . '                    <carton />' . "\n";
					$xml = $xml . '                    <warehouseid>' . $warehouseId . '</warehouseid>' . "\n";
					$xml = $xml . '                    <categoryid />' . "\n";
					$xml = $xml . '                </OrderItem>' . "\n";
				}
			}
			$xml = $xml . '            </orderItems>' . "\n";
			$xml = $xml . '        </Order>' . "\n";
			
			$xml = $xml . '    </Batch>' . "\n";
			$xml = $xml . '</OrderUpload>';
			
			$soap = "";
			$soap = $soap . '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			$soap = $soap . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"' . "\n";
			$soap = $soap . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' . "\n";
			$soap = $soap . '<soap:Body>' . "\n";
			$soap = $soap . $xml . "\n";
			$soap = $soap . '</soap:Body>' . "\n";
			$soap = $soap . '</soap:Envelope>';
			
			/*
			echo '<pre>';
			print_r(str_replace("<", "&lt;", str_replace(">", "&gt;", $soap)));
			echo '</pre>';
		    die(include("../inc/php/footer.php"));
		    */
			
			$postResponse = http_post_data (
				$postUrl,
				$soap,
				array(
					"headers" => array(
						"Content-Type" => "text/xml; charset=UTF-8",
						"SOAPAction" => "http://chrome52/webservices/OrderUpload",
					),
					"timeout" => 60,
				)
			);
			
			if ($postResponse) {
				
				/*
				echo "<br /><br />\nRESPONSE:<br /><pre>";
				print_r(str_replace("<", "&lt;", str_replace(">", "&gt;", $postResponse)));
				echo "</pre>";
				*/
				
				$xmlResponse = strstr($postResponse, "<?xml ");
				$domDocument = new DomDocument('1.0');
				
				if ($xmlResponse) {
					try {
						$domDocument->loadXML($xmlResponse);
					} catch (Exception $e) {
						print_r('Error loading XML from response message: ' . mysql_error());
						print_r("<br /><br />" . $e);
						die(include("../inc/php/footer.php"));
					}
				}
				
				if ($domDocument->getElementsByTagName("result")->item(0)->nodeValue == "SUCCESS") {
					
					$lstOrderIds = "";
					$nodOrderIds = $domDocument->getElementsByTagName("orderids");
					
					foreach ($nodOrderIds as $nodOrderId) {
						foreach ($nodOrderId->childNodes as $orderId) {
							
							$orderIncrementId = preg_replace("/[^0-9]/", "", $orderId->nodeValue);
							$status = "Complete";
							$comment = "BlastRamp Order ID: " . $orderId->nodeValue;
							$notify = false;
							$lstOrderIds = $lstOrderIds . preg_replace("/[^0-9]/", "", $orderId->nodeValue) . ", ";
							
							$fmsProxy->salesOrderAddComment($sessionId, $orderIncrementId, $status, $comment, $notify);
							
						}
					}
						
					$log .= "<strong>Successfully Transmitted:</strong>&nbsp;" . $lstOrderIds . "<br />";
					
				} elseif ($domDocument->getElementsByTagName("result")->item(0)->nodeValue == "FAILURE") {
					
					$log .= "ERROR:<br />" . $domDocument->getElementsByTagName("error")->item(0)->nodeValue;
					
				} else {
					
					$log .= "ERROR: Could not upload Orders. Result: " . $domDocument->getElementsByTagName("result")->item(0)->nodeValue;
					
					/*
					echo "<br /><br />\nRESPONSE:<br /><pre>";
					print_r(str_replace("<", "&lt;", str_replace(">", "&gt;", $xmlResponse)));
					echo "</pre>";
					*/
					
				} // end if ($domDocument->getElementsByTagName("result")->item(0)->nodeValue == "SUCCESS")
				
			} else {
				
				$log .= "ERROR: Could not upload Orders. POST Response: " . $postResponse;
				
			} // end if ($postResponse)

		} // foreach ($_SESSION["arrOrders"] as $order)

	} // end if ($_POST["btnUpdate"])

?>

<?php if ($preview) { ?>
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
			oTable = $("#orders").dataTable( {
				"sPaginationType": "scrolling"
			});
		});
	</script>
<?php } // end if ($preview) { ?>

<form method="post" name="frmSelectCompany" action="<?php $_SERVER["PHP_SELF"]?>">
	<table class="center" width="800px">
		<tr class="buttons">
			<td align="left">
				<select class="select" name="selExport" tabindex="1">
					<option value="" selected>select export</option>
					<?php mysql_data_seek($qryExports, 0); ?>
					<?php while ($arrRow = mysql_fetch_assoc($qryExports)) { ?>
						<option value="<?php echo $arrRow['exportid'] ?>"><?php echo $arrRow['name'] ?></option>
					<?php } // end while ($arrRow = mysql_fetch_assoc($qryExports)) ?>
				</select>
			</td>
			<td align="right">
				<input name="btnPreview" type="submit" value="preview" tabindex="2">
				<input name="btnUpdate" type="submit" value="update" tabindex="3" <?php if (!$preview) {echo 'disabled="disabled"';}?>>
			</td>
		</tr>
		<?php if ($preview) { ?>
			<input name="selExport" type="hidden" value="<?php echo $intExportId ?>">
			<tr class="title"><td colspan="2">
				<?php echo $strExportName ?>
			</td></tr>
			<tr class="data"><td colspan="2">
				<table class="clear" id="orders" width="100%">
					<thead>
						<tr>
							<th class="a_left"><div class="label">Order ID</div><div class="sort_direction"></div></th>
							<th class="a_left"><div class="label">Name</div><div class="sort_direction"></div></th>
							<th class="a_right"><div class="label">Total</div><div class="sort_direction"></div></th>
							<th class="a_right"><div class="label">Date</div><div class="sort_direction"></div></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($_SESSION["arrOrders"] as $order) { ?>
							<?php //print_r($_SESSION["arrOrders"]);?>
							<?php //$objOrderInfo = $fmsProxy->salesOrderInfo($sessionId, $order->increment_id); ?>
							<?php //print_r($objOrderInfo);?>
							<?php //foreach ($arrInventory as $keyRow => $arrKeyedRow) { ?>
							<tr>
								<td align="left"><?php echo $order->increment_id ?></td>
								<td align="left"><?php echo $order->billing_lastname .", ". $order->billing_firstname ?></td>
								<td align="right"><?php echo $order->grand_total ?></td>
								<td align="right"><?php echo $order->updated_at ?></td>
							</tr>
						<?php } // end foreach ($_SESSION["arrOrders"] as $order) ?>
					</tbody>
				</table>
			</td></tr>
		<?php } // end if ($preview) { ?>
		<?php if ($update) { ?>
			<tr><td colspan="2"><?php echo $log; ?></td></tr>
		<?php } // end if ($update) ?>
	</table>
</form>

<?php include("../inc/php/footer.php"); ?>
