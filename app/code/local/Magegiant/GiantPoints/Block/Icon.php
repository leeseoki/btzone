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
class Magegiant_GiantPoints_Block_Icon extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_giantPointsHtml = null;
    protected $_rewardAnchorHtml = null;

    /**
     * prepare block's layout
     *
     */
    public function _prepareLayout()
    {
        $this->setTemplate('magegiant/giantpoints/customer/account/icon.phtml');

        return parent::_prepareLayout();
    }

    /**
     * Render block HTML, depend on mode of name showed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('giantpoints/config')->getGeneralConfig('show_point_icon')) {
            return '';
        }
        if ($this->getIsAnchorMode()) {
            if (is_null($this->_rewardAnchorHtml)) {
                $html = parent::_toHtml();
                if ($html) {
                    $this->_rewardAnchorHtml = $html;
                } else {
                    $this->_rewardAnchorHtml = '';
                }
            }

            return $this->_rewardAnchorHtml;
        } else {
            if (is_null($this->_giantPointsHtml)) {
                $html = parent::_toHtml();
                if ($html) {
                    $this->_giantPointsHtml = $html;
                } else {
                    $this->_giantPointsHtml = '';
                }
            }

            return $this->_giantPointsHtml;
        }
    }

    /**
     * get Reward Info Link for giant points system
     *
     * @return string
     */
    public function getRewardInfoUrl()
    {
        return Mage::helper('giantpoints/config')->getRewardInfoUrl();
    }
}
