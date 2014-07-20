<?php

class MDN_M2eErpPatch_Model_Observer {

    /**
     * Update product attribute if available_qty change
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesorderplanning_productavailabilitystatus_aftersave(Varien_Event_Observer $observer) {
        $productAvailabilityStatus = $observer->getEvent()->getproductavailabilitystatus();
        if ($this->fieldHasChanged($productAvailabilityStatus, 'pa_available_qty') || (mage::getStoreConfig('m2eerppatch/general/init_mode') == 1)) {
            $availableQty = $productAvailabilityStatus->getpa_available_qty();
            $productId = $productAvailabilityStatus->getpa_product_id();
            $product = mage::getModel('catalog/product')->load($productId);
            $attributeCode = mage::getStoreConfig('m2eerppatch/general/qty_attribute');
            //$product->setData($attributeCode, $availableQty)->save();

            if (!is_null($availableQty))
            {
                Mage::getSingleton('catalog/product_action')
                        ->updateAttributes(array($productId), array($attributeCode => $availableQty), 0);
            }
        }
    }

    private function fieldHasChanged($object, $fieldname) {
        if ($object->getData($fieldname) != $object->getOrigData($fieldname))
            return true;
        else
            return false;
    }

}

