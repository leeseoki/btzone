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
 * @package     MageGiant_Giantpoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_Giantpoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Actions_Earning_OrderInvoiced
    extends Magegiant_GiantPoints_Model_Actions_Abstract
    implements Magegiant_GiantPoints_Model_Actions_Interface
{

    protected $_actionType = Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING;

    /**
     * @return int
     */
    public function getPointAmount()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order     = $invoice->getOrder();
            $isInvoice = true;
        } else {
            $order     = $invoice;
            $isInvoice = false;
        }

        $maxEarn = $order->getGiantpointsEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'order_invoiced')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn <= 0) {
            return 0;
        }

        if (!$isInvoice) {
            return (int)$maxEarn;
        }

        return $invoice->getGiantpointsEarn();
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
        } else {
            $order = $invoice;
        }

        return Mage::helper('giantpoints')->__('Earn points for purchasing order #%s', $order->getIncrementId());
    }

    /**
     * @param null $transaction
     * @return mixed
     */
    public function getCommentHtml($transaction = null)
    {
        if (is_null($transaction)) {
            return $this->getComment();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $editUrl = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $transaction->getOrderId()));
        } else {
            $editUrl = Mage::getUrl('sales/order/view', array('order_id' => $transaction->getOrderId()));
        }

        return Mage::helper('giantpoints')->__(
            'Earn points for purchasing order %s',
            '<a href="' . $editUrl . '" title="'
            . Mage::helper('giantpoints')->__('View Order')
            . '">#' . $transaction->getOrderIncrementId() . '</a>'
        );
    }

    /**
     * set transaction data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     *
     * @return Magegiant_GiantPoints_Model_Actions_Interface
     */
    public function updateTransaction()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
        } else {
            $order = $invoice;
        }

        $transactionData = array(
            'status'             => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_base_amount'  => $order->getBaseGrandTotal(),
            'order_amount'       => $order->getGrandTotal(),
            'base_discount'      => $invoice->getGiantpointsBaseDiscount(),
            'discount'           => $invoice->getGiantpointsDiscount(),
            'store_id'           => $order->getStoreId(),
            'comment'            => $this->getComment(),
        );

        // Set expire time for current transaction
        $transactionData['expiration_date'] = $this->getExpirationDate($order->getStoreId());
        $this->setTransaction($transactionData);

        return parent::updateTransaction();
    }
}
