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
 * Action Spend Point for Order
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Actions_Spending_Creditmemo
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
        $creditmemo = $this->getData('action_object');

        return (int)$creditmemo->getRefundSpentPoints();
    }

    /**
     * @return mixed
     */
    public function updateTransaction()
    {
        $creditmemo = $this->getData('action_object');
        $order      = $creditmemo->getOrder();

        $transactionData = array(
            'status'             => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_base_amount'  => $order->getBaseGrandTotal(),
            'order_amount'       => $order->getGrandTotal(),
            'base_discount'      => $creditmemo->getGiantpointsBaseDiscount(),
            'discount'           => $creditmemo->getGiantpointsDiscount(),
            'store_id'           => $order->getStoreId(),
            'comment'            => $this->getComment(),
            'notice'             => $creditmemo->getIncrementId(),
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
        $creditmemo = $this->getData('action_object');
        $order      = $creditmemo->getOrder();

        return Mage::helper('giantpoints')->__('Retrieve points spent on refunded order #%s', $order->getIncrementId());
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
            'Retrieve points spent on refunded order %s',
            '<a href="' . $editUrl . '" title="'
            . Mage::helper('giantpoints')->__('View Order')
            . '">#' . $transaction->getOrderIncrementId() . '</a>'
        );
    }
}
