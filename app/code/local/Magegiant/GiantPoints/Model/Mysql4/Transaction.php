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
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('giantpoints/transaction', 'transaction_id');
    }

    /**
     * update point spent
     *
     * @param $transaction
     * @return $this
     */
    public function applyPointSpent($transaction)
    {
        $totalAmount = -$transaction->getPointAmount();
        if ($totalAmount <= 0) {
            return $this;
        }

        $read  = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        // Select all available transactions
        $selectSql = $read->select()->reset()
            ->from(array('t' => $this->getMainTable()), array('transaction_id', 'point_amount', 'point_spent'))
            ->where('customer_id = ?', $transaction->getCustomerId())
            ->where('point_amount > point_spent')
            ->where('status = ?', Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED)
            ->order(new Zend_Db_Expr('ISNULL(expiration_date) ASC, expiration_date ASC'));

        $trans = $read->fetchAll($selectSql);
        if (empty($trans) || !is_array($trans)) {
            return $this;
        }
        $usedIds   = array();
        $lastId    = 0;
        $lastSpent = 0;
        foreach ($trans as $tran) {
            $availableAmount = $tran['point_amount'] - $tran['point_spent'];
            if ($totalAmount < $availableAmount) {
                $lastSpent = $tran['point_spent'] + $totalAmount;
                $lastId    = $tran['transaction_id'];
                break;
            }
            $totalAmount -= $availableAmount;
            $usedIds[] = $tran['transaction_id'];
            if ($totalAmount == 0) {
                break;
            }
        }
        // Update all depend transactions
        if (count($usedIds)) {
            $write->update($this->getMainTable(), array(
                'point_spent' => new Zend_Db_Expr('point_amount')
            ), array(
                new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $usedIds) . ' )')
            ));
        }
        if ($lastId) {
            $write->update($this->getMainTable(), array(
                'point_spent' => new Zend_Db_Expr((string)$lastSpent)
            ), array(
                'transaction_id = ?' => $lastId
            ));
        }

        return $this;
    }

    /**
     * update point balance
     *
     * @param $transaction
     * @return $this
     */
    public function applyPointBalance($transaction)
    {
        $totalAmount = -$transaction->getPointAmount();
        if ($totalAmount <= 0) {
            return $this;
        }

        $read  = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        // Select all completed transactions
        $selectSql = $read->select()->reset()
            ->from(array('t' => $this->getMainTable()), array(
                'transaction_id', 'point_balance'
            ))
            ->where('customer_id = ?', $transaction->getCustomerId())
            ->where('order_id = ?', $transaction->getOrderId())
            ->where('action_type = ?', Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_EARNING)
            ->where('point_balance > 0');
        $trans     = $read->fetchAll($selectSql);
        if (empty($trans) || !is_array($trans)) {
            return $this;
        }
        $usedIds     = array();
        $lastId      = 0;
        $lastBalance = 0;
        foreach ($trans as $tran) {
            if ($totalAmount < $tran['point_balance']) {
                $lastId      = $tran['transaction_id'];
                $lastBalance = $tran['point_balance'] - $totalAmount;
                break;
            }
            $totalAmount -= $tran['point_balance'];
            $usedIds[] = $tran['transaction_id'];
            if ($totalAmount == 0) {
                break;
            }
        }

        // Update all depend transactions
        if (count($usedIds)) {
            $write->update($this->getMainTable(), array(
                'point_balance' => new Zend_Db_Expr('0')
            ), array(
                new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $usedIds) . ' )')
            ));
        }
        if ($lastId) {
            $write->update($this->getMainTable(), array(
                'point_balance' => new Zend_Db_Expr((string)$lastBalance),
            ), array(
                'transaction_id = ?' => $lastId
            ));
        }

        return $this;
    }

    /**
     * @param $transIds
     * @return $this
     */
    public function increaseExpireEmail($transIds)
    {
        $this->_getWriteAdapter()->update($this->getMainTable(), array(
            'expire_email_sent' => new Zend_Db_Expr('expire_email_sent + 1')
        ), array(
            new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $transIds) . ' )')
        ));

        return $this;
    }


}