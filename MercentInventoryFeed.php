<?php

/*
Things that need customization on this script:
$storeId
$username
$password
*/

//Include Mage and run as admin
require_once 'app/Mage.php';

$storeId = 1;

//FTP information
$host = 'sftp.mercent.com';
$port = 21;
$username = 'DebnrooFTP';
$password = '9r_drAVeq$*w';
$folder = 'incoming/';

Mage::app()->setCurrentStore($storeId);
$websiteId = Mage::app()->getStore()->getWebsiteId();
$storeCode = Mage::app()->getStore()->getCode();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);

//Feed name and folder
$fileName = 'MercentInventory.txt';
$fileDir = sprintf('%s/mercentfeed', Mage::getBaseDir('media'));
$uploadFile = sprintf('%s/%s', $fileDir, $fileName);

$results;

//Create output directory if not exists
if(!file_exists($fileDir)){
    mkdir($fileDir);
    chmod($fileDir, 0777);
}

//Write to log file
file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Inventory Feed Script\n");

//Set memory limit
ini_set("memory_limit", "1024M");
ini_set("upload_max_filesize", "256M");
ini_set("post_max_size", "256M");

//Write to log file
file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Initialize Database\n", FILE_APPEND);

//Table variables
$catalogProductFlatTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_flat') . '_' . $storeId;
$catalogProductVarcharTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
$catalogProductRelationTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_relation');
$catalogProductIntTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
$catalogInventoryStockTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
$catalogProductWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_website');
$eavAttributeTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');
$catalogProductBundleSelection = Mage::getSingleton('core/resource')->getTableName('catalog_product_bundle_selection');

//Initialize database connection
try {
    $read = Mage::getSingleton('core/resource')->getConnection('core_read');

    $sql = "
SELECT distinct flat.entity_id as entity_id,
flat.sku as sku,
stock.is_in_stock as is_in_stock,
stock.backorders as backorders,
round(coalesce(bundle_selection.bundle_qty, stock.qty)) as qty,
coalesce((SELECT max(value) from " . $catalogProductVarcharTable . " as entity_varchar inner join " . $eavAttributeTable . " as eav_attribute on entity_varchar.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='fulfillment_latency' and entity_varchar.entity_id=flat.entity_id), (SELECT max(value) from " . $catalogProductVarcharTable . " as entity_varchar inner join " . $eavAttributeTable . " as eav_attribute on entity_varchar.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='fulfillment_latency' and entity_varchar.entity_id=parent_flat.entity_id)) as fulfillment_latency
FROM " . $catalogProductFlatTable . " as flat
inner join " . $catalogProductWebsiteTable . " as product_website on flat.entity_id=product_website.product_id
inner join " . $catalogInventoryStockTable . " as stock on flat.entity_id=stock.product_id
left outer join " . $catalogProductRelationTable . " as relation on flat.entity_id=relation.child_id
left outer join " . $catalogProductFlatTable . " as parent_flat on relation.parent_id=parent_flat.entity_id
left outer join (
    select parent_product_id, min(qty DIV selection_qty) as bundle_qty from " . $catalogProductBundleSelection . " cpbs inner join " . $catalogInventoryStockTable . " ci on cpbs.product_id=ci.product_id group by parent_product_id
) as bundle_selection on flat.entity_id=bundle_selection.parent_product_id
where exists (SELECT * from " . $catalogProductIntTable . " as enabled inner join " . $eavAttributeTable . " as eav_attribute on enabled.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='status' and enabled.entity_id=flat.entity_id and enabled.value=1)
and not(parent_flat.type_id = 'bundle' and flat.visibility=1)
and not(parent_flat.sku is null and flat.visibility=1)
and not(flat.type_id in ('grouped', 'configurable', 'giftcard', 'virtual', 'downloadable'))
and product_website.website_id=".$websiteId;

    $results = $read->query($sql);

    //Write to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Initialize Database Complete\n", FILE_APPEND);
}
catch (Exception $exception) {
    //Write error to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Initialize Database\n".$exception."\n", FILE_APPEND);
    exit();
}

//Read each row and generate the file
try {
    $file = fopen($uploadFile, 'w');
    $isFirstRow = 1;

    while ($details = $results->fetch(PDO::FETCH_ASSOC)) {
        if ($isFirstRow == 1) {
            $isFirstRow = 0;
            fputcsv($file, array_keys($details), "\t", '"');
        }
        fputcsv($file, $details, "\t", '"');
    }

    fclose($file);

    //Write to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." ".$fileName." Feed Generation Complete\n", FILE_APPEND);
}
catch (Exception $exception) {
    //Write error to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Generate ".$fileName."\n".$exception."\n", FILE_APPEND);
    exit();
}

try {
    $gzStream = gzopen($uploadFile.".gz", 'w9'); //write to compression level 9

    gzwrite($gzStream, file_get_contents($uploadFile));
    gzclose($gzStream);
}
catch (Exception $exception) {
    //Write error to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to GZip ".$fileName."\n".$exception."\n", FILE_APPEND);
}

//FTP the file
try {
    if($connection = ftp_connect($host, $port)) {
        $login = ftp_login($connection, $username, $password);
        ftp_pasv($connection, true);
        if(ftp_put($connection, $folder.$fileName.".gz", $uploadFile.".gz", FTP_BINARY)){
        //Write to log file
        file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." FTP of ".$fileName.".gz"." to ".$host." Complete\n", FILE_APPEND);
        }
        else {
            throw new Exception('Could not upload file.');
        }
    }
}
catch (Exception $exception) {
    //Write error to log file
    file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile.".gz"), date(DateTime::ATOM)." Unable to FTP ".$fileName.".gz"." to ".$host."\n".$exception."\n", FILE_APPEND);
}

?>
