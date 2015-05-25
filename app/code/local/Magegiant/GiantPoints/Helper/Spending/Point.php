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
 * GiantPoints Helper to show spending point on Shopping Cart/ Checkout Page/ Admin Create Order
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Helper_Spending_Point extends Magegiant_GiantPoints_Helper_Conversion_Abstract
{
    /**
     * get spending calculation
     *
     * @return Magegiant_GiantPoints_Helper_Calculation_Spending
     */
    public function getConversion()
    {
        return Mage::helper('giantpoints/conversion_spending');
    }

    /**
     * get current working with quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * check reward points is enable to use or not
     *
     * @return boolean
     */
    public function allowSpending()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled(Mage::helper('giantpoints/customer')->getStoreId())) {
            return false;
        }
        if ($this->getQuote()->getBaseGrandTotal() < 0.0001
            && !$this->getConversion()->getTotalRulePoint()
        ) {
            return false;
        }
        if (!Mage::helper('giantpoints/customer')->isAllowSpend($this->getQuote()->getStoreId())) {
            return false;
        }

        return true;
    }

    /**
     * get all spending rules available for current shopping cart
     *
     * @return array
     */
    public function getSpendingRules()
    {
        $cacheKey = 'spending_rules_array';
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $container = new Varien_Object(array(
            'spending_rules' => array()
        ));
        Mage::dispatchEvent('giantpoints_block_spend_get_rules', array(
            'container' => $container,
        ));
        $this->saveCache($cacheKey, $container->getSpendingRules());

        return $this->getCache($cacheKey);
    }

    /**
     * get all spending rule with type is slider
     *
     * @return array
     */
    public function getSliderRules()
    {
        $rules = array();
        $rule  = $this->getConversion()->getSpendingRateAsRule();
        if ($rule && $rule->getId()) {
            $rules[] = $rule;
        }
        foreach ($this->getSpendingRules() as $rule) {
            if ($rule->getSimpleAction() == 'by_price') {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * get all spending rule with type is checkbox
     *
     * @return array
     */
    public function getCheckboxRules()
    {
        $rules          = array();
        $customerPoints = $this->getCustomerTotalPoints() - $this->getConversion()->getPointItemSpent();
        foreach ($this->getSpendingRules() as $rule) {
            if (in_array($rule->getId(), $this->getCheckedData()) ||
                ($rule->getSimpleAction() == 'fixed'
                    && $rule->getPointAmount() <= $customerPoints
                )
            ) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * get JSON string used for JS
     *
     * @param array $rules
     * @return string
     */
    public function getRulesJson($rules = null)
    {
        if (is_null($rules)) {
            $rules = $this->getSliderRules();
        }
        $result = array();
        $quote  = $this->getQuote();
        foreach ($rules as $rule) {
            $ruleOptions = array();
            if ($this->getCustomerPoint($quote) < $rule->getPointAmount()) {
                $ruleOptions['optionType'] = 'needPoint';
                $ruleOptions['needPoint']  = Mage::helper('giantpoints')->addLabelForPoint($rule->getPointAmount() - $this->getCustomerPoint($quote));
            } else {
                $sliderOption = array();

                $sliderOption['minPoints'] = 0;
                $sliderOption['pointStep'] = (int)$rule->getPointAmount();
                $maxPoints                 = $this->getCustomerPoint($quote);
                if ($rule->getMaxPoints() > 0 && $maxPoints > $rule->getMaxPoints()) {
                    $maxPoints = $rule->getMaxPoints();
                }
                if ($maxPoints > $this->getConversion()->getRuleMaxPointsForQuote($rule, $quote)) {
                    $maxPoints = $this->getConversion()->getRuleMaxPointsForQuote($rule, $quote);
                }
                // Refine max points
                if ($sliderOption['pointStep']) {
                    $maxPoints = floor($maxPoints / $sliderOption['pointStep']) * $sliderOption['pointStep'];
                }
                $sliderOption['maxPoints'] = max(0, $maxPoints);

                $ruleOptions['sliderOption'] = $sliderOption;
                $ruleOptions['optionType']   = 'slider';
            }
            $result[$rule->getId()] = $ruleOptions;
        }

        return Mage::helper('core')->jsonEncode($result);
    }

    /**
     * get customer total points on his balance
     *
     * @return int
     */
    public function getCustomerTotalPoints()
    {
        return Mage::helper('giantpoints/customer')->getBalance();
    }

    /**
     * get customer point after he use to spend for order (estimate)
     *
     * @return int
     */
    public function getCustomerPoint($quote)
    {
        if (!$this->hasCache('customer_point')) {
            $points = $this->getCustomerTotalPoints();
            $points -= $this->getConversion()->getPointItemSpent();
            $points -= $this->getConversion()->getCheckedRulePoint();
            if ($points < 0) {
                $points = 0;
            }
            $maxPoints = Mage::helper('giantpoints/conversion_spending')->getMaxPointsPerOrder($quote->getStoreId());
            if ($maxPoints && $maxPoints < $points) {
                $points = $maxPoints;
            }
            $this->saveCache('customer_point', $points);
        }
        return $this->getCache('customer_point');
    }

    /**
     * get current customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::helper('giantpoints/customer')->getCustomer();
    }

    /**
     * format discount for a rule
     *
     * @param Varien_Object $rule
     * @return string
     */
    public function formatDiscount($rule)
    {
        $store = Mage::app()->getStore(Mage::helper('giantpoints/customer')->getStoreId());
        if ($rule->getId() == 'rate') {
            $price = $store->convertPrice($rule->getBaseRate());
        } else {
            if ($rule->getDiscountStyle() == 'cart_fixed') {
                $price = $store->convertPrice($rule->getDiscountAmount());
            } else {
                return round($rule->getDiscountAmount(), 2) . '%';
            }
        }

        return $store->formatPrice($price);
    }

    /**
     * get slider rules date that applied
     *
     * @return Varien_Object
     */
    public function getSliderData()
    {
        $session = Mage::getSingleton('checkout/session');

        return new Varien_Object($session->getRewardSalesRules());
    }

    /**
     * get checked rule data that applied
     *
     * @return array
     */
    public function getCheckedData()
    {
        if (!$this->hasCache('checked_data')) {
            $session            = Mage::getSingleton('checkout/session');
            $rewardCheckedRules = $session->getRewardCheckedRules();
            if (!is_array($rewardCheckedRules)) {
                $this->saveCache('checked_data', array());
            } else {
                $this->saveCache('checked_data', array_keys($rewardCheckedRules));
            }
        }

        return $this->getCache('checked_data');
    }

    /**
     * check current checkout session is using point or not
     *
     * @return boolean
     */
    public function isUsedPoint()
    {
        return Mage::getSingleton('checkout/session')->getData('is_used_point');
    }

    public function useMaxPointByDefault($store = null)
    {
        return Mage::helper('giantpoints/config')->useMaxPointByDefault($store);
    }
}
