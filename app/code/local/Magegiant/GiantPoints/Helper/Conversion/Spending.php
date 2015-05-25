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
class Magegiant_GiantPoints_Helper_Conversion_Spending extends Magegiant_GiantPoints_Helper_Conversion_Abstract
{

    const XML_PATH_SPEND_FOR_TAX          = 'giantpoints/spending/spend_for_tax';
    const XML_PATH_FREE_SHIPPING          = 'giantpoints/spending/free_shipping';
    const XML_PATH_SPEND_FOR_SHIPPING     = 'giantpoints/spending/spend_for_shipping';
    const XML_PATH_SPEND_FOR_SHIPPING_TAX = 'giantpoints/spending/spend_for_shipping_tax';
    const XML_PATH_ORDER_REFUND_STATUS    = 'giantpoints/spending/order_refund_state';

    /**
     * get Max point that customer can used to spend for an order
     *
     * @param mixed $store
     * @return int
     */
    public function getMaxPointsPerOrder($store = null)
    {
        $maxPerOrder = Mage::helper('giantpoints/config')->getMaxPointForOrder($store);
        if ($maxPerOrder > 0) {
            return $maxPerOrder;
        }

        return 0;
    }

    /**
     * get Total Point that customer used to spent for the order
     *
     * @return int
     */
    public function getTotalSpendingPoints($quote = null)
    {
        if (!$quote)
            $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $container = new Varien_Object(array(
            'total_point_spent' => $address->getGiantpointsSpent()
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_get_total_point', array(
            'container' => $container,
        ));

        return $container->getTotalPointSpent();
    }

    /**
     * get discount (Base Currency) by points of each product item on the shopping cart
     * with $item is null, result is the total discount of all items
     *
     * @param Mage_Sales_Model_Quote_Item|null $item
     * @return float
     */
    public function getPointItemDiscount($item = null)
    {
        $container = new Varien_Object(array(
            'point_item_discount' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_point_item_discount', array(
            'item'      => $item,
            'container' => $container,
        ));

        return $container->getPointItemDiscount();
    }

    /**
     * get point that customer used to spend for each product item
     * with $item is null, result is the total points used for all items
     *
     * @param Mage_Sales_Model_Quote_Item|null $item
     * @return int
     */
    public function getPointItemSpent($item = null)
    {
        $container = new Varien_Object(array(
            'point_item_spent' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_point_item_spent', array(
            'item'      => $item,
            'container' => $container,
        ));

        return $container->getPointItemSpent();
    }

    /**
     * pre collect total for quote/address and return quote total
     *
     * @param Mage_Sales_Model_Quote              $quote
     * @param null|Mage_Sales_Model_Quote_Address $address
     * @return float
     */
    public function getQuoteBaseTotal($quote, $address = null)
    {
        $helperConfig = Mage::helper('giantpoints/config');
        $cacheKey     = 'quote_base_total';
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if (is_null($address)) {
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
        }
        $baseSubtotalWithDiscount  = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
        $pointsSpendingCalculation = $helperConfig->getPointsSpendingCalculation();
        $spend_tax                 = false;
        if ($pointsSpendingCalculation != Magegiant_GiantPoints_Model_System_Config_Source_Calculation::POINTS_BEFORE_TAX) {
            $baseSubtotalWithDiscount += $address->getData('base_tax_amount');
            $spend_tax = true;
        }
        $baseTotal = $baseSubtotalWithDiscount - $this->getPointItemDiscount();
        if ($helperConfig->allowSpendPointForShippingFee($quote->getStoreId())) {
            if ($helperConfig->allowSpendPointForShipping()) {
                $baseTotal += $address->getBaseShippingAmount();
            }
            if ($helperConfig->allowSpendPointForShippingTax($quote->getStoreId()) && $spend_tax) {
                if (Mage::helper('tax')->shippingPriceIncludesTax()) {
                    $baseTotal += $address->getBaseShippingTaxAmount();
                }
            }
        }
        $this->saveCache($cacheKey, $baseTotal);

        return $baseTotal;
    }

    /**
     * get discount (Base Currency) by points that spent with check rule type
     *
     * @return float
     */
    public function getCheckedRuleDiscount()
    {
        $container = new Varien_Object(array(
            'checked_rule_discount' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_checked_rule_discount', array(
            'container' => $container,
        ));

        return $container->getCheckedRuleDiscount();
    }

    /**
     * get points used to spend for checked rules
     *
     * @return int
     */
    public function getCheckedRulePoint()
    {
        $container = new Varien_Object(array(
            'checked_rule_point' => 0
        ));
        Mage::dispatchEvent('giantpoints_conversion_spending_checked_rule_point', array(
            'container' => $container,
        ));

        return $container->getCheckedRulePoint();
    }

