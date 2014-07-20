<?php

class MDN_SimpleBarcodeInventory_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * 
     */
    public function checkSettings()
    {
        $barcodeAttribute = Mage::getStoreConfig('simple_barcode_inventory/general/barcode_attribute');
        if (!$barcodeAttribute)
            return false;
        return true;
    }
    
    /**
     * Return product from barcode
     * @param type $barcode
     */
    public function getProduct($barcode)
    {
        //standard mode
        if (!Mage::getStoreConfig('advancedstock/erp/is_installed'))
        {
            //get barcode attribute
            $barcodeAttribute = Mage::getStoreConfig('simple_barcode_inventory/general/barcode_attribute');
            if (!$barcodeAttribute)
                throw new Exception('Barcode attribute is not set in system > configuration > simple barcode inventory');

            //searchh for product
            $product = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter($barcodeAttribute, $barcode)
                            ->getFirstItem();
            if ($product->getId())
                return $product;
            else
                return null;
        }
        else
        {
            //erp mode
            $product = Mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($barcode);
            return $product;
        }
    }
    
}
