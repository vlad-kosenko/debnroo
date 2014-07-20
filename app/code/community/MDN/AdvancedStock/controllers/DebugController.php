<?php

class MDN_AdvancedStock_DebugController extends Mage_Adminhtml_Controller_Action {

    public function DebugAction() {
    

$po = Mage::getModel('Purchase/Order')->load(86);
$order = Mage::getModel('sales/order')->load(21211);


Mage::helper('DropShipping/Attachment')->getAttachment('palmer', $po, $order);

die('ini');
        
    }

}
