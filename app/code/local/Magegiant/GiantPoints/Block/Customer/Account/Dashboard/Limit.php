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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Limit extends Magegiant_GiantPoints_Block_Abstract
{

    public function getBalanceLimit()
    {
        return Mage::helper('giantpoints/config')->getMaxPointPerCustomer();
    }

    /**
     * get exprice days
     *
     * @return mixed
     */
    public function getExpireDays()
    {
        return Mage::helper('giantpoints/config')->getExpireDays();
    }
}
