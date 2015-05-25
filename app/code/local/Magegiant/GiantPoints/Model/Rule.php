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
class Magegiant_GiantPoints_Model_Rule extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('giantpoints/rule');
    }

    public function _afterLoad()
    {
        if ($this->hasConditionsSerialized()) {
            $this->setPointsConditions(Mage::helper('giantpoints')->unhashIt($this->getConditionsSerialized()));
            $this->unsConditionsSerialized();
        }
        if ($this->getOnholdDuration() != 0) {
            $this->setIsOnholdEnabled(1);
        }
        Mage::dispatchEvent('giantpoints_behavior_rule_after_load', array('rule' => $this));

        return parent::_afterLoad();
    }

    public function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->hasPointsConditions()) {
            $this->setConditionsSerialized(Mage::helper('giantpoints')->hashIt($this->getPointsConditions()));
            $this->unsPointsConditions();
        }

        // we handle these differently
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }

        // we handle these differently
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        return $this;
    }

    public function loadPost(array $rule)
    {
        foreach ($rule as $key => $value) {
            /**
             * convert dates into Zend_Date
             */
            if (in_array($key, array('from_date', 'to_date')) && $value) {
                $value = Mage::app()->getLocale()->date(
                    $value,
                    Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                    null,
                    false
                );
            }
            $this->setData($key, $value);
        }

        return $this;
    }

    /**
     * @param $customer
     * @param $action
     * @return null|Varien_Object
     */
    public function getRuleByCondition($customer, $condition)
    {
        $behavior_rule = $this->getCollection()
            ->addAvailableFilter($customer->getGroupId(), $customer->getWebsiteId())
            ->addRuleConditionFilter($condition)
            ->getFirstItem();
        if ($behavior_rule && $behavior_rule->getId()) {
            return $behavior_rule;
        }

        return null;

    }

    /**
     * @param $customer
     * @param $action
     * @return null|Varien_Object
     */
    public function getAllRulesByCondition($condition, $customer = null)
    {
        $behavior_rules = $this->getCollection()
            ->addRuleConditionFilter($condition);
        if ($customer && $customer->getId()) {
            $behavior_rules->addAvailableFilter($customer->getGroupId(), $customer->getWebsiteId());
        }
        if ($behavior_rules->getSize()) {
            return $behavior_rules;
        }

        return array();

    }

}