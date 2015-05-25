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
class Magegiant_GiantPoints_Model_Block_Observer
{
    /**
     * process event block to html
     *
     * @param $observer
     */
    public function afterOutputHtml($observer)
    {
        $block     = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        if (empty($transport)) {
            return $this;
        }
        if (Mage::getStoreConfigFlag('advanced/modules_disable_output/Magegiant_GiantPoints')) {
            return $this;
        }
        $this->appendToplinkLabel($block, $transport);
        $this->appendPointsSummary($block, $transport);
        $this->appendCartPointsSpender($block, $transport);
        $this->appendPaymentSpending($block, $transport);
        $this->appendRewardNewsletter($block, $transport);
        $this->appendRewardPoll($block, $transport);
        $this->appendToCatalogListing($block, $transport);

        return $this;
    }

    /**
     * add top link
     *
     * @param $block
     * @param $transport
     * @return $this
     */
    public function appendToplinkLabel($block, $transport)
    {
        if (!Mage::helper('giantpoints/config')->allowShowOnToplinks() || !Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this;
        }
        if (version_compare(Mage::getVersion(), '1.9.0.0', '>=')) {
            if ($block->getBlockAlias() == 'welcome') {
                $html        = $transport->getHtml();
                $append_html = $block->getLayout()->createBlock('giantpoints/toplinks_label')->renderView();
                // Check that content is not already integrated.
                if ($append_html != "" && strpos($html, $append_html) === false) {
                    $html .= $append_html;
                }
                $transport->setHtml($html);
            }

        } else {
            if ($block->getBlockAlias() == 'topLinks') {
                $html        = $transport->getHtml();
                $append_html = $block->getChildHtml('giantpoints_toplinks_label');
                // Check that content is not already integrated.
                if ($append_html != "" && strpos($html, $append_html) === false) {
                    $html = $append_html . $html;
                }

                $transport->setHtml($html);
            }
        }

        return $this;
    }

    /**
     * Append the points summary message in the dashboard.
     *
     * @param unknown_type $block
     * @param unknown_type $transport
     */
    public function appendPointsSummary($block, $transport)
    {

        if (!Mage::helper('giantpoints/config')->allowShowOnTopDashBoard()) {
            return $this;
        }

        if ($block->getBlockAlias() == 'top' && $block->getChild('giant_points_summary')) {
            $html        = $transport->getHtml();
            $append_html = $block->getChildHtml('giant_points_summary');

            // Check that content is not already integrated.
            if ($append_html && strpos($html, $append_html) === false) {
                $html = $append_html . $html;
            }

            $transport->setHtml($html);
        }

        return $this;
    }

    /**
     * Append the shopping cart points spender box in the shopping box
     *
     * @param unknown_type $block
     * @param unknown_type $transport
     */
    public function appendCartPointsSpender($block, $transport)
    {
        if ($block->getBlockAlias() == 'coupon' && $block->getChild('checkout_cart_spending')) {
            $html        = $transport->getHtml();
            $append_html = $block->getChildHtml('checkout_cart_spending');

            // Check that content is not already integrated.
            if ($append_html && strpos($html, $append_html) === false) {
                $html = $append_html . $html;
            }

            $transport->setHtml($html);
        }

        return $this;
    }

    public function appendPaymentSpending($block, $transport)
    {
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                . '_' . $block->getRequest()->getRequestedControllerName()
                . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'checkout_onepage_index') {
                return;
            }
            $html      = $transport->getHtml();
            $isOnePage = false;
            if (Mage::helper('core')->isModuleEnabled('Magegiant_Onestepcheckout') && Mage::helper('onestepcheckout/config')->isEnabled()) {
                $append_html = $block->getLayout()
                    ->createBlock('giantpoints/checkout_onepage_payment_spending')
                    ->setTemplate('magegiant/giantpoints/checkout/onestepcheckout/payment/magegiant_spending.phtml')
                    ->renderView();
            } else if (Mage::helper('core')->isModuleEnabled('IWD_Opc') && Mage::getStoreConfigFlag('opc/global/status')) {
                $append_html = $block->getLayout()
                    ->createBlock('giantpoints/checkout_onepage_payment_spending')
                    ->setTemplate('magegiant/giantpoints/checkout/onepage/payment/iwd_spending.phtml')
                    ->renderView();

            } else if (Mage::helper('core')->isModuleEnabled('Idev_OneStepCheckout') && Mage::getStoreConfigFlag('onestepcheckout/general/rewrite_checkout_links')) {
                $append_html = $block->getLayout()
                    ->createBlock('giantpoints/checkout_onepage_payment_spending')
                    ->setTemplate('magegiant/giantpoints/checkout/onestepcheckout/payment/idev_spending.phtml')
                    ->renderView();

            } else if (Mage::helper('core')->isModuleEnabled('TM_FireCheckout')) {
                $append_html = $block->getLayout()
                    ->createBlock('giantpoints/checkout_onepage_payment_spending')
                    ->setTemplate('magegiant/giantpoints/checkout/onestepcheckout/payment/firecheckout_spending.phtml')
                    ->renderView();
            } else {
                $append_html = $block->getLayout()
                    ->createBlock('giantpoints/checkout_onepage_payment_spending', 'checkout.payment.spending')
                    ->setTemplate('magegiant/giantpoints/checkout/onepage/payment/spending.phtml')
                    ->renderView();
                $isOnePage   = true;

            }
            if ($isOnePage) {
                $html .= '
            <script type="text/javascript">
                MagegiantGiantPointsCore.loadPaymentSpendingPoint($("checkout-payment-method-load"),' . Mage::helper('core')->jsonEncode(array('html' => $append_html)) . ');
            </script>';
            } else {
                $html .= '
            <script type="text/javascript">
                MagegiantGiantPointsCore.loadPaymentSpendingPointOnestep($("checkout-payment-method-load"),' . Mage::helper('core')->jsonEncode(array('html' => $append_html)) . ');
            </script>';
            }
            $transport->setHtml($html);

