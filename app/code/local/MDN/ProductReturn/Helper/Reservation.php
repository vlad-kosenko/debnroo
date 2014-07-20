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
class MDN_ProductReturn_Helper_Reservation extends Mage_Core_Helper_Abstract
{
	
	/**
	 * Reserve product for RMA
	 *
	 * @param unknown_type $productId
	 * @param unknown_type $qty
	 * @param unknown_type $rma
	 */
	public function reserveProduct($rma, $productId, $qty)
	{
		//load datas
		$product = mage::getModel('catalog/product')->load($productId);
		$stock = $product->getStockItem();

		//check if qty is available
		if (!($stock->getqty() >= $qty))
			throw new Exception($this->__('Stock level too low for reservation'));
		
		//decrease product stock
		$stock->setqty($stock->getqty() - $qty)->save();
		
	}
	
	/**
	 * Release product for RMA
	 *
	 * @param unknown_type $productId
	 * @param unknown_type $qty
	 * @param unknown_type $rma
	 */
	public function releaseProduct($rma, $productId, $qty)
	{
		//increase product stock
		$product = mage::getModel('catalog/product')->load($productId);
		$stock = $product->getStockItem();
		
		$stock->setqty($stock->getqty() + $qty)->save();
		
	}
	
	/**
	 * Affect reserved products to created order
	 *
	 * @param unknown_type $order
	 */
	public function affectProductsToCreatedOrder($rma, $order)
	{
		//release products
		foreach ($rma->getReservations() as $product)
		{
			$this->releaseProduct($rma, $product->getrr_product_id(), $product->getrr_qty());
		}
		
	}
}