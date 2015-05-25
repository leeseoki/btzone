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
class Belvg_Sizes_Model_Main extends Mage_Core_Model_Abstract
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/main');
        $this->setIdFieldName('id');
    }
    
    public function getSizes($cat_id, $standard_id, $dem)
    {
        if (isset($dem)) {
            foreach ($dem as $key=>$item) {
                $dem[$key] = $this->_recalculate($item, $mode = 'toDefUnits');
            }
        }
        
        
        $it = (int)Mage::getStoreConfig('sizes/calculation/tolerance');
        $result = array();
        $fit = array();
        foreach ((array)$dem as $key=>$value) {
            $val_id = $this->_getValueId($cat_id, $key, $value);
            $val_id = current($this->filterByStandard($standard_id, $val_id));
            $val_id_gt = $this->_getValueId($cat_id, $key, $value + ($value*$it/100));
            $val_id_gt = current($this->filterByStandard($standard_id, $val_id_gt));
            $val_id_lt = $this->_getValueId($cat_id, $key, $value - ($value*$it/100));
            $val_id_lt = current($this->filterByStandard($standard_id, $val_id_lt));
            
            if ($val_id || $val_id_gt || $val_id_lt) {
                $fit[$key] = array( 'eq' => $this->getSizeLabel($val_id),
                                    'gt' => $this->getSizeLabel($val_id_gt),
                                    'lt' => $this->getSizeLabel($val_id_lt) );
            }
        }

        return $this->_findOptimalSizes($fit, count($dem));
    }
    
    protected function filterByStandard($standard_id, $value_ids)
    {
        $filter = Mage::getModel('sizes/standardsvalues')->getCollection()
                                                         ->addFieldToFilter('standard_id', $standard_id)
                                                         ->getColumnValues('value_id');
        return array_intersect($value_ids, $filter);
    }
    
    protected function _findOptimalSizes($fit, $num)
    {
        $dim_qty = (int)Mage::getStoreConfig('sizes/calculation/qty');
        $tmp = current($fit);
        $result = array();
        if (is_array($tmp)) {
            foreach ($tmp as $key=>$value) {
                foreach ($fit as $dim=>$row) {
                    if ($t = array_search($value, $row)) {
                        $result[$value][$dim] = $t;
                    }
                }
                
                if (count($result[$value]) < $num) {
                    unset($result[$value]);    
                }
            }
      
            $optimal = '';
            $gt = $lt = array();
            foreach ($result as $key=>$size) {
                $tmp = array_count_values($size);
                if (isset($tmp['eq'])) {
                    if ($tmp['eq'] == $num) {
                        $optimal = $key;  
                    }
                }

                if (isset($tmp['gt']) && isset($tmp['eq'])) {
                    if (($tmp['gt'] <= $dim_qty) && ($tmp['eq'] == ($num - $tmp['gt']))) {
                        $gt[] = array( 'value' => $key,
                                         'dim' => $this->getDemLabel(array_search('gt', $size)) );
                    }
                }
                
                if (isset($tmp['lt']) && isset($tmp['eq'])) {
                    if (($tmp['lt'] <= $dim_qty) && ($tmp['eq'] == ($num - $tmp['lt']))) {
                        $lt[] = array( 'value' => $key,
                                         'dim' => $this->getDemLabel(array_search('lt', $size)) );
                    }
                }
            }
            
            return array( 'optimal' => $optimal,
                               'gt' => $gt,
                               'lt' => $lt );
        } else {
            return array( 'optimal' => FALSE,
                               'gt' => FALSE,
                               'lt' => FALSE );
        }
    }
    
    private function _getValueId($cat_id, $dem_id, $value)
    {
        return $this->getCollection()
                    ->addFieldToFilter('cat_id', (int)$cat_id)
                    ->addFieldToFilter('dem_id', $dem_id)
                    ->addFieldToFilter('max', array('gt' => $value))
                    ->addFieldToFilter('min', array('lteq' => $value))
                    ->getColumnValues('value_id');
    }
    
    public function getSizeLabel($id)
    {
        return Mage::getModel('sizes/standardsvalues')->load($id)->getValue();
    }
    
    public function getDemLabel($id)
    {
        $tmp = Mage::getModel('sizes/dem')->getDems($id);
        return $tmp[0]['label'];
    }
    
    public function getLimits($cat_id, $dem_id, $standard_id)
    {
        $filter = Mage::getModel('sizes/standardsvalues')->getCollection()
                                                         ->addFieldToFilter('standard_id', $standard_id)
                                                         ->getColumnValues('value_id');
        $tmp = $this->getCollection()
                    ->addFieldToFilter('value_id', array('in' => $filter))
                    ->addFieldToFilter('cat_id', $cat_id)
                    ->addFieldToFilter('dem_id', $dem_id);
        $nmbr = (int)Mage::getConfig()->getNode('sizes/unitsizes/' . Mage::getStoreConfig('sizes/settings/unit_sizes') . '/decimals');
        return array('step' => pow(0.1, $nmbr),
                      'max' => number_format($this->_recalculate(max($tmp->getColumnValues('max')), 'toStoreUnits'), $nmbr, '.', ''),
                      'min' => number_format($this->_recalculate(min($tmp->getColumnValues('min')), 'toStoreUnits'), $nmbr, '.', ''));
    }
    
    protected function _recalculate($value, $mode = 'toDefUnits')
    {
        $tmp = (array)Mage::getConfig()->getNode('sizes/unitsizes/' . Mage::getStoreConfig('sizes/settings/unit_sizes'));
        if ($mode == 'toStoreUnits') {
            $value = $tmp['multiplier'] * $value;
        } else {
            $value = $value / $tmp['multiplier'];
        }
        
        return $value;        
    }
    
}