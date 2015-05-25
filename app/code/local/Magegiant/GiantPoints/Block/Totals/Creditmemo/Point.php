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
 * Giantpoints Total Point Spend (for creditmemo) Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Totals_Creditmemo_Point extends Magegiant_GiantPoints_Block_Abstract
{
    public function initTotals()
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $totalsBlock = $this->getParentBlock();
        $creditmemo  = $totalsBlock->getCreditmemo();
        $helper      = Mage::helper('giantpoints');
        $storeId     = $creditmemo->getStoreId();
        if ($creditmemo->getGiantpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Refund Earned'),
                'value'       => $helper->addLabelForPoint($creditmemo->getGiantpointsEarn(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($creditmemo->getGiantpointsSpent()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giantpoints_earn_label',
                'label'       => $this->__('Refund Earned'),
                'value'       => $helper->addLabelForPoint($creditmemo->getGiantpointsSpent(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
        if ($creditmemo->getInviteeEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'invitation_earn_label',
                'label'       => $this->__('Refund for Invitation'),
                'value'       => $helper->addLabelForPoint($creditmemo->getInviteeEarn(), $storeId),
                'is_formated' => true,
            )), 'subtotal');
        }
    }
}
