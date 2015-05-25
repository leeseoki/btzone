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
 * GiantPoints Customer Account and Balance Helper
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Helper_Customer extends Mage_Core_Helper_Abstract
{


    /**
     * giant account model
     *
     * @var Magegiant_GiantPoints_Model_Customer
     */
    protected $_giantCustomer = null;

    /**
     * current customer ID
     *
     * @var int
     */
    protected $_customerId = null;

    /**
     * current working store ID
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * get current customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    public function getCustomer()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $this->_customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();

            return $this->_customer;
        }
        if (Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();

            return $this->_customer;
        }

        return $this->_customer;
    }

    /**
     * get current customer ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        if (is_null($this->_customerId)) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->_customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();

                return $this->_customerId;
            } else {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            }
            if ($customerId) {
                $this->_customerId = $customerId;
            } else {
                $this->_customerId = 0;
            }
        }

        return $this->_customerId;
    }

    /**
     * get current working store id, used when checkout
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            if (Mage::app()->isSingleStoreMode()) {
                $this->_storeId = Mage::app()->getStore()->getId();
            } else if (Mage::app()->getStore()->isAdmin()) {
                $this->_storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
            } else {
                $this->_storeId = Mage::app()->getStore()->getId();
            }
        }

        return $this->_storeId;
    }

    /**
     * get current giant points customer account
     *
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getAccount()
    {
        if (is_null($this->_giantCustomer)) {
            $this->_giantCustomer = Mage::getModel('giantpoints/customer');
            if ($this->getCustomerId()) {
                $this->_giantCustomer->load($this->getCustomerId(), 'customer_id');
                $this->_giantCustomer->setData('customer', $this->getCustomer());
            }
        }

        return $this->_giantCustomer;
    }

    /**
     * get Reward by Customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getAccountByCustomer($customer)
    {
        $giantAccount = $this->getAccountByCustomerId($customer->getId());
        if (!$giantAccount->hasData('customer')) {
            $giantAccount->setData('customer', $customer);
        }

        return $giantAccount;
    }

    /**
     * get Reward Account by Customer ID
     *
     * @param int $customerId
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getAccountByCustomerId($customerId = null)
    {
        if (empty($customerId) || $customerId == $this->getCustomerId()
        ) {
            return $this->getAccount();
        }

        return Mage::getModel('giantpoints/customer')->load($customerId, 'customer_id');
    }

    /**
     * get giant points balance of current customer
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->getAccount()->getPointBalance();
    }

    /**
     * get string of points balance formated
     *
     * @return string
     */
    public function getBalanceFormated()
    {
        return Mage::helper('giantpoints')->addLabelForPoint(
            $this->getBalance(), $this->getStoreId()
        );
    }

    /**
     * get string of points balance formated
     * Balance is estimated after customer use point to spent
     *
     * @return string
     */
    public function getBalanceAfterSpentFormated()
    {
        return Mage::helper('giantpoints')->addLabelForPoint(
            $this->getBalance() - Mage::helper('giantpoints/conversion_spending')->getTotalPointSpent(), $this->getStoreId()
        );
    }

    /**
     * check show customer giant points on top link
     *
     * @param type $store
     * @return boolean
     */
    public function showOnToplink($store = null)
    {
        return Mage::helper('giantpoints/config')->allowShowOnTopLink($store);
    }

    /**
     * check customer can use point to spend for order or not
     *
     * @param type $store
     * @return boolean
     */
    public function isAllowSpend($store = null)
    {
        $minPoint = Mage::helper('giantpoints/config')->getMinimumRedeemPoint($store);
        if ($minPoint > $this->getBalance()) {
            return false;
        }

        return true;
    }

    /*Create Reward Customer*/
    public function createRewardCustomer($customer)
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardAccount         = Mage::getModel('giantpoints/customer');
        $rewardAccount->setCustomerId($customer->getId());
        if ($isSubscribedByDefault) {
            $rewardAccount
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        try {
            $rewardAccount->save();
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            return false;
        }

        return $rewardAccount;
    }
}
