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
class Magegiant_GiantPoints_Model_System_Config_Source_Pages
{
    const HOME_PAGE     = 'cmsindex';
    const CATEGORY_PAGE = 'catalogcategory';
    const PRODUCT_PAGE  = 'catalogproduct';


    public function getPageOption()
    {
        return array(
            self::HOME_PAGE     => Mage::helper('giantpoints')->__('Home page'),
            self::CATEGORY_PAGE => Mage::helper('giantpoints')->__('Product list'),
            self::PRODUCT_PAGE  => Mage::helper('giantpoints')->__('Product detail'),
        );
    }

    public function toOptionArray()
    {

        $options = array();
        foreach ($this->getPageOption() as $code => $label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }

        return $options;
    }
}

