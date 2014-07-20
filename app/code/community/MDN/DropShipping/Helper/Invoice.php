<?php

class MDN_DropShipping_Helper_Invoice extends Mage_Core_Helper_Abstract {

    /**
     * Store invoice id in ordertoprepare model
     *
     * @param unknown_type $OrderId
     * @param unknown_type $InvoiceId
     */
    public function StoreInvoiceId($OrderId, $InvoiceId) {
		$item = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($OrderId);
        $item->setinvoice_id($InvoiceId)->save();
    }
    
    /**
     * Create invoice
     *
     */
    public function createInvoice($new_order) {
        //$convertor = Mage::getModel('sales/convert_order');
        //$invoice = $convertor->toInvoice($new_order);

        //parcourt les elements de la commande
        $hasProducts = false;
        $itemsToInvoice = array();
        foreach ($new_order->getAllItems() as $orderItem) {
            //ajout au invoice
            //$InvoiceItem = $convertor->itemToInvoiceItem($orderItem);
            $qty = $orderItem->getqty_ordered() - $orderItem->getqty_invoiced();
            if ($qty > 0) {
                //$InvoiceItem->setQty($qty);
                //$invoice->addItem($InvoiceItem);
                $hasProducts = true;
                $itemsToInvoice[$orderItem->getId()] = $qty;
            }
        }

        if (!$hasProducts)
            return null;

        $logfile = 'dropshipping_create_invoice.log';
        $debug = 'Create invoice for order #'.$new_order->getIncrementId();

        try {
            
            if (!$new_order->canInvoice()) {
                $debug .= ' : Can not invoice !';
                return false;
            }
            
            $invoice = Mage::getModel('sales/service_order', $new_order)->prepareInvoice($itemsToInvoice);
            if ($invoice->canCapture())
            {
                $captureMode = 'online';              
                $debug .= ', capture invoice '.$captureMode;
                $invoice->setRequestedCaptureCase($captureMode);
            }
            else
                $debug .= ',do not capture invoice';
            
            //save invoice
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
            //$invoice->save();

            //link order & invoice
            $this->StoreInvoiceId($new_order->getid(), $invoice->getincrement_id());
            $debug .= ', invoiceid = '.$invoice->getincrement_id();


        } catch (Exception $ex) {
            $debug .= ', '.$ex->getMessage();
            Mage::helper('DropShipping')->logException($ex);
            throw new Exception('Error while creating Invoice for Order ' . $new_order->getincrement_id() . ': ' . $ex->getMessage());
        }
        
        Mage::helper('DropShipping')->log($debug);
        
        //sauvegarde la facture
        //$invoice->collectTotals();
        //$invoice->register();

        //$invoice->getOrder()->setIsInProcess(true);
        /*$transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
        $invoice->pay();
        $invoice->save();*/

        //mode de paiement
        /*$payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('banktransfer');
        $payment->setOrder($new_order);
        $new_order->addPayment($payment);
        $payment->pay($invoice);
        $payment->save();*/

        return $invoice;
    }

}