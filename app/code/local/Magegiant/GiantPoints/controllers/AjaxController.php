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
class Magegiant_GiantPoints_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Checkout Cart apply spend points
     */
    public function applyPointAction()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled()) {
            $this->norouteAction();

            return;
        }
        $session = Mage::getSingleton('checkout/session');
        $session->setData('is_used_point', true);
        $session->setRewardSalesRules(array(
            'rule_id'      => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        $cart   = Mage::getSingleton('checkout/cart');
        $result = array();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
            $result           = array(
                'success'  => true,
                'messages' => array(),
                'blocks'   => array(),
            );
            $result['blocks'] = $this->getUpdater()->getBlocks();
        } else {
            $result['reload'] = true;
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Checkout onepage apply spend point
     */
    public function applyPointOnepageAction()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled()) {
            $this->norouteAction();

            return;
        }
        $session         = Mage::getSingleton('checkout/session');
        $is_point_amount = $this->getRequest()->getParam('is_used_point');
        $session->setData('is_used_point', $is_point_amount);
        if ($is_point_amount) {
            $session->setRewardSalesRules(array(
                'rule_id'      => $this->getRequest()->getParam('reward_sales_rule'),
                'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
            ));
        }
        $result = array();
        $session->getQuote()->collectTotals()->save();
        $layout               = Mage::app()->getLayout();
        $fullTargetActionName = 'checkout_onepage_paymentmethod';
        $result['blocks']     = $this->getUpdater()->getBlocks($layout, $fullTargetActionName);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Magegiant Onestepcheckout apply spent point
     */
    public function applyPointOnestepAction()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled()) {
            $this->norouteAction();

            return;
        }
        $session         = Mage::getSingleton('checkout/session');
        $is_point_amount = $this->getRequest()->getParam('is_used_point');
        $session->setData('is_used_point', $is_point_amount);
        if ($is_point_amount) {
            $session->setRewardSalesRules(array(
                'rule_id'      => $this->getRequest()->getParam('reward_sales_rule'),
                'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
            ));
        }
        $result = array();
        $session->getQuote()->collectTotals()->save();
        $layout               = Mage::app()->getLayout();
        $fullTargetActionName = 'onestepcheckout_index_index';
        $result['blocks']     = $this->getUpdater()->getBlocks($layout, $fullTargetActionName);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyPointIdevOnestepAction()
    {
        if (!Mage::helper('giantpoints/config')->isEnabled()) {
            $this->norouteAction();

            return;
        }
        $session         = Mage::getSingleton('checkout/session');
        $is_point_amount = $this->getRequest()->getParam('is_used_point');
        $session->setData('is_used_point', $is_point_amount);
        if ($is_point_amount) {
            $session->setRewardSalesRules(array(
                'rule_id'      => $this->getRequest()->getParam('reward_sales_rule'),
                'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
            ));
        }
        $result = array();
        $session->getQuote()->collectTotals()->save();
        $layout               = Mage::app()->getLayout();
        $fullTargetActionName = 'onestepcheckout_index_index';
        $result['blocks']     = $this->getUpdater()->getBlocks($layout, $fullTargetActionName);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @return Magegiant_GiantPoints_Model_Updater
     */
    public function getUpdater()
    {
        return Mage::getSingleton('giantpoints/updater');
    }

}
