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
class Magegiant_GiantPoints_Model_Cookie extends Mage_Core_Model_Cookie
{
    const COOKIE_GIANTPOINT_REFERRAL = 'giantpoints_referral';

    /**
     *
     * @param type $name
     * @param type $type
     * @param type $option
     */
    public function getCookie($name = null)
    {
        return $this->get($name);
    }

    /**
     * set cookie
     *
     * @param type $name
     * @param type $value
     * @param type $option
     * @return type
     */
    public function setCookie($name, $value, $option = array('path' => '/', 'expire' => true))
    {
        return $this->set($name, $value, $option['expire'], $option['path']);
    }

}
