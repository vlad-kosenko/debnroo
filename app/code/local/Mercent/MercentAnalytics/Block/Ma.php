<?php
/**
 * Mercent
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
 * @category   Mercent
 * @package    Mercent_MercentAnalytics
 */


/**
 * Analytics Page Block
 *
 * @category   Mercent
 * @package    Mercent_MercentAnalytics
 * @author     Mercent
 */
class Mercent_MercentAnalytics_Block_Ma extends Mage_Core_Block_Text
{
	 /**
     * Retrieve Quote Data HTML
     *
     * @return string
     */
    /*public function getMercentSearchHtml()
    {
		$query = Mage::getBlockSingleton('catalogsearch/result')->helper('catalogSearch')->getEscapedQueryText();

        if (!$query) {
            return '';
        }

		$number = Mage::getBlockSingleton('catalogsearch/result')->getResultCount();
		if (!isset($numer)) { $numer = 0;};

        $html = '';
		// OPTIONAL: ADD HTML ECHO TO TRACK INTERNAL SITE SEARCH

        return $html;
    } */

    /**
     * Retrieve Quote Data HTML
     *
     * @return string
     */
    public function getMercentQuoteOrdersHtml()
    {
        $quote = $this->getQuote();
        if (!$quote) {
            return '';
        }

        if ($quote instanceof Mage_Sales_Model_Quote) {
            $quoteId = $quote->getId();
        } else {
            $quoteId = $quote;
        }

        if (!$quoteId) {
            return '';
        }

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('quote_id', $quoteId)
            ->load();

        $result = array();
        $html = '';
        foreach ($orders as $order) {
            $html .= $this->setOrder($order)->getOrderHtml();
        }

        $result = array("html" => $html, "noscript" => '');
        return $result;
    }

    /**
     * Retrieve Order Data HTML
     *
     * @return string
     */
    public function getOrderHtml()
    {

        $order = $this->getOrder();
        if (!$order) {
            return '';
        }

        if (!$order instanceof Mage_Sales_Model_Order) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!$order) {
            return '';
        }
		$base_url = Mage::getBaseUrl();
		$html = '';
		$noscript = '';
		$noscript_temp = '';
		$noscript_count = 0;
		$sku = '';
		$name = '';
		$id = '';
		$url = '';
		$units = '';
		$amounts = '';

        $address = $order->getBillingAddress();

        $html .= 'mr_conv["type"] = "order";'."\n";

		$_totalData = $order->getData();
		$_grand = $_totalData['grand_total'];
		$_sub = $_totalData['subtotal'];
		$_ship = $_totalData['shipping_amount'];
		$_itemTax = $_totalData['tax_amount'] - $_totalData['shipping_tax_amount'];
		$_shippingTax = $_totalData['shipping_tax_amount'];
		$_totalTax = $_totalData['tax_amount'];
		$_discount = $_totalData['discount_amount'];

		$_total = $_sub - $_discount;

		$html .= 'mr_conv["id"] = "' . $order->getIncrementId() . '";'."\n";
		$html .= 'mr_conv["customerId"] = "' . $this->customerID() . '";'."\n";
		$html .= 'mr_conv["amount"] = "' . number_format($_sub, 2) . '";'."\n";
		$html .= 'mr_conv["shipping"] = "' . number_format($_ship, 2) . '";'."\n";
		$html .= 'mr_conv["tax"] = "' . number_format($_totalTax, 2) . '";'."\n";
		$html .= 'mr_conv["discount"] = "' . abs($_discount) . '";'."\n";
		$html .= 'mr_conv["postalCode"] = "' . $address->getPostcode() . '";'."\n";
		$html .= 'mr_conv["countryCode"] = "' . $address->getCountry() . '";'."\n"."\n";



		foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

			$_product = Mage::getModel('catalog/product')->load($item->getProductId());

   			/*
   			echo "<pre>";
   			echo var_dump($_product->getData());
   			echo "</pre>";
			*/
            $sku =  $_product->getSku();
			$name =  $item->getName();
			$id = $item->getProductId();
			$url =  $_product->getProductUrl();
			$units = $item->getQtyOrdered();
			$amounts = $item->getRowTotal();
			$position = $noscript_count+1;

			$html .= 'mr_convOrderItem["sku"] = "' . $sku . '";'."\n";
			$html .= 'mr_convOrderItem["title"] = "' . str_replace('"', '\"', $name) . '";'."\n";
			$html .= 'mr_convOrderItem["url"] = "' . $url . '";'."\n";
			$html .= 'mr_convOrderItem["qty"] = "' . number_format($units, 0) . '";'."\n";
			$html .= 'mr_convOrderItem["extPrice"] = "' . number_format($amounts, 2) . '";'."\n";
			$html .= 'mr_addConvOrderItem(mr_convOrderItem)'."\n"."\n";

        }
        $html .= 'mr_sendConversion();'."\n";

        return $html;
    }

    /**
     * Retrieve Mercent Account Identifier
     *
     * @return string
     */
    public function getAccount()
    {
        if (!$this->hasData('account')) {
            $this->setAccount(Mage::getStoreConfig('mercent/analytics/account'));
        }
        return $this->getData('account');
    }


  /**
    public function pageGroup()
    {
        return $this->_getData('body_class');
    }

	*/


	public function customerID()
    {
		if($this->helper('customer')->isLoggedIn()) {
			$cid = Mage::getSingleton('customer/session')->getCustomerId();
			$customer = $cid;
		} else {
			$customer = "Not Logged";
		}
		return $customer;
    }

    /**
     * Retrieve current page URL
     *
     * @return string
     */
    public function getPageName()
    {
        if (!$this->hasData('page_name')) {
            //$queryStr = '';
            //if ($this->getRequest() && $this->getRequest()->getQuery()) {
            //    $queryStr = '?' . http_build_query($this->getRequest()->getQuery());
            //}
            $this->setPageName(Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']));
        }
        return $this->getData('page_name');
    }

    /**
     * Prepare and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::getStoreConfigFlag('mercent/analytics/active')) {
            return '';
        }

		/**
		 * YWATracker.setDocumentGroup("' . $this->pageGroup() . '");
		 */

		 //$product = Mage::getModel('catalog/product');
		 $httporhttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		 $orderData = $this->getMercentQuoteOrdersHtml();
		 /*
		 $this->addText('PLW DEBUG');
		 $this->addText(print_r($orderData,true));
		 */

		$this->addText('<!-- BEGIN MERCENT TRACKING CODE -->');
        $this->addText('
<script type="text/javascript">
//<![CDATA[
var maJsHost = (("https:" == document.location.protocol) ? "https://cdn." : "http://cdn.");
document.write(unescape("%3Cscript src=\'" + maJsHost + "mercent.com/js/tracker.js\' type=\'text/javascript\'%3E%3C/script%3E"));
//]]>
</script>


<script type="text/javascript">
<!--
mr_merchantID  = "' . $this->getAccount() . '";
mr_Track();
// -->
</script>
		');

		/*
		* Check for order conversion
		*/
		if($orderData){
			$this->addText('<script type="text/javascript">
<!--
'.$orderData['html'].'
// -->
</script>
');

		} // end conversion check

        $this->addText('<!-- END MERCENT TRACKING CODE -->');

        return parent::_toHtml();
    }
}
