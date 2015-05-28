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
class Belvg_Sizes_Block_Popup_Charts extends Belvg_Sizes_Block_Popup
{
    protected $_dims;
    protected $_vals;

    public function getTableHead()
    {
        $this->_helper = Mage::helper('sizes');
        return ($this->_helper->getChartsDirection() == 'horizontal')?$this->getSizeDimensions():$this->getStandardValues();
    }
    
    public function getSizeDimensions()
    {
        if ($this->_dims) {
            return $this->_dims;
        }
    
        $cat_id = $this->getCatId();
        $dim_ids = explode(',', Mage::getModel('sizes/cat')->load($cat_id)->getDimIds());
        $this->_dims = Mage::getModel('sizes/demlabels')->getCollection()
            ->addFIeldToFilter('dem_id', $dim_ids);
        
        return $this->_dims;
    }
    
    public function getStandardValues()
    {
        if ($this->_vals) {
            return $this->_vals;
        }
        
        $this->_vals =  Mage::getModel('sizes/standardsvalues')->getCollection()
            ->addFieldToFilter('standard_id', $this->getStandardId())->setOrder('sort_order', 'ASC');
            
        return $this->_vals;
    }
    
    public function getChartTableBody()
    {
        $cat_id = $this->getCatId();
        $t = Mage::getModel('sizes/main')->getCollection()->addFieldToFilter('cat_id', $cat_id);
            
        $grid = array();
        if ($this->_helper->getChartsDirection() == 'horizontal') {
            foreach ($this->getSizeDimensions() as $dim) { 
                $dim_id = $dim->getDemId();
                foreach ($t->getData() as $item) {
                    if ($item['dem_id'] == $dim_id) {
                        $grid[$dim_id][$item['value_id']] = $item['min'] . ' - ' . $item['max'];
                    }
                }
            }
        } else { 
            foreach ($this->getStandardValues() as $dim) { 
                $value_id = $dim->getValueId();
                foreach ($t->getData() as $item) {
                    if ($item['value_id'] == $value_id) {
                        $grid[$value_id][$item['dem_id']] = $item['min'] . ' - ' . $item['max'];
                    }
                }
            }
        }

        return $this->prepareTableGrid($grid);
    }
    
    public function prepareTableGrid($grid)
    {
        $result = array();
        if ($this->_helper->getChartsDirection() == 'horizontal') {
            foreach ($this->getStandardValues() as $value) {
                foreach ($this->getSizeDimensions() as $dim) {
                    $result[$value->getValue()][$dim->getPreparedLabel()] = $grid[$dim->getDemId()][$value->getValueId()];
                }
            }
        } else {
            foreach ($this->getSizeDimensions() as $dim) {
                foreach ($this->getStandardValues() as $value) {
                    $result[$dim->getPreparedLabel()][$value->getValue()] = $grid[$value->getValueId()][$dim->getDemId()];
                }
            }
        }
        
        return $result;   
    }
    
}