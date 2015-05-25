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
class Magegiant_GiantPoints_Block_Catalog_Product_List_Abstract extends Magegiant_GiantPoints_Block_Abstract
{
    protected $_product = null;

    /**
     * @return false|Mage_Core_Model_Abstract|mixed
     */
    public function getProduct()
    {
        return $this->_product == null ? Mage::getModel('catalog/product') : $this->_product;
    }

    /**
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product = $product;

        return $this;
    }

    /**
     * @return array
     */
    public function getPointsEarned()
    {
        $pointsEarned = 0;
        if ($this->getProduct()) {
            if (is_null($this->getData('points_' . $this->getProduct()->getId()))) {
                try {
                    $pointsSummary = 0;
                    $customer      = Mage::helper('giantpoints/customer')->getCustomer();
                    $rate          = Mage::getModel('giantpoints/rate')
                        ->loadByDirection(Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT);
                    if ($rate && $rate->getPoints() && $rate->getMoney()) {
                        /* Points amount by rate */
                        $pointsSummary += $rate->exchange($this->getProduct()->getFinalPrice());
                        $maximumPointsPerCustomer = Mage::helper('giantpoints/config')->getMaxPointPerCustomer();
                        if ($maximumPointsPerCustomer) {
                            $customersPoints = 0;
                            if ($customer) {
                                $customersPoints = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer)->getBalance();
                            }
                            if ($pointsSummary + $customersPoints > $maximumPointsPerCustomer) {
                                $pointsSummary = $maximumPointsPerCustomer - $customersPoints;
                            }
                        }
                    }

                    $this->setData('points_' . $this->getProduct()->getId(), $pointsSummary);
                } catch (Exception $e) {
                    Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                }
                Mage::dispatchEvent('giantpoints_block_product_list_earning_point_before', array(
                        'container' => $this,
                    )
                );
            }

            return $this->getData('points_' . $this->getProduct()->getId());
        }

        return $pointsEarned;
    }
}