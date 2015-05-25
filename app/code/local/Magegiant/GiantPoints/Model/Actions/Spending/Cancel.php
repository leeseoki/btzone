<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
* https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/**
 * Action Cancel Spent Points for Order
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Actions_Spending_Cancel
    extends Magegiant_GiantPoints_Model_Actions_Abstract
    implements Magegiant_GiantPoints_Model_Actions_Interface
{
    protected $_actionType = Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING;

    /**
     * Calculate and return point amount that spent for order
     *
     * @return int
     */
    public function getPointAmount()
    {
        $order = $this->getData('action_object');

        return (int)$order->getRefundSpentPoints();
    }


    /**
     * update data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     *
     * @return Magegiant_GiantPoints_Model_Action_Interface
     */
    public function updateTransaction()
    {
        $order = $this->getData('action_object');
        $transactionData = array(
            'status'             => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_base_amount'  => $order->getBaseGrandTotal(),
            'order_amount'       => $order->getGrandTotal(),
            'base_discount'      => $order->getGiantpointsBaseDiscount(),
            'discount'           => $order->getGiantpointsDiscount(),
            'store_id'           => $order->getStoreId(),
            'comment'            => $this->getComment(),
        );

        // Set expire time for current transaction
        $transactionData['expiration_date'] = $this->getExpirationDate($order->getStoreId());

        $this->setTransaction($transactionData);

        return parent::updateTransaction();
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        $order = $this->getData('action_object');
        return Mage::helper('giantpoints')->__('Retrieve points spent on cancelled order #%s', $order->getIncrementId());
    }

    /**
     * @param null $transaction
     * @return mixed
     */
    public function getCommentHtml($transaction = null)
    {
        if (is_null($transaction)) {
            return $this->getTitle();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $editUrl = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $transaction->getOrderId()));
        } else {
            $editUrl = Mage::getUrl('sales/order/view', array('order_id' => $transaction->getOrderId()));
        }

        return Mage::helper('giantpoints')->__(
            'Retrieve points spent on cancelled order %s',
            '<a href="' . $editUrl . '" title="'
            . Mage::helper('giantpoints')->__('View Order')
            . '">#' . $transaction->getOrderIncrementId() . '</a>'
        );
    }
}
