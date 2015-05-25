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
class Magegiant_GiantPoints_Block_Social_Earning extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_helperBehavior = null;
    protected $_helperRefer = null;

    public function __construct()
    {
        parent::_construct();
        if (Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsBehavior'))
            $this->_helperBehavior = Mage::helper('giantpointsbhv/config');
        if (Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsRefer'))
            $this->_helperRefer = Mage::helper('giantpointsrefer/config');
    }

    protected function _isShowFaceBook()
    {
        if (!$this->_helperBehavior)
            return false;
        $page = $this->getCurrentPage();
        if ($this->_helperBehavior->isShowFacebookLike()
            && in_array($page, $this->_helperBehavior->getFacebookPageView())
        ) {
            if ($this->getPointsForFacebook()) {
                $message = $this->_helperBehavior->__('You will earn %s for a Facebook like.', Mage::helper('giantpoints')->addLabelForPoint($this->getPointsForFacebook()));
                $this->createMessage($message);
            }

            return true;
        }

        return false;
    }

    /**
     * Check google earning is enabled
     *
     * @return bool
     */
    protected function _isShowGoogle()
    {
        if (!$this->_helperBehavior)
            return false;
        $page = $this->getCurrentPage();
        if ($this->_helperBehavior->isShowGoogle()
            && in_array($page, $this->_helperBehavior->getGooglePageView())
        ) {
            if ($this->getPointsForGoogle()) {
                $message = $this->_helperBehavior->__('You will earn %s for a Google plus +1.', Mage::helper('giantpoints')->addLabelForPoint($this->getPointsForGoogle()));
                $this->createMessage($message);
            }

            return true;
        }

        return false;
    }

    protected function _isShowTwitter()
    {
        if (!$this->_helperBehavior)
            return false;
        $page = $this->getCurrentPage();
        if ($this->_helperBehavior->isShowTwitter()
            && in_array($page, $this->_helperBehavior->getTwitterPageView())
        ) {
            if ($this->getPointsForTwitter()) {
                $message = $this->_helperBehavior->__('You will earn %s for a Twitter tweet.', Mage::helper('giantpoints')->addLabelForPoint($this->getPointsForTwitter()));
                $this->createMessage($message);
            }

            return true;
        }

        return false;
    }

    protected function _isShowSharing()
    {
        if (!$this->_helperRefer)
            return false;
        $page = $this->getCurrentPage();
        if ($this->_helperRefer->isEnabled()
            && in_array($page, Mage::helper('giantpointsrefer/config')->getSharingPageView())
        ) {
            return true;
        }

        return false;
    }

    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function createMessage($message)
    {
        if (!Mage::registry('social_message')) {
            Mage::register('social_message', $message);
        } else {
            Mage::unregister('social_message');
            Mage::register('social_message', $this->_helper->__('Like or share to receive points'));
        }
    }

    public function getRewardEarnInfo()
    {
        if (Mage::helper('giantpoints/config')->isEnabledSocialEarnMessage()) {
            return Mage::registry('social_message');
        }

        return '';
    }

    public function getCurrentUrl()
    {
        if ($this->getReferralCode() == '')
            return parent::getCurrentUrl();

        return parent::getCurrentUrl() . '?cod=' . $this->getReferralCode();
    }

    /**
     * Check Social Earning is enabled
     *
     * @return bool
     */
    public function canShow()
    {
        if ($this->_isShowFaceBook() || $this->_isShowGoogle() || $this->_isShowTwitter() || $this->_isShowSharing())
            return true;

        return false;
    }
}
