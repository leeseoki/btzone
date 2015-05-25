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
 * GiantPoints Show Earning Point on Shopping Cart Page
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Checkout_Cart_Earning extends Magegiant_GiantPoints_Block_Abstract
{

    protected $_quote;

    public function __construct()
    {
        parent::__construct();
        $this->_quote = Mage::getModel('checkout/session')->getQuote();
    }

    /**
     * allow show on checkout cart page
     *
     * @return mixed
     */
    public function canShow()
    {
        if (!$this->_quote->hasItems() || $this->_quote->getHasError()) {
            return false;
        }

        return Mage::helper('giantpoints/config')->showOnCheckoutCartPage();
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        if (is_null($this->getData('giantpoints'))) {
            try {
                $pointAmount = 0;
                $earningInfo = array();
                /*Point for rate*/
                if ($this->_quote->isVirtual()) {
                    $address = $this->_quote->getBillingAddress();
                } else {
                    $address = $this->_quote->getShippingAddress();
                }
                if (Mage::helper('giantpoints/config')->getPointsEarningCalculation() == Magegiant_GiantPoints_Model_System_Config_Source_Calculation::POINTS_BEFORE_TAX) {
                    $amountToPoint = $this->_quote->getData('base_subtotal_with_discount');
                } else {
                    $baseSubtotal  = $this->_quote->getData('base_subtotal_with_discount');
                    $taxAmount     = $address->getData('base_tax_amount');
                    $amountToPoint = $baseSubtotal + $taxAmount;
                }
                if (Mage::helper('giantpoints/config')->getEarningByShipping($this->_quote->getStoreId())) {
                    $amountToPoint += $address->getBaseShippingAmount();
                }
                if ($amountToPoint > 0) {
                    try {
                        $rate = Mage::getModel('giantpoints/rate')
                            ->loadByDirection(Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT);
                        if ($rate && $rate->getId()) {
                            $pointForRate = $rate->exchange($amountToPoint);
                            $pointAmount += $pointForRate;
                            $earningInfo[] = new Varien_Object(array(
                                'label'        => $this->__('Earning By Rate'),
                                'point_amount' => $pointForRate
                            ));
                        }
                    } catch (Exception $e) {
                        Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
                    }
                }

                /*end rate*/

                /*Point for Referral's*/

                $pointForReferral = Mage::helper('giantpoints/conversion_earning')->getPointForInvitee();
                if ($pointForReferral > 0.01) {
                    $pointAmount += $pointForReferral;
                    $earningInfo[] = new Varien_Object(array(
                        'label'        => $this->__('Referral Earn'),
                        'point_amount' => $pointForReferral
                    ));
                }
                /*end Referral*/

                $maximumPointsPerCustomer = Mage::helper('giantpoints/config')->getMaxPointPerCustomer();
                if ($maximumPointsPerCustomer) {
                    $customersPoints = 0;

                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    if ($customer) {
                        $customersPoints = Mage::getModel('giantpoints/customer')->getAccountByCustomer($customer)->getBalance();
                    }

                    if ($pointAmount + $customersPoints > $maximumPointsPerCustomer) {
                        $pointAmount = $maximumPointsPerCustomer - $customersPoints;
                    }
                }
                $this->setData('point_amount', $pointAmount);
                $this->setData('earning_info', $earningInfo);
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }
        Mage::dispatchEvent('giantpoints_block_checkout_earning_point_before', array(
                'container' => $this,
            )
        );
        $giantPoints = new Varien_Object(array(
                'point_amount' => $this->getData('point_amount'),
                'info'         => $this->getData('earning_info')
            )
        );

        return $giantPoints;
    }

    public function getMoney()
    {
        if (is_null($this->getData('money'))) {
            $points = $this->getPoints()->getPointAmount();
            $rate   = Mage::getModel('giantpoints/rate')
                ->loadByDirection(Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY);
            $money  = 0;
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
}
