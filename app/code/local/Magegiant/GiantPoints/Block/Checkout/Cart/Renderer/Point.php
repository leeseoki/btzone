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
 * GiantPoints Show Cart Total (Review about Earning/Spending Reward Points)
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Checkout_Cart_Renderer_Point extends Mage_Checkout_Block_Total_Default
{
    public function __construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/giantpoints/checkout/cart/renderer/point.phtml');
    }

    /**
     * check giant points system is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::helper('giantpoints/config')->isEnabled();
    }

    /**
     * get total points that customer use to spend for order
     *
     * @return int
     */
    public function getSpendingPoint($quote=null)
    {
        return Mage::helper('giantpoints/conversion_spending')->getTotalSpendingPoints($quote);
    }

    /**
     * get total points that customer can earned by purchase order
     *
     * @return int
     */
    public function getEarningPoint()
    {
        return Mage::helper('giantpoints/conversion_earning')->getEarnedPointsSummary();
    }

    public function getEarningPointForInvitee()
    {
        return Mage::helper('giantpoints/conversion_earning')->getPointForInvitee();
    }

    public function customerIsGuest()
    {
        return Mage::getModel('customer/session')->getCustomer()->getId() ? false : true;
    }

    /**
     * check giant points is enabled
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
