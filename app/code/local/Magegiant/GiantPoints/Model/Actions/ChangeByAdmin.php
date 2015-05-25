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
 * Action change points by admin
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Model_Actions_ChangeByAdmin
    extends Magegiant_GiantPoints_Model_Actions_Abstract
    implements Magegiant_GiantPoints_Model_Actions_Interface
{
    protected $_actionType = Magegiant_GiantPoints_Model_Actions_Abstract::GIANTPOINTS_ACTION_TYPE_BOTH;

    /**
     * Calculate and return point amount that admin changed
     *
     * @return int
     */
    public function getPointAmount()
    {
        $actionObject = $this->getData('action_object');
        if (!is_object($actionObject)) {
            return 0;
        }

        return (int)$actionObject->getPointAmount();
    }

    /**
     * @return mixed|string
     */
    public function getComment()
    {
        $actionObject = $this->getData('action_object');
        if (!is_object($actionObject)) {
            return '';
        }

        return (string)$actionObject->getData('comment');
    }

    /**
     * @param null $transaction
     * @return mixed|string
     */
    public function getCommentHtml($transaction = null)
    {
        if (is_null($transaction)) {
            return $this->getComment();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            return '<strong>' . $transaction->getNotice() . ': </strong>' . $transaction->getComment();
        }

        return $transaction->getComment();
    }

    /**
     * @return $this|array
     */
    public function updateTransaction()
    {
        $transactionData = array(
            'status'  => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED,
            'comment' => $this->getComment(),
        );
        if ($user = Mage::getSingleton('admin/session')->getUser()) {
            $transactionData['notice'] = Mage::helper('giantpoints')->__('Create by ') . $user->getUsername();
        }
        $actionObject = $this->getData('action_object');
        if (is_object($actionObject) && $actionObject->getExpirationDay() && $this->getPointAmount() > 0) {
            $expireTime                         = $actionObject->getExpirationDay() * 86400 + Mage::helper('giantpoints')->getMageTime();
            $transactionData['expiration_date'] = date('Y-m-d H:m:i', $expireTime);
        }
        $this->setTransaction($transactionData);

        return parent::updateTransaction();
    }
}
