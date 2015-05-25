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
class Magegiant_GiantPoints_Model_System_Config_Source_Slider
{
    public function toOptionArray()
    {
        return array(
            array('value' => '#3399cc', 'label' => Mage::helper('giantpoints')->__('Default')),
            array('value' => 'orange', 'label' => Mage::helper('giantpoints')->__('Orange')),
            array('value' => 'green', 'label' => Mage::helper('giantpoints')->__('Green')),
            array('value' => 'black', 'label' => Mage::helper('giantpoints')->__('Black')),
            array('value' => 'blue', 'label' => Mage::helper('giantpoints')->__('Blue')),
            array('value' => 'darkblue', 'label' => Mage::helper('giantpoints')->__('Dark Blue')),
            array('value' => 'pink', 'label' => Mage::helper('giantpoints')->__('Pink')),
            array('value' => 'red', 'label' => Mage::helper('giantpoints')->__('Red')),
            array('value' => 'violet', 'label' => Mage::helper('giantpoints')->__('Violet')),
            array('value' => 'custom', 'label' => Mage::helper('giantpoints')->__('Custom')),
        );
    }
}

