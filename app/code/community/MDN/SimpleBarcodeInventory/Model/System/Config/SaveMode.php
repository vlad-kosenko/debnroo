<?php

class MDN_SimpleBarcodeInventory_Model_System_Config_SaveMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {

            $options = array();
            
            $options[] = array(
                'value' => 'immediate',
                'label' => Mage::helper('SimpleBarcodeInventory')->__('Immediate'),
            );

            $options[] = array(
                'value' => 'manual',
                'label' => Mage::helper('SimpleBarcodeInventory')->__('Manual'),
            );
            
            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}