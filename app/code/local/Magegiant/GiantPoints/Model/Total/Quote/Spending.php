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
 * GiantPoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Total_Quote_Spending extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_calculator;

    public function __construct()
    {
        $this->setCode('giantpoints_spending');
        $this->_calculator = Mage::getSingleton('giantpoints/rate');
    }

    /**
     * collect giantpoints points total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magegiant_GiantPoints_Model_Total_Quote_Point
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if (!Mage::helper('giantpoints/config')->isEnabled($quote->getStoreId())) {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getData('is_used_point')) {
            return $this;
        }
        Mage::dispatchEvent('giantpoints_collect_spending_total_points_before', array(
            'address' => $address,
        ));
        $rewardSalesRules   = $session->getRewardSalesRules();
        $rewardCheckedRules = $session->getRewardCheckedRules();
        $helper             = Mage::helper('giantpoints/conversion_spending');
        $helperConfig       = Mage::helper('giantpoints/config');

        $baseTotal = $helper->getQuoteBaseTotal($quote, $address);
        $maxPoints = Mage::helper('giantpoints/customer')->getBalance();
        if ($maxPointsPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
            $maxPoints = min($maxPointsPerOrder, $maxPoints);
        }
        $maxPoints -= $helper->getPointItemSpent();
        if ($maxPoints <= 0) {
            return $this;
        }

        $baseDiscount = 0;
        $pointUsed    = 0;

        // Checked Rules Discount First
        if (is_array($rewardCheckedRules)) {
            $newRewardCheckedRules = array();
            foreach ($rewardCheckedRules as $ruleData) {
                if ($baseTotal < 0.0001)
                    break;
                $rule = $helper->getQuoteRule($ruleData['rule_id']);
                if (!$rule || !$rule->getId() || $rule->getSimpleAction() != 'fixed') {
                    continue;
                }
                if ($maxPoints < $rule->getPointAmount()) {
                    $session->addNotice($helper->__('Not enough points to spend'));
                    continue;
                }
                $points       = $rule->getPointAmount();
                $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                if ($ruleDiscount < 0.0001) {
                    continue;
                }

                $baseTotal -= $ruleDiscount;
                $maxPoints -= $points;

                $baseDiscount += $ruleDiscount;
                $pointUsed += $points;

                $newRewardCheckedRules[$rule->getId()] = array(
                    'rule_id'       => $rule->getId(),
                    'point_amount'  => $points,
                    'base_discount' => $ruleDiscount,
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
            $session->setRewardCheckedRules($newRewardCheckedRules);
            Mage::dispatchEvent('totals_quote_reward_checked_rule', array(
                'rules' => $newRewardCheckedRules
            ));
        }

        // Sales Rule (slider) discount Last
        if (is_array($rewardSalesRules)) {
            $newRewardSalesRules = array();
            if ($baseTotal > 0.0 && isset($rewardSalesRules['rule_id'])) {
                $rule = $helper->getQuoteRule($rewardSalesRules['rule_id']);
                if ($rule && $rule->getId() && $rule->getSimpleAction() == 'by_price') {
                    $points       = min($rewardSalesRules['point_amount'], $maxPoints);
                    $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                    if ($ruleDiscount > 0.0) {
                        $baseTotal -= $ruleDiscount;
                        $baseDiscount += $ruleDiscount;
                        $pointUsed += $points;
                        $newRewardSalesRules = array(
                            'rule_id'       => $rule->getId(),
                            'point_amount'  => $points,
                            'base_discount' => $ruleDiscount,
                        );
                    }
                }
            }
            $session->setRewardSalesRules($newRewardSalesRules);
            Mage::dispatchEvent('totals_quote_reward_sales_rule', array(
                'rules' => $newRewardSalesRules
            ));
        }
        // verify quote total data
        if ($baseTotal < 0.0001) {
            $baseDiscount = $helper->getQuoteBaseTotal($quote, $address);
        }
        if ($baseDiscount) {
            $discount = $quote->getStore()->convertPrice($baseDiscount);
            $address->setGiantpointsBaseDiscount($baseDiscount);
            $address->setGiantpointsDiscount($discount);
            //            update discount amount
            $address->setBaseDiscountAmount($address->getBaseDiscountAmount() - $baseDiscount);
            $address->setDiscountAmount($address->getDiscountAmount() - $discount);
            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);
            $address->setGiantpointsSpent($pointUsed);
            //Set discount description
            if ($address->getDiscountDescription()) {
                $address->setDiscountDescription($address->getDiscountDescription() . ', ' . $helperConfig->getDiscountLabel());
            } else {
                $address->setDiscountDescription($helperConfig->getDiscountLabel());
            }

        }
        /*Calculator discount for each item*/
        $items      = $this->_getAddressItems($address);
        $item_count = 0;
        foreach ($items as $item) {
            $item_count++;
            if ($item_count == count($items)) {
                $item->setIsLast(true);
            } else {
                $item->setIsLast(false);
            }
            if ($item->getParentItemId()) {
                continue;
            }
            $eventArgs['item'] = $item;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_calculator->processSpentPoint($child, $address);
                    $eventArgs['item'] = $child;
                    $address->setBaseTaxAmount($address->getBaseTaxAmount() - $child->getSpentPointBaseTax());
                    $address->setTaxAmount($address->getTaxAmount() - $child->getSpentPointTax());
                    $address->setBaseGrandTotal($address->getBaseGrandTotal() - $child->getSpentPointBaseTax());
                    $address->setGrandTotal($address->getGrandTotal() - $child->getSpentPointTax());
                    Mage::dispatchEvent('giantpoints_quote_address_discount_item_after', $eventArgs);
                }
            } else {
                $this->_calculator->processSpentPoint($item, $address);
                $address->setBaseTaxAmount($address->getBaseTaxAmount() - $item->getSpentPointBaseTax());
                $address->setTaxAmount($address->getTaxAmount() - $item->getSpentPointTax());
                $address->setBaseGrandTotal($address->getBaseGrandTotal() - $item->getSpentPointBaseTax());
                $address->setGrandTotal($address->getGrandTotal() - $item->getSpentPointTax());
                Mage::dispatchEvent('giantpoints_quote_address_discount_item_after', $eventArgs);
            }
        }

        Mage::dispatchEvent('giantpoints_collect_spending_total_points_after', array(
            'address' => $address,
        ));
        if ($address->getBaseGrandTotal() < 0) {
            $address->setBaseGrandTotal(0);
            $address->setGrandTotal(0);
        }
        if ($address->getBaseTaxAmount() < 0) {
            $address->setBaseTaxAmount(0);
            $address->setTaxAmount(0);
        }


        return $this;
    }

}
