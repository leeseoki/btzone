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
class Magegiant_GiantPoints_Block_Adminhtml_System_Config_Form_Field_Rate
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:70px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $money       = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $point       = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $currency    = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $convertIcon = '<img src="' . Mage::helper('giantpoints')->getConvertIconSrc() . '"/>';

        return $money . ' ' . $currency
        . $convertIcon . $point . Mage::helper('adminhtml')->__('Point') . ' ';
    }
}
