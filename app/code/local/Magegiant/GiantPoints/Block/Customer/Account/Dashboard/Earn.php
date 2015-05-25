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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Earn extends Magegiant_GiantPoints_Block_Abstract
{
    public function getCanShow()
    {
        $rate = $this->getEarningRate();
        if ($rate && $rate->getId()) {
            $canShow = true;
        } else {
            $canShow = false;
        }
        $container = new Varien_Object(array(
            'can_show' => $canShow
        ));
        Mage::dispatchEvent('giantpoints_block_dashboard_earn_can_show', array(
            'container' => $container,
        ));

        return $container->getCanShow();
    }

    /**
     * get earning rate
     *
     * @return Magegiant_GiantPoints_Model_Rate
     */
    public function getEarningRate()
    {
        if (!$this->hasData('earning_rate')) {
            $this->setData('earning_rate',
                Mage::getModel('giantpoints/rate')->getConversionRate(Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT)
            );
        }

        return $this->getData('earning_rate');
    }

    /**
     * get current money formated of rate
     *
     * @param Magegiant_GiantPoints_Model_Rate $rate
     * @return string
     */
    public function getCurrentMoney($rate)
    {
        if ($rate && $rate->getId()) {
            $money = $rate->getMoney();

            return Mage::app()->getStore()->convertPrice($money, true);
        }

        return '';
    }
}
