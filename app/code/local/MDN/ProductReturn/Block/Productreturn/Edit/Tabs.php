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
 * Order view tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Productreturn_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productreturn_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ProductReturn')->__('Product Return Edit'));

    }

    protected function _beforeToHtml()
    {
    	$rmaId = mage::app()->getRequest()->getParam('rma_id');
    	
    	$this->addTab('main', array(
            'label'     => Mage::helper('ProductReturn')->__('Main'),
            'content'   =>  $this->getLayout()->createBlock('ProductReturn/Productreturn_Edit_Tab_Info')->setTemplate('ProductReturn/Tab/Info.phtml')->toHtml(),
    		'active'    => true
    	));
        
    	if ($rmaId)
    	{
			$this->addTab('history', array(
	            'label'     => Mage::helper('ProductReturn')->__('History'),
	            'content'   =>  $this->getLayout()->createBlock('ProductReturn/Productreturn_Edit_Tab_History')->setTemplate('ProductReturn/Tab/History.phtml')->toHtml(),
	        ));

			$this->addTab('reservation', array(
	            'label'     => Mage::helper('ProductReturn')->__('Products reservation'),
	            'content'   =>  $this->getLayout()->createBlock('ProductReturn/Productreturn_Edit_Tab_Reservation')->setTemplate('ProductReturn/Tab/Reservation.phtml')->toHtml(),
	        ));
	        
	        $this->addTab('action', array(
	            'label'     => Mage::helper('ProductReturn')->__('Process products'),
	            'content'   =>  $this->getLayout()->createBlock('ProductReturn/Productreturn_Edit_Tab_Actions')->setTemplate('ProductReturn/Tab/Actions.phtml')->toHtml(),
	        ));

	        //raise event to allow other extension to add tabs
	        Mage::dispatchEvent('productreturn_edit_create_tabs', array('tab' => $this, 'rma' => $this->getRma(), 'layout' => $this->getLayout()));
    	}
    	   
		//select tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
        	$defaultTab = 'main';
        $this->setActiveTab($defaultTab);
    	    
    	return parent::_beforeToHtml();
    }
    
    public function getRma()
	{
		return mage::registry('current_rma');
	}
    
	
}
