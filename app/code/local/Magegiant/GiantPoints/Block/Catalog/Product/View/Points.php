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
class Magegiant_GiantPoints_Block_Catalog_Product_View_Points extends Magegiant_GiantPoints_Block_Abstract
{
    public function canShow()
    {
        return Mage::helper('giantpoints/config')->showOnProductViewPage();
    }

    public function getEarningPointHtml()
    {
        $child = $this->getChild("giantpoints_catalogrule_earning");
        if (empty($child)) return "";

        $child->setProduct($this->getProduct());

        return $this->getChildHtml("giantpoints_catalogrule_earning");
    }

    /**
     * @return bool
     */
    public function printOptionsPrice()
    {
        if (!version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
            return false;
        }

        return (!$this->getProduct()->isComposite() && !$this->getProduct()->getHasOptions());
    }

    /**
     * Get JSON encripted configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {

        $config = array();

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice   = $this->getProduct()->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice);

        $config = array('productId' => $this->getProduct()->getId(), 'priceFormat' => Mage::app()->getLocale()->getJsPriceFormat(), 'includeTax' => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false', 'showIncludeTax' => Mage::helper('tax')->displayPriceIncludingTax(), 'showBothPrices' => Mage::helper('tax')->displayBothPrices(), 'productPrice' => Mage::helper('core')->currency($_finalPrice, false, false), 'productOldPrice' => Mage::helper('core')->currency($_regularPrice, false, false), 'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1), 'defaultTax' => $defaultTax, 'currentTax' => $currentTax, 'idSuffix' => '_clone', 'oldPlusDisposition' => 0, 'plusDisposition' => 0, 'oldMinusDisposition' => 0, 'minusDisposition' => 0);

        $responseObject = new Varien_Object ();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config [$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     *
     */
    public function getProduct()
    {
        if ($this->getCurrentProduct())
            return $this->getCurrentProduct();

        return Mage::registry('current_product');

    }
}