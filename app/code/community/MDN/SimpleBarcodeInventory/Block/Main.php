<?php

class MDN_SimpleBarcodeInventory_Block_Main extends Mage_Core_Block_template {

    /**
     * 
     * @return type
     */
    public function getModes()
    {
        $modes = array();
        
        $modes['decrease'] = $this->__('Decrease mode');
        $modes['increase'] = $this->__('Increase mode');
        $modes['manual'] = $this->__('Manual mode');
        
        return $modes;
    }
    
    /**
     * 
     */
    public function getDefaultMode()
    {
        return Mage::getStoreConfig('simple_barcode_inventory/general/default_mode');
    }
    
    /**
     * 
     */
    public function getProductInformationUrl()
    {
       return Mage::helper('adminhtml')->getUrl('*/*/getProductInformation', array('barcode' => 'XXX'));
    }

    /**
     * 
     * @return type
     */
    public function CommitProductStockUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/commitProductStock', array('product_id' => 'XXX', 'new_stock_value' => 'YYY'));
    }

    /**
     * 
     * @return type
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/massSave');
    }
    
    /**
     * 
     */
    public function isImmediateMode()
    {
        return (Mage::getStoreConfig('simple_barcode_inventory/general/save_mode') == 'immediate');
    }
    
}
