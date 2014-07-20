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
class MDN_ProductReturn_Helper_CreateOrder extends MDN_ProductReturn_Helper_CreateAbstract {

    /**
     * Create Order reference
     *
     * @param:
     */
    public function CreateOrder($object) {
        $websiteId = $object['website_id'];
        $storeId = $object['store_id'];
        $groupId = $object['customer_group_id'];
        $srcOrder = mage::getModel('sales/order')->load($object['order_id']);

        $customer = null;
        if ($object['customer_id'] != '')
            $customer = mage::getModel('customer/customer')->load($object['customer_id']);
        else {
            if ($object['customer_email'] == '') {
                $customer = mage::getModel('customer/customer')->load($object['customer_id']);
                $customer->setFirstname('');
                $customer->setLastname('');
            }
        }

        //info order
        $new_order = Mage::getModel('sales/order');
        $new_order->reset();

        $new_order->setCustomerIsGuest($object['customer_guest']);

        if ($object['customer_guest'] == 0) {
            $new_order->setcustomer_id($customer->getId());
            $new_order->setCustomerGroupId($customer->getGroupId());
        }

        $new_order->setCustomerFirstname($object['customer_firstname']);
        $new_order->setCustomerLastname($object['customer_lastname']);
        $new_order->setcustomer_prefix($object['customer_prefix']);
        $new_order->setcustomer_middlename($object['customer_middlename']);
        $new_order->setcustomer_taxvat($object['customer_taxvat']);
        $new_order->setcustomer_suffix($object['customer_suffix']);

        //$new_order->setstate($object['state']);
        $new_order->setweight($object['weight']);
        $new_order->setcustomer_note_notify($object['customer_note_notify']);


        $new_order->setCustomerEmail($object['customer_email']);
        $new_order->setcreated_at('now()');
        $new_order->setStore_id($storeId);


        $new_order->setstore_to_base_rate($object['store_to_base_rate']);
        $new_order->setstore_to_order_rate($object['store_to_order_rate']);
        $new_order->setorder_currency_code($object['order_currency_code']);
        $new_order->setbase_currency_code($object['base_currency_code']);
        $new_order->setstore_currency_code($object['store_currency_code']);
        $new_order->setGlobalCurrencyCode($object['global_to_currency_rate']);
        $new_order->setBaseToGlobalRate($object['base_to_global_rate']);
        $new_order->setBaseToOrderRate($object['base_to_order_rate']);

        //flat model vs EAV model :(
        $entity_type_id_adress = null;
        if (mage::helper('ProductReturn/FlatOrder')->ordersUseEavModel())
            $entity_type_id_adress = Mage::getResourceModel("sales/order_address")->getTypeId();

        //shipping address
        $shipping_address = Mage::getModel('sales/order_address');
        foreach ($object['shipping_address'] as $key => $value)
            $shipping_address->setData($key, $value);
        $shipping_address->setentity_type_id($entity_type_id_adress);
        $shipping_address->setId(null);
        $new_order->setShippingAddress($shipping_address);

        //billing address
        $billing_address = Mage::getModel('sales/order_address');
        foreach ($object['billing_address'] as $key => $value)
            $billing_address->setData($key, $value);
        $billing_address->setentity_type_id($entity_type_id_adress);
        $billing_address->setId(null);
        $new_order->setBillingAddress($billing_address);

        //payment method
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod($object['payment_method']);
        $new_order->setPayment($payment);

        //shipping method
        $ShippingAmount = $object['shipping_cost'];
        $ShippingTaxAmount = $object['shipping_taxamount'];
        $new_order->setshipping_method($object['shipping_method']);
        $new_order->setshipping_description($this->getShippingMethodLabel($object['shipping_method']));

        //shipping values
        $new_order->setshipping_amount((double) $ShippingAmount);
        $new_order->setbase_shipping_amount((double) $ShippingAmount);
        $new_order->setshipping_tax_amount((double) $ShippingTaxAmount);
        $new_order->setbase_shipping_tax_amount((double) $ShippingTaxAmount);

        $new_order
                ->setGrandTotal($ShippingAmount + $ShippingTaxAmount)
                ->setBaseGrandTotal($ShippingAmount + $ShippingTaxAmount)
                ->setTaxAmount($ShippingTaxAmount)
                ->setBaseTaxAmount($ShippingTaxAmount);

        //add products
        foreach ($object['products'] as $item) {
            $productId = $item['product_id'];
            $qty = (int) $item['qty'];
            $price = $item['price'];
            $priceHt = $item['price_ht'];
            $taxAmount = $price - $priceHt;

            //Ajoute le produit
            $product = Mage::GetModel('catalog/product')->setStoreId($storeId)->load($productId);
            if (($product->getId()) && ($qty > 0)) {
                $NewOrderItem = Mage::getModel('sales/order_item')
                                ->setProductId($product->getId())
                                ->setSku($product->getSku())
                                ->setName($item['product_name'])
                                ->setWeight($product->getWeight())
                                ->setTaxClassId($product->getTaxClassId())
                                ->setCost($product->getCost())
                                ->setOriginalPrice($priceHt)
                                ->setIsQtyDecimal(0)
                                ->setProduct($product)
                                ->setPrice((double) $priceHt)
                                ->setBasePrice((double) $priceHt)
                                ->setQtyOrdered($qty)
                                ->setTaxAmount($taxAmount)
                                ->setBaseTaxAmount($taxAmount)
                                //->setTaxPercent($taxRate)
                                ->setRowTotal($priceHt * $qty)
                                ->setBaseRowTotal($priceHt * $qty)
                                ->setRowWeight($product->getWeight() * $qty)
								->setproduct_type('simple');

                //ajoute a la commande
                $new_order->addItem($NewOrderItem);
                $new_order
                        ->setSubtotal($new_order->getSubtotal() + $priceHt * $qty)
                        ->setBaseSubtotal($new_order->getBaseSubtotal() + $priceHt * $qty)
                        ->setGrandTotal($new_order->getGrandTotal() + (($taxAmount + $priceHt) * $qty))
                        ->setBaseGrandTotal($new_order->getBaseGrandTotal() + (($taxAmount + $priceHt) * $qty))
                        ->setTaxAmount($new_order->getTaxAmount() + $taxAmount * $qty)
                        ->setBaseTaxAmount($new_order->getBaseTaxAmount() + $taxAmount * $qty);
            }
        }

        //save order
        $new_order->setstate('new', 'pending', '', false);
        $new_order->setcreated_at(date("Y-m-d G:i"));
        $new_order->setupdated_at(date("Y-m-d G:i"));
        $new_order->setorder_type('rma');
        $new_order->save();
        $billing_address->save();
        $shipping_address->save();

        //add comments to order & credit memo
        $rma = mage::getModel('ProductReturn/Rma')->load($object['rma_id']);
        $comment = mage::helper('ProductReturn')->__('Created for Product return #%s', $rma->getrma_ref());
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $new_order->getId()));
        $rma->addHistoryRma('<a href="' . $url . '">' . mage::helper('ProductReturn')->__('Order #%s created', $new_order->getincrement_id()) . '</a>');

        $new_order->addStatusToHistory($new_order->getStatus(), $comment, false);
        $new_order->save();


        //store order in rma/products
        foreach ($object['products'] as $product) {
            if ((!isset($product['rp_id'])) || ($product['rp_id'] == null))
                continue;

            //update rma product
            $caption = mage::helper('ProductReturn')->__('Order #%s', $new_order->getincrement_id());
            if ($product['action'] == 'exchange')
                $caption .= ' ' . mage::helper('ProductReturn')->__('Exchanged with %s', $product['product_name']) . ' ';
            $rmaProduct = mage::getModel('ProductReturn/RmaProducts')->load($product['rp_id']);
            $rmaProduct->setrp_action_processed(1)
                    ->setrp_rp_destination_processed(1)
                    ->setrp_associated_object($caption)
                    ->setrp_object_type('order')
                    ->setrp_object_id($new_order->getId())
                    ->setrp_action($product['action'])
                    ->setrp_destination($product['destination'])
                    ->save();

            //manage destination
            if ($product['action'] == 'exchange') {
                //if it is an exchange, use initial product
                $product['product_id'] = $rmaProduct->getrp_product_id();

                //if product is configurable, find the sub product
                if ($rmaProduct->hasSubProduct()) {
                    $product['product_id'] = $rmaProduct->getSubProductId();
                }

                $description = mage::helper('ProductReturn')->__('Product return #%s', $rma->getrma_ref());
                $this->manageProductDestination($product, $srcOrder->getStore()->getwebsite_id(), $description, $rma->getrma_id());
            } else {
                $description = mage::helper('ProductReturn')->__('Product return #%s', $rma->getrma_ref());
                $this->manageProductDestination($product, $srcOrder->getStore()->getwebsite_id(), $description, $rma->getrma_id());
            }
        }

        //dispatch event to notify for this new order created for rma
        Mage::dispatchEvent('productreturn_order_created_for_rma', array('order' => $new_order, 'rma' => $rma));

        return $new_order;
    }

    /**
     * Return label for shipping method
     *
     * @param unknown_type $method
     */
    protected function getShippingMethodLabel($selectedMethod) {
        $label = '';

        $carriers = Mage::getStoreConfig('carriers', 0);
        foreach ($carriers as $key => $item) {
            if ($item['model']) {
                $model = mage::getModel($item['model']);
                $allowedMethods = $model->getAllowedMethods();
                if ($allowedMethods) {
                    foreach ($allowedMethods as $methodKey => $method) {
                        $finalKey = $key . '_' . $methodKey;

                        if ($selectedMethod == $finalKey)
                            $label = $model->getConfigData('title') . ' - ' . $method;
                    }
                }
            }
        }

        return $label;
    }

}

?>