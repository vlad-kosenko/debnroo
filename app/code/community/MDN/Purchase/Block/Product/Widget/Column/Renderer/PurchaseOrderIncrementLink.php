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
class MDN_Purchase_Block_Product_Widget_Column_Renderer_PurchaseOrderIncrementLink
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$url = $this->getUrl('Purchase/Orders/Edit', array('po_num' => $row->getpop_order_num()));
    	$retour = '<a href="'.$url.'">'.$row->getpo_order_id().'</a>';
    	
    	//retourne
        return $retour;
    }
}