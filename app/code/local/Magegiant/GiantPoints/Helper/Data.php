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
class Magegiant_GiantPoints_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CSS_PATH = 'css/magegiant/';

    /**
     * add link to select all option
     *
     * @param $id
     * @return string of html
     */
    public function addSelectAll($id)
    {
        return
            '<span>
                    <a id="select_' . $id . '" style="cursor:pointer" onclick="$$(\'#' . $id . ' option\').each(function(el){el.selected=true});this.hide();$(\'unselect_' . $id . '\').show()">' . $this->__('Select All') . '</a>
                <a id="unselect_' . $id . '" style="cursor:pointer;display:none;" onclick="$$(\'#' . $id . ' option\').each(function(el){el.selected=false});this.hide();$(\'select_' . $id . '\').show()">' . $this->__('Unselected') . '</a>
        </span>';
    }

    /**
     * get convert icon src
     *
     * @return string
     */
    public function getConvertIconSrc()
    {
        return $this->getBaseMediaUrl() . 'magegiant' . DS . 'giantpoints' . DS . 'icon' . DS . 'default' . DS . 'exchange.gif';
    }

    /**
     * get base media url
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
    }

    /**
     * get current time in magento
     *
     * @return int
     */
    public function getMageTime()
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());

        return $currentTimestamp;
    }

    /**
     * get date by format in magento
     *
     * @param null $format
     * @return date formate
     */
    public function getMageDate($format = null)
    {
        $date = Mage::getModel('core/date')->date($format ? $format : 'Y-m-d');

        return $date;
    }

    /**
     * add label for point
     *
     * @param      $points
     * @param null $storeId
     * @return string
     */
    public function addLabelForPoint($points, $storeId = null)
    {
        $position = Mage::helper('giantpoints/config')->getPointLabelPosition($storeId);
        $label    = '';
        switch ($position) {
            case Magegiant_GiantPoints_Model_System_Config_Source_LabelPosition::AFTER_AMOUNT:
                $label = $this->getPointLabelAfter($points, $storeId);
                break;
            case Magegiant_GiantPoints_Model_System_Config_Source_LabelPosition::BEFORE_AMOUNT:
                $label = $this->getPointLabelBefore($points, $storeId);
                break;
        }

        /**
         * Add zero points label
         *
         * @Author: Michael
         */
        if ($points == 0) {
            $label = Mage::helper('giantpoints/config')->getZeroPoints($storeId);
        }

        $canAddStrong = true; //Add config neu can

        if ($canAddStrong) {
            $label = '<span class="giantpoints-label">' . $label . '</span>';
        }

        return $label;
    }

    public function getPointLabelAfter($points, $store)
    {
        if ($points > 1) {
            return $points . Mage::helper('giantpoints/config')->getPluralPointLabel($store);
        }

        return $points . Mage::helper('giantpoints/config')->getPointLabel($store);
    }


    /**
     *
     * @param      $points
     * @param      $store
     * @param bool $haveCharacter
     * @return string
     */
    public function getPointLabelBefore($points, $store)
    {
        if ($points > 1) {
            return Mage::helper('giantpoints/config')->getPluralPointLabel($store) . $points;
        }

        return Mage::helper('giantpoints/config')->getPointLabel($store) . $points;
    }

    /**
     * @param $value
     * @return array|mixed
     */
    public function unhashIt($value)
    {
        if (is_null($value)) {
            return array();
        }
        $unhashed = json_decode(base64_decode($value));
        $unhashed = ( array )$unhashed;

        return $unhashed;
    }

    public function hashIt($value)
    {
        if (is_null($value)) {
            $value = array();
        }

        return base64_encode(json_encode($value));
    }

    /**
     * Returns product's URL as configured in Magento admin.
     *
     * @return string Product URL
     */
    public function getProductUrl($product)
    {
        if (!$product) {
            return null;
        }
        $url = Mage::getBaseUrl();

        $rewrite = Mage::getModel('core/url_rewrite');

        if ($product->getStoreId()) {
            $rewrite->setStoreId($product->getStoreId());
        } else {
            $rewrite->setStoreId(Mage::app()->getStore()->getId());
        }

        $idPath = 'product/' . $product->getId();
        if ($product->getCategoryId() && Mage::getStoreConfig('catalog/seo/product_use_categories')) {
            $idPath .= '/' . $product->getCategoryId();
        }

        $rewrite->loadByIdPath($idPath);

        if ($rewrite->getId()) {
            $url .= $rewrite->getRequestPath();

            return $url;
        }

        $url .= $product->getUrlKey() . Mage::helper('catalog/product')->getProductUrlSuffix();

        return $url;
    }

    /**
     * Add log message to var/log/giantpoints.log
     *
     * @param      $message
     * @param null $level
     * @param null $file_name
     */
    public function log($message, $level = null, $file_name = null)
    {
        if (is_null($file_name))
            $file_name = 'giantpoints.log';

        return Mage::log($message, $level, $file_name);
    }

    /**
     * get Tinybox CSS file
     *
     * @return string
     */
    public function getTinyboxCss()
    {
        $cssPath = self::CSS_PATH;
        if (Mage::helper('core')->isModuleOutputEnabled('Magegiant_SocialLogin') && Mage::helper('sociallogin')->isEnabled()) {
            return '';
        }

        return $cssPath . 'tinybox2/style.css';
    }

    public function removeBreakLine($str)
    {
        return preg_replace("/\r|\n/", "", $str);
    }
}