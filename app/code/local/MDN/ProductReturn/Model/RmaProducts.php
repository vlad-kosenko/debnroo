<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_RmaProducts extends Mage_Core_Model_Abstract {
    
    const kDestinationStock = 'Back to stock';
    const kDestinationSupplier = 'Back to supplier';
    const kDestinationCustomer = 'Back to customer';
    const kDestinationDestroy = 'Destroy';

    private $_rma = null;

    public function _construct() {
        parent::_construct();
        $this->_init('ProductReturn/RmaProducts');
    }

    /**
     * Return possible destination for 1 product line
     *
     */
    public function getDestinations() {
        $retour = array();
        $retour[] = '';
        $retour[self::kDestinationStock] = mage::helper('ProductReturn')->__(self::kDestinationStock);
        $retour[self::kDestinationSupplier] = mage::helper('ProductReturn')->__(self::kDestinationSupplier);
        $retour[self::kDestinationCustomer] = mage::helper('ProductReturn')->__(self::kDestinationCustomer);
        $retour[self::kDestinationDestroy] = mage::helper('ProductReturn')->__(self::kDestinationDestroy);
        return $retour;
    }

    /**
     * Return reasons array
     *
     * @return unknown
     */
    public function getReasons() {
        $retour = array();

        //get reasons in configuration
        $other_reason = Mage::getStoreConfig('productreturn/product_return/other_reason');
        $array_other_reason = explode(';', $other_reason);

        if (is_array($array_other_reason)) {
            foreach ($array_other_reason as $reason) {
                if (!empty($reason))
                    $retour [$reason] = $reason;
            }
        }

        return $retour;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $product
     * @return unknown
     */
    public function productIsDisplayed($product) {
        $retour = true;

        switch ($product->getproduct_type()) {
            case 'bundle':
                $retour = true;
                break;
            case 'configurable':
                $retour = true;
                break;
            default:
                //load order item
                $orderItemid = $product->getitem_id();
                if ($orderItemid) {
                    $orderItem = mage::getModel('sales/order_item')->load($orderItemid);
                    if ($orderItem->getId()) {
                        if ($orderItem->getparent_item_id()) {
                            //do not display product with parent = configurable
                            $parentItemId = $orderItem->getparent_item_id();
                            $parentItem = mage::getModel('sales/order_item')->load($parentItemId);
                            if (($parentItem) && ($parentItem->getproduct_type() == 'configurable'))
                                $retour = false;
                        }
                    }
                }
                break;
        }

        return $retour;
    }

    /**
     * Enter description here...
     *
     */
    public function getProductName($product) {
        $value = '<b>' . $product->getname() . '</b>';
        if ($product->getproduct_type() == 'configurable') {
            //add sub products
            $collection = mage::getModel('sales/order_item')
                            ->getCollection()
                            ->addFieldToFilter('parent_item_id', $product->getitem_id());
            foreach ($collection as $subProduct) {
                $value .= '<br><i>' . $subProduct->getname() . '</i>';

                //add product configurable attributes values
                $attributesDescription = mage::helper('ProductReturn/Configurable')->getDescription($subProduct->getproduct_id(), $product->getrp_product_id());
                if ($attributesDescription != '')
                    $value .= '<br>' . $attributesDescription;
            }
        }
        return $value;
    }

    /**
     * Return true if has sub product
     */
    public function hasSubProduct() {
        $product = mage::getModel('catalog/product')->load($this->getrp_product_id());
        if ($product->gettype_id() == 'configurable')
            return true;
        else
            return false;
    }

    /**
     * Return sub product
     */
    public function getSubProductId() {
        $salesOrderItem = $this->getrp_orderitem_id();

        $collection = mage::getModel('sales/order_item')
                        ->getCollection()
                        ->addFieldToFilter('parent_item_id', $salesOrderItem);
        foreach ($collection as $subProduct) {
            return $subProduct->getproduct_id();
        }

        throw new Exception('Unable to find sub product !');
    }

    /**
     * Return stock movement identificator
     *
     * @return unknown
     */
    protected function getSmUi() {
        return 'rma_item_' . $this->getId();
    }

    /**
     * Return associated RMA
     *
     */
    public function getRma() {
        if ($this->_rma == null) {
            $rmaId = $this->getrp_rma_id();
            $this->_rma = mage::getModel('ProductReturn/Rma')->load($rmaId);
        }
        return $this->_rma;
    }

}