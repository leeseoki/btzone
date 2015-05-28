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
 *******************************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Block_Popup extends Mage_Core_Block_Template
{
    protected $_catId;
    
    public function getDems()
    {
        return Mage::getModel('sizes/dem')->getDems($this->_getDemIds(), $this->getCatId(), $this->getStandardId());
    }
    
    public function getLimits($dem_id)
    {
        $cat_id = $this->getCatId();
        $standard_id = $this->getStandardId();
        return Mage::getModel('sizes/main')->getLimits($cat_id, $dem_id, $standard_id);
    }
    
    protected function _getDemIds()
    {
        $cat_id = $this->getCatId();
        $result = Mage::getModel('sizes/main')->getCollection()
                                              ->addFieldToFilter('cat_id', $cat_id)
                                              ->getColumnValues('dem_id');
        $result = array_unique($result);
        $filter = explode(',', Mage::getModel('sizes/cat')->load($cat_id)->getDimIds());
        
        return (array)array_intersect($filter, $result);                           
    }
    
    public function getCatId()
    {
        if (!$this->_catId) {
            $product_id = $this->getRequest()->getParam('product_id');
            $this->_catId = (int) Mage::getModel('sizes/products')
                ->getCollection()
                ->addFieldToFilter('product_id', $product_id)
                ->getLastItem()->getCatId();
        }
        
        return $this->_catId;
    }
    
    public function getCategoryLabel()
    {
        return Mage::getModel('sizes/cat')->getCategoryLabel($this->getCatId());
    }
    
    public function getStandardId()
    {
        $product_id = $this->getRequest()->getParam('product_id');
        return (int)Mage::getModel('sizes/products')->getCollection()
                                                    ->addFieldToFilter('product_id', $product_id)
                                                    ->getLastItem()->getStandardId();
    }
    
    public function getSizes()
    {
        if ($dem = $this->getRequest()->getParam('sizes')) {
            return Mage::getModel('sizes/main')->getSizes($this->getCatId(), $this->getStandardId(), $dem);
        } else {
            return FALSE;
        }
    }
    
    public function getCatImageUrl()
    {
        return Mage::helper('sizes')->getCatImageUrl($this->getCatId());
    }
    
}