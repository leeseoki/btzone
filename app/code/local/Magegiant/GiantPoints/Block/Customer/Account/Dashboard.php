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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard extends Magegiant_GiantPoints_Block_Abstract
{

    /**
     * get current balance of customer as text
     *
     * @return string
     */
    public function getBalanceText()
    {
        return $this->formatPointBalance();
    }

    /**
     * get point money balance of customer
     *
     * @return string
     */
    public function getPointMoney()
    {
        $pointAmount = $this->getPointBalance();
        if ($pointAmount > 0) {
            $rate = Mage::getModel('giantpoints/rate')->getRate(Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY);
            if ($rate && $rate->getId()) {
                $baseAmount = $pointAmount * $rate->getMoney() / $rate->getPoints();

                return Mage::app()->getStore()->convertPrice($baseAmount, true);
            }
        }

        return '';
    }

    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl('customer/account/');
    }

    public function getInfoPageUrl()
    {
        $toReturn = "";

        if (Mage::helper('giantpoints/config')->isShowInfoPage() && Mage::helper('giantpoints/config')->getInfoPageId()) {
            $toReturn = $this->getUrl('giantpoints/index/infopage/');
        }

        return $toReturn;
    }

}
