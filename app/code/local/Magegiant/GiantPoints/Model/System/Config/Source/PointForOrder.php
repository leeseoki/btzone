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
class Magegiant_GiantPoints_Model_System_Config_Source_PointForOrder
{
    const FIRST_ORDER_ONLY = 1;
    const FOR_EACH_ORDER   = 2;

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::FIRST_ORDER_ONLY,
                'label' => Mage::helper('giantpoints')->__('First order')
            ),
            array(
                'value' => self::FOR_EACH_ORDER,
                'label' => Mage::helper('giantpoints')->__('Each order')
            )
        );
    }
}