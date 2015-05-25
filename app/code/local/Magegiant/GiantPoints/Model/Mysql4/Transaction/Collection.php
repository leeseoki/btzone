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
 * Giantpoints Resource Collection Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Mysql4_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    const OLD_LOCK_DAYS = 3;

    protected $_isLocked;

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/transaction');
    }


    /**
     * add avaiable balance filter
     *
     * @return $this
     */
    public function addAvailableBalanceFilter()
    {

        $this->getSelect()->where('point_amount > point_spent')->where('point_amount > 0');

        return $this;
    }

    public function addBalanceActiveFilter()
    {
        $this->getSelect()->where('status =?', Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED);
        $this->getSelect()->where('point_amount > point_spent')->where('point_amount > 0');

        return $this;
    }

    public function addNotLockedFilter()
    {
        $this->getSelect()->where('COALESCE(is_locked,0) < 1');

        return $this;
    }

    public function addExpiredFilter()
    {
        $curZendDate = new Zend_Date();
        $curDateTime = $curZendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->getSelect()->where('expiration_date <= ?', $curDateTime);

        return $this;
    }

    public function addOnholdFilter()
    {
        $curZendDate = new Zend_Date();
        $curDateTime = $curZendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->getSelect()->where('onhold_date <= ?', $curDateTime);

        return $this;
    }

    /**
     * get total by field of this collection
     *
     * @param string $field
     * @return number
     */
    public function getFieldTotal($field = 'point_amount')
    {
        $this->_renderFilters();

        $sumSelect = clone $this->getSelect();
        $sumSelect->reset(Zend_Db_Select::ORDER);
        $sumSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $sumSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $sumSelect->reset(Zend_Db_Select::COLUMNS);

        $sumSelect->columns("SUM(`$field`)");

        return $this->getConnection()->fetchOne($sumSelect, $this->_bindParams);
    }

    /**
     * lock transaction
     *
     * @param int $state
     * @return $this
     */
    public function lock($state = 1)
    {
        $onlyWhere = preg_replace('/.*(where.*)/i', '$1', $this->getSelect()->assemble());
        $lockQuery
                   = "UPDATE `" . $this->getMainTable() . "` SET is_locked=" . intval($state) . ", lock_changed_date='" . now()
            . "' $onlyWhere";
        $this->getResource()->getReadConnection()->raw_query($lockQuery);
        $this->_isLocked = $state;

        return $this;
    }

    /**
     * unlock transaction
     *
     * @return $this
     */
    public function unlock()
    {
        return $this->lock(0);
    }

    /**
     * check earned point to day
     *
     * @param $dateTimestamp
     * @return $this
     */
    public function limitByDay($dateTimestamp)
    {
        $currentDate = date('Y-m-d', $dateTimestamp);
        $this->getSelect()->where('date(change_date) = ?', $currentDate);

        return $this;
    }

    public function addExpiredAfterDaysFilter($daysToAdd)
    {
        $currentZendDate = new Zend_Date();
        $dateAfterDays   = $currentZendDate->addDay($daysToAdd + 1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $this
            ->getSelect()
            ->where('expiration_date < ?', $dateAfterDays)
            ->where('expire_email_sent = 0')
            ->order('reward_id');

        return $this;
    }

    /**
     * Already shared Url
     *
     * @param $rewardId
     * @param $url
     * @param $actionCode
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function hasAlreadyUrl($rewardId, $url, $actionCode, $status = null)
    {
        $filter_url = $this->_removeCodeFromUrl($url);
        $this->addFieldToFilter('reward_id', $rewardId)
            ->addFieldToFilter('notice', Mage::helper('core')->jsonEncode(array('url' => $filter_url)))
            ->addFieldToFilter('action_code', $actionCode);
        if (!is_null($status)) {
            $this->addFieldToFilter('status', $status);
        }

        return $this;
    }

    /**
     * Already shared Purchase
     *
     * @param $rewardId
     * @param $actionCode
     * @param $product_id
     * @param $order_id
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function hasAlreadyPurchase($rewardId, $actionCode, $product_id, $order_id)
    {
        $filter      = Mage::helper('core')->jsonEncode(
            array('product_id' => $product_id,
                  'order_id'   => $order_id
            )
        );
        $alreadyUrls = $this->addFieldToFilter('reward_id', $rewardId)
            ->addFieldToFilter('notice', $filter)
            ->addFieldToFilter('action_code', $actionCode);

        return $alreadyUrls;
    }

    /**
     * Already sent email to friends
     *
     * @param $rewardId
     * @param $actionCode
     * @param $product_id
     * @param $order_id
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function hasAlreadySent($rewardId, $actionCode, $notice)
    {
        $filter      = Mage::helper('core')->jsonEncode($notice);
        $alreadySent = $this->addFieldToFilter('reward_id', $rewardId)
            ->addFieldToFilter('notice', $filter)
            ->addFieldToFilter('action_code', $actionCode);

        return $alreadySent;
    }

    protected function _removeCodeFromUrl($url)
    {
        $array_url = explode('?', $url);

        return $array_url[0];
    }

    public function getTransactionByNotice($customer_id, $action_code, $notice)
    {
        $transactions = $this->addFieldToFilter('customer_id', $customer_id)
            ->addFieldToFilter('action_code', $action_code)
            ->addFieldToFilter('notice', Mage::helper('core')->jsonEncode($notice));

        return $transactions;
    }
}