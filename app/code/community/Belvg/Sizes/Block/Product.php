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
class Belvg_Sizes_Block_Product extends Mage_Catalog_Block_Product_View
{

    public function getDems()
    {
        $product_id = (int)$this->getProduct()->getId();
        return Mage::getModel('sizes/dem')->getDems($this->_getDemIds($product_id), $this->_getCatId($product_id), $this->_getStandardId($product_id));
    }
    
    protected function _getDemIds($product_id)
    {
        $cat_id = $this->_getCatId($product_id);
        $result = Mage::getModel('sizes/main')->getCollection()
                                              ->addFieldToFilter('cat_id', $cat_id)
                                              ->getColumnValues('dem_id');
        $result = array_unique($result);
        $filter = explode(',', Mage::getModel('sizes/cat')->load($cat_id)->getDimIds());
        
        return (array)array_intersect($filter, $result);         
    }
    
    protected function _getCatId($product_id)
    {
        return (int)Mage::getModel('sizes/products')->getCollection()
                                                    ->addFieldToFilter('product_id', $product_id)
                                                    ->getLastItem()->getCatId();
    }
    
    protected function _getStandardId($product_id)
    {
        return (int)Mage::getModel('sizes/products')->getCollection()
                                                    ->addFieldToFilter('product_id', $product_id)
                                                    ->getLastItem()->getStandardId();
    }
    
}