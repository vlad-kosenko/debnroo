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
class MDN_ProductReturn_Block_Productreturn_Edit_Tab_History
    extends Mage_Adminhtml_Block_Widget_Form
{
	private $_rma = null;
	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ProductReturn/Tab/History.phtml');
		
    }

	public function getRmahistory()
	{
		$collection = mage::getModel('ProductReturn/RmaHistory')->loadByRma($this->getRma()->getId());
		
		return $collection;
	}
	
	public function getRma()
	{
		return mage::registry('current_rma');
	}


}
