<?php
	class BlastRamp {
		
		private $strUrl = "";
		private $strVendorcode = "";
		private $strVendorkey = "";
		private $strWherehouse = "";
		private $strMode = "";
		private $strCompanyName = "";
	    
		public function __construct( $cn, $vc, $vk, $wh, $sc ) {
			
			$this->strUrl = "http://www.ioperate.net/WS/ws_inventory.asp";
			$this->strMode = "ALL";
			//$this->strMode = "CIS";
			//$this->strMode = "ATA";
			
			$this->strCompanyName = $cn;
			$this->strVendorcode = $vc;
			$this->strVendorkey = $vk;
			$this->strWherehouse = $wh;
			$this->strStoreCode = $sc;
	    	
	    } // __construct()
		
		function __destruct() {
			
		}
	    
	    public function getCompanyName() {
	    	return $this->strCompanyName;
	    }
	    
	    public function getCsvInventory() {
	    	
			$csvData = "store,sku,description,qty\n";
	    	$arrData = $this->getArrInventory();
			
			foreach ($arrData as $arrRow) {
				$csvData = $csvData . implode(",", $arrRow) . "\n";
			}
			
			return $csvData;
	    	
	    } // getCsvInventory()
	
	    public function getArrInventory() {
	    	
	    	$csvData = $this->getRawCsv();
			
			$arrRows = explode(chr(10), trim($csvData));
			$arrHeader = explode(",", trim(array_shift($arrRows)));
			
			$arrArrKeyedRows = array();
			foreach ($arrRows as $keyRow => $csvRow) {
				$arrRow = explode(",", trim($csvRow));
		        $arrKeyedRows["store"] = $this->strStoreCode;
				foreach($arrHeader as $keyHeader => $val) {
		            $arrKeyedRows[$val] = $arrRow[$keyHeader];
		        }
		        $arrArrKeyedRows[$keyRow] = $arrKeyedRows;
			}
			
			return $arrArrKeyedRows;

	    } // getArrInventory()
	
	    public function getXmlInventory() {
	    	
	    	$arrArrKeyedRows = $this->getArrInventory();
			$domFreeMyStore = new DomDocument('1.0');
			
			$freemystore = $domFreeMyStore->appendChild($domFreeMyStore->createElement('FreeMyStore'));
			$header = $freemystore->appendChild($domFreeMyStore->createElement('Header'));
			$actions = $freemystore->appendChild($domFreeMyStore->createElement('Actions'));
			
			$username = $header->appendChild($domFreeMyStore->createElement('UserName'));
			$apikey = $header->appendChild($domFreeMyStore->createElement('APIKey'));
			
			$username->appendChild($domFreeMyStore->createTextNode('fmsadmin'));
			$apikey->appendChild($domFreeMyStore->createTextNode('soochow'));
			
			foreach($arrArrKeyedRows as $arrKeyedRow) {
				$updateinventory = $actions->appendChild($domFreeMyStore->createElement('UpdateInventory'));
				$sku = $updateinventory->appendChild($domFreeMyStore->createElement('SKU'));
				$qty = $updateinventory->appendChild($domFreeMyStore->createElement('QTY'));
				$instock = $updateinventory->appendChild($domFreeMyStore->createElement('InStock'));
				$managestock = $updateinventory->appendChild($domFreeMyStore->createElement('ManageStock'));
				$useconfigmanagestock = $updateinventory->appendChild($domFreeMyStore->createElement('UseConfigManageStock'));
				
				$sku->appendChild($domFreeMyStore->createTextNode($arrKeyedRow["SKU"]));
				$qty->appendChild($domFreeMyStore->createTextNode($arrKeyedRow["Quantity"]));
				$instock->appendChild($domFreeMyStore->createTextNode('1'));
				$managestock->appendChild($domFreeMyStore->createTextNode('1'));
				$useconfigmanagestock->appendChild($domFreeMyStore->createTextNode('1'));
			}
			
			$domFreeMyStore->formatOutput = true;
			
			$xml = $domFreeMyStore->saveXML();
			
			return $xml;
	    	
	    } // getXmlInventory
	    
	    public function getRawCsv() {
	    	
	    	$rawData = $this->getRawData();
			
			$domDocument = new DomDocument('1.0');
			@$domDocument->loadHTML($rawData);
		      
			$csvData = $domDocument->getElementsByTagName("body")->item(0)->nodeValue;
			
			return $csvData;
	    	
	    } // getRawCsv()
	
	    private function getRawData() {
	    	
	    	$error = false;
			$url = $this->strUrl . "?VC=" . $this->strVendorcode . "&VK=" . $this->strVendorkey . "&WH=" . $this->strWherehouse . "&MODE=" . $this->strMode;
			
			//die($url);
			
			try {
				$fsData = fopen($url, "r");
			} catch (Exception $e) {
				$error = true;
				$rawData = "Error connecting to " . $url;
			}
			
			if (!$error) {
				$rawData = "";
				while(!feof($fsData)) {
					$buffer = fread($fsData, 4096);
					$rawData = $rawData . $buffer;
				}
				
				$rawData = str_replace("<br>", chr(10), $rawData);
				$rawData = str_replace("|", ",", $rawData);
			}
			
			return $rawData;
			
	    } // getRawData()
	    
	}
?>