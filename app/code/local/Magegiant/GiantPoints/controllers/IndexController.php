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
 * GiantPoints Index Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (Mage::helper('giantpoints/config')->isEnabled()) {
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        } else {
            $this->_redirect('customer/account/');
        }
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Reward Points'));
        $this->renderLayout();
    }

    /**
     * process setting action
     */
    public function settingAction()
    {
        $_helper        = Mage::helper('giantpoints');
        $isSubsc        = (int)$this->getRequest()->getParam('is_subscribed');
        $isSubscExp     = (int)$this->getRequest()->getParam('is_subscribed_exp');
        $customer       = Mage::getSingleton('customer/session')
            ->getCustomer();
        $rewardCustomer = Mage::getModel('giantpoints/customer')
            ->getAccountByCustomer($customer);
        if (!$rewardCustomer || !$rewardCustomer->getId()) {
            $rewardCustomer->setCustomerId($customer->getId());
        }
        if (!($rewardCustomer->getNotificationUpdate() == $isSubsc)) {
            $message = $isSubsc ? $_helper->__('The subscription for balance update notification has been saved')
                : $_helper->__('The subscription for balance update notification has been removed');
            Mage::getSingleton('core/session')->addSuccess($message);
        }

        if (!($rewardCustomer->getNotificationExpire() == $isSubscExp)) {
            $message = $isSubscExp ? $_helper->__('The subscription for points expiration notification has been saved')
                : $_helper->__('The subscription for points expiration notification has been removed');
            Mage::getSingleton('core/session')->addSuccess($message);
        }

        if (
            ($rewardCustomer->getNotificationUpdate() == $isSubsc)
            && ($rewardCustomer->getNotificationExpire() == $isSubscExp)
        ) {
            Mage::getSingleton('core/session')->addNotice(
                $_helper->__('Email Notification Settings was not changed')
            );
        }

        try {
            $rewardCustomer
                ->setNotificationUpdate($isSubsc)
                ->setNotificationExpire($isSubscExp)
                ->save();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_redirect('giantpoints/index');
    }

    /**
     * Get info page by Id
     * return html or noRoute
     */
    public function infopageAction()
    {
        $pageId = Mage::helper('giantpoints/config')->getInfoPageId();
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Return Magegiant_GiantPoints_Block_Customer_Referral_General
     */
    public function referralAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}