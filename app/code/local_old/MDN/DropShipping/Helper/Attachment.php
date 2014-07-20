<?php

class MDN_DropShipping_Helper_Attachment extends Mage_Core_Helper_Abstract {

    /**
     * Return possible attachment types
     */
    public function getAttachmentTypes() {
        $types = array();

        $types['pdf'] = $this->__('Pdf');
        $types['simple_xml'] = $this->__('Simple XML');
        $types['simple_xml'] = $this->__('CSV');
        $types['nothing'] = $this->__('Nothing');

        $types['utm'] = $this->__('UTM');
        $types['palmer'] = $this->__('Palmer');
        //$types['lazrart'] = $this->__('Lazrart');

        return $types;
    }

    /**
     * Return attachment based on the type
     * @param type $type
     * @param type $po
     * @param type $order
     */
    public function getAttachment($type, $po, $order) {

        switch ($type) {
            case 'simple_xml':

                //simple xml content
                $fakeShipment = $this->createFakeShipments($order, $po);
                $xml = Mage::getModel('DropShipping/ExportType_SimpleXml')->getContent(array($fakeShipment));
                $Attachment = array();
                $Attachment['name'] = mage::helper('purchase')->__('Order #%s', $order->getincrement_id()) . '.xml';
                $Attachment['content'] = $xml;
                return $Attachment;

                break;
            case 'pdf':

                //Fake Packing slip
                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($fakeShipment));
                $Attachment = array();
                $Attachment['name'] = mage::helper('purchase')->__('Packing slips #%s', $order->getincrement_id()) . '.pdf';
                $Attachment['content'] = $pdf->render();
                return $Attachment;

            case 'csv':

                //csv content
                $fakeShipment = $this->createFakeShipments($order, $po);
                $xml = Mage::getModel('DropShipping/ExportType_Csv')->getContent(array($fakeShipment));
                $Attachment = array();
                $Attachment['name'] = mage::helper('purchase')->__('Order #%s', $order->getincrement_id()) . '.csv';
                $Attachment['content'] = $xml;
                return $Attachment;

                break;

            case 'utm':
                
                $csv = '';
                
                $fakeShipment = $this->createFakeShipments($order, $po);
                foreach($fakeShipment->getAllItems() as $item)
                {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    
                    $line = '13664,';
                    $line .= $order->getincrement_id().',';
                    $line .= $order->getCustomerName().',';
                    $line .= $order->getShippingAddress()->getStreet(1).',';
                    $line .= $order->getShippingAddress()->getStreet(2).',';
                    $line .= $order->getShippingAddress()->getCity().',';
                    $line .= $order->getShippingAddress()->getRegion().',';
                    $line .= $order->getShippingAddress()->getPostcode().',';
                    $line .= $order->getShippingAddress()->getCountry().',';
                    $line .= 'debnroo@gmail.com,';
                    $line .= '970-416-6300,';
                    $line .= $product->getmpn();
                    $line .= $item->getQty().",";
                    
                    $line .= $product->getCost().",";
                    $line .= $order->getShippingDescription();
                    
                    $csv .= $line."\n";
                }
                
                $Attachment = array();
                $Attachment['name'] = mage::helper('purchase')->__('Order #%s', $order->getincrement_id()) . '.csv';
                $Attachment['content'] = $csv;
                return $Attachment;
                
                break;

            case 'palmer':

                $csv = '';

                $line = "";

                $line .= "Order".",";
                $line .= "140".",";
                $line .= '"Deb Mowery"'.",";
                $line .= '"'.$order->getCustomerName().'"'.',';
                $line .= '"'.implode(' ', $order->getShippingAddress()->getStreet()).'"'.',';
                $line .= '"'.$order->getShippingAddress()->getCity().'"'.',';
                $line .= '"'.$order->getShippingAddress()->getRegion().'"'.',';
                $line .= '"'.$order->getShippingAddress()->getPostcode().'"'.',';
                $line .= '"'.$order->getShippingAddress()->getCountry().'"'.',';
                $line .= '"'.$order->getShippingDescription().'"'.',';
                $line .= $order->getincrement_id();
                $csv .= $line."\n";

                $fakeShipment = $this->createFakeShipments($order, $po);
                foreach($fakeShipment->getAllItems() as $item)
                {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());

                    $line = "Item".",";
                    $line .= $product->getSku().",";
                    $line .= $item->getQty();
                    
                    $csv .= $line."\n";
                }
                
                $Attachment = array();
                $Attachment['name'] = mage::helper('purchase')->__('Order #%s', $order->getincrement_id()) . '.csv';
                $Attachment['content'] = $csv;
                return $Attachment;
                
                break;

            case 'lazrart':

                break;

            case 'nothing':
                //nothing
                break;
        }
    }


    /**
     * Create fake shipments
     * @param type $orders
     */
    protected function createFakeShipments($order, $po) {


        $convertor = Mage::getModel('sales/convert_order');
        $shipment = $convertor->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->isDummy(true) && !$orderItem->getQtyToShip()) {
                continue;
            }
            if ($orderItem->getIsVirtual()) {
                continue;
            }

            foreach($po->GetProducts() as $pop)
            {
                if ($pop->getpop_product_id() == $orderItem->getproduct_id())
                {
                    $ShipmentItem = $convertor->itemToShipmentItem($orderItem);
                    $ShipmentItem->setQty($orderItem->getqty_ordered());
                    $shipment->addItem($ShipmentItem);
                }
            }
        }

        $shipment->setOrder($order);

        return $shipment;
    }
    
}