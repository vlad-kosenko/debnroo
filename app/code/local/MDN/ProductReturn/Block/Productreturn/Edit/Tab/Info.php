<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order history tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Productreturn_Edit_Tab_Info
    extends Mage_Adminhtml_Block_Widget_Form
{
	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ProductReturn/Tab/Info.phtml');
        
    			
    }

	public function getRma()
	{
		return mage::registry('current_rma');
	}
	
	public function getSalesOrderUrl()
	{
		return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $this->getRma()->getSalesOrder()->getId()));
	}

	public function getCustomerUrl()
	{
		return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getRma()->getCustomer()->getId()));
	}
	
	public function getCustomerAddressesAsCombo($name, $value)
	{
		if(!$this->getRma()->IamGuest())
		{
			$retour = '<select name="'.$name.'" id="'.$name.'">';
			$addresses= $this->getRma()->getCustomer()->getAddresses();
			foreach($addresses as $address)
			{
				$selected = '';
				if ($value == $address->getId())
					$selected = ' selected="selected" ';
				$retour .= '<option value="'.$address->getId().'" '.$selected.'>'.$address->getFormated().'</option>';
			}
		
			$retour .= '</select>';
		}
		
		return $retour;	
	}
	

	
	public function getStatusesAsCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$statuses = $this->getRma()->getStatuses();
		foreach($statuses as $key => $label)
		{
			$selected = '';
			if ($value == $key)
				$selected = ' selected="selected" ';
		
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
		}
	
		$retour .= '</select>';
		return $retour;	
		
	}

		public function getRmahistory()
	{
		$collection = mage::getModel('ProductReturn/RmaHistory')->loadByRma($this->getRma()->getId());
		
		return $collection;
	}
	
	
	public function getReasonsAsCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$reasons = $this->getRma()->getReasons();
		
		foreach($reasons as $key => $label)
		{
			$selected = '';
			if ($value == $key)
				$selected = ' selected="selected" ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
		}
	
		$retour .= '</select>';
		return $retour;	
		
	}
	
	public function getCarriersAsCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
    	$carriers = Mage::getStoreConfig('carriers', 0);
    	$retour .= '<option value="" ></option>';
		
		if(empty($value))
			$value = Mage::getStoreConfig('productreturn/product_return/default_shipment');
			
		foreach($carriers as $item)
		{
			$selected = '';
			$instance = mage::getModel($item['model']);
			$code = $item['model'];			
			if ($value == $code)
				$selected = ' selected="selected" ';
			$retour .= '<option value="'.$code.'" '.$selected.'>'.$instance->getConfigData('title').'</option>';
		}
		$retour .= '</select>';
		return $retour;	
		
	}
	
	public function showBtnNewOrder(){
		
		if($this->getRma()->getrma_status() == 'rma_accepted')
			return true;
		else
			return false;
	}
	

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function canNotifyCustomer()
	{
		return (($this->getRma()->getrma_status() != MDN_ProductReturn_Model_Rma::kStatusNew) && ($this->getRma()->getrma_status() != MDN_ProductReturn_Model_Rma::kStatusRequested));
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function getProductReasonCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$reasons = mage::getModel('ProductReturn/RmaProducts')->getReasons();
		
		foreach($reasons as $key => $label)
		{
			$selected = '';
			if ($value == $key)
				$selected = ' selected="selected" ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
		}
	
		$retour .= '</select>';
		return $retour;	
	}

	public function getAvailableQty($product)
	{
		return  (int)($product->getqty_invoiced() - $product->getqty_refunded() - $product->getqty_canceled());
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function hasBeenProcessed($product)
	{
		return ($product->getrp_action_processed() == 1);
	}
	
	/**
	 * Return true if we are creating a new product return
	 *
	 * @return unknown
	 */
	public function isCreationMode()
	{
		return (Mage::app()->getRequest()->getParam('rma_id') == '');
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
	 * Enter description here...
	 *
	 * @param unknown_type $product
	 */
	public function getProductUrl($product)
	{
		return $this->getUrl('AdvancedStock/Products/Edit', array('product_id' => $product->getrp_product_id()));
	}
	
	/**
	 * Add js translation
	 *
	 * @param unknown_type $text
	 */
	public function addJsTranslation($text)
	{
		$html = "Translator.add('".$text."', '".$this->__($text)."');";
		return $html;
	}

	/**
	 * Define if product reservation is enabled
	 *
	 * @return unknown
	 */
	public function productReservationEnabled()
	{
		return ($this->getRma()->getId() > 0);
	}

	/**
	 * Return reserved
	 *
	 * @param unknown_type $rpId
	 */
	public function getReservedQty($rpId)
	{
		die('to implement : add reserved_qty column in rma');
	}
}
