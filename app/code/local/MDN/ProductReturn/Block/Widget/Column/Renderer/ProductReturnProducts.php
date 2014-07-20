<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnProducts
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '';
    	$products = $row->getProducts();
    	foreach($products as $product)
    	{
    		if ($product->getrp_qty() > 0)
    			$retour .= $product->getrp_qty().'x '.$product->getrp_product_name().'<br>';
    	}
    	return $retour;
    }
}