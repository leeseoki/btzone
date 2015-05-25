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
class Magegiant_GiantPoints_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * check giant points system is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::helper('giantpoints/config')->isEnabled();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * get reward customer account
     *
     * @return mixed
     */
    public function getRewardCustomer()
    {
        if (Mage::getSingleton('customer/session')->getCustomerId()) {
            $customer       = Mage::getSingleton('customer/session')->getCustomer();
            $rewardCustomer = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer);

            return $rewardCustomer;
        }

        return null;
    }

    /**
     * get points balance
     *
     * @return mixed
     */
    public function getPointBalance()
    {
        if ($this->getRewardCustomer()) {
            return $this->getRewardCustomer()->getBalance();
        }

        return null;
    }

    /**
     * format points balance
     */
    public function formatPointBalance()
    {
        if ($this->getPointBalance()) {
            $point_balance = $this->getPointBalance();

            return Mage::helper('giantpoints')->addLabelForPoint($point_balance, $this->getRewardCustomer()->getCustomer()->getStoreId());
        } else {
            return Mage::helper('giantpoints')->addLabelForPoint(0);
        }
    }

    /**
     * Get current customer session
     *
     * @return Mage_Customer_Model_Customer|null
     */
    public function getCustomer()
    {
        return Mage::helper('giantpoints/customer')->getCustomer();
    }

    /**
     * Get Current Url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->helper('core/url')->getCurrentUrl();
    }

    /**
     * Get customer referral code
     *
     * @return string
     */
    public function getReferralCode()
    {
        $customer = $this->getCustomer();
        if ($customer && $customer->getId())
            return Mage::helper('giantpoints/crypt')->encrypt($this->getCustomer()->getId());

        return '';
    }

    /**
     * get referral email
     *
     * @return string
     */
    public function getReferralEmail()
    {
        $customer = $this->getCustomer();
        if ($customer && $customer->getId())
            return $customer->getEmail();

        return '';
    }

    /**
     * get Block maping(use for Spending Point)
     *
     * @return array
     */
    public function getBlockMap()
    {
        $updater = Mage::getModel('giantpoints/updater');
        $result  = array();
        foreach ($updater->getMap() as $action => $blocks) {
            $result[$action] = array_keys($blocks);
        }

        return $result;
    }

    /**
     * get Point Icon before earning message
     *
     * @return mixed
     */
    public function getPointIcon()
    {
        return Mage::helper('giantpoints')->removeBreakLine(Mage::getBlockSingleton('giantpoints/icon')->toHtml());
    }

    /**
     * @param      $message
     * @param bool $clear
     * @return mixed
     */
    public function getEarningMessage($message, $clear = true)
    {
        $earningMessage = $this->getLayout()->createBlock('core/template')
            ->setTemplate('magegiant/giantpoints/earning/message.phtml')
            ->setMessage($message)
            ->setHasClear($clear)
            ->setPointIcon($this->getPointIcon())->toHtml();

        return Mage::helper('giantpoints')->removeBreakLine($earningMessage);
    }

    public function customerIsGuest()
    {
        return Mage::getModel('customer/session')->getCustomer()->getId() ? false : true;
    }

    public function getCurrentPage()
    {
        return Mage::app()->getRequest()->getModuleName() . Mage::app()->getRequest()->getControllerName();
    }
}
