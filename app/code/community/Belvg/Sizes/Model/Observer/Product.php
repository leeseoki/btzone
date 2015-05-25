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
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Sizes_Model_Observer_Product
{
    
    public function saveData(Varien_Event_Observer $observer)
    {
        $params = (array)Mage::app()->getRequest()->getParam('sizes');
        $params['product_id'] = $observer->getEvent()->getProduct()->getId();
        $model = Mage::getModel('sizes/products');
        $id = $model->getCollection()
                    ->addFieldToFilter('product_id', $params['product_id'])
                    ->getLastItem()
                    ->getId();
        if (!empty($id)) {
            $params['id'] = $id;
        }
        
        try {
            $model->setData($params)->save();        
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException($e, Mage::helper('sizes')->__('There are an errors during saving sizes parameters in DB'));
        }
    }
    
}