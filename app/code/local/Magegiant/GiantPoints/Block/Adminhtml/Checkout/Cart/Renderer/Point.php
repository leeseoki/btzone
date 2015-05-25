<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Checkout_Cart_Renderer_Point extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    public function __construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/giantpoints/order/cart/renderer/point.phtml');
    }

    /**
     * check giant points system is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::helper('giantpoints/config')->isEnabled($this->getStore());
    }


    /**
     * get total points that customer use to spend for order
     *
     * @return int
     */
    public function getSpendingPoint()
    {
        return Mage::helper('giantpoints/conversion_spending')->getTotalSpendingPoints();
    }

    /**
     * get total points that customer can earned by purchase order
     *
     * @return int
     */
    public function getEarningPoint()
    {
        return Mage::helper('giantpoints/conversion_earning')->getTotalEarningPoints();
    }
}
