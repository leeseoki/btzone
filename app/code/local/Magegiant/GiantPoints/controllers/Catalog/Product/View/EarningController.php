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
 * @package     Magegiant_GiantPointsRule
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * GiantPointsRule Index Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPointsRule
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Catalog_Product_View_EarningController extends Mage_Core_Controller_Front_Action
{
    /*
      *  AJAX Call , update Grouped product points earning when the qty changes.
      *  @param : int  - product_id
      *  @param : array() - associated product and new qty's
      *
      */
    public function updatePointsAction()
    {
        try {
            $storeId     = Mage::app()->getStore()->getId(); // return current store id
            $productId   = $this->getRequest()->get("product_id");
            $productType = $this->getRequest()->get("product_type");
            $product     = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
            if ($productType == 'grouped') {
                $associated_prod = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($associated_prod as $item) {
                    $item->setQty($this->getRequest()->get($item->getId()));
                }
            } else if ($productType == 'config') {
                $price = $this->getRequest()->getParam("product_price");
                $product->setPrice($price);
            }
            Mage::register('product', $product);
            $response = $this->getLayout()->createBlock('giantpoints/catalog_product_view_points_earning')
                ->setTemplate('magegiant/giantpoints/catalog/product/view/points/earning.phtml')
                ->setCurrentProduct($product)
                ->toHtml();
        } catch (Exception $e) {
            $response = false;
            Mage::helper('giantpoints')->log($e);
        }
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody($response);

        return $this;
    }
}