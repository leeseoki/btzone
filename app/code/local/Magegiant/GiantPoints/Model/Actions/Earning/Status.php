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
 * @package     MageGiant_GiantPointsRule
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsRule Status Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPointsRule
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Actions_Earning_Status extends Varien_Object
{
    const STATUS_CANCELLED        = 1;
    const STATUS_PENDING_APPROVAL = 3;
    const STATUS_PENDING_EVENT    = 4;
    const STATUS_APPROVED         = 5;
    const STATUS_PENDING_TIME     = 6;

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_APPROVED         => Mage::helper('giantpoints')->__('Approved'),
            self::STATUS_PENDING_APPROVAL => Mage::helper('giantpoints')->__('Pending: Approval'),
        );
    }

    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $options;
    }

    public function toOptionArray()
    {
        return self::getOptionHash();
    }

}