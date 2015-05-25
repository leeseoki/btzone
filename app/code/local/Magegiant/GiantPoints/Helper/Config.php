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
class Magegiant_GiantPoints_Helper_Config extends Mage_Core_Helper_Abstract
{

    const GIANTPOINTS_GENERAL_CONFIGRATION  = 'giantpoints/general/';
    const GIANTPOINTS_GENERAL_ENABLE        = 'giantpoints/general/enable';
    const GIANTPOINTS_EARNING_CONFIGRATION  = 'giantpoints/earning/';
    const GIANTPOINTS_SPENDING_CONFIGRATION = 'giantpoints/spending/';
    const GIANTPOINTS_DISPLAY_CONFIGRATION  = 'giantpoints/display/';
    const GIANTPOINTS_DESIGN_CONFIGRATION   = 'giantpoints/design/';
    const GIANTPOINTS_EMAIL_CONFIGRATION    = 'giantpoints/email/';
    const GIANTPOINTS_ADVANCED_CONFIGRATION = 'giantpoints/advanced/';

    /*Begin General Configration*/
    /**
     * check giant points is enabled
     *
     * @param null $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfigFlag(self::GIANTPOINTS_GENERAL_ENABLE, $storeId);
    }

    /**
     * get general config
     *
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_GENERAL_CONFIGRATION . $code, $storeId);
    }

    /**
     * get point label config
     *
     * @param null $storeId
     * @return mixed
     */
    public function getPointLabel($storeId = null)
    {
        return $this->getGeneralConfig('point_label', $storeId);
    }

    public function getPluralPointLabel($storeId = null)
    {
        return $this->getGeneralConfig('plural_point_label', $storeId);
    }

