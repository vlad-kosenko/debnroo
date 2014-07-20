<?php

class MDN_Purchase_Block_SupplyNeeds_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    private $_mode = null;
    private $_orderId = null;

    public function __construct() {
        parent::__construct();
        $this->setId('SupplyNeedsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('supply_needs');
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));

        $this->setDefaultSort('status', 'asc');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setUseAjax(true);
    }

    public function setMode($mode, $orderId) {
        $this->_mode = $mode;
        $this->_orderId = $orderId;
    }

    /**
     *
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $warehouseId = Mage::helper('purchase/SupplyNeeds')->getCurrentWarehouse();
        if (!$warehouseId) {
            $collection = Mage::getModel('Purchase/SupplyNeeds')
                    ->getCollection();
        } else {
            $collection = Mage::getModel('Purchase/SupplyNeedsWarehouse')
                    ->getCollection()
                    ->addFieldToFilter('stock_id', $warehouseId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('manufacturer_id', array(
            'header' => Mage::helper('purchase')->__('Manufacturer'),
            'index' => 'manufacturer_id',
            'type' => 'options',
            'options' => $this->getManufacturersAsArray(),
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'index' => 'sku'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('purchase')->__('Name'),
            'index' => 'name'
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this, 'product_id');

        $this->addColumn('status', array(
            'header' => Mage::helper('purchase')->__('Status'),
            'index' => 'status',
            'align' => 'center',
            'type' => 'options',
            'options' => mage::getModel('Purchase/SupplyNeeds')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supply_needs/display_details_column'))
        {
            $this->addColumn('sn_details', array(
                'header' => Mage::helper('purchase')->__('Details'),
                'index' => 'sn_details',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'product_id_field_name' => 'product_id',
                'product_name_field_name' => 'name'
            ));
        }

        $this->addColumn('sn_needed_qty', array(
            'header' => Mage::helper('purchase')->__('Qty'),
            'index' => 'qty_min',
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_NeededQty',
            'filter' => false
        ));

        $this->addColumn('qty_for_po', array(
            'header' => Mage::helper('purchase')->__('Qty for PO'),
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_QtyForPo',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('waiting_for_delivery_qty', array(
            'header' => Mage::helper('purchase')->__('Waiting for<br>delivery'),
            'index' => 'waiting_for_delivery_qty',
            'type' => 'number'
        ));

        $this->addColumn('sn_suppliers_name', array(
            'header' => Mage::helper('purchase')->__('Suppliers'),
            'index' => 'product_id',
            'filter' => 'Purchase/Widget_Column_Filter_SupplyNeeds_Suppliers',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_Suppliers',
            'sortable' => false
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('purchase')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getproduct_id',
            'actions' => array(
                array(
                    'caption' => Mage::helper('purchase')->__('View'),
                    'url' => array('base' => 'AdvancedStock/Products/Edit'),
                    'field' => 'product_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Url to refresh grid with ajax
     */
    public function getGridUrl() {
        return $this->getUrl('Purchase/SupplyNeeds/AjaxGrid', array('_current' => true, 'po_num' => $this->_orderId, 'mode' => $this->_mode));
    }

    /**
     * Return suppliers list as array
     *
     */
    public function getSuppliersAsArray() {
        $retour = array();

        //charge la liste des pays
        $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');
        foreach ($collection as $item) {
            $retour[$item->getsup_id()] = $item->getsup_name();
        }
        return $retour;
    }

    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray() {
        $retour = array();

        $manufacturerAttributeId = Mage::getStoreConfig('purchase/supplyneeds/manufacturer_attribute');

        //get manufacturers
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('main_table.attribute_id', $manufacturerAttributeId)
                ->load(false);
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        //ajoute au menu
        foreach ($manufacturers as $manufacturer) {
            $retour[$manufacturer['value']] = $manufacturer['label'];
        }

        return $retour;
    }

    /**
     * 
     */
    public function getWarehouses() {
        $collection = Mage::getModel('AdvancedStock/Warehouse')
                ->getCollection()
                ->addFieldToFilter('stock_disable_supply_needs', 0);
        return $collection;
    }

    /**
     * Return current warehouse
     * @return type 
     */
    public function getCurrentWarehouse() {
        return Mage::helper('purchase/SupplyNeeds')->getCurrentWarehouse();
    }

}