    /**
     * get discount (base currency) by points that spent with slider rule type
     *
     * @return float
     */
    public function getSliderRuleDiscount()
    {
        $session          = Mage::getSingleton('checkout/session');
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['base_discount']) && $session->getData('is_used_point')
        ) {
            return $rewardSalesRules['base_discount'];
        }

        return 0;
    }

    /**
     * get points used to spend by slider rule
     *
     * @return int
     */
    public function getSliderRulePoint()
    {
        $session          = $this->getSession();
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['point_amount']) && $session->getData('is_used_point')
        ) {
            return $rewardSalesRules['point_amount'];
        }

        return 0;
    }

    /**
     * get total point spent by rules on shopping cart
     *
     * @return int
     */
    public function getTotalRulePoint()
    {
        return $this->getCheckedRulePoint() + $this->getSliderRulePoint();
    }

    /**
     * get quote spending rule by RuleID
     *
     * @param int|'rate' $ruleId
     * @return Varien_Object
     */
    public function getQuoteRule($ruleId = 'rate')
    {
        $cacheKey = "quote_rule_model:$ruleId";
        if (!$this->hasCache($cacheKey)) {
            if ($ruleId == 'rate') {
                $this->saveCache($cacheKey, $this->getSpendingRateAsRule());

                return $this->getCache($cacheKey);
            }
            $container = new Varien_Object(array(
                'quote_rule_model' => null
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_quote_rule_model', array(
                'container' => $container,
                'rule_id'   => $ruleId,
            ));
            $this->saveCache($cacheKey, $container->getQuoteRuleModel());
        }

        return $this->getCache($cacheKey);
    }

    /**
     * get Spend Rates as a special rule (with id = 'rate')
     *
     * @return Varien_Object|false
     */
    public function getSpendingRateAsRule()
    {
        $customerGroupId = $this->getCustomerGroupId();
        $websiteId       = $this->getWebsiteId();
        $cacheKey        = "rate_as_rule:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('giantpoints/rate')->getConversionRate(
            Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            $this->saveCache($cacheKey, new Varien_Object(array(
                'point_amount'  => $rate->getPoints(),
                'base_rate'     => $rate->getMoney(),
                'simple_action' => 'by_price',
                'id'            => 'rate',
            )));
        } else {
            $this->saveCache($cacheKey, false);
        }

        return $this->getCache($cacheKey);
    }


    /**
     * get max points can used to spend for a quote
     *
     * @param Varien_Object          $rule
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    public function getRuleMaxPointsForQuote($rule, $quote)
    {
        $cacheKey = "rule_max_points_for_quote:{$rule->getId()}";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointAmount()) {
                $quoteTotal = $this->getQuoteBaseTotal($quote);
                $maxPoints  = ceil(($quoteTotal - $this->getCheckedRuleDiscount()) / $rule->getBaseRate()
                    ) * $rule->getPointAmount();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRulePoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                        $maxPoints = floor($maxPoints / $rule->getPointAmount()) * $rule->getPointAmount();
                    } else {
                        $maxPoints = 0;
                    }
                }
                $this->saveCache($cacheKey, $maxPoints);
            }
        } else {
            $container = new Varien_Object(array(
                'rule_max_points' => 0
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_rule_max_points', array(
                'rule'      => $rule,
                'quote'     => $quote,
                'container' => $container,
            ));
            $this->saveCache($cacheKey, $container->getRuleMaxPoints());
        }
        if (!$this->hasCache($cacheKey)) {
            $this->saveCache($cacheKey, 0);
        }

        return $this->getCache($cacheKey);
    }

    /**
     * get discount for quote when a rule is applied and recalculate real point used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object          $rule
     * @param int                    $points
     * @return float
     */
    public function getQuoteRuleDiscount($quote, $rule, &$points)
    {
        $cacheKey = "quote_rule_discount:{$rule->getId()}:$points";

        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointAmount()) {
                $baseTotal = $this->getQuoteBaseTotal($quote) - $this->getCheckedRuleDiscount();
                $maxPoints = ceil($baseTotal / $rule->getBaseRate()) * $rule->getPointAmount();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRulePoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                    } else {
                        $maxPoints = 0;
                    }
                }
                $points = min($points, $maxPoints);
                $points = floor($points / $rule->getPointAmount()) * $rule->getPointAmount();
                //$this->saveCache($cacheKey, $points * $rule->getBaseRate() / $rule->getPointAmount());
                $discount = $points * $rule->getBaseRate() / $rule->getPointAmount();
                $this->saveCache($cacheKey, $discount);
            } else {
                $points = 0;
                $this->saveCache($cacheKey, 0);
            }
        } else {
            $container = new Varien_Object(array(
                'quote_rule_discount' => 0,
                'points'              => $points
            ));
            Mage::dispatchEvent('giantpoints_conversion_spending_quote_rule_discount', array(
                'rule'      => $rule,
                'quote'     => $quote,
                'container' => $container,
            ));
            $points = $container->getPoints();
            $this->saveCache($cacheKey, $container->getQuoteRuleDiscount());
        }

        return $this->getCache($cacheKey);
    }

}