    public function getZeroPoints($storeId = null)
    {
        return $this->getGeneralConfig('zero_points', $storeId);

    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPointLabelPosition($storeId = null)
    {
        return $this->getGeneralConfig('point_label_position', $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDashboardLabel($storeId = null)
    {
        return $this->getGeneralConfig('dashboard_label', $storeId);
    }

    public function getIconUrl()
    {
        return Mage::getBaseUrl('media') . 'magegiant/giantpoints/icon/';
    }

    /**
     * get point image on store, default is template image url
     *
     * @param mixed $store
     * @return string image url
     */
    public function getPointIcon($store = null)
    {
        if ($imgPath = $this->getGeneralConfig('point_icon', $store)) {
            return $this->getIconUrl() . $imgPath;
        }

        return $this->getIconUrl() . 'default/point.png';
    }

    public function getShareIcon($storeId = null)
    {
        if ($imgPath = $this->getGeneralConfig('share_icon', $storeId)) {
            return $this->getIconUrl() . $imgPath;
        }

        return $this->getIconUrl() . 'default/share_icon.png';
    }

    /**
     * get Image (by HTML code)
     *
     * @param boolean $hasAnchor
     * @return string
     */
    public function getPointIconHtml($hasAnchor = false)
    {
        return Mage::getBlockSingleton('giantpoints/icon')
            ->setIsAnchorMode($hasAnchor)
            ->toHtml();
    }

    /**
     * get max point per customer
     *
     * @param null $storeId
     * @return int
     */
    public function getMaxPointPerCustomer($storeId = null)
    {
        return (int)$this->getGeneralConfig('maximum_points_per_customer', $storeId);
    }

    public function isShowInfoPage($storeId = null)
    {
        return (bool)$this->getGeneralConfig('show_info_page', $storeId);
    }

    /**
     * get info page id
     *
     * @param null $storeId
     * @return int
     */
    public function getInfoPageId($storeId = null)
    {
        return $this->getGeneralConfig('info_page', $storeId);
    }
    /*End General Config*/

    /*Begin Earning Configration*/

    /**
     * get earning config
     *
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getEarningConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_EARNING_CONFIGRATION . $code, $storeId);
    }

    /**
     * get rounding method
     *
     * @param      $number
     * @param null $store
     * @return float
     */
    public function getRoundingMethod($number, $store = null)
    {
        switch ($this->getEarningConfig('rounding_method', $store)) {
            case
            'floor':
                return floor($number);
            case 'ceil':
                return ceil($number);
        }
        return round($number);
    }

    /**
     * get expire days config
     *
     * @return int
     */
    public function getExpireDays($storeId = null)
    {
        return (int)$this->getEarningConfig('expire_days', $storeId);
    }

    /**
     *
     * @param null $storeId
     */
    public function getPointsEarningCalculation($storeId = null)
    {
        return (int)$this->getEarningConfig('calculate_points_order', $storeId);
    }

    /**
     * get earning by shipping
     *
     * @param null $storeId
     * @return bool
     */
    public function getEarningByShipping($storeId = null)
    {
        return (bool)$this->getEarningConfig('by_shipping', $storeId);
    }

    /**
     * get config cancel points earned on order refund
     *
     * @param null $storeId
     */
    public function allowCancelEarnedPoint($storeId = null)
    {
        return (bool)$this->getEarningConfig('cancel_earned_points_on_order_refund', $storeId);
    }


    /*End Earning Config*/

    /*Begin Spending Config*/

    /**
     * get spending config
     *
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getSpendingConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_SPENDING_CONFIGRATION . $code, $storeId);
    }

    /**
     * get discount label show in total order
     *
     * @param null $storeId
     */

    public function getDiscountLabel($storeId = null)
    {
        return $this->getSpendingConfig('discount_label', $storeId);
    }

    /**
     * get minimum redem points
     *
     * @param $storeId
     * @return int
     */
    public function getMinimumRedeemPoint($storeId = null)
    {
        return (int)$this->getSpendingConfig('redeemable_points', $storeId);
    }

    /**
     * get max point for order
     *
     * @param $storeId
     * @return int
     */
    public function getMaxPointForOrder($storeId = null)
    {
        return (int)$this->getSpendingConfig('max_points_per_order', $storeId);
    }

    /**
     * spend point for tax
     *
     * @param $storeId
     * @return bool
     */
    public function getPointsSpendingCalculation($storeId = null)
    {
        return (int)$this->getSpendingConfig('calculate_spending_points_order', $storeId);
    }

    /**
     * spend point for shipping fee
     *
     * @param $storeId
     * @return bool
     */
    public function allowSpendPointForShippingFee($storeId = null)
    {
        return (bool)$this->getSpendingConfig('shipping_fee', $storeId);
    }

    /**
     * get allow refund sent point or not
     *
     * @param $storeId
     */
    public function allowRefundSpentPoint($storeId)
    {
        return (bool)$this->getSpendingConfig('refund_spent_points_on_order_refund', $storeId);
    }

    /**
     * spend point for shipping
     *
     * @param $storeId
     * @return bool
     */
    public function allowSpendPointForShipping($storeId = null)
    {
        return (bool)$this->getSpendingConfig('spend_for_shipping', $storeId);
    }

    public function useMaxPointByDefault($storeId = null)
    {
        return (bool)$this->getSpendingConfig('use_max_point_default', $storeId);
    }

    /**
     * get Design Config
     *
     * @param null $storeId
     * @return mixed|string
     */
    public function getDesignConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::GIANTPOINTS_DESIGN_CONFIGRATION . $code, $store);
    }

    public function getSliderStyle($storeId = null)
    {
        if ($this->getDesignConfig('slider_style', $storeId) != 'custom')
            return $this->getDesignConfig('slider_style', $storeId);

        return '#' . $this->getDesignConfig('slider_style_custom', $storeId);
    }

    /**
     * send point for shipping tax
     *
     * @param $storeId
     * @return bool
     */
    public function allowSpendPointForShippingTax($storeId = null)
    {
        return (bool)$this->getSpendingConfig('spend_for_shipping_tax', $storeId);
    }

    /*End Spending Config*/


    /*Begin Display Config*/
    /**
     * get display config
     *
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getDisplayConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_DISPLAY_CONFIGRATION . $code, $storeId);
    }

    /**
     * Show on right/left mini cart
     *
     * @param null $store
     * @return mixed
     */
    public function showOnMiniCart($store = null)
    {
        return $this->getDisplayConfig('minicart', $store);
    }

    /**
     * allow show on product view page
     *
     * @param null $store
     * @return mixed
     */
    public function showOnProductViewPage($store = null)
    {
        return $this->getDisplayConfig('product', $store);
    }

    public function showOnProductListPage($store = null)
    {
        return $this->getDisplayConfig('product_list', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function showOnCheckoutCartPage($store = null)
    {
        return $this->getDisplayConfig('checkout_cart', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */

    public function showDetailEarning($store = null)
    {
        return $this->getDisplayConfig('checkout_cart_detail', $store);
    }

    /**
     * allow show giant point on top link
     *
     * @param null $storeId
     * @return bool
     */
    public function allowShowOnToplinks($storeId = null)
    {
        return (bool)$this->getDisplayConfig('toplinks', $storeId);
    }

    public function allowShowOnTopDashBoard($storeId = null)
    {
        return (bool)$this->getDisplayConfig('top_dashboard', $storeId);
    }

    public function isEnabledSocialEarnMessage($storeId = null)
    {
        return (bool)$this->getDisplayConfig('show_social_earn_message', $storeId);
    }

    /*End Display config*/

    /*Begin Email Configration*/
    /**
     * get email config
     *
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getEmailConfig($code, $storeId = null)
    {
        $storeId = !is_null($storeId) ? $storeId : Mage::app()->getStore()->getId();

        return Mage::getStoreConfig(self::GIANTPOINTS_EMAIL_CONFIGRATION . $code, $storeId);
    }

    /**
     * get allow send email update balance to customer or not
     *
     * @param null $storeId
     * @return mixed
     */
    public function isEmailEnabled($storeId = null)
    {
        return (bool)$this->getEmailConfig('enable', $storeId);
    }

    public function getIsSubscribedByDefault($storeId = null)
    {
        return (bool)$this->getEmailConfig('subscribe_by_default', $storeId);
    }

    public function getSendEmailExpireBeforeDays($storeId = null)
    {
        return (int)$this->getEmailConfig('before_expire_days', $storeId);
    }
    /*End Email Config*/


}