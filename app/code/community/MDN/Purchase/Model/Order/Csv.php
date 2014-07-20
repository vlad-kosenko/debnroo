<?php

/**
 * Export purchase order to csv format
 *
 */
class MDN_Purchase_Model_Order_Csv  extends Mage_Core_Model_Abstract
{
    private $_order;
    private $_endLine = "\r\n";

    /**
     * Set purchase order
     * @param <type> $order
     * @return MDN_Purchase_Model_Order_Csv
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Return file name
     * @return <type>
     */
    public function getFileName()
    {
        return mage::helper('purchase')->__('Purchase Order #%s from %s', $this->_order->getpo_order_id(), $this->_order->getSupplier()->getsup_name()).'.csv';
    }

    /**
     * return content type
     */
    public function getContentType()
    {
        return 'text/csv';
    }

    /**
     * Return csv content
     */
    public function getCsv()
    {
        // get customer group name related to this purchase order.
        $purchaseOrderId = $this->_order->getpo_num();
        $orderItems = Mage::helper('DropShipping/Order')->getAssociatedOrderItems($purchaseOrderId);
        foreach($orderItems as $orderItem)
        {
            $salesOrder = $orderItem->getOrder();
            $salesOrder = Mage::getModel('sales/order')->load($salesOrder->getId());
            $customerGroupId = $salesOrder->getCustomerGroupId();
            $groupname = Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
        }

        if (trim(strtolower($groupname)) == 'amazon buyers') {
            $template = 'sku;supplier_sku;name;qty;unit_price;discount;tax_rate;subtotal;total;carrier'.$this->_endLine;
            $csv = $template;
            foreach($this->_order->getProducts() as $product)
            {
                $line = $template;

                $line = str_replace('supplier_sku', $product->getpop_supplier_ref(), $line);
                $line = str_replace('sku', $product->getsku(), $line);
                $line = str_replace('name', $product->getpop_product_name(), $line);
                $line = str_replace('qty', $product->getpop_qty(), $line);
                $line = str_replace('unit_price', $product->getpop_price_ht(), $line);
                $line = str_replace('discount', $product->getpop_discount(), $line);
                $line = str_replace('tax_rate', $product->getpop_tax_rate(), $line);
                $line = str_replace('subtotal', $product->getRowTotal(), $line);
                $line = str_replace('total', $product->getRowTotalWithTaxes(), $line);
            
                $weight = $product->getpop_weight();
                if($weight < 0.8125 ) {
                    $carrier = 'USPS First Class Mail';
                } elseif($weight >= 0.8125 && $weight <= 2.0 ) {
                    $carrier = 'USPS Priority';
                } elseif($weight > 2.0) {
                    $carrier = 'UPS Ground';
                }
                $line = str_replace('carrier', $carrier, $line);

                $csv .= $line;
            }
        } else {
            $template = 'sku;supplier_sku;name;qty;unit_price;discount;tax_rate;subtotal;total'.$this->_endLine;
            $csv = $template;
            foreach($this->_order->getProducts() as $product)
            {
                $line = $template;

                $line = str_replace('supplier_sku', $product->getpop_supplier_ref(), $line);
                $line = str_replace('sku', $product->getsku(), $line);
                $line = str_replace('name', $product->getpop_product_name(), $line);
                $line = str_replace('qty', $product->getpop_qty(), $line);
                $line = str_replace('unit_price', $product->getpop_price_ht(), $line);
                $line = str_replace('discount', $product->getpop_discount(), $line);
                $line = str_replace('tax_rate', $product->getpop_tax_rate(), $line);
                $line = str_replace('subtotal', $product->getRowTotal(), $line);
                $line = str_replace('total', $product->getRowTotalWithTaxes(), $line);
                $csv .= $line;
            }            
        }

        return $csv;
    }

}