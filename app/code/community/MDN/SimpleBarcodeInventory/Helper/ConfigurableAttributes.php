<?php

class MDN_SimpleBarcodeInventory_Helper_ConfigurableAttributes extends Mage_Core_Helper_Abstract {

    /**
     * Return configurable attributes values
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function getDescription($productId) {
        $description = '';

        //manage exceptions
        $product = mage::getModel('catalog/product')->load($productId);
        if ((!$product->getId()) || ($product->gettype_id() != 'simple'))
            return $description;

        //find configurable product
        $configurableProduct = $this->getConfigurableProduct($product);
        if (!$configurableProduct)
            return $description;
        if ($configurableProduct->gettype_id() != 'configurable')
            return $description;

        //build attributes string
        $attributes = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray($configurableProduct);
        foreach ($attributes as $att) {
            $description .= $att['label'] . ': ' . $product->getAttributeText($att['attribute_code']) . ', ';
        }
        if (strlen($description) > 2)
            $description = substr($description, 0, strlen($description) - 2);

        if ($description != '')
            $description = ' (' . $description . ') ';

        return $description;
    }

    /**
     * Return configurable product from simple product
     *
     * @param unknown_type $product
     */
    public function getConfigurableProduct($product) {
        $parentIdArray = $this->getProductParentIds($product);
        foreach ($parentIdArray as $parentId) {
            $parent = mage::getModel('catalog/product')->load($parentId);
            return $parent;
        }

        return null;
    }

    /**
     * return parents for one product
     */
    public function getProductParentIds($product) {
        $productId = null;
        if (is_object($product))
            $productId = $product->getId();
        else
            $productId = $product;
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
        return $parentIds;
    }

}