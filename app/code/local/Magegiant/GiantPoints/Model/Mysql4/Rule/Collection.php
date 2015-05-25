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
class Magegiant_GiantPoints_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/rule');
    }

    /**
     *
     * @param type $customerGroupId
     * @param type $websiteId
     * @param type $date
     */
    public function addAvailableFilter($customerGroupId, $websiteId, $date = null)
    {
        if (is_null($date)) {
            $date = Mage::helper('giantpoints')->getMageDate();
        }
        $this->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
            ->addFieldToFilter('website_ids', array('finset' => $websiteId));
        $this->getSelect()
            ->where("(from_date IS NULL) OR (DATE(from_date) <= ?)", $date)
            ->where("(to_date IS NULL) OR (DATE(to_date) >= ?)", $date);

        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function addRuleConditionFilter($condition)
    {
        $this->addFieldToFilter('conditions_serialized', Mage::helper('giantpoints')->hashIt($condition))
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', Varien_Data_Collection::SORT_ORDER_DESC);;

        return $this;
    }
}