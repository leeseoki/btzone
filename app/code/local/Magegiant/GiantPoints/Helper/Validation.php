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
class Magegiant_GiantPoints_Helper_Validation extends Mage_Core_Helper_Abstract
{
    /**
     * returns true if this is a valid e-mail address
     *
     * @param string $email
     */
    public function isValidEmail($email)
    {
        $validator = new Zend_Validate_EmailAddress();

        return $validator->isValid($email);
    }
    /**
     * get affiliate link, invited customer will be redirect to this link
     *
     * @param null $storeId
     * @return mixed
     */
    /**
     * Validate URL
     * Allows for port, path and query string validations
     *
     * @param    string $url string containing url user input
     * @return   boolean     Returns TRUE/FALSE
     */
    public function isValidUrl($url)
    {
        return is_string(filter_var($url, FILTER_VALIDATE_URL));
    }
}