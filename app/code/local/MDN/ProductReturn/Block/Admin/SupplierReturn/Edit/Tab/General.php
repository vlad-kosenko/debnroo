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
class MDN_ProductReturn_Block_Admin_SupplierReturn_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ProductReturn/Tab/Info.phtml');
        
    			
    }

    public function getRsr(){
        return mage::registry('current_rsr');
    }
    
    
    public function getStatusesAsCombo($name, $value)
    {
	$retour = '<select name="'.$name.'" id="'.$name.'">';
	$statuses = $this->getRsr()->getReturnStatuses();
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
}
