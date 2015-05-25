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
class Magegiant_GiantPoints_Model_Updater
{
    const TARGET_LAYOUT_FULL_ACTION_NAME          = 'checkout_cart_index';
    const REVIEW_CART_BLOCK_NAME                  = 'checkout.cart.totals';
    const ONEPAGE_PAYMENT_METHOD_BLOCK_NAME       = 'root';
    const GIANT_ONESTEP_PAYMENT_METHOD_BLOCK_NAME = 'onestepcheckout.onestep.form.payment.methods';
    const GIANT_ONESTEP_REVIEW_CART_BLOCK_NAME    = 'onestepcheckout.onestep.form.review.cart';
    /*Idev Onetstepcheckout*/
    const IDEV_ONESTEP_PAYMENT_METHOD_BLOCK_NAME = 'choose-payment-method';
    const IDEV_ONESTEP_REVIEW_CART_BLOCK_NAME    = 'onestepcheckout.summary';
    protected $_map = array(
        'applyPoint'            => array(
            'review_cart' => self::REVIEW_CART_BLOCK_NAME,
        ),
        'applyPointOnepage'     => array(
            'review_payment' => self::ONEPAGE_PAYMENT_METHOD_BLOCK_NAME,
        ),
        'applyPointOnestep'     => array(
            'review_payment' => self::GIANT_ONESTEP_PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'    => self::GIANT_ONESTEP_REVIEW_CART_BLOCK_NAME,
        ),
        'applyPointIdevOnestep' => array(
            'review_payment' => self::IDEV_ONESTEP_PAYMENT_METHOD_BLOCK_NAME,
            'review_cart'    => self::IDEV_ONESTEP_REVIEW_CART_BLOCK_NAME,
        ),
    );

    public function getMap()
    {
        $container = new Varien_Object(
            array('map' => $this->_map)
        );
        Mage::dispatchEvent('giantpoints_updater_get_map_before', array(
                'container' => $container
            )
        );

        return $container->getMap();
    }

    /**
     * @param null $controller
     *
     * @return array
     */
    public function getBlocks($layout = null, $fullTargetActionName = null)
    {
        $map = $this->getMap();
        if (is_null($layout)) {
            $layout = Mage::app()->getLayout();
        }
        if (is_null($fullTargetActionName)) {
            $fullTargetActionName = self::TARGET_LAYOUT_FULL_ACTION_NAME;
        }
        $this->_initLayout($layout, $fullTargetActionName);

        $actionName = Mage::app()->getRequest()->getActionName();
        if (!array_key_exists($actionName, $map)) {
            return array();
        }
        if (!is_array($map[$actionName])) {
            return array();
        }
        $blocks = array();
        foreach ($map[$actionName] as $key => $blockName) {
            $block = $layout->getBlock($blockName);
            if ($block) {
                $blocks[$key] = $block->toHtml();
            }
        }

        return $blocks;
    }

    protected function _initLayout($layout, $fullTargetActionName)
    {
        /* -- START-- copypaste from Mage_Core_Controller_Varien_Action -- START -- */
        $update = $layout->getUpdate();
        $update->addHandle('default'); //load default handle
        $update->addHandle('STORE_' . Mage::app()->getStore()->getCode()); // load store handle
        $package = Mage::getSingleton('core/design_package');
        $update->addHandle(
            'THEME_' . $package->getArea() . '_' . $package->getPackageName() . '_' . $package->getTheme('layout')
        ); // load theme handle
        $update->addHandle(strtolower($fullTargetActionName)); // load action handle
        Mage::dispatchEvent(
            'controller_action_layout_load_before',
            array('action' => Mage::app()->getFrontController()->getAction(), 'layout' => $layout)
        );
        $update->load();
        $layout->generateXml();
        $layout->generateBlocks();
        /* -- END -- copypaste from Mage_Core_Controller_Varien_Action -- END -- */
    }
}