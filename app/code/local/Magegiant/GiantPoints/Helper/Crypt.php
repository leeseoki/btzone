<?php
/**
 * MageGiant
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
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement.html
 */

/**
 * Decrypts a string using magento's functions
 *
 * @param string $code
 */

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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Crypt extends Mage_Core_Helper_Abstract
{
    public function decrypt($data)
    {
        return str_replace("\x0", '', trim($this->_getEncrypter()->decrypt(base64_decode((string)$data))));
    }


    /**
     * Encrypts a string using Magento'
     * s funcitons
     *
     * @param string $code
     */
    public function encrypt($data)
    {
        return base64_encode($this->_getEncrypter()->encrypt((string)$data));
    }

    /**
     * @return Mage_Core_Model_Encryption
     */
    protected function _getEncrypter()
    {
        return Mage::getSingleton('core/encryption');
    }

    public function getCustomerByCode($code)
    {
        $customer = Mage::getModel('customer/customer')->load($this->decrypt($code));
        if ($code && $customer->getId()) {
            return $customer;
        }

        return false;
    }

}