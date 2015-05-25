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
 * GiantPoints Show Earning Point on Mini Cart Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Checkout_Sidebar_Action extends Magegiant_GiantPoints_Block_Checkout_Cart_Earning
{

    /**
     * Check store is enable for display on minicart sidebar
     *
     * @return type
     */
    public function enableDisplay()
    {
        return Mage::helper('giantpoints/config')->showOnMiniCart();
    }

    public function getEarningPoints()
    {
        $giantPoints = $this->getPoints();
        $pointAmount = $giantPoints->getPointAmount();
        if ($pointAmount) {
            return Mage::helper('giantpoints')->addLabelForPoint($pointAmount);
        }

        return null;
    }
}
