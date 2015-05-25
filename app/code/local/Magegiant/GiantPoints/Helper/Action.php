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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Action extends Mage_Core_Helper_Abstract
{
    /**
     *
     */
    const CONFIG_ACTION_NODE = 'global/giantpoints/actions';

    /**
     * get action node in config.xml
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getActionNode()
    {
        $config = Mage::getConfig()->getNode(self::CONFIG_ACTION_NODE);
        if (!is_null($config))
            return $config;

        return null;
    }

    /**
     * get action model
     *
     * @param $action_code
     * @return SimpleXMLElement[]
     */
    public function getActionModel($action_code)
    {
        $actionClass = $this->getActionNode()->$action_code;
        $model       = Mage::getSingleton($actionClass);
        if (!($model instanceof Magegiant_GiantPoints_Model_Actions_Abstract)) {
            throw new Exception($this->__('Action model need extend from %s',
                    'Magegiant_GiantPoints_Model_Actions_Abstract')
            );
        }
        if (!$model->getActionCode()) {
            $model->setActionCode($action_code);
        }

        return $model;
    }

    /**
     * create transaction
     *
     * @param       $action_code
     * @param array $additionalData =array('action_object'=>$object,'customer'=>$customer,'notice'=>$notice)
     * @return false|Mage_Core_Model_Abstract
     * @throws Exception
     */
    public function createTransaction($action_code, $additionalData = array('action_object' => null, 'customer' => null, 'notice' => array()))
    {
        if (is_null($additionalData['customer'])) {
            throw new Exception($this->__('Customer must be existed.'));
        }
        $actionModel = $this->getActionModel($action_code);
        /**
         * set transaction data
         */
        $actionModel->setData($additionalData)->updateTransaction();
        $transaction = Mage::getModel('giantpoints/transaction');
        if (is_array($actionModel->getTransaction())) {
            $transaction->setData($actionModel->getTransaction());
        }

        if (!$transaction->hasData('store_id')) {
            $transaction->setData('store_id', Mage::app()->getStore()->getId());
        }
        $customer = $additionalData['customer'];
        if (array_key_exists('notice', $additionalData)) {
            $notice = $additionalData['notice'];
            if (is_array($notice) && array_key_exists('url', $notice) && Mage::helper('giantpoints/validation')->isValidUrl($notice['url'])) {
                $array_url     = explode('?', $notice['url']);
                $notice['url'] = $array_url[0];
            }
            if (is_array($notice))
                $notice = Mage::helper('core')->jsonEncode($notice);
        } else {
            $notice = '';
        }
        try {
            $transaction->addTransaction(array(
                'point_amount'   => (int)$actionModel->getPointAmount(),
                'customer_id'    => $customer->getId(),
                'customer'       => $customer,
                'customer_email' => $customer->getEmail(),
                'action_code'    => $action_code,
                'action_type'    => $actionModel->getActionType(),
                'change_date'    => Mage::helper('giantpoints')->getMageDate('Y/m/d H:m:i'),
                'notice'         => $notice,
            ));
        } catch (Exception $e) {
            Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
        }

        return $transaction;
    }

}