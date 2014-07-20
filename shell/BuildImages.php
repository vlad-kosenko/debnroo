<?php
ini_set('memory_limit','2048M');

    // include the Mage engine
    require_once '../app/Mage.php';
    Mage::app();
    
    $fileName = $argv[1];
    $logFile = 'OCM_Site_Builder_Image.log';
    $logFileError = 'OCM_Site_Builder_Image_Error.log';
    $mediaLocation = Mage::getBaseDir('media')."/import/";

	$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
	$product = Mage::getModel('catalog/product');
	
	
	function logger($logMessage, $isError = 0)
	{
		global $logFile;
		global $logFileError;
		
		echo $logMessage."\n";
		if ($isError = 0)
			Mage::log($logMessage,null, $logFile);
		elseif ($isError = 1)
			Mage::log($logMessage,null, $logFileError);
	}
	
	function removeAllImages($productId)
	{
	
	    $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
	    $items = $mediaApi->items($productId);
	    foreach($items as $item)
	        $mediaApi->remove($productId, $item['file']);
	}
	
	logger("Opening file: ".$fileName);
	
	$firstRowSkipped = false;
	if (($handle = fopen($fileName, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 100000, ",")) !== FALSE) 
		{
			if ($firstRowSkipped)
			{
				
				$sku = $data[0];
				$imageName = $data[1];
				$imageFullPath = $mediaLocation.$imageName;
				//$imageType = $data[2];
				//$exclude = $data[3];
				//$imageLabel = $data[4];	
				
			if (strlen(strstr($imageName,'.jpg'))>0) 
			{
				$mime = 'image/jpeg';
			}
			if (strlen(strstr($imageName,'.png'))>0) 
			{
				$mime = 'image/png';
			}
			if (strlen(strstr($imageName,'.gif'))>0)
			{
				$mime = 'image/gif';
			}
								
				$newImage = array(
						'file' => array(
								'name' => str_replace("/","_",$imageName),
								'content' => base64_encode(file_get_contents($imageFullPath)),
								'mime'    => $mime
								),
						//'label'    => $imageLabel,
						'types'    => array("image","small_image","thumbnail"), //array($imageType),
						'exclude'  => 0 //$exclude
						);
				try 
				{
					$productId = $product->getIdBySku($sku);
					if ($imageType == "delete")
					{
						logger("Deleting Image SKU# ".$sku, 0);
						removeAllImages($productId);
					}
					else
					{
						logger("Trying to add image, sku# ".$sku." | ".$imageFullPath, 0);
						$imageFilename = $mediaApi->create($productId, $newImage);
						logger("Added Image SKU# ".$sku." | ".$imageName, 0);
					}
				}
				catch (Exception $e) 
				{
					logger("Error with SKU # ".$sku, 1);
					logger($e);
				}
			}
			$firstRowSkipped = true;
		}		
	}
	fclose($handle);
?>
