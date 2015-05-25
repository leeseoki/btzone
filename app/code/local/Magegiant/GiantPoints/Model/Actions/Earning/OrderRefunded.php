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
class Magegiant_GiantPoints_Model_Actions_Earning_OrderRefunded
    extends Magegiant_GiantPoints_Model_Actions_Abstract
    implements Magegiant_GiantPoints_Model_Actions_Interface
{

    protected $_actionType = Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING;

    /**
     * @return int
     */
    public function getPointAmount()
    {
        $creditmemo = $this->getData('action_object');

        return -(int)$creditmemo->getRefundRateEarnedPoints();
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        $creditmemo = $this->getData('action_object');
        $order      = $creditmemo->getOrder();

        return Mage::helper('giantpoints')->__('Taken back points for refunding order #%s', $order->getIncrementId());
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
            'Taken back points for refunding order %s',
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
        $this->setTransaction($transactionData);

        return parent::updateTransaction();
    }
}
