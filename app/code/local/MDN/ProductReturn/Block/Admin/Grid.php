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
class MDN_ProductReturn_Block_Admin_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
        $this->setDefaultSort('rma_created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		
        $collection = Mage::getModel('ProductReturn/Rma')
        	->getCollection()
            ->join('sales/order', 'rma_order_id=entity_id')
        	;
        	
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
     
        //raise event to allow other modules to add columns
        Mage::dispatchEvent('productreturn_grid_preparecolumns', array('grid'=>$this));
        
        $this->addColumn('rma_ref', array(
            'header'=> Mage::helper('ProductReturn')->__('Ref'),
            'index' => 'rma_ref',
            'width' => '100px'
        ));
       
        $this->addColumn('rma_created_at', array(
            'header'=> Mage::helper('ProductReturn')->__('Date'),
            'index' => 'rma_created_at',
            'type'	=> 'date'
        ));

        $this->addColumn('rma_customer_name', array(
            'header'=> Mage::helper('ProductReturn')->__('Customer'),
            'index' => 'rma_customer_name',
            'renderer'  => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnCustomerName'
        ));

        $this->addColumn('rma_status', array(
            'header'=> Mage::helper('ProductReturn')->__('Status'),
            'index' => 'rma_status',
            'type' => 'options',
            'options' => mage::getModel('ProductReturn/Rma')->getStatuses(),
        ));

        $this->addColumn('rma_products', array(
            'header'=> Mage::helper('ProductReturn')->__('Products'),
            'index' => 'rma_products',
            'renderer'  => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnProducts',
            'filter' => false,
            'sortable' => false
        ));
        
        $this->addColumn('rma_reception_date', array(
            'header'=> Mage::helper('ProductReturn')->__('Reception Date'),
            'index' => 'rma_reception_date',
            'type'	=> 'date'
        ));
        
        $this->addColumn('comments', array(
            'header'=> Mage::helper('ProductReturn')->__('Comments'),
            'renderer'  => 'MDN_ProductReturn_Block_Widget_Column_Renderer_Comments'
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'ProductReturn/Admin/Edit'),
                        'field'   => 'rma_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));
        
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('ProductReturn/Admin/Edit', array('rma_id' => $row->getId()));
    }
    
    
}
