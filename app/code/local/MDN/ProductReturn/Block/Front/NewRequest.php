<?php

class MDN_ProductReturn_Block_Front_NewRequest extends Mage_Core_Block_Template
{
	private $_salesOrder = null;
	private $_rma = null;
	
	/**
	 * Return selected sales order
	 *
	 * @return unknown
	 */
	public function getSalesOrder()
	{
		if ($this->_salesOrder == null)
		{
			$salesOrderId = $this->getRequest()->getParam('order_id');
			$this->_salesOrder = mage::getModel('sales/order')->load($salesOrderId);
		}
		return $this->_salesOrder;
	}
	
	/**
	 * Return Rma being created
	 *
	 */
	public function getRma()
	{
		if ($this->_rma == null)
		{
			$this->_rma = mage::getModel('ProductReturn/Rma');
			$this->_rma->setrma_order_id($this->getSalesOrder()->getId());
			$this->_rma->setrma_customer_id($this->getCustomer()->getId());
			$this->_rma->setrma_customer_name($this->getCustomer()->getName());
			$this->_rma->setrma_customer_phone($this->getSalesOrder()->getShippingAddress()->gettelephone());
			$this->_rma->setrma_customer_email($this->getCustomer()->getemail());
			$this->_rma->setrma_ref(mage::helper('ProductReturn')->createRmaReference($this->_rma));
		}
		return $this->_rma;
	}
	
	/**
	 * return current customer
	 *
	 */
	public function getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}
	
	/**
	 * Customer addresses as combo
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getCustomerAddressesAsCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$addresses = $this->getCustomer()->getAddresses();
		foreach($addresses as $address)
		{
			$selected = '';
			if ($value == $address->getId())
				$selected = ' selected="selected" ';
			$retour .= '<option value="'.$address->getId().'" '.$selected.'>'.$address->getFormated().'</option>';
		}
	
		$retour .= '</select>';
		return $retour;	
	}

	/**
	 * Return combo box to select return reason for product
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function getReasonSelect($name)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$reasons = mage::getModel('ProductReturn/RmaProducts')->getReasons();
		
		foreach($reasons as $key => $label)
		{
			$selected = '';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
		}
	
		$retour .= '</select>';
		return $retour;	
		
	}
	
	/**
	 * Return combo box to select qty to return
	 *
	 * @param unknown_type $name
	 * @param unknown_type $max
	 * @return unknown
	 */
	public function getQtySelect($name, $max)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		for ($i=0;$i<=$max;$i++)
		{
			$retour .= '<option value="'.$i.'">'.$i.'</option>';			
		}
		$retour .= '</select>';
		return $retour;	
	}
	
	/**
	 * Define if a product can be displayed in product return form
	 *
	 * @param unknown_type $product
	 */
	public function displayProduct($product)
	{
		return mage::getModel('ProductReturn/RmaProducts')->productIsDisplayed($product);
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function getProductName($product)
	{
		return mage::getModel('ProductReturn/RmaProducts')->getProductName($product);
	}

	
	/**
	 * URLSSSS
	 *
	 * @return unknown
	 */
	public function getReturnUrl()
	{
		return $this->getUrl('ProductReturn/Front/List');
	}	
	public function getSubmitUrl()
	{
		return $this->getUrl('ProductReturn/Front/SubmitRequest');
	}
	
}
