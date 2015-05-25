<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Conversion_Earning extends Magegiant_GiantPoints_Helper_Conversion_Abstract
{

    /**
     * get Total Point that customer can earn by purchase current order/ quote
     *
     * @param null|Mage_Sales_Model_Quote $quote
     * @return int
     */
    public function getTotalEarningPoints($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        $customer      = Mage::helper('giantpoints/customer')->getCustomer();
        $amountToPoint = $this->getAmountToPoints($quote);
        /*Earning rate*/
        $totalEarnPoints = $this->getEarningByRate($amountToPoint, $quote->getStoreId());
        /*Check max earning points*/
        $maximumPointsPerCustomer = Mage::helper('giantpoints/config')->getMaxPointPerCustomer();
        if ($maximumPointsPerCustomer) {
            $customersPoints = 0;
            if ($customer) {
                $customersPoints = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer)->getBalance();
            }
            if ($totalEarnPoints + $customersPoints > $maximumPointsPerCustomer) {
                $totalEarnPoints = $maximumPointsPerCustomer - $customersPoints;
            }
        }
        $totalEarnPoints = max($totalEarnPoints, 0);
        $container       = new Varien_Object(array(
            'total_earn_points' => $totalEarnPoints,
        ));
        Mage::dispatchEvent('giantpoints_calculation_earning_total_points', array(
            'quote'     => $quote,
            'container' => $container,
        ));

        return $container->getTotalEarnPoints();
    }

    public function getAmountToPoints($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $helperConfig             = Mage::helper('giantpoints/config');
        $pointsEarningCalculation = $helperConfig->getPointsEarningCalculation();
        $baseSubtotalWithDiscount = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
        if (Mage::helper('giantpoints/config')->getEarningByShipping($quote->getStoreId())) {
            $baseSubtotalWithDiscount += $address->getBaseShippingAmount();
        }

        if ($pointsEarningCalculation != Magegiant_GiantPoints_Model_System_Config_Source_Calculation::POINTS_BEFORE_TAX) {
            $baseSubtotalWithDiscount += $address->getBaseTaxAmount();
        }
        $baseGrandTotal = max(0, $baseSubtotalWithDiscount);

        return $baseGrandTotal;
    }

    /**
     * calculate quote earning points by system rate
     *
     * @param float $baseGrandTotal
     * @param mixed $store
     * @return int
     */
    public function getEarningByRate($baseGrandTotal, $store = null)
    {
        $customerGroupId = $this->getCustomerGroupId();
        $websiteId       = $this->getWebsiteId();

        $cacheKey = "earning_rate_points:$customerGroupId:$websiteId:$baseGrandTotal";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('giantpoints/rate')->getConversionRate(
            Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            if ($baseGrandTotal < 0) {
                $baseGrandTotal = 0;
            }
            $points = Mage::helper('giantpoints/config')->getRoundingMethod(
                $baseGrandTotal * $rate->getPoints() / $rate->getMoney(), $store
            );
            $this->saveCache($cacheKey, $points);
        } else {
            $this->saveCache($cacheKey, 0);
        }

        return $this->getCache($cacheKey);
    }

    public function getEarnedPointsSummary($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        return $address->getGiantpointsEarn();
    }


    public function getReferralProgram($quote = null)
    {
        $referralProgram = new Varien_Object();
        $data            = array(
            'referral_id'   => 0,
            'invitee_earn'  => 0,
            'referral_earn' => 0
        );
        $referralProgram->setData($data);
        if (!$this->isEnabledReferralSystem())
            return $referralProgram;
        $code = $this->getReferralCode();
        if (!$code) {
            return $referralProgram;
        }
        $invitee_earn     = 0;
        $pointsCommission = 0;
        $customer_id      = Mage::helper('giantpoints/crypt')->decrypt($code);
        $customer         = Mage::getModel('customer/customer')->load($customer_id);
        if (!$customer || !$customer->getId())
            return $referralProgram;
        /*Check code used by themselves*/
        $current_customer = Mage::helper('giantpoints/customer')->getCustomer();
        if ($current_customer && $current_customer->getId() == $customer_id) {
            return $referralProgram;
        }
        /*calculator point for invitee*/
        $invitee_earn += $this->getPointForInvitee();
        /*calculator point for referral*/
        $pointsCommission += $this->getPointCommission();
        /*end referral*/
        $data = array(
            'referral_id'   => $customer_id,
            'invitee_earn'  => $invitee_earn,
            'referral_earn' => $pointsCommission
        );
        $referralProgram->setData($data);
        Mage::dispatchEvent('giantpoints_referrer_earning_before', array(
            'referral' => $referralProgram
        ));

        return $referralProgram;
    }


    /**
     * get point for invitee
     *
     * @return int
     */
    public function getPointForInvitee()
    {
        if (!$this->isEnabledReferralSystem() || !$this->getReferralCode())
            return false;

        $referralConfig = Mage::helper('giantpointsrefer/config');
        $amountToPoints = $this->getAmountToPoints();
        if ($referralConfig->getInvitedDiscountType() == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            return $referralConfig->getInvitedPointsFixed();
        } else {
            $invitedPointsPercent = $referralConfig->getInvitedPointsPercent();
            if ($invitedPointsPercent) {
                $amountEarn     = $amountToPoints * $invitedPointsPercent / 100;
                $conversionRate = explode(',', $referralConfig->getInvitedConversionRate());
                $newAmount      = (int)Mage::helper('giantpoints/config')->getRoundingMethod(
                    $amountEarn * $conversionRate['1'] / $conversionRate['0']
                );

                return $newAmount;
            }
        }
    }

    public function getPointCommission()
    {
        if (!$this->isEnabledReferralSystem() || !$this->getReferralCode())
            return false;
        $referralConfig = Mage::helper('giantpointsrefer/config');
        $amountToPoints = $this->getAmountToPoints();
        if ($referralConfig->getReferralDiscountType() == Magegiant_GiantPoints_Model_System_Config_Source_DiscountType::TYPE_FIXED) {
            return $referralConfig->getPointsCommissionFix();
        } else {
            $referralPointsPercent = $referralConfig->getPointsCommissionPercent();
            if ($referralPointsPercent) {
                $amountEarn     = $amountToPoints * $referralPointsPercent / 100;
                $conversionRate = explode(',', $referralConfig->getReferralConversionRate());
                $newAmount      = (int)Mage::helper('giantpoints/config')->getRoundingMethod(
                    $amountEarn * $conversionRate['1'] / $conversionRate['0']
                );

                return $newAmount;
            }
        }
    }

    public function getReferralCode()
    {
        $cookie     = Mage::getModel('giantpoints/cookie');
        $cookie_key = Magegiant_GiantPoints_Model_Cookie::COOKIE_GIANTPOINT_REFERRAL;
        $code       = $cookie->getCookie($cookie_key);

        return $code;
    }

    public function isEnabledReferralSystem($store = null)
    {
        return Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsRefer') && Mage::helper('giantpointsrefer/config')->isEnabled($store);
    }
}