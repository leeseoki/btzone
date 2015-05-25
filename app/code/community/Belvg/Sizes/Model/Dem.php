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
class Belvg_Sizes_Model_Dem extends Mage_Core_Model_Abstract
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/dem');
        $this->setIdFieldName('dem_id');
    }
    
    public function getDems($demIds, $catId=FALSE, $standardId=FALSE)
    {   
        if ($demIds) {
            $prefix = Mage::getConfig()->getTablePrefix();
            $store_id = Mage::app()->getStore()->getStoreId();
            $collection = $this->getCollection()->addFieldToFilter('main_table.dem_id', $demIds);
            $collection->getSelect()
                       ->joinLeft(array('labels' => $prefix . 'belvg_sizes_dem_labels'), "labels.dem_id = main_table.dem_id AND (labels.store_id=" . $store_id . " OR labels.store_id=0)", array('labels.label', 'labels.store_id'));
            $tmp = $collection->getData();
            $dem_ids = array();
            $result = array();
            foreach ($tmp as $key=>$item) {
                if ($item['store_id'] == $store_id) {
                    $result[] = $item;
                    $dem_ids[] = $item['dem_id'];
                    unset($item[$key]);
                }
            }
            
            foreach ($tmp as $key=>$item) {
                if (!in_array($item['dem_id'], $dem_ids)) {
                    $result[] = $item;
                }
            }
            
            foreach ($result as $key=>$item) {
                if ($catId && $standardId) {
                    if ($this->_isDemEmpty($catId, $item['dem_id'], $standardId)) {
                        return FALSE;
                    }
                }
                
                $result[$key]['label'] = htmlspecialchars($result[$key]['label'], ENT_QUOTES);
            }
            
            return $result;
        } else {
            return FALSE;
        }
    }
    
    protected function _isDemEmpty($cat_id, $dem_id, $standard_id)
    {
        $filter = Mage::getModel('sizes/standardsvalues')->getCollection()
                                                         ->addFieldToFilter('standard_id', $standard_id)
                                                         ->getColumnValues('value_id');
        $tmp = Mage::getModel('sizes/main')
                   ->getCollection()
                   ->addFieldToFilter('value_id', array('in' => $filter))
                   ->addFieldToFilter('cat_id', $cat_id)
                   ->addFieldToFilter('dem_id', $dem_id);
        if (!$tmp->getData()) {
            return TRUE;
        } else {
            return FALSE;
        }
                    
    }
    
}