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
 * GiantPoints Helper
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Helper_Conversion_Abstract extends Mage_Core_Helper_Abstract
{

    /**
     * Cache helper data to Memory
     *
     * @var array
     */
    protected $_giantCache = array();

    /**
     * check cache is existed or not
     *
     * @param string $cacheKey
     * @return boolean
     */
    public function hasCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_giantCache)) {
            return true;
        }

        return false;
    }

    /**
     * save value to cache
     *
     * @param string $cacheKey
     * @param mixed  $value
     * @return Magegiant_GiantPoints_Helper_Conversion_Abstract
     */
    public function saveCache($cacheKey, $value = null)
    {
        $this->_giantCache[$cacheKey] = $value;

        return $this;
    }

    /**
     * get cache value by cache key
     *
     * @param  $cacheKey
     * @return mixed
     */
    public function getCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_giantCache)) {
            return $this->_giantCache[$cacheKey];
        }

        return null;
    }

    /**
     * get customer group id, depend on current checkout session (admin, frontend)
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if (!$this->hasCache('abstract_customer_group_id')) {
            if (Mage::app()->getStore()->isAdmin()) {
                $customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
                $this->saveCache('abstract_customer_group_id', $customer->getGroupId());
            } else {
                $this->saveCache('abstract_customer_group_id', Mage::getSingleton('customer/session')->getCustomerGroupId()
                );
            }
        }

        return $this->getCache('abstract_customer_group_id');
    }

    /**
     * get Website ID, depend on current checkout session (admin, frontend)
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if (!$this->hasCache('abstract_website_id')) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->saveCache('abstract_website_id', Mage::getSingleton('adminhtml/session_quote')->getStore()->getWebsiteId()
                );
            } else {
                $this->saveCache('abstract_website_id', Mage::app()->getStore()->getWebsiteId());
            }
        }

        return $this->getCache('abstract_website_id');
    }

    /**
     * get current checkout quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

}
