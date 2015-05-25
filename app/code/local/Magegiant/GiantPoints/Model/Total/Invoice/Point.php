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
class Magegiant_GiantPoints_Model_Total_Invoice_Point extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total when create Invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_addEarnedByRate($invoice);
        $this->_addInviteeEarned($invoice);
        $this->_addReferralEarned($invoice);
        $this->_addSpentPointDiscount($invoice);

        return $this;
    }

    /**
     * process add point which earned for referral
     *
     * @param $invoice
     * @return $this
     */
    protected function _addReferralEarned($invoice)
    {
        $order     = $invoice->getOrder();
        $earnPoint = 0;
        $maxEarn   = $order->getReferralEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'order_invoiced_referral')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            $totalEarn = 0;
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getReferralEarn();
                if (!$item->isLast()) {
                    $itemPoint = ceil($itemPoint * $item->getQty() / $orderItem->getQtyOrdered());
                }
                $item->setReferralEarn($earnPoint);
                $totalEarn += $itemPoint;
            }
            if ($earnPoint >= $maxEarn)
                $totalEarn = $maxEarn;
            $invoice->setReferralEarn($totalEarn);
        }

        return $this;
    }

    /**
     * process add point which earned for referral link
     *
     * @param $invoice
     * @return $this
     */
    protected function _addInviteeEarned($invoice)
    {
        $order     = $invoice->getOrder();
        $earnPoint = 0;
        $maxEarn   = $order->getInviteeEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'invitee_invoiced')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            $totalEarn = 0;
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getInviteeEarn();
                if (!$item->isLast()) {
                    $itemPoint = ceil($itemPoint * $item->getQty() / $orderItem->getQtyOrdered());
                }
                $item->setInviteeEarn($earnPoint);
                $totalEarn += $itemPoint;
            }
            if ($earnPoint >= $maxEarn)
                $totalEarn = $maxEarn;
            $invoice->setInviteeEarn($totalEarn);
        }

        return $this;
    }

    /**
     * process add point which earned for order invoiced
     *
     * @param $invoice
     * @return $this
     */
    protected function _addEarnedByRate($invoice)
    {
        $order   = $invoice->getOrder();
        $maxEarn = $order->getGiantpointsEarn();
        $maxEarn -= (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_code', 'order_invoiced')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            $totalEarn = 0;
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint = (int)$orderItem->getGiantpointsEarn();
                if (!$item->isLast()) {
                    $itemPoint = ceil($itemPoint * $item->getQty() / $orderItem->getQtyOrdered());
                }
                $item->setGiantpointsEarn($itemPoint);
                $totalEarn += $itemPoint;
            }
            if ($totalEarn >= $maxEarn)
                $totalEarn = $maxEarn;
            $invoice->setGiantpointsEarn($totalEarn);
        }


        return $this;
    }

    protected function _addSpentPointDiscount($invoice)
    {
        $order        = $invoice->getOrder();
        $itemInvoiced = 0;
        $itemOrdered  = 0;
        if ($order->hasInvoices()) {
            foreach ($order->getInvoiceCollection() as $invoiced) {
                foreach ($invoiced->getAllItems() as $item) {
                    $itemInvoiced += $item->getQty();
                }
            }
        }
        foreach ($order->getAllItems() as $item) {
            $itemOrdered += $item->getQty();
        }
        if ($itemInvoiced == $itemOrdered) {
            $invoice->setIsLastInvoice(true);
        }
        if ($order->getGiantpointsDiscount() < 0.0001) {
            return;
        }
        $invoice->setGiantpointsDiscount(0);
        $invoice->setGiantpointsBaseDiscount(0);

        $totalDiscount     = 0;
        $baseTotalDiscount = 0;

        /**
         * Checking if shipping discount was added in previous invoices.
         * So basically if we have invoice with positive discount and it
         * was not canceled we don't add shipping discount to this one.
         */
        $addShippingDicount = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getGiantpointsDiscount()) {
                $addShippingDicount = false;
            }
        }

        if ($addShippingDicount) {
            $totalDiscount     = $totalDiscount + $invoice->getOrder()->getShippingDiscount();
            $baseTotalDiscount = $baseTotalDiscount + $invoice->getOrder()->getBaseShippingDiscount();
        }

        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount     = (float)$orderItem->getGiantpointsDiscount();
            $baseOrderItemDiscount = (float)$orderItem->getGiantpointsBaseDiscount();
            $orderItemQty          = $orderItem->getQtyOrdered();

            if ($orderItemDiscount && $orderItemQty) {
                /**
                 * Resolve rounding problems
                 */
                $discount     = $orderItemDiscount - $orderItem->getGiantpointsDiscountInvoiced();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getGiantpointsBaseDiscountInvoiced();
                if (!$item->isLast()) {
                    $activeQty    = $orderItemQty - $orderItem->getQtyInvoiced();
                    $discount     = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                }
                $item->setGiantpointsDiscount($discount);
                $item->setGiantpointsBaseDiscount($baseDiscount);
                $orderItem->setGiantpointsDiscountInvoiced($orderItem->getGiantpointsDiscountInvoiced() + $discount);
                $orderItem->setGiantpointsBaseDiscountInvoiced($orderItem->getGiantpointsBaseDiscountInvoiced() + $baseDiscount);
                $totalDiscount += $discount;
                $baseTotalDiscount += $baseDiscount;
            }
        }
        $invoice->setDiscountDescription($order->getDiscountDescription());
        $invoice->setGiantpointsDiscount($totalDiscount);
        $invoice->setGiantpointsBaseDiscount($baseTotalDiscount);

        return $this;

    }
}
