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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Policy extends Magegiant_GiantPoints_Block_Abstract
{
    /**
     * earning transaction will be expired after days
     * 
     * @return int
     */
    public function getTransactionExpireDays()
    {
        $days = (int)Mage::getStoreConfig(Magegiant_GiantPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE);
        return max(0, $days);
    }
    
    public function getHoldingDays()
    {
        $days = (int)Mage::getStoreConfig(Magegiant_GiantPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS);
        return max(0, $days);
    }
    
    public function getMaxPointBalance()
    {
        $maxBalance = (int)Mage::getStoreConfig(Magegiant_GiantPoints_Model_Transaction::XML_PATH_MAX_BALANCE);
        return max(0, $maxBalance);
    }
    
    /**
     * Minimum point allowed to redeem
     * 
     * @return int
     */
    public function getRedeemablePoints()
    {
        $points = (int)Mage::getStoreConfig(Magegiant_GiantPoints_Helper_Customer::XML_PATH_REDEEMABLE_POINTS);
        return max(0, $points);
    }
    
    public function getMaxPerOrder()
    {
        $points = (int)Mage::getStoreConfig(
            Magegiant_GiantPoints_Helper_Calculation_Spending::XML_PATH_MAX_POINTS_PER_ORDER
        );
        return max(0, $points);
    }
}
