<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /****************************************
 *    MAGENTO EDITION USAGE NOTICE       *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /****************************************
 *    DISCLAIMER                         *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    v1.0.0
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Block_Adminhtml_Categories_Edit_Tabs_Image extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $cat_id	= $this->getRequest()->getParam('cat_id');
        $helper = Mage::helper('sizes');

        $stores = Mage::getModel('core/store')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $stores->getSelect()
               ->joinLeft(array('websites' => $prefix . 'core_website'), 'websites.website_id = main_table.website_id', 'websites.name as website_name');
        $stores = $stores->getData();
       
        $extensions = implode(',', $helper->getImageExtensions());
        foreach ($stores as $key=>$store) {
            $fieldset[$key] = $form->addFieldset('code_' . $store['website_name'] . ' / ' . $store['name'], array('legend' => $store['website_name'] . ' / ' . $store['name']));
            $fieldset[$key]->addField('image_' . $store['website_name'] . ' / ' . $store['name'], 'image', array(
                'name'      => $store['store_id'],
                'label'     => $helper->__('Upload Image') . ' ' . '(' . $extensions . ')',
            ))->setAfterElementHtml("<img id='cat_img' width='" . $helper->getImageWidth() . "' height='" . $helper->getImageHeight() . "' src='" . $helper->getCatImageUrl($cat_id, $store['store_id']) . "'><div><small>" . $helper->__('Default Image Size') . " - " . $helper->getImageWidth() . "x" . $helper->getImageHeight() . "</small></div>");				
        }
        
        $form->setFieldNameSuffix('image');
        $this->setForm($form);
    }
    
}