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
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Mysql4_Setup extends Mage_Core_Model_Resource_Setup
{
    const XML_BIRTHDAY_CONFIGURATION = 'customer/address/dob_show';

    /**
     * update option insert birthday
     */
    public function updateBirthdayConfig()
    {
        $birthdayConfig = Mage::getStoreConfig(self::XML_BIRTHDAY_CONFIGURATION);
        if ($birthdayConfig == '') {
            Mage::getModel('core/config')->saveConfig('path/to/your/variable', 'opt');
        }
    }


}
