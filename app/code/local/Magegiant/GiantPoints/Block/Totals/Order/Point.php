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
 * You should write block extended from this block when you write plugin
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Totals_Order_Point extends Magegiant_GiantPoints_Block_Abstract
{
    public function initTotals()
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $totalsBlock = $this->getParentBlock();
        $order       = $totalsBlock->getOrder();
        $helper      = Mage::helper('giantpoints');
        $storeId     = $order->getStoreId();
        if ($order->getGiantpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Earned'),
                'value'       => $helper->addLabelForPoint($order->getGiantpointsEarn(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($order->getGiantpointsSpent()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_spend_label',
                'label'       => $this->__('Spent'),
                'value'       => $helper->addLabelForPoint($order->getGiantpointsSpent(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($order->getInviteeEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'invitation_earn_label',
                'label'       => $this->__('Invitation Earned'),
                'value'       => $helper->addLabelForPoint($order->getInviteeEarn(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
    }

}
