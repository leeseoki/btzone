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
class Magegiant_GiantPoints_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * Checkout Page
     */
    public function indexAction()
    {
        if (!Mage::helper('giantpoints')->isEnable()) {
            return $this->_redirectUrl(Mage::getBaseUrl());
        }

        $session = Mage::getSingleton('checkout/session');
        if ($usePoint = $this->getRequest()->getParam('point_amount')) {
            $session->setData('point_amount', $usePoint);
            $session->setData('point_amount', $this->getRequest()->getParam('point_amount'));
        } else {
            $session->unsetData('point_amount');
        }
        $session->getQuote()->collectTotals()->save();

        $paymentBlock = $this->getLayout()->createBlock('giantpoints/checkout_onepage_payment_rewrite_methods');
        $this->getResponse()->setBody($paymentBlock->toHtml());
    }

    /**
     * Fix for One Step Checkout
     */
    public function onestepcheckoutAction()
    {
        $this->indexAction();
    }

    /**
     * checkout page behaviors
     */
    public function changeUsePointAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('point_amount', $this->getRequest()->getParam('point_amount'));

        $result        = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock            = $this->getLayout()->createBlock('giantpoints/checkout_onepage_payment_spending');
            $result['html']          = $paymentBlock->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function changePointAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setRewardSalesRules(array(
            'rule_id'   => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        $result        = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock            = $this->getLayout()->createBlock('giantpoints/checkout_onepage_payment_spending');
            $result['html']          = $paymentBlock->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Update Total for shopping cart Page
     */
    public function updateTotalAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('point_amount', true);
        $session->setRewardSalesRules(array(
            'rule_id'   => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        $cart   = Mage::getSingleton('checkout/cart');
        $result = array();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            $block           = $this->getLayout()->createBlock('checkout/cart_totals')
                ->setTemplate('checkout/cart/totals.phtml');
            $result['total'] = $block->toHtml();
        } else {
            $result['refresh'] = true;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
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

    /**
     * One Step Checkout page behaviors
     */
    public function changeUsePointOscAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('point_amount', $this->getRequest()->getParam('point_amount'));

        $result        = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock            = $this->getLayout()->createBlock('giantpoints/checkout_onepage_payment_spending');
            $paymentBlock->setOneStepCheckout(true);
            $result['html'] = $paymentBlock->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function changePointOscAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setRewardSalesRules(array(
            'rule_id'   => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));

        $result        = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock            = $this->getLayout()->createBlock('giantpoints/checkout_onepage_payment_spending');
            $paymentBlock->setOneStepCheckout(true);
            $result['html'] = $paymentBlock->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
