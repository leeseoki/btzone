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
 * GiantPoints Checkout Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Adminhtml_CheckoutController extends Mage_Adminhtml_Controller_Action
{
    public function applyPointAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('is_used_point', true);
        $session->setRewardSalesRules(array(
            'rule_id'   => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
    }

    public function checkboxRuleAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('point_amount', true);
        $rewardCheckedRules = $session->getRewardCheckedRules();
        if (!is_array($rewardCheckedRules)) $rewardCheckedRules = array();
        if ($ruleId = $this->getRequest()->getParam('rule_id')) {
            if ($this->getRequest()->getParam('is_used')) {
                $rewardCheckedRules[$ruleId] = array(
                    'rule_id'   => $ruleId,
                    'point_amount' => null,
                );
            } elseif (isset($rewardCheckedRules[$ruleId])) {
                unset($rewardCheckedRules[$ruleId]);
            }
            $session->setRewardCheckedRules($rewardCheckedRules);
        }
    }
}
