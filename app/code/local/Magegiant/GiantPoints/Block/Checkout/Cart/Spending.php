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

/**
 * GiantPoints Show Spending Point on Shopping Cart Page
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Checkout_Cart_Spending extends Magegiant_GiantPoints_Block_Abstract
{

    /**
     *
     * @return Magegiant_GiantPoints_Helper_Spending_Point
     */
    public function getSpendingHelper()
    {
        return Mage::helper('giantpoints/spending_point');
    }

    /**
     * call method that defined from spending helper
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $helper = $this->getSpendingHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
        }

        return parent::__call($method, $args);
    }
}
