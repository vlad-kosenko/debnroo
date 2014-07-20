<?php

class MDN_SimpleBarcodeInventory_Model_System_Config_Mode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {

            $options = array();
            
            $options[] = array(
                'value' => 'decrease',
                'label' => Mage::helper('SimpleBarcodeInventory')->__('Decrease mode'),
            );
            
            $options[] = array(
                'value' => 'increase',
                'label' => Mage::helper('SimpleBarcodeInventory')->__('Increase mode'),
            );            
            
            $options[] = array(
                'value' => 'manual',
                'label' => Mage::helper('SimpleBarcodeInventory')->__('Manual mode'),
            );
            
            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}