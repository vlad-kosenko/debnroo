<?php

class MDN_AdvancedStock_Block_Inventory_Edit_Tabs_ScannedProducts extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('InventoryScannedProducts');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setUseAjax(true);
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $inventory = Mage::registry('current_inventory');
        
        $collection = $inventory->getScannedProducts();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('sku', array(
            'header'=> Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Product'),
            'index' => 'name'
        ));
                
        
        $this->addColumn('shelf_location', array(
            'header'=> Mage::helper('AdvancedStock')->__('Location'),
            'index' => 'shelf_location'
        ));
        
        $this->addColumn('scanned_qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Scanned qty'),
            'index' => 'scanned_qty',
            'type' => 'number'
        ));
        
        $this->addColumn('expected_qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Expected qty'),
            'index' => 'expected_qty',
            'type' => 'number'
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('AdvancedStock')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getentity_id',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('AdvancedStock')->__('View product'),
                        'url'     => array('base'=>'AdvancedStock/Products/Edit'),
                        'field'   => 'product_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));
        
        $inventory = Mage::registry('current_inventory');
        $url = '*/*/exportCsvScannedProducts/ei_id/'.$inventory->getId();
        $this->addExportType($url, Mage::helper('AdvancedStock')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        $inventory = Mage::registry('current_inventory');
        return $this->getUrl('AdvancedStock/Inventory/AjaxScannedProducts', array('ei_id' => $inventory->getId()));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

}
