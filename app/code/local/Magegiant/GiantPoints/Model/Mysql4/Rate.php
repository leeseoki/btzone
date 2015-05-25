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
class Magegiant_GiantPoints_Model_Mysql4_Rate extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('giantpoints/rate', 'rate_id');
    }

    public function loadRateByCustomerWebsiteDirection($rate, $customer, $website, $direction)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('FIND_IN_SET(?, customer_group_ids)', $customer->getGroupId())
            ->where('FIND_IN_SET(?, website_ids)', $website->getId())
            ->where('direction = ?', $direction)
            ->order('priority ' . Zend_Db_Select::SQL_DESC);;
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $rate->addData($data);
        }
        $this->_afterLoad($rate);

        return $this;
    }

}