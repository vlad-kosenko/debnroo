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
class MDN_Orderpreparation_Helper_Shipment extends Mage_Core_Helper_Abstract {
    /*
     * Create shipment
     *
     */

    public function CreateShipment(&$order, $warehouseId = null, $operatorId = null) {
        try {
            $convertor = Mage::getModel('sales/convert_order');
            $shipment = $convertor->toShipment($order);

            Mage::dispatchEvent('orderpreparartion_before_create_shipment', array('order' => $order));

            //browse order items
            $items = $this->GetItemsToShipAsArray($order->getid(), $warehouseId, $operatorId);
            foreach ($order->getAllItems() as $orderItem) {
                //skip les cas sp�ciaux
                if (!$orderItem->isDummy(true) && !$orderItem->getQtyToShip()) {
                    continue;
                }
                if ($orderItem->getIsVirtual()) {
                    continue;
                }

                //add product to shipment
                if (isset($items[$orderItem->getitem_id()])) {
                    $ShipmentItem = $convertor->itemToShipmentItem($orderItem);
                    $ShipmentItem->setQty($items[$orderItem->getitem_id()]);
                    $shipment->addItem($ShipmentItem);
                }
            }

            //save shipmeent
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

            //save shipment id in order_to_prepare item
            $this->StoreShipmentId($order->getid(), $shipment->getincrement_id(), $warehouseId, $operatorId);

            return $shipment;
            
            Mage::dispatchEvent('orderpreparartion_after_create_shipment', array('order' => $order, 'shipment' => $shipment));
        } catch (Exception $ex) {
            Mage::logException($ex);
            throw new Exception('Error while creating Shipment for Order ' . $order->getincrement_id() . ': ' . $ex->getMessage());
        }
        
        return null;
    }

    /*
     * Get items to ship for order id
     *
     * @param unknown_type $OrderId
     */

    public function GetItemsToShipAsArray($OrderId, $warehouseId = null, $operatorId = null) {
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);

        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $retour = array();
        foreach ($collection as $item) {
            $retour[$item->getorder_item_id()] = $item->getqty();
        }

        return $retour;
    }

    /*
     * Store shipment id in ordertoprepare model
     *
     * @param unknown_type $OrderId
     */

    public function StoreShipmentId($OrderId, $ShipmentId, $warehouseId = null, $operatorId = null) {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);
        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $orderToPrepare = $collection->getFirstItem();
        $orderToPrepare->setshipment_id($ShipmentId)->save();
    }

    /*
     * Check if shipment is created for one order
     *
     */

    public function ShipmentCreatedForOrder($OrderId, $warehouseId = null, $operatorId = null) {
        $collection = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $OrderId);
        if ($warehouseId)
            $collection->addFieldToFilter('preparation_warehouse', $warehouseId);
        if ($operatorId)
            $collection->addFieldToFilter('user', $operatorId);

        $orderToPrepare = $collection->getFirstItem();

        if ($orderToPrepare->getshipment_id() != 0)
            return true;
        else
            return false;
    }

}