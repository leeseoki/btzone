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
 * Giantpoints Total Point Spend Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Totals_Order_Point extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $order       = $totalsBlock->getOrder();
        if (!$order || !$order->getId())
            return $this;
        $helper  = Mage::helper('giantpoints');
        $storeId = $order->getStoreId();
        if ($order->getGiantpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Earned'),
                'value'       => $helper->addLabelForPoint($order->getGiantpointsEarn(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($order->getGiantpointsSpent()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_spend_label',
                'label'       => $this->__('Spent'),
                'value'       => $helper->addLabelForPoint($order->getGiantpointsEarn(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($order->getInviteeEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'invitation_earn_label',
                'label'       => $this->__('Invitation Earned'),
                'value'       => $helper->addLabelForPoint($order->getInviteeEarn(), $storeId),
                'strong'      => true,
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($order->getReferralEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'referral_earn_label',
                'label'       => $this->__('Referrer\'s Earned'),
                'value'       => $helper->addLabelForPoint($order->getReferralEarn(), $storeId),
                'is_formated' => true,
                'strong'      => true,
            )), 'subtotal');
        }
        // Show Refunded Points at here
        $refundSpentPoints = (int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_type', Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_SPENDING)
            ->addFieldToFilter('point_amount', array('gt' => 0))
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($refundSpentPoints > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_refund_spent',
                'label'       => $this->__('Refund spent'),
                'value'       => $this->_addPointLabel($refundSpentPoints, $storeId),
                'is_formated' => true,
                'area'        => 'footer',
            )));
        }

        $refundEarnedPoints = -(int)Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('action_type', Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING)
            ->addFieldToFilter('point_amount', array('lt' => 0))
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($refundEarnedPoints > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_refund_earned',
                'label'       => $this->__('Refund earned'),
                'value'       => $this->_addPointLabel($refundEarnedPoints, $storeId),
                'is_formated' => true,
                'area'        => 'footer',
            )));
        }
    }

    /**
     *
     * @param $points
     * @param $store
     * @return string
     */
    protected function _addPointLabel($points, $store)
    {
        return Mage::helper('giantpoints')->addLabelForPoint($points, $store);
    }

}
