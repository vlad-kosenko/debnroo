<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Single
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getLogAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->listingProduct->isStoppable()) {

            if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            } else {
                $this->listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                $this->listingProduct->deleteInstance();
            }

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $data = $this->getRequestObject()->getData();
        $this->logRequestMessages();

        return $this->buildRequestDataObject($data)->getData();
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            $this->checkAndRemoveNeededItems();
            return $response;
        }

        if ($response['already_stop']) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was already stopped on eBay');
                parent::MESSAGE_TEXT_KEY => 'Item was already stopped on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getResponseObject()->processSuccess($response);
        $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $this->checkAndRemoveNeededItems();
        return $response;
    }

    // ########################################

    protected function checkAndRemoveNeededItems()
    {
        if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {
            return;
        }

        $this->listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
        $this->listingProduct->deleteInstance();
    }

    // ########################################
}