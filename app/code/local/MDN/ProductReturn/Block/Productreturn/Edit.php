<?php

/**
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Productreturn_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    private $_rma = null;

    /**
     * Define buttons
     *
     */
    public function __construct() {

        $this->_objectId = 'id';
        $this->_controller = 'Productreturn'; //nom du controller
        $this->_blockGroup = 'ProductReturn'; //nom du module
        //init rma object
        $this->getRma();

        parent::__construct();


        $this->_updateButton('save', 'label', Mage::helper('ProductReturn')->__('Save'));
        $this->_updateButton('save', 'onclick', 'validateProductReturnForm()');
        $this->_removeButton('delete');

        if ($this->getRma()->getId()) {
            $this->_addButton(
                    'print',
                    array(
                        'label' => Mage::helper('ProductReturn')->__('Print'),
                        'onclick' => "window.location.href='" . $this->getUrl('ProductReturn/Admin/Print', array('rma_id' => $this->getRma()->getId())) . "'"
                    )
            );

            $this->_addButton(
                    'delete',
                    array(
                        'label' => Mage::helper('ProductReturn')->__('Delete'),
                        'class' => 'delete',
                        'onclick' => "if (confirm('" . $this->__('Sure ?') . "')) { window.location.href='" . $this->getUrl('ProductReturn/Admin/Delete', array('rma_id' => $this->getRma()->getId())) . "' }"
                    )
            );

            $this->_addButton(
                    'products_received',
                    array(
                        'label' => Mage::helper('ProductReturn')->__('Products received'),
                        'onclick' => "window.location.href='" . $this->getUrl('ProductReturn/Admin/ProductsReceived', array('rma_id' => $this->getRma()->getId())) . "'"
                    )
            );

            if ($this->canNotifyCustomer()) {
                $this->_addButton(
                        'notify',
                        array(
                            'label' => Mage::helper('ProductReturn')->__('Notify Customer'),
                            'onclick' => "window.location.href='" . $this->getUrl('ProductReturn/Admin/Notify', array('rma_id' => $this->getRma()->getId())) . "'"
                        )
                );
            }
        }
    }

    /**
     * main title
     *
     * @return unknown
     */
    public function getHeaderText() {
        if ($this->getRma()->getId())
            return Mage::helper('ProductReturn')->__('Product Return Edit');
        else
            return Mage::helper('ProductReturn')->__('New Product Return');
    }

    /**
     * return rma object
     *
     * @return unknown
     */
    public function getRma() {
        if ($this->_rma == null) {
            $rmaId = Mage::app()->getRequest()->getParam('rma_id', false);
            $model = Mage::getModel('ProductReturn/Rma');

            //set default values if creation mode
            if ($rmaId == null) {
                $order = mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id', false));
                $this->_rma = $model;
                $this->_rma->setrma_order_id($order->getId());
                $this->_rma->setrma_customer_id($order->getcustomer_id());
                $this->_rma->setrma_customer_name($this->getRma()->getCustomer()->getName());
                $this->_rma->setrma_customer_phone($order->getShippingAddress()->gettelephone());
                $this->_rma->setrma_customer_email($this->getRma()->getCustomer()->getemail());
                $this->_rma->setrma_ref(mage::helper('ProductReturn')->createRmaReference($this->getRma()));

                //select order address
                $this->_rma->setrma_address_id($order->getshipping_address_id());
            } else {
                $this->_rma = $model->load($rmaId);
            }

            mage::register('current_rma', $this->_rma);
        }
        return $this->_rma;
    }

    /**
     * return save url
     *
     * @return unknown
     */
    public function getSaveUrl() {
        return $this->getUrl('ProductReturn/Admin/Save');
    }

    /**
     * return back url
     *
     * @return unknown
     */
    public function GetBackUrl() {
        return $this->getUrl('ProductReturn/Admin/Grid');
    }

    public function canNotifyCustomer() {
        return (($this->getRma()->getrma_status() != MDN_ProductReturn_Model_Rma::kStatusNew) && ($this->getRma()->getrma_status() != MDN_ProductReturn_Model_Rma::kStatusRequested));
    }

}
