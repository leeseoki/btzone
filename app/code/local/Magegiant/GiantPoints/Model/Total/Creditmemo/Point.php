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
 * Giantpoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Total_Creditmemo_Point extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect total when create Creditmemo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if (Mage::helper('giantpoints/config')->allowRefundSpentPoint($order->getStoreId())) {
            $this->_processSpentRefund($creditmemo);
        }
        if (Mage::helper('giantpoints/config')->allowCancelEarnedPoint($order->getStoreId())) {
            $this->_processRateEarnedRefund($creditmemo);
            $this->_processInviteeEarnedRefund($creditmemo);
            $this->_processReferralEarnedRefund($creditmemo);
        }

    }

    /**
     * process refund point earned by rate
     *
     * @param $creditmemo
     * @return $this
     */
    protected function _processRateEarnedRefund($creditmemo)
    {
        $order            = $creditmemo->getOrder();
        $totalRefundPoint = 0;
        $maxRefund        = $order->getGiantpointsEarn();
        $maxRefund -= abs((int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'order_refunded')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal());
        if ($maxRefund >= 0) {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getGiantpointsEarn();
                $ratio     = $itemPoint / $orderItem->getQtyOrdered();
                if (!$item->isLast()) {
                    $itemPoint = ceil($item->getQty() * $ratio);
                }
                //                check refunded
                if ($orderItem->getQtyRefunded() && $itemPoint > 0) {
                    $itemPointRefunded = ceil($orderItem->getQtyRefunded() * $ratio);
                    $totalRefundPoint -= $itemPointRefunded;
                }
                $item->setGiantpointsEarn($itemPoint);
                $totalRefundPoint += $itemPoint;
            }
            if ($this->isLast($creditmemo) || $totalRefundPoint >= $maxRefund)
                $totalRefundPoint = $maxRefund;
            $creditmemo->setGiantpointsEarn($totalRefundPoint);
        }


        return $this;
    }

    /**
     * process refund point earned by invitee
     *
     * @param $creditmemo
     * @return $this
     */
    protected function _processInviteeEarnedRefund($creditmemo)
    {
        $order            = $creditmemo->getOrder();
        $totalRefundPoint = 0;
        $maxRefund        = $order->getInviteeEarn();
        $maxRefund -= abs((int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'invitee_refunded')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal());
        if ($maxRefund >= 0) {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getInviteeEarn();
                $ratio     = $itemPoint / $orderItem->getQtyOrdered();
                if (!$item->isLast()) {
                    $itemPoint = ceil($item->getQty() * $ratio);
                }
                //                check refunded
                if ($orderItem->getQtyRefunded() && $itemPoint > 0) {
                    $itemPointRefunded = ceil($orderItem->getQtyRefunded() * $ratio);
                    $totalRefundPoint -= $itemPointRefunded;
                }
                $item->setInviteeEarn($itemPoint);
                $totalRefundPoint += $itemPoint;
            }
            if ($this->isLast($creditmemo) || $totalRefundPoint >= $maxRefund)
                $totalRefundPoint = $maxRefund;
            $creditmemo->setInviteeEarn($totalRefundPoint);
        }


        return $this;
    }

    /**
     * process refund point earned by referral
     *
     * @param $creditmemo
     * @return $this
     */
    protected function _processReferralEarnedRefund($creditmemo)
    {
        $order            = $creditmemo->getOrder();
        $totalRefundPoint = 0;
        $maxRefund        = $order->getReferralEarn();
        $maxRefund -= abs((int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'referral_refunded')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal());
        if ($maxRefund >= 0) {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getReferralEarn();
                $ratio     = $itemPoint / $orderItem->getQtyOrdered();
                if (!$item->isLast()) {
                    $itemPoint = ceil($item->getQty() * $ratio);
                }
                //                check refunded
                if ($orderItem->getQtyRefunded() && $itemPoint > 0) {
                    $itemPointRefunded = ceil($orderItem->getQtyRefunded() * $ratio);
                    $totalRefundPoint -= $itemPointRefunded;
                }
                $item->setReferralEarn($itemPoint);
                $totalRefundPoint += $itemPoint;
            }
            if ($this->isLast($creditmemo) || $totalRefundPoint >= $maxRefund)
                $totalRefundPoint = $maxRefund;
            $creditmemo->setReferralEarn($totalRefundPoint);
        }


        return $this;
    }

    protected function _processSpentRefund($creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($creditmemo->getGrandTotal() == 0) {
            $creditmemo->setIsLastCreditmemo(false);
        }
        if ($order->getGiantpointsDiscount() < 0.0001) {
            return;
        }
        $creditmemo->setGiantpointsDiscount(0);
        $creditmemo->setGiantpointsBaseDiscount(0);
        $totalGiantpointsDiscount     = 0;
        $baseTotalGiantpointsDiscount = 0;
        /**
         * Calculate how much shipping discount should be applied
         * basing on how much shipping should be refunded.
         */
        $baseShippingAmount = $creditmemo->getBaseShippingAmount();
        if ($baseShippingAmount) {
            $baseShippingDiscount         = $baseShippingAmount * $order->getBaseShippingGiantpointsDisocunt() / $order->getBaseShippingAmount();
            $shippingDiscount             = $order->getShippingAmount() * $baseShippingDiscount / $order->getBaseShippingAmount();
            $totalGiantpointsDiscount     = $totalGiantpointsDiscount + $shippingDiscount;
            $baseTotalGiantpointsDiscount = $baseTotalGiantpointsDiscount + $baseShippingDiscount;
        }

        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount     = (float)$orderItem->getGiantpointsDiscountInvoiced();
            $baseOrderItemDiscount = (float)$orderItem->getGiantpointsBaseDiscountInvoiced();
            $orderItemQty          = $orderItem->getQtyInvoiced();

            if ($orderItemDiscount && $orderItemQty) {
                $discount     = $orderItemDiscount - $orderItem->getGiantpointsDiscountRefunded();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getGiantpointsBaseDiscountRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $discount     = $creditmemo->roundPrice(
                        $discount / $availableQty * $item->getQty(), 'regular', true
                    );
                    $baseDiscount = $creditmemo->roundPrice(
                        $baseDiscount / $availableQty * $item->getQty(), 'base', true
                    );
                }
                $item->setGiantpointsDiscount($discount);
                $item->setGiantpointsBaseDiscount($baseDiscount);
                $orderItem->setGiantpointsDiscountRefunded($orderItem->getGiantpointsDiscountRefunded() + $discount);
                $orderItem->setGiantpointsBaseDiscountRefunded($orderItem->getGiantpointsBaseDiscountRefunded() + $baseDiscount);
                $totalGiantpointsDiscount += $discount;
                $baseTotalGiantpointsDiscount += $baseDiscount;
            }
        }
        $creditmemo->setDiscountDescription($order->getDiscountDescription());
        $creditmemo->setGiantpointsDiscount($totalGiantpointsDiscount);
        $creditmemo->setGiantpointsBaseDiscount($baseTotalGiantpointsDiscount);
//        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalGiantpointsDiscount);
//        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalGiantpointsDiscount);

        return $this;
    }

    /**
     * check credit memo is last or not
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }

        return true;
    }
}
