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
class Magegiant_GiantPoints_Model_Cron
{
    const STOP_MAIL = 'giantpoints_stop_email';

    public function processTransactions()
    {
        $this->expireTransactions();
        $this->completeOnholdTransation();
    }

    public function completeOnholdTransation()
    {
        $onholdTransactions = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addNotLockedFilter()
            ->addOnholdFilter();
        foreach ($onholdTransactions as $trans) {
            $trans->completeTransaction();
        }

        return $this;
    }

    public function expireTransactions()
    {
        $stores    = array();
        $allStores = true;
        foreach (Mage::app()->getStores(true) as $_store) {
            if (Mage::helper('giantpoints/config')->isEnabled($_store)) {
                $stores[$_store->getId()] = $_store->getId();
            } else {
                $allStores = false;
            }
        }
        // expire transactions
        $expiredTransactions = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addAvailableBalanceFilter()
            ->addNotLockedFilter()
            ->addExpiredFilter();
        if (count($expiredTransactions)) {
            $expiredTransactions->lock();
            $rewardAccount = Mage::getSingleton('giantpoints/customer');
            $customer      = Mage::getSingleton('customer/customer');
            foreach ($expiredTransactions as $_transaction) {
                try {
                    $_transaction->setRewardCustomer($rewardAccount->load($_transaction->getRewardId()));
                    $_transaction->setData('customer', $customer->load($_transaction->getCustomerId()));
                    $_transaction->expireTransaction();
                } catch (Exception $e) {
                    Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                }
            }
        }
        unset($expiredTransactions);
        // send before expire email to customer
        $beforeDays = array();
        foreach ($stores as $_store) {
            if (!Mage::helper('giantpoints/config')->isEmailEnabled($_store)) {
                $allStores = false;
                continue;
            }
            $_beforeDays = Mage::helper('giantpoints/config')->getSendEmailExpireBeforeDays($_store);
            if ($_beforeDays <= 0) {
                $allStores = false;
            } else {
                $beforeDays[$_beforeDays][$_store] = $_store;
            }
        }
        if ($allStores && count($beforeDays) == 1) { // all stores
            reset($beforeDays);
            $_beforeDays = key($beforeDays);
            $this->sendBeforeExpireEmail($_beforeDays);
        } elseif (count($beforeDays)) { // each group stores
            foreach ($beforeDays as $_beforeDays => $_storeIds) {
                $this->sendBeforeExpireEmail($_beforeDays, $_storeIds);
            }
        }

        return $this;
    }

    /**
     * send email to customer before transaction is expired
     *
     * @param int        $beforeDays
     * @param null|array $storeIds
     */
    public function sendBeforeExpireEmail($beforeDays, $storeIds = null)
    {
        $transactions = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addBalanceActiveFilter()
            ->addExpiredAfterDaysFilter($beforeDays);
        if (is_array($storeIds) && count($storeIds)) {
            $transactions->addFieldToFilter('store_id', array('in' => $storeIds));
        }
        if (!count($transactions)) {
            return;
        }
        $rewardAccount = Mage::getSingleton('giantpoints/customer');
        $customer      = Mage::getSingleton('customer/customer');
        $transIds      = array();
        foreach ($transactions as $transaction) {
            try {
                $transaction->setRewardCustomer($rewardAccount->load($transaction->getRewardId()));
                $transaction->setData('customer', $customer->load($transaction->getCustomerId()));
                if ($transaction->sendBeforeExpireEmail())
                    $transIds[] = $transaction->getId();
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }
        if (count($transIds)) {
            try {
                Mage::getResourceModel('giantpoints/transaction')->increaseExpireEmail($transIds);
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }
    }

    protected function _createRewardAccount($customer)
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardAccount         = Mage::getModel('giantpoints/customer');
        $rewardAccount->setCustomerId($customer->getId());
        if ($isSubscribedByDefault) {
            $rewardAccount
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        $rewardAccount->save();

        return $rewardAccount;
    }


}
