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
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Settings extends Magegiant_GiantPoints_Block_Abstract
{

    /**
     * get current reward points account
     *
     * @return Magegiant_GiantPoints_Model_Customer
     */
    public function getRewardAccount()
    {
        $rewardAccount = $this->getRewardCustomer();
        if (!$rewardAccount->getId()) {
            $rewardAccount->setNotificationUpdate(0)
                ->setNotificationExpire(0);
        }

        return $rewardAccount;
    }

    /**
     * get setting action
     *
     * @return string
     */
    public function getSettingAction()
    {
        return 'setting';
    }
}
