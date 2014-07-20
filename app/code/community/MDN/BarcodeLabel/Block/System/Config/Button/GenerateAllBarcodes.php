<?php

class MDN_BarcodeLabel_Block_System_Config_Button_GenerateAllBarcodes extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('BarcodeLabel/Admin/GenerateForAllProducts');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Generate barcodes')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}