            return $this;
        }

        return $this;
    }

    public function appendRewardNewsletter($block, $transport)
    {
        if ($block instanceof Mage_Newsletter_Block_Subscribe) {
            $html        = $transport->getHtml();
            $append_html = $block->getChildHtml('behavior_newsletter');
            if ($append_html && strpos($html, $append_html) === false) {
                $html .= $append_html;
            }

            $transport->setHtml($html);
        }
        if ($block instanceof Mage_Customer_Block_Newsletter) {
            $html        = $transport->getHtml();
            $append_html = $block->getChildHtml('behavior_newsletter');
            if ($append_html && strpos($html, $append_html) === false) {
                $html .= $append_html;
            }

            $transport->setHtml($html);
        }
    }

    public function appendRewardPoll($block, $transport)
    {
        if ($block instanceof Mage_Poll_Block_ActivePoll) {
            $html        = $transport->getHtml();
            $append_html = $block->getChildHtml('behavior_right_poll');
            if ($append_html && strpos($html, $append_html) === false) {
                $html .= $append_html;
            }

            $transport->setHtml($html);
        }
    }

    /**
     * Append Earning Point To Product List
     *
     * @param $block
     * @param $transport
     * @return $this
     */
    public function appendToCatalogListing($block, $transport)
    {

        // Should we be checking this auto-integration?
        if (!Mage::helper('giantpoints/config')->showOnProductListPage()) {
            return $this;
        }

        // Block is a price block.
        if (!($block instanceof Mage_Catalog_Block_Product_List)) {
            return $this;
        }
        $all_products = $block->getLoadedProductCollection();
        $html         = $transport->getHtml();
        $html         = $this->_updateCatalogProductListing($html, $all_products, $block);

        $transport->setHtml($html);

        return $this;

    }

    /**
     *
     * @param string                                    $html
     * @param Mage_Eav_Model_Entity_Collection_Abstract $all_products
     * @param Mage_Catalog_Block_Product_List           $block
     */
    protected function _updateCatalogProductListing($html, $all_products, $block)
    {
        $is_list_mode_display = strpos($html, 'class="products-list" id="products-list">') !== false;
        foreach ($all_products as $_product) {
            $product_id         = $_product->getId();
            $giant_points_block = $block->getLayout()->createBlock('giantpoints/catalog_product_list_points', 'giantpoints_catalog_product_list_points');
            $block->insert($giant_points_block);
            $giant_points_block->setProduct($_product);
            $points_html = $giant_points_block->toHtml();
            $isRwdTheme  = Mage::helper('giantpoints/version')->getPackageName() === "rwd";

            //  If no content, dont integrate
            if (empty($points_html)) {
                continue;
            }
            // Check that content is not already integrated.
            if (strpos($html, $points_html) !== false) {
                continue;
            }

            $replaced_html = null;
            if (Mage::helper('giantpoints/version')->isEnterprise() && !$isRwdTheme) {
                $pattern = '/(<button )[^>]*(product\/' . $product_id . '\/)(.*)(<\/button>)/isU';
            } else {
                if ($is_list_mode_display) {
                    $pattern = '/(<ul class="add-to-links">)((\s)*)(<li>)((\s)*)(<a href=")[^>]*(product\/' . $product_id . '\/)(.*)(<\/a>)(.*)(<\/li>)((\s)*)(<\/ul>)/isU';
                } else {
                    $pattern = '/(<div class="actions">)((\s)*)(<button )[^>]*(product\/' . $product_id . '\/)(.*)(<\/button>)(.*)(<\/div>)/isU';
                }
            }
            $points_html = preg_replace('#(\\$|\\\\)#', '\\\\$1', $points_html);

            $replacement   = $points_html . '${0}';
            $replaced_html = preg_replace($pattern, $replacement, $html);

            //Nothing got replaced , some times if the product id's doesnt include in the url's the url key does , so
            if ($replaced_html == $html) {
                $productUrl = preg_quote($_product->getProductUrl());
                $productUrl = str_replace("/", "\/", $productUrl);
                $pattern    = '/(<div class="actions">)((\s)*)(<button )[^>]*(' . $productUrl . ')(.*)(<\/button>)(.*)(<\/div>)/isU';
                if (Mage::helper('giantpoints/version')->isEnterprise() && !$isRwdTheme) {
                    $pattern = '/(<button )[^>]*(' . $productUrl . ')(.*)(<\/button>)/isU';
                }

                $replaced_html = preg_replace($pattern, $replacement, $html);
            }
            if ($replaced_html == $html) {
                $pattern       = '/(<ul class="add-to-links">)((\s)*)(<li>)((\s)*)(<a href=")[^>]*(product\/' . $product_id . '\/)(.*)(<\/a>)(.*)(<\/li>)((\s)*)(<\/ul>)/isU';
                $replaced_html = preg_replace($pattern, $replacement, $html);
            }

            if (!empty($replaced_html)) {
                $html = $replaced_html;
            }
        }

        return $html;
    }
}