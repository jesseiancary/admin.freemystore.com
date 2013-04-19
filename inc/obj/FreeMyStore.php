<?php
	class FreeMyStore {
		
		private $dbFmsAdmin;
	
		function __construct() {
			
			$this->dbFmsAdmin = mysql_connect("localhost", "root", "soochow");
			if (!$this->dbFmsAdmin) {
			    print_r("Could not connect to FreeMyStore database: " . mysql_error());
			    die(include("../inc/php/footer.php"));
			} else {
				mysql_select_db("freemystore", $this->dbFmsAdmin);
			}
			
		}
		
		function __destruct() {
			
			mysql_close($this->dbFmsAdmin);
			
		}
		
		public function getCompanies($companyid = 0) {
			
			$strQuery = "SELECT * FROM admin_company"
							. (($companyid > 0) ? (" WHERE companyid = " . $companyid) : "");
			$qryCompanies = mysql_query($strQuery, $this->dbFmsAdmin);
			if (!$qryCompanies) {
			    print_r("Error selecting companies: " . mysql_error());
			    die(include("../inc/php/footer.php"));
			}
			
			if ($companyid > 0) {
				return mysql_fetch_assoc($qryCompanies);
			} else {
				return $qryCompanies;
			}
			
		}
		
		public function getExports($exportid = 0, $enabled = true) {
			
			$strQuery = "SELECT * FROM admin_companyExport WHERE exportid IS NOT NULL"
							. ($enabled ? " AND enabled = true" : "")
							. (($exportid > 0) ? (" AND exportid = " . $exportid) : "");
			$qryExports = mysql_query($strQuery, $this->dbFmsAdmin);
			if (!$qryExports) {
			    print_r("Error selecting exports: " . mysql_error());
			    die(include("../inc/php/footer.php"));
			}
			
			if ($exportid > 0) {
				return mysql_fetch_assoc($qryExports);
			} else {
				return $qryExports;
			}
			
		}
		
		public function updateInventory($exportid, $csv) {
			
			$export = $this->getExports($exportid);
			
			$companyid = (int) $export["companyid"];
			$company = $this->getCompanies($companyid);
			
			$csvFilePath = $company["magedir"] . "var/tmp/" . $company["storecode"] . ".csv";
			$this->writeCsvFile($csvFilePath, $csv);
			
			$dbConfig = array(
			    "host"				=> "localhost",
			    "dbname"			=> $company["dbname"],
			    "username"			=> $company["dbuser"],
			    "password"			=> $company["dbpassword"],
			    "driver_options"	=> array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8")
			);
			
			require_once $company["magedir"] . "app/Mage.php";
			$db_magento = Zend_Db::factory("Pdo_Mysql", $dbConfig);
			$tmpTableName = "tmp_inventory_" . $company["storecode"];
			$this->updateTempTableFromFile($db_magento, $tmpTableName, $csvFilePath);   
			$query = $db_magento->select()->from($tmpTableName);
			$contents_array = $db_magento->fetchAll($query);
			 
			foreach ($contents_array as $line) {
			   
			    $sku = $line["sku"];
			    $qty = $line["qty"];
			           
			    $exists = $db_magento->query("SELECT COUNT(sku) cnt FROM catalog_product_entity WHERE sku = '$sku' LIMIT 1");
			    $find_product = (($exists->fetchObject()->cnt) > 0) ? true : false;
			 
			    if ($find_product == true) {
			        $entity_id = $this->getEntityID_bySKU($db_magento, $sku);   
			        $this->updateQTY ($db_magento, $entity_id, $qty);
			    }
			}
			
			$db_magento->query("DROP TABLE IF EXISTS $tmpTableName");
			
		}
		
		private function writeCsvFile($filename, $contents) {
			$csvfile = fopen($filename, "w");
			fwrite($csvfile, $contents);
			fclose($csvfile);
		}
		
		private function getEntityID_bySKU($db_magento, $sku) {
		    $entity_row = $db_magento->query("SELECT entity_id FROM catalog_product_entity pe WHERE pe.sku = '$sku'")->fetchObject();
		    $entity_id  = $entity_row->entity_id;
		    return $entity_id;
		}
		
		private function updateQTY($db_magento, $entity_id, $qty) {
			
		    $db_magento->query("UPDATE cataloginventory_stock_item si, cataloginventory_stock_status ss
		         SET   si.qty = '$qty', si.is_in_stock = IF('$qty'>0, 1, 0),
		               ss.qty = '$qty', ss.stock_status = IF('$qty'>0, 1, 0)
		         WHERE si.product_id = '$entity_id' AND si.product_id = ss.product_id ");
			
		}
		
		private function updateTempTableFromFile($db_magento, $tableName, $path){
			
			$db_magento->query("CREATE TABLE IF NOT EXISTS $tableName ( store varchar(16), sku varchar(100), description varchar(255), qty int )");
		    $db_magento->query("TRUNCATE TABLE $tableName");
		 
		    $db_magento->query("LOAD DATA LOCAL INFILE '$path'
		                      INTO TABLE $tableName
		                      CHARACTER SET cp1251
		                      FIELDS TERMINATED BY ','
		                      LINES TERMINATED BY '\n'
		                      IGNORE 1 LINES
		                      (store, sku, description, qty)");
			
		}
		
		private function getAttributeEAV_id ($db_magento, $attribute_name)
		{
		    $result = $db_magento->query("SELECT attribute_id FROM eav_attribute WHERE entity_type_id = 4 AND attribute_code = '$attribute_name'")->fetchObject()->attribute_id;
		    return $result;
		}
		
	}
?>