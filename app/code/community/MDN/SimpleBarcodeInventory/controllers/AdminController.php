<?php

class MDN_SimpleBarcodeInventory_AdminController extends Mage_Adminhtml_Controller_Action {

    /**
     * 
     */
    public function IndexAction() {
        //check that barcode attribute is set
        if (!Mage::helper('SimpleBarcodeInventory')->checkSettings())
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('SimpleBarcodeInventory')->__('Module is not configured, please go in system > configuration > Simple barcode inventory'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Return product information
     */
    public function getProductInformationAction() {

        $response = array();
        $response['error'] = false;
        $response['message'] = '';

        try {
            $barcode = $this->getRequest()->getParam('barcode');

            $product = Mage::helper('SimpleBarcodeInventory')->getProduct($barcode);
            if (!$product)
                throw new Exception($this->__('No product matches to barcode %s', $barcode));

            $response['message'] = $product->getName();
            $response['product_information'] = $product->getData();
            $response['product_information']['image_url'] = $this->getImageUrl($product);
            $response['product_information']['barcode'] = $barcode;

            $response['product_stock'] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getData();
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['message'] = $ex->getMessage();
        }

        //return response
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * Update product stock level using ajax (single product commit)
     * @throws Exception
     */
    public function commitProductStockAction() {
        $response = array();
        $response['error'] = false;
        $response['message'] = '';

        try {
            $productId = $this->getRequest()->getParam('product_id');
            $stock = $this->getRequest()->getParam('new_stock_value');

            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product)
                throw new Exception($this->__('Unable to load product #%s', $productId));

            $product->getStockItem()->setQty($stock)->save();

            $response['message'] = $this->__('Stock level updated !');
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['message'] = $ex->getMessage();
        }

        //return response
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * Mass stock level save action
     */
    public function massSaveAction() {
        $changes = $this->getRequest()->getPost('changes');
        $changes = explode(';', $changes);
        $count = 0;
        foreach ($changes as $item) {
            $t = explode('=', $item);
            if (count($t) == 2) {
                list($productId, $newStockLevel) = $t;
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $stockItem->setQty($newStockLevel)->save();
                $count++;
            }
        }

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('New stocks applied'));
        $this->_redirect('*/*/index');
    }

    /**
     * Return image url for product
     * @param <type> $product
     * @return <type>
     */
    protected function getImageUrl($product) {
        if ($product->getSmallImage()) {
            return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
        } else {
            //try to find image from configurable product
            $configurableProduct = Mage::helper('SimpleBarcodeInventory/ConfigurableAttributes')->getConfigurableProduct($product);
            if ($configurableProduct) {
                if ($configurableProduct->getSmallImage()) {
                    return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $configurableProduct->getSmallImage();
                }
            }
        }

        return '';
    }

}
