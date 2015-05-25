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
class Magegiant_GiantPoints_Model_System_Config_Source_DiscountType
{
    const TYPE_FIXED      = 1;
    const TYPE_PERCENTAGE = 2;

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TYPE_FIXED,
                'label' => Mage::helper('giantpoints')->__('Fixed')
            ),
            array(
                'value' => self::TYPE_PERCENTAGE,
                'label' => Mage::helper('giantpoints')->__('Percentage')
            )
        );
    }
}