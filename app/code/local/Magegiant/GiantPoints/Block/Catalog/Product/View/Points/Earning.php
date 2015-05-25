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
class Magegiant_GiantPoints_Block_Catalog_Product_View_Points_Earning extends Magegiant_GiantPoints_Block_Catalog_Product_View_Points
{
    /**
     * get earned points for current product
     *
     * @return mixed
     */
    public function getPoints()
    {
        if (is_null($this->getData('earning_points'))) {
            try {
                $pointsSummary      = 0;
                $customer           = Mage::helper('giantpoints/customer')->getCustomer();
                $product            = $this->getProduct();
                $prices_include_tax = Mage::helper('tax')->priceIncludesTax();
                if ($this->isItem($product)) {
                    $qty = ($product->getQty() > 0) ? $product->getQty() : 1;
                    if ($prices_include_tax) {
                        $price = $product->getBaseRowTotal();
                        $price += $product->getBaseTaxAmount();
                    } else {
                        $price = $product->getBaseRowTotal();
                    }
                } else {
                    $qty   = 1;
                    $price = $product->getFinalPrice();
                }
                $rate = Mage::getModel('giantpoints/rate')
                    ->loadByDirection(Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT);
                if ($rate && $rate->getId()) {
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                        $associated_prod = $product->getTypeInstance(true)->getAssociatedProducts($product);
                        $price           = 0;
                        foreach ($associated_prod as $item) {
                            $qty = $item->getQty() > -1 ? $item->getQty() : 1;
                            $price += $item->getFinalPrice() * $qty;
                        }
                    }
                    $pointsSummary += $rate->exchange($price);
                }
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
                $this->setData('earning_points', $pointsSummary);
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }
        Mage::dispatchEvent('giantpoints_block_product_view_earning_point_before', array(
                'container' => $this,
            )
        );

        return $this->getData('earning_points');
    }


    public function getMoney($points)
    {
        if (is_null($this->getData('money'))) {
            $rate  = Mage::getModel('giantpoints/rate')
                ->loadByDirection(Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY);
            $money = 0;
            if ($rate && $rate->getId()) {
                $money = $rate->exchange($points);
                $this->setData('money', $money);
            } else {
                $this->setData('money', $money);
            }
        }

        return $this->getData('money');
    }

    public function customerIsGuest()
    {
        return Mage::getModel('customer/session')->getCustomer()->getId() ? false : true;
    }

    /**
     * @param $obj
     * @return bool
     */
    private function isItem($obj)
    {
        $ret = false;
        if ($obj instanceof Mage_Sales_Model_Quote_Item || $obj instanceof Mage_Sales_Model_Quote_Item_Abstract || $obj instanceof Mage_Sales_Model_Quote_Address_Item || $obj instanceof Mage_Sales_Model_Order_Item || $obj instanceof Mage_Sales_Model_Order_Invoice_Item || $obj instanceof Mage_Sales_Model_Order_Creditmemo_Item || $obj instanceof Mage_Sales_Model_Order_Shipment_Item) { // params are function($rule)
            $ret = true;
        }

        return $ret;
    }
}