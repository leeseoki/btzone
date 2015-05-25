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
 * GiantPoints Observer Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Adminhtml_Observer
{


    /**
     * @param $observer
     */
    public function applyPointsFromCustomerEdit($observer)
    {
        if ($request = $observer->getRequest()) {
            $pointsToAdd = $request->getPost('change_balance');
            if (empty($pointsToAdd)) {
                return $this;
            }
            $comment        = $request->getPost('giantpoints_comment');
            $expireDays     = $request->getPost('giantpoints_expiration_day');
            $customer       = $observer->getCustomer();
            $obj            = new Varien_Object(array(
                'point_amount'   => $pointsToAdd,
                'comment'        => $comment,
                'expiration_day' => (int)$expireDays,
            ));
            $additionalData = array(
                'customer'      => $customer,
                'action_object' => $obj,
                'notice'        => null,


            );
            $transaction    = Mage::helper('giantpoints/action')->createTransaction(
                'change_by_admin', $additionalData
            );
            if (!$transaction->getId()) {
                throw new Exception(
                    Mage::helper('giantpoints')->__('Cannot create transaction, please recheck form information.')
                );
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('giantpoints')->__('Transaction has been created successfully.')
            );
        }
    }

    /**
     * @param $observer
     */
    public function applyPointsNotificationFromCustomerEdit($observer)
    {
        if ($request = $observer->getRequest()) {
            $customer                  = $observer->getCustomer();
            $balanceUpdateNotification = (int)$request->getPost('notification_update');
            $pointsExpireNotification  = (int)$request->getPost('notification_expire');
            $giantCustomer             = Mage::getModel('giantpoints/customer')
                ->getAccountByCustomer($customer);
            if (!$giantCustomer || !$giantCustomer->getId()) {
                $giantCustomer->setCustomerId($customer->getId());
            }
            try {
                $giantCustomer
                    ->setNotificationUpdate($balanceUpdateNotification)
                    ->setNotificationExpire($pointsExpireNotification)
                    ->save();
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
        }

        return $this;
    }

    /**
     * process event to force create credit memo when purchase order by points
     *
     * @param type $observer
     */
    public function salesOrderLoadAfter($observer)
    {
        $order = $observer['order'];
        if ($order->getGiantpointsDiscount() < 0.0001
            || Mage::app()->getStore()->roundPrice($order->getGrandTotal()) > 0
            || $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED
            || $order->isCanceled()
            || $order->canUnhold()
        ) {
            return $this;
        }
        foreach ($order->getAllItems() as $item) {
            if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                $order->setForcedCanCreditmemo(true);

                return $this;
            }
        }
    }

    /**
     * process event to turn off forced credit memo of order
     *
     * @param type $observer
     */
    public function salesOrderCreditmemoRefund($observer)
    {
        $creditmemo = $observer['creditmemo'];
        $order      = $creditmemo->getOrder();
        if ($order->getGiantpointsDiscount() && $order->getForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(false);
        }
    }

    /**
     * transfer giant points discount to Paypal gateway
     *
     * @param type $observer
     */
    public function paypalPrepareLineItems($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            if ($paypalCart = $observer->getPaypalCart()) {
                $salesEntity = $paypalCart->getSalesEntity();

                $baseDiscount = $salesEntity->getGiantpointsBaseDiscount();
                if ($baseDiscount < 0.0001 && $salesEntity instanceof Mage_Sales_Model_Quote) {
                    $helper       = Mage::helper('giantpoints/conversion_spending');
                    $baseDiscount = $helper->getPointItemDiscount();
                    $baseDiscount += $helper->getCheckedRuleDiscount();
                    $baseDiscount += $helper->getSliderRuleDiscount();
                }
                if ($baseDiscount > 0.0001) {
                    $paypalCart->updateTotal(
                        Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,
                        (float)$baseDiscount,
                        Mage::helper('giantpoints')->__('Use points on spend')
                    );
                }
            }

            return $this;
        }
        $salesEntity = $observer->getSalesEntity();
        $additional  = $observer->getAdditional();
        if ($salesEntity && $additional) {
            $baseDiscount = $salesEntity->getGiantpointsBaseDiscount();
            if ($baseDiscount < 0.0001 && $salesEntity instanceof Mage_Sales_Model_Quote) {
                $helper       = Mage::helper('giantpoints/conversion_spending');
                $baseDiscount = $helper->getPointItemDiscount();
                $baseDiscount += $helper->getCheckedRuleDiscount();
                $baseDiscount += $helper->getSliderRuleDiscount();
            }

            if ($baseDiscount > 0.0001) {
                $items   = $additional->getItems();
                $items[] = new Varien_Object(array(
                    'name'   => Mage::helper('giantpoints')->__('Use points on spend'),
                    'qty'    => 1,
                    'amount' => -(float)$baseDiscount,
                ));
                $additional->setItems($items);
            }
        }
    }

    /**
     * @return $this
     */
    public function adminhtmlSystemConfigSave()
    {
        $section = Mage::app()->getRequest()->getParam('section');
        if ($section == 'giantpoints') {
            $websiteCode   = Mage::app()->getRequest()->getParam('website');
            $storeCode     = Mage::app()->getRequest()->getParam('store');
            $css_generator = Mage::getSingleton('giantpoints/generator_css');
            $css_generator->generateCss($websiteCode, $storeCode, 'design');
        }

        return $this;
    }

    /**
     * Add spent points when reorder
     * @param $observer
     * @return $this
     */
    public function salesConvertOrderToQuote($observer)
    {
        $order = $observer->getOrder();
        if ($order->getGiantpointsSpent()) {
            Mage::getSingleton('checkout/session')->setData('old_spent_point',$order->getGiantpointsSpent());
        }
        return $this;
    }
}
