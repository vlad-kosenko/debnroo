<?php

class MDN_BarcodeLabel_AdminController extends Mage_Adminhtml_Controller_Action {

    /**
     * Generate barcodes for all products
     * except barcode already existing 
     */
    public function GenerateForAllProductsAction() {
        
        try {
            Mage::helper('BarcodeLabel/Generation')->generateForAllProducts();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Barcodes generated'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
        }

        //confirm & redirect
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'barcodelabel'));
    }

    /**
     * Return label preview
     */
    public function LabelPreviewAction() {
        //get datas
        $productId = $this->getRequest()->getParam('product_id');
        $img = Mage::helper('BarcodeLabel/Label')->getImage($productId);
        
        //return image
        header('Content-type: image/gif');
        imagegif($img);
        die();
    }

    /**
     * Print labels for 1 product
     */
    public function PrintProductLabelsAction() {

        //Get data
        $productId = $this->getRequest()->getParam('product_id');
        $count = $this->getRequest()->getParam('count');
        $param = array($productId => $count);

        //create PDF
        $pdfModel = Mage::getModel('BarcodeLabel/Pdf_Labels');
        $pdf = $pdfModel->getPdf($param);
        $this->_prepareDownloadResponse(mage::helper('BarcodeLabel')->__('Barcode labels') . '.pdf', $pdf->render(), 'application/pdf');
    }

    /**
     * Print labels for all children products of a configurable product
     */
    public function PrintChildrenProductLabelsAction() {

        // array that contain id of children with stock  
        $arrayStockChildren = array();

        // get the id of config product
        $productId = $this->getRequest()->getParam('product_id');

        // get the ids of childrens products
        $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);

        foreach ($childrenIds[0] as $keyChildrenId => $valueChildrenId) {

            // get the stock of each children products
            $qtyStock = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($valueChildrenId)->getQty();

            // formatting an array
            $arrayStockChildren[$valueChildrenId] = $qtyStock;
        }


        //create PDF
        $pdfModel = Mage::getModel('BarcodeLabel/Pdf_Labels');
        $pdf = $pdfModel->getPdf($arrayStockChildren);
        $this->_prepareDownloadResponse(mage::helper('BarcodeLabel')->__('Barcode labels') . '.pdf', $pdf->render(), 'application/pdf');
    }

    /**
     * mass action on product for printing labels 
     */
    public function printSelectedProductLabelAction() {

        // getting id of each product selected
        $productIds = $this->getRequest()->getPost('product');

        // array that contain id of children with stock  
        $arrayStockChildren = array();

        foreach ($productIds as $productId) {

            $productType = Mage::getModel('catalog/product')->load($productId)->getTypeId();
            if ($productType != 'configurable') {
                $qtyStock = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId)->getQty();
                $arrayStockChildren[$productId] = $qtyStock;
            } else {

                // if selected product is a parent id, then find childrens id
                $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);
                foreach ($childrenIds[0] as $valueChildrenId) {
                    $qtyStock = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($valueChildrenId)->getQty();
                    $arrayStockChildren[$valueChildrenId] = $qtyStock;
                }
            }

        }

        //create PDF
        $pdfModel = Mage::getModel('BarcodeLabel/Pdf_Labels');
        $pdf = $pdfModel->getPdf($arrayStockChildren);
        $this->_prepareDownloadResponse(mage::helper('BarcodeLabel')->__('Barcode labels') . '.pdf', $pdf->render(), 'application/pdf');
    }

}