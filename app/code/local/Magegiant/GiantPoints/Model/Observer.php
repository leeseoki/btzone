<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Observer
{
    protected static $_customerNotSet = true;

    /**
     * process point after order save
     *
     * @param $observer
     * @return $this
     */
    public function salesOrderSaveAfter($observer)
    {
        $order = $observer['order'];
        if (!$this->_isEnabledModule($order->getStoreId())) {
            return $this;
        }
        if ($order->getCustomerIsGuest() || !$order->getCustomerId()) {
            return $this;
        }
        // Refund point that customer used to spend for this order (when order is canceled)
        $allowRefundSpending = Mage::helper('giantpoints/config')->allowRefundSpentPoint($order->getStoreId());
        if ($allowRefundSpending && $order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
            $maxPoint = $order->getGiantpointsSpent();
            $maxPoint -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'spending_cancel')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($maxPoint > 0) {
                $order->setRefundSpentPoints($maxPoint);
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if (!$customer->getId()) {
                    return $this;
                }
                $additionalData = array(
                    'customer'      => $customer,
                    'action_object' => $order,
                    'notice'        => null,


                );
                Mage::helper('giantpoints/action')->createTransaction(
                    'spending_cancel', $additionalData
                );
            }
        }

        return $this;
    }

    /**
     * Process invoice after save
     *
     * @param type $observer
     * @return Magegiant_GiantPoints_Model_Observer
     */
    public function salesOrderInvoiceSaveAfter($observer)
    {
        $invoice = $observer['invoice'];
        if (!$this->_isEnabledModule($invoice->getStoreId())) {
            return $this;
        }
        $this->_earnPointsForRate($invoice);

        return $this;
    }


    protected function _earnPointsForRate($invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getCustomerIsGuest() || !$order->getCustomerId()
            || $invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID
            || !$order->getGiantpointsEarn()
        ) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $invoice,
            'notice'        => null,


        );
        Mage::helper('giantpoints/action')->createTransaction(
            'order_invoiced', $additionalData
        );
    }

    /**
     * refine refund input
     *
     * @param $observer
     * @return $this
     */
    public function salesOrderCreditmemoRegisterBefore($observer)
    {
        $request    = $observer['request'];
        $creditmemo = $observer['creditmemo'];
        if (!$this->_isEnabledModule($creditmemo->getStoreId())) {
            return $this;
        }
        $input = $request->getParam('creditmemo');
        $order = $creditmemo->getOrder();
        if (Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId())) {
            //refund earn by rate

            $maxRateEarnedRefund = (int)Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('action_code', 'order_invoiced')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($maxRateEarnedRefund > $order->getGiantpointsEarn()) {
                $maxRateEarnedRefund = $order->getGiantpointsEarn();
            }
            if (isset($input['refund_rate_earned_points']) && $input['refund_rate_earned_points'] > 0) {
                $refundPoints     = (int)$input['refund_rate_earned_points'];
                $refundRatePoints = min($refundPoints, $maxRateEarnedRefund);
                $creditmemo->setRefundRateEarnedPoints(max($refundRatePoints, 0));
                $creditmemo->setGiantpointsEarn($creditmemo->getGiantpointsEarn());
            } else {
                $refundPoints     = (int)$creditmemo->getGiantpointsEarn();
                $refundRatePoints = min($refundPoints, $maxRateEarnedRefund);
                $creditmemo->setRefundRateEarnedPoints(max($refundRatePoints, 0));
                $creditmemo->setGiantpointsEarn($creditmemo->getGiantpointsEarn());
            }
        }
        // Refund point to customer (that he used to spend)
        $maxPoint = $order->getGiantpointsSpent();
        $maxPoint -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'spending_creditmemo')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if (isset($input['refund_spent_points']) && $input['refund_spent_points'] > 0) {
            $refundPoints = (int)$input['refund_spent_points'];
            $refundPoints = min($refundPoints, $maxPoint);
            $creditmemo->setRefundSpentPoints(max($refundPoints, 0));
        } else {
            $refundPoints = min($creditmemo->getGiantpointsDiscount(), $maxPoint);
            $creditmemo->setRefundSpentPoints(max($refundPoints, 0));
        }


        return $this;
    }

    /**
     * process credit memo save after
     *
     * @param $observer
     * @return $this
     */
    public function salesOrderCreditmemoSaveAfter($observer)
    {
        $creditmemo = $observer['creditmemo'];
        $order      = $creditmemo->getOrder();
        if (!$this->_isEnabledModule($order->getStoreId())) {
            return $this;
        }
        // Refund spent points
        if (Mage::helper('giantpoints/config')->allowRefundSpentPoint($order->getStoreId())) {
            if ($creditmemo->getRefundSpentPoints() > 0) {
                $this->_refundSpentPoints($creditmemo);
            }
        }

        // Deduce rate earned points
        if (Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId())) {
            if ($creditmemo->getRefundRateEarnedPoints() > 0) {
                $this->_refundRateEarnedPoints($creditmemo);
            }
        }

        return $this;
    }

    protected function _refundSpentPoints($creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getGiantpointsSpent() <= 0) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $creditmemo,
            'notice'        => null,


        );
        Mage::helper('giantpoints/action')->createTransaction(
            'spending_creditmemo', $additionalData
        );
    }

    /**
     * refund point earned by rate
     *
     * @param $creditmemo
     * @return $this
     */
    protected function _refundRateEarnedPoints($creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getGiantpointsEarn() <= 0 || $creditmemo->getRefundRateEarnedPoints() <= 0) {
            return $this;
        }
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $customer,
            'action_object' => $creditmemo,
            'notice'        => null,


        );
        Mage::helper('giantpoints/action')->createTransaction(
            'order_refunded', $additionalData
        );
    }

    /**
     * reduce point after spent order
     *
     * @param $observer
     * @return $this
     */
    public function salesOrderPlaceAfter($observer)
    {
        $order = $observer->getOrder();
        if (!$this->_isEnabledModule($order->getStoreId())) {
            return $this;
        }
        $quote = $observer->getQuote();
        if ($order->getCustomerIsGuest()) {
            return $this;
        }
        $additionalData = array(
            'customer'      => $quote->getCustomer(),
            'action_object' => $order,
            'notice'        => null,


        );
        // Process spending points for order
        if ($order->getGiantpointsSpent() > 0) {
            Mage::helper('giantpoints/action')->createTransaction('spending_order',
                $additionalData
            );
        }

        if ($order->getGiantpointsEarn() && !Mage::app()->getStore()->isAdmin() && $order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
            $message = Mage::helper('giantpoints')->__('
            You earned %s for the order you just placed.<br />
            The points you earned are currently pending the completion of your order. You will be able to spend these points after we finish processing your order.'
                , Mage::helper('giantpoints')->addLabelForPoint($order->getGiantpointsEarn()));
            $this->_dispatchSuccess($message);
        }

        return $this;
    }

    /**
     *
     * @param $observer
     */
    public function quoteDestroy($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setCatalogRules(array());
        $session->setData('point_amount', 0);
        $session->setRewardSalesRules(array());
        $session->setRewardCheckedRules(array());
    }

    protected function _dispatchSuccess($str_msg)
    {
        /* @var $message Mage_Core_Model_Message */
        $message_factory = Mage::getSingleton('core/session');
        $message_factory->addSuccess($str_msg);
    }

    /*Behavior Earning Process*/
    // function observes customer save on frontend
    public function customerSaveBefore($observer)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
        if ($customer->isObjectNew() && !Mage::registry('giantpoints_current_customer')) {
            Mage::register('giantpoints_current_customer', $customer);
        }
    }

    /**
     * customer register
     *
     * @param $observer
     * @return $this
     */
    public function postdispatchCustomerAccountCreatePost($observer)
    {
        if (!$this->_isEnabledModule()) {
            return $this;
        }
        $customer = Mage::registry('giantpoints_current_customer');
        if (!$customer || !$customer->getId()) {
            return $this;
        }
        self::$_customerNotSet = false;
        $rewardAccount         = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount || !$rewardAccount->getId()) {
            $this->_createRewardCustomer($customer);
        }

    }

    /**
     * @param $_customerId
     * @param $_productIdToVerify
     * @return bool
     */
    protected function _hasCustomerBoughtThisProduct($_customerId, $_productIdToVerify)
    {
        $result                    = false;
        $childrenIds               = array();
        $groupedProductChildrenIds = array();
        $productIsGrouped          = false;

        $collectionOfOrders = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', $_customerId);

        $product = Mage::getModel('catalog/product')->load($_productIdToVerify);

        if ($product->isGrouped()) {
            $productIsGrouped          = true;
            $childrenIds               = $product->getTypeInstance()->getChildrenIds($_productIdToVerify);
            $groupedProductChildrenIds = $childrenIds[Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED];
        }
        foreach ($collectionOfOrders as $order) {
            foreach ($order->getItemsCollection() as $item) {
                $masterStatus = $item->getStatusName(Mage_Sales_Model_Order_Item::STATUS_MIXED);

                //virtual and downloadable products
                if ((bool)$item->getData("is_virtual")) {
                    $masterStatus = $item->getStatusName(Mage_Sales_Model_Order_Item::STATUS_INVOICED);
                }

                //Grouped product
                if ($productIsGrouped) {
                    if (in_array($item->getProductId(), $groupedProductChildrenIds)
                        && $item->getStatus() == $masterStatus
                    ) {
                        $result = true;
                    }
                }
                if ($item->getProductId() == $_productIdToVerify
                    && ($item->getStatus() == $masterStatus || $item->getQtyInvoiced() > $item->getQtyRefunded())
                ) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    protected function _isNotSetInCustomer($customer, $tagRelationId)
    {
        $rewardAccount = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);
        if (!$rewardAccount->getTagsEarned()) {
            return true;
        }
        $arrayOfTagRelationIds = explode(',', $rewardAccount->getTagsEarned());
        if (in_array($tagRelationId, $arrayOfTagRelationIds)) {
            return false;
        }
        $arrayOfTagRelationIds[] = $tagRelationId;
        $rewardAccount->setTagsEarned(implode(',', $arrayOfTagRelationIds));
        $rewardAccount->save();

        return true;
    }

    /**
     * create reward customer
     *
     * @param $rewardAccount
     */
    protected function _createRewardCustomer($customer)
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardAccount         = Mage::getModel('giantpoints/customer');
        $rewardAccount->setCustomerId($customer->getId());
        if ($isSubscribedByDefault) {
            $rewardAccount
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        try {
            $rewardAccount->save();
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);

            return false;
        }

        return $rewardAccount;
    }

    public function pageLoadBeforeFront($observer)
    {
        $event = $observer->getEvent();
        if (!$event)
            return $this;
        $this->_processReferral();
        $this->_processRewrite();
    }

    /**
     * proccess when visitor click a referrer link
     *
     * param: cod: string
     *
     * @return $this
     */
    protected function _processReferral()
    {
        $code = Mage::app()->getRequest()->getParam('cod', null);
        if (!$code)
            return $this;

        $customer = Mage::helper('giantpoints/crypt')->getCustomerByCode($code);
        if (!$customer) {
            return $this;
        }
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $cookie->setCookie($cookie_key, $code);
        $current_url = explode('?', Mage::helper('core/url')->getCurrentUrl());
        $url         = $current_url[0];
        Mage::app()->getResponse()->setRedirect($url, 301);

        return $this;
    }

    protected function _processRewrite()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled() || Mage::helper('giantpoints/version')->isRawVerAtLeast('1.8.0.0')) {
            return $this;
        }
        $node  = Mage::getConfig()->getNode('global/models/adminhtml/rewrite');
        $dnode = Mage::getConfig()->getNode('global/models/adminhtml/drewrite/config_data');
        $node->appendChild($dnode);

        return $this;
    }

    /**
     * Check module is enabled
     */
    protected function _isEnabledModule($storeId = null)
    {
        return (bool)Mage::helper('giantpoints/config')->isEnabled($storeId);
    }
}