<?php
	
	$response = "";
	
	/* FUNCTIONS **********************************************************************************/
	
	function error($error) {
		
		$errorfile = fopen("error.log", "a");
		fwrite($errorfile, date("YmdHis") . ": " . $error . "\n");
		fclose($errorfile);
		
		$response = $response . $error . "<br>";
		
		/*
		$xmlstr = <<<XML
<?xml version='1.0'?>
<FreeMyStore>
	<Date>date("YmdHis")</Date>
	<Error>
		<Message>$error</Message>
	</Error>
</FreeMyStore>
XML;
		
		echo $xmlstr;
		*/
		
	}

	/* POST ***************************************************************************************/
	
	// TRY http_get_request_body() HERE. Replace lines 43 - 47	
	$xmlData = "";
	$xmlFileName = "xmlfile" . date("YmdHis") . ".xml";
	$xmlfile = fopen($xmlFileName, "w");

	if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
		$postdata = fopen( "php://input", "rb" );
		
		while(!feof($postdata)) {
			$buffer = fread($postdata, 4096);
			fwrite($xmlfile, $buffer);
			$xmlData = $xmlData . $buffer;
		}
		
		fclose($postdata);
	}
	
	fwrite($xmlfile, "this is a test");
	
	fclose($xmlfile);
	
	/* DOM ****************************************************************************************/
	
	$domDocument = new DomDocument('1.0');
	
	try {
		$domDocument->loadXML($xmlData);
	} catch (Exception $e) {
		error("Caught exception: " .  $e->getMessage());
	}
	
	try {
		$domDocument->schemaValidate("xml.xsd");
	} catch (Exception $e) {
		error("Caught exception: " .  $e->getMessage());
	}
	
	/* API LOGIN **********************************************************************************/
	
	$username = $domDocument->getElementsByTagName("UserName")->item(0)->nodeValue;
	$apikey = $domDocument->getElementsByTagName("APIKey")->item(0)->nodeValue;
	
	$fmsProxy = new SoapClient('https://www.freemystore.com/cart/api/v2_soap/?wsdl=1');

	try {
		$sessionId = $fmsProxy->login($username, $apikey);
	} catch (Exception $e) {
		error("Caught exception: " .  $e->getMessage());
		error("Login: " .  $username);
	}
	
	/* ACTIONS ************************************************************************************/
	
	$actionsNode = $domDocument->getElementsByTagName("Actions")->item(0);
		
	foreach ($actionsNode->childNodes AS $action) {
		switch ($action->nodeName) {
		case "UpdateInventory":
			
			$sku = $action->getElementsByTagName("SKU")->item(0)->nodeValue;
			$qty = $action->getElementsByTagName("QTY")->item(0)->nodeValue;
			$instock = $action->getElementsByTagName("InStock")->item(0)->nodeValue;
			$managestock = $action->getElementsByTagName("ManageStock")->item(0)->nodeValue;
			$useconfigmanagestock = $action->getElementsByTagName("UseConfigManageStock")->item(0)->nodeValue;
			
			$stock = $fmsProxy->catalogInventoryStockItemUpdateEntity;
			$stock->qty = $qty;
			$stock->is_in_stock = $instock;
			$stock->manage_stock = $managestock;
			$stock->use_config_manage_stock = $useconfigmanagestock;
			
			try {
				//if (stristr($sku, "PPK"))
				$updateResponse = $fmsProxy->catalogInventoryStockItemUpdate($sessionId, $sku, $stock);
			} catch (Exception $e) {
				error("Caught exception: " .  $e->getMessage());
				error("Product: " .  $sku);
			}
			
			//print_r("<br>UpdateInventory... SKU: " . $sku . " QTY: " . $qty);
			
			break;
		default:
			//print_r("Action " . $action->nodeName . " not available.");
		}
		//print_r("<br>Action is: " . $action->nodeName);
	}
	
	$fmsProxy->endSession($sessionId);
	
	echo $response;
	
?>