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
class Magegiant_GiantPoints_Model_Transaction extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING   = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED  = 3;
    const STATUS_EXPIRED   = 4;
    protected $_rewardCustomer;
    /* XML email path config*/
    const XML_PATH_EMAIL_ENABLE                   = 'giantpoints/email/enable';
    const XML_PATH_EMAIL_SENDER                   = 'giantpoints/email/sender';
    const XML_PATH_EMAIL_UPDATE_POINT_BALANCE     = 'giantpoints/email/update_balance';
    const XML_PATH_EMAIL_BEFORE_POINT_EXPIRE      = 'giantpoints/email/before_expire_transaction';
    const XML_PATH_EMAIL_POINT_EXPIRE_BEFORE_DAYS = 'giantpoints/email/before_expire_days';

    /*End XML emal path*/
    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/transaction');
    }

    protected function _beforeSave()
    {
        $this->setData('change_date', Mage::helper('giantpoints')->getMageDate('Y-m-d H:i:s'));

        return parent::_beforeSave();
    }

    /**
     * get transaction status as hash array
     *
     * @return array
     */
    public function getStatusHash()
    {
        return array(
            self::STATUS_PENDING   => Mage::helper('giantpoints')->__('Pending'),
            self::STATUS_COMPLETED => Mage::helper('giantpoints')->__('Completed'),
            self::STATUS_CANCELED  => Mage::helper('giantpoints')->__('Canceled'),
            self::STATUS_EXPIRED   => Mage::helper('giantpoints')->__('Expired'),
        );
    }

    /**
     * get transaction status as hash array
     *
     * @return array
     */
    public function getStatusArray()
    {
        $options = array();
        foreach ($this->getStatusHash() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }

        return $options;
    }

    /**
     * get reward customer
     *
     * @return reward customer object
     */
    public function getRewardCustomer()
    {
        if (!$this->_rewardCustomer) {
            $this->setRewardCustomer(Mage::getModel('giantpoints/customer')->load($this->getRewardId()));
        }

        return $this->_rewardCustomer;
    }

    /**
     * set reward customer
     *
     * @param $customer
     */
    public function setRewardCustomer($customer)
    {
        $this->_rewardCustomer = $customer;
    }

    /**
     * add transaction to change giant points
     *
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function addTransaction($data = array())
    {
        $this->addData($data);

        if (!$this->getPointAmount()) {
            throw new Exception(
                Mage::helper('giantpoints')->__('Zero transaction amount')
            );
        }
        $rewardCustomer = Mage::getModel('giantpoints/customer')->getAccountByCustomer($this->getCustomer());
        if (!$rewardCustomer || !$rewardCustomer->getId()) {
            $rewardCustomer = $this->createRewardCustomer();
        }
        $this->setRewardCustomer($rewardCustomer);
        $this->setRewardId($rewardCustomer->getId());
        if ($rewardCustomer->getPointBalance() + $this->getPointAmount() < 0) {
            throw new Exception(
                Mage::helper('giantpoints')->__('Account balance is not enough to create this transaction.')
            );
        }
        // set default status if not define

        if (!$this->getData('status')) {
            $this->setData('status', self::STATUS_PENDING);
        }
        switch ($this->getActionType()) {
            case Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING:
                $this->_addEarningTransaction();
                break;
            case Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_SPENDING:
                $this->_addSpendingTransaction();
                break;
            default:
                $this->_addDefaultTransaction();
                break;
        }

        return $this;
    }

    /**
     * create reward customer if not exist
     */
    public function createRewardCustomer()
    {
        $isSubscribedByDefault = Mage::helper('giantpoints/config')->getIsSubscribedByDefault();
        $rewardCustomer        = Mage::getModel('giantpoints/customer');
        $rewardCustomer->setCustomerId($this->getCustomerId())
            ->setData('point_balance', 0)
            ->setData('spent_balance', 0)
            ->setData('notification_update', 0)
            ->setData('notification_expire', 0);

        if ($isSubscribedByDefault) {
            $rewardCustomer
                ->setNotificationUpdate(1)
                ->setNotificationExpire(1);
        }
        try {
            $rewardCustomer->save();
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

        return $rewardCustomer;
    }

    /**
     * add action type is earning
     *
     * @return $this
     * @throws Exception
     */
    protected function _addEarningTransaction()
    {
        if ($this->getPointAmount() < 0) {
            throw new Exception(Mage::helper('giantpoints')->__('points amount must be greater 0'));
        }
        $rewardCustomer = $this->getRewardCustomer();
        $this->setPointBalance($this->getPointAmount());
        if ($this->getData('status') == self::STATUS_COMPLETED) {
            $maxBalance = Mage::helper('giantpoints/config')->getMaxPointPerCustomer($this->getStoreId());
            if ($maxBalance > 0 && $rewardCustomer->getPointBalance() + $this->getPointAmount() > $maxBalance) {
                if ($maxBalance > $rewardCustomer->getPointBalance()) {
                    $this->setPointAmount($maxBalance - $rewardCustomer->getPointBalance());
                    $this->setPointBalance($maxBalance - $rewardCustomer->getPointBalance());
                    $rewardCustomer->setPointBalance($maxBalance);
                } else {
                    return $this;
                }
            } else {
                $this->setPointBalance($this->getPointAmount());
                $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() + $this->getPointAmount());
            }
            if ($this->getData('action_type') == Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING) {
                $rewardCustomer->setPointSpent($rewardCustomer->getPointSpent() - $this->getPointAmount());
                $this->_getResource()->applyPointSpent($this);
            }
            try {
                $this->save();
                $rewardCustomer->save();
                $this->sendUpdateBalanceEmail($rewardCustomer);
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        } else {
            $this->save();
        }
    }

    /**
     * add action type is spending
     * return $this
     */
    protected function _addSpendingTransaction()
    {
        if ($this->getPointAmount() > 0)
            throw new Exception(Mage::helper('giantpoints')->__('points amount must be lower 0'));
        $rewardCustomer = $this->getRewardCustomer();
        $rewardCustomer->setPointSpent($rewardCustomer->getPointSpent() - $this->getPointAmount());
        $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() + $this->getPointAmount());
        if (!$this->getData('status')) {
            $this->setData('status', self::STATUS_COMPLETED);
        }
        try {
            $rewardCustomer->save();
            $this->save();
            if ($this->getData('action_type') == Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_REFUNDING) {
                // Update balance points for transaction depend on account/ order
                $this->_getResource()->applyPointBalance($this);
            }
            $this->_getResource()->applyPointSpent($this);
            $this->sendUpdateBalanceEmail($rewardCustomer);
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

    }

    /**
     * process action type is both
     * return
     */
    protected function _addDefaultTransaction()
    {
        if ($this->getPointAmount() > 0) {
            $this->_addEarningTransaction();
        } else {
            $this->_addSpendingTransaction();
        }
    }


    /**
     * complete transaction
     *
     * @return $this
     * @throws Exception
     */
    public function completeTransaction()
    {
        if (!$this->getId() || !$this->getCustomerId()
            || !$this->getRewardCustomer() || $this->getPointAmount() <= 0
            || $this->getStatus() != self::STATUS_PENDING
        ) {
            throw new Exception(Mage::helper('giantpoints')->__('Invalid transaction data to complete.'));
        }
        $rewardCustomer = $this->getRewardCustomer();
        // dispatch event when complete a transaction
        $this->setStatus(self::STATUS_COMPLETED);

        $maxBalance = $maxBalance = Mage::helper('giantpoints/config')->getMaxPointPerCustomer($this->getStoreId());
        if ($maxBalance > 0 && $this->getPointBalance() > 0
            && $rewardCustomer->getPointBalance() + $this->getPointBalance() > $maxBalance
        ) {
            if ($maxBalance > $rewardCustomer->getPointBalance()) {
                $this->setPointAmount($maxBalance - $rewardCustomer->getPointBalance() + $this->getPointAmount() - $this->getPointBalance());
                $this->setPointBalance($maxBalance - $rewardCustomer->getPointBalance());
                $rewardCustomer->setPointBalance($maxBalance);
                $this->sendUpdateBalanceEmail($rewardCustomer);
            } else {
                throw new Exception(
                    Mage::helper('giantpoints')->__('Maximum points allowed in account balance is %s.', $maxBalance)
                );
            }
        } else {
            $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() + $this->getPointBalance());
            $this->sendUpdateBalanceEmail($rewardCustomer);
        }

        // Save reward account and transaction to database
        $rewardCustomer->save();

        $this->save();

        return $this;
    }

    /**
     * Cancel Transaction, allow for Pending, On Hold and Completed transaction
     * only cancel transaction with amount > 0
     * Cancel mean that similar as we do not have this transaction
     *
     * @return Magegiant_GiantPoints_Model_Transaction
     */
    public function cancelTransaction()
    {
        if (!$this->getId() || !$this->getCustomerId()
            || !$this->getRewardId() || $this->getPointAmount() <= 0
            || $this->getStatus() > self::STATUS_COMPLETED || !$this->getStatus()
        ) {
            throw new Exception(Mage::helper('giantpoints')->__('Invalid transaction data to cancel.'));
        }


        if ($this->getStatus() != self::STATUS_COMPLETED) {
            $this->setStatus(self::STATUS_CANCELED);
            $this->save();

            return $this;
        }
        $this->setStatus(self::STATUS_CANCELED);
        $rewardCustomer = $this->getRewardCustomer();
        if ($rewardCustomer->getPointBalance() < $this->getPointBalance()) {
            throw new Exception(Mage::helper('giantpoints')->__('Account balance is not enough to cancel.'));
        }
        $rewardCustomer->setPointBalance($rewardCustomer->getPointBalance() - $this->getPointBalance());
        $this->sendUpdateBalanceEmail($rewardCustomer);
        // Save reward account and transaction to database
        $rewardCustomer->save();
        $this->save();

        // Change point used for other transaction
        if ($this->getPointSpent() > 0) {
            $pointAmount = $this->getPointAmount();
            $this->setPointAmount(-$this->getPointSpent());
            $this->_getResource()->applyPointSpent($this);
            $this->setPointAmount($pointAmount);
        }

        return $this;
    }

    /**
     * Expire Transaction, allow for Pending, On Hold and Completed transaction
     * only expire transaction with amount > 0
     *
     * @return $this
     */
    public function expireTransaction()
    {
        if (!$this->getId() || !$this->getCustomerId()
            || !$this->getRewardId() || $this->getPointAmount() <= $this->getPointSpent()
            || $this->getStatus() > self::STATUS_COMPLETED || !$this->getStatus()
            || strtotime($this->getExpirationDate()) > time() || !$this->getExpirationDate()
        ) {
            throw new Exception(Mage::helper('giantpoints')->__('Invalid transaction data to expire.'));
        }
        if ($this->getStatus() != self::STATUS_COMPLETED) {
            $this->setStatus(self::STATUS_EXPIRED);
            $this->save();

            return $this;
        }

        $this->setStatus(self::STATUS_EXPIRED);
        $rewardCustomer = $this->getRewardCustomer();
        $rewardCustomer->setPointBalance(
            $rewardCustomer->getPointBalance() - $this->getPointAmount() + $this->getPointSpent()
        );
        $this->sendUpdateBalanceEmail($rewardCustomer);

        // Save reward account and transaction to database
        $rewardCustomer->save();
        $this->save();

        return $this;
    }

    /**
     * get status label of transaction
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statushash = $this->getStatusHash();
        if (isset($statushash[$this->getStatus()])) {
            return $statushash[$this->getStatus()];
        }

        return '';
    }

    /**
     * send email update point balance
     *
     * @param null $rewardCustomer
     * @return $this
     */
    public function sendUpdateBalanceEmail($rewardCustomer = null)
    {
        if (!Mage::helper('giantpoints/config')->isEmailEnabled() || $this->getActionCode() == 'customer_birthday') {
            return $this;
        }
        if (is_null($rewardCustomer)) {
            $rewardCustomer = $this->getRewardCustomer();
        }
        if (!$rewardCustomer->getNotificationUpdate()) {
            return $this;
        }
        $customer = $this->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->load($rewardCustomer->getData('customer_id'));
        }
        if (!$customer->getId()) {
            return $this;
        }
        $store     = Mage::app()->getStore($this->getStoreId());
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        Mage::getModel('core/email_template')
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => $store->getId()
            ))->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_UPDATE_POINT_BALANCE, $store),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $store),
                $customer->getEmail(),
                $customer->getName(),
                array(
                    'store'         => $store,
                    'customer'      => $customer,
                    'comment'       => $this->getComment(),
                    'amount'        => $this->getPointAmount(),
                    'total'         => $rewardCustomer->getPointBalance(),
                    'point_amount'  => Mage::helper('giantpoints')->addLabelForPoint($this->getPointAmount(), $store->getId()),
                    'point_balance' => Mage::helper('giantpoints')->addLabelForPoint($rewardCustomer->getPointBalance(), $store->getId()),
                    'status'        => $this->getStatusLabel(),
                )
            );

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * send email before point exprire
     *
     * @return $this
     */
    public function sendBeforeExpireEmail($rewardCustomer = null)
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_EMAIL_ENABLE, $this->getStoreId())) {
            return $this;
        }
        if (is_null($rewardCustomer)) {
            $rewardCustomer = $this->getRewardCustomer();
        }
        if (!$rewardCustomer->getNotificationExpire()) {
            return $this;
        }
        $customer = $this->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->load($rewardCustomer->getCustomerId());
        }
        if (!$customer->getId()) {
            return $this;
        }

        $store     = Mage::app()->getStore($this->getStoreId());
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $mail = Mage::getModel('core/email_template');
        $mail->setDesignConfig(array(
            'area'  => 'frontend',
            'store' => $store->getId()
        ))->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_BEFORE_POINT_EXPIRE, $store),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $store),
                $customer->getEmail(),
                $customer->getName(),
                array(
                    'store'          => $store,
                    'customer'       => $customer,
                    'comment'        => $this->getComment(),
                    'amount'         => $this->getPointAmount(),
                    'spent'          => $this->getPointSpent(),
                    'total'          => $rewardCustomer->getPointBalance(),
                    'point_amount'   => Mage::helper('giantpoints')->addLabelForPoint($this->getPointAmount(), $store->getId()),
                    'point_spent'    => Mage::helper('giantpoints')->addLabelForPoint($this->getPointSpent(), $store->getId()),
                    'point_balance'  => Mage::helper('giantpoints')->addLabelForPoint($rewardCustomer->getPointBalance(), $store->getId()),
                    'status'         => $this->getStatusLabel(),
                    'expirationdays' => round((strtotime($this->getExpirationDate()) - Mage::helper('giantpoints')->getMageTime()) / 86400),
                    'expirationdate' => Mage::getModel('core/date')->date('M d, Y H:i:s', $this->getExpirationDate()),
                )
            );

        $translate->setTranslateInline(true);
        if ($mail->getSentSuccess()) {
            return true;
        }

        return false;
    }


}