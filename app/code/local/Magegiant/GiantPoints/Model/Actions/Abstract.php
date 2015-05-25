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
class Magegiant_GiantPoints_Model_Actions_Abstract extends Varien_Object
{
    /**
     * Action Code
     *
     * @var string
     */
    protected $_actionCode = null;
    protected $_actionType = null;
    /**
     * define action type
     */
    const GIANTPOINTS_ACTION_TYPE_EARNING   = 1;
    const GIANTPOINTS_ACTION_TYPE_SPENDING  = 2;
    const GIANTPOINTS_ACTION_TYPE_REFUNDING = 3;
    const GIANTPOINTS_ACTION_TYPE_BOTH      = 4;

    /**
     * action data
     */
    protected $_actionData = array();

    /**
     * transaction data
     */
    protected $_transactionData = array();

    /**
     * get action code
     *
     * @return string
     */
    public function getActionCode()
    {
        return $this->_actionCode;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setActionCode($value)
    {
        $this->_actionCode = $value;

        return $this;
    }

    /**
     * get action data
     *
     * @return mixed
     */
    public function getActionData()
    {
        return $this->_actionData;
    }

    /**
     * set action data
     *
     * @param array|string $data
     * @return Varien_Object|void
     */
    public function setActionData($data)
    {
        $this->_actionData = $data;
    }

    /**
     * get expire days
     *
     * @return int
     */
    public function getExpireDays($storeId = null)
    {
        return Mage::helper('giantpoints/config')->getExpireDays($storeId);
    }

    /**
     * get expire date
     *
     * @return date formated
     */
    public function getExpirationDate($storeId = null, $expireDays = null)
    {
        if ($expireDays) {
            $expireTime = $expireDays * 86400 + Mage::helper('giantpoints')->getMageTime();

            return date('Y-m-d', $expireTime);
        }
        if ($this->getExpireDays($storeId)) {
            $expireTime = $this->getExpireDays($storeId) * 86400 + Mage::helper('giantpoints')->getMageTime();

            return date('Y-m-d', $expireTime);
        }

        return null;

    }

    public function getOnholdDate($days)
    {
        $date = $days * 86400 + Mage::helper('giantpoints')->getMageTime();

        return date('Y-m-d', $date);
    }


    /**
     * get action type option
     *
     * @return array
     */
    public function getActionsTypeOption()
    {
        return array(
            self::GIANTPOINTS_ACTION_TYPE_BOTH     => Mage::helper('giantpoints')->__('Both'),
            self::GIANTPOINTS_ACTION_TYPE_EARNING  => Mage::helper('giantpoints')->__('Earning'),
            self::GIANTPOINTS_ACTION_TYPE_SPENDING => Mage::helper('giantpoints')->__('Spending'),

        );
    }

    /**
     * get action type array
     *
     * @return array
     */
    public function getActionsTypeArray()
    {
        $actionType = array();
        foreach ($this->getActionsTypeOption() as $value => $label) {
            $actionType[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $actionType;
    }

    /**
     * set transaction data
     *
     * @param $transaction
     */
    public function setTransaction($transaction)
    {
        $this->_transactionData = $transaction;
    }

    /**
     * get transaction
     *
     * @return array
     */
    public function getTransaction()
    {
        return $this->_transactionData;
    }

    /**
     * prepare transaction
     *
     * @return $this
     */
    public function updateTransaction()
    {
        return $this;
    }

    public function getActionType()
    {
        return $this->_actionType;
    }


}
