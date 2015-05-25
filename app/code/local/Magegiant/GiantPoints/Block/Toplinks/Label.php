<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Toplinks_Label extends Magegiant_GiantPoints_Block_Abstract
{
    /**
     * set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/giantpoints/toplinks/label.phtml');
    }

    /**
     * get points summary
     *
     * @return string
     */
    public function getPointsSummary()
    {
        return $this->formatPointBalance();
    }

    protected function _toHtml()
    {
        if ($this->getCustomer() && $this->getCustomer()->getId()) {
            return parent::_toHtml();
        }
        return '';
    }
}
