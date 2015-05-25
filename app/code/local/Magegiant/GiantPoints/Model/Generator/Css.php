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
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
class Magegiant_GiantPoints_Model_Generator_Css extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function generateCss($websiteCode, $storeCode, $section)
    {
        if ($websiteCode) {
            if ($storeCode) {
                $this->_generateStoreCss($storeCode, $section);
            } else {
                $this->_generateWebsiteCss($websiteCode, $section);
            }
        } else {
            $stores = Mage::app()->getWebsites(false, true);
            foreach ($stores as $store) {
                $this->_generateWebsiteCss($store, $section);
            }
        }
    }

    protected function _generateWebsiteCss($websiteCode, $section)
    {
        $websites = Mage::app()->getWebsite($websiteCode);
        foreach ($websites->getStoreCodes() as $store) {
            $this->_generateStoreCss($store, $section);
        }
    }

    protected function _generateStoreCss($storeCode, $section)
    {
        if (!Mage::app()->getStore($storeCode)->getIsActive()) return;
        $store       = '_' . $storeCode;
        $cssFile     = $section . $store . '.css';
        $cssFileDir  = Mage::helper('giantpoints/generator_css')->getGeneratedCssDir() . $cssFile;
        $cssTemplate = Mage::helper('giantpoints/generator_css')->getTemplatePath() . $section . '.phtml';
        Mage::register('giantpoints_generator_css_store', $storeCode);
        try {
            $cssGenerated = Mage::app()->getLayout()->createBlock('giantpoints/generator_css')
                ->setData('area', 'frontend')
                ->setTemplate($cssTemplate)
                ->setStoreId(Mage::app()->getStore($storeCode)->getId())
                ->toHtml();
            if (empty($cssGenerated)) {
                throw new Exception(Mage::helper('giantpoints')->__("Template file is empty or doesn't exist: %s", $cssTemplate));
            }
            $varienFile = new Varien_Io_File();
            $varienFile->setAllowCreateFolders(true);
            $varienFile->open(array('path' => Mage::helper('giantpoints/generator_css')->getGeneratedCssDir()));
            $varienFile->streamOpen($cssFileDir, 'w+', 0777);
            $varienFile->streamLock(true);
            $varienFile->streamWrite($cssGenerated);
            $varienFile->streamUnlock();
            $varienFile->streamClose();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giantpoints')->__('Failed generating CSS file: %s in %s', $cssFile, Mage::helper('giantpoints/generator_css')->getGeneratedCssDir()) . '<br/>Message: ' . $e->getMessage());
            Mage::logException($e);
        }
        Mage::app()->getCacheInstance()->flush();
        Mage::unregister('giantpoints_generator_css_store');
    }
}