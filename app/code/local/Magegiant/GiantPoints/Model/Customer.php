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
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Customer extends Mage_Core_Model_Abstract
{
    /**
     * @var null
     */
    protected $_rewardAccount = null;

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

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/customer');
    }

    /**
     * get giant_points_customer by id
     *
     * @param $id
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomerById($id)
    {
        return $this->load($id, 'customer_id');
    }

    /**
     * get magento customer
     *
     * @return null
     */
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
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function getAccount()
    {
        if (is_null($this->_rewardAccount)) {
            $this->_rewardAccount = Mage::getModel('giantpoints/customer');
            if ($this->getCustomerId()) {
                $this->_rewardAccount->load($this->getCustomerId(), 'customer_id');
                $this->_rewardAccount->setData('customer', $this->getCustomer());
            }
        }

        return $this->_rewardAccount;
    }

    /**
     * @param $customer
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getAccountByCustomer($customer)
    {
        $rewardAccount = $this->getAccountByCustomerId($customer->getId());
        if (!$rewardAccount->hasData('customer')) {
            $rewardAccount->setData('customer', $customer);
        }
        $this->_rewardAccount = $rewardAccount;

        return $rewardAccount;
    }

    /**
     * get Giant Points Account by Customer ID
     *
     * @param int $customerId
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getAccountByCustomerId($customerId = null)
    {
        if (!$customerId || $customerId == $this->getCustomerId()) {
            return $this->getAccount();
        }
        $rewardAccount        = Mage::getModel('giantpoints/customer')->load($customerId, 'customer_id');
        $this->_rewardAccount = $rewardAccount;

        return $rewardAccount;
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
     * get customer transaction
     *
     * @param $customer
     * @return mixed
     */
    public function getCustomerTransactions($customer)
    {
        $customer = $this->getAccountByCustomer($customer);

        return Mage::getResourceModel('giantpoints/transaction_collection')->addFieldToFilter('reward_id', $customer->getId());
    }

}