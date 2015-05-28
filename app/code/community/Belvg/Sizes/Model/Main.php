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
        $helper = Mage::helper('sizes');
        if (isset($dem)) {
            foreach ($dem as $key=>$item) {
                $helper->saveSizeInSession($key, $item);
                $dem[$key] = $this->_recalculate($item, $mode = 'toDefUnits');
            }
        }
        
        
        $it = (int)Mage::getStoreConfig('sizes/calculation/tolerance');
        $result = array();
        $fit = array();
        foreach ((array)$dem as $key=>$value) {
            $val_id = $this->_getValueId($cat_id, $key, $value);
            $val_id_gt = $tmp = $this->_getValueId($cat_id, $key, $value + ($value*$it/100));
            $val_id_lt = $tmp = $this->_getValueId($cat_id, $key, $value - ($value*$it/100));
            $val_id_gt = (!empty($val_id_gt))?$val_id_gt:$val_id;
            $val_id_lt = (!empty($val_id_lt))?$val_id_lt:$val_id;
            
            $val_id = $this->filterByStandard($standard_id, $val_id);
            $val_id_gt = $this->filterByStandard($standard_id, $val_id_gt);
            $val_id_lt = $this->filterByStandard($standard_id, $val_id_lt);
            
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
    
    protected function _defineEq($eqs)
    {
        foreach ($eqs as $key=>$eq) {
            if (isset($prevKey)) {
                if (isset($result)) {
                    $result = array_intersect($result, $eqs[$key]); 
                } else {
                    $result = array_intersect($eqs[$prevKey], $eqs[$key]);
                }
            }
            $prevKey = $key;
        }
    
        return $result;
    }
    
    protected function _findOptimalSizes($fit, $num)
    {
        $dim_qty = (int)Mage::getStoreConfig('sizes/calculation/qty');
        $tmp = current($fit);
        $result = array();
        /*$optimal = FALSE;*/
        //$gt = FALSE;
        //$lt = FALSE;
        
        if (is_array($tmp)) {
                foreach ($fit as $dim=>$row) {
                    $eqs[$dim] = $row['eq'];
                }
                
                if ($eq = $this->_defineEq($eqs)) {
                    $optimal = current($eq);
                } else {
                    $optimal = FALSE;
                }
                
                $array = $eqs;
                foreach ($fit as $dim=>$row) {
                    if (!isset($gt)) {
                    $array[$dim] = $row['gt'];
                        if ($eq = $this->_defineEq($array)) {
                            foreach ($eq as $item) {
                                if ($item != $optimal) {
                                    $gt[] = array('value' => $item,
                                                    'dim' => $this->getDemLabel($dim));
                                }
                            }
                        }
                    }
                    
                    if (!isset($lt)) {                    
                    $array[$dim] = $row['lt'];
                        if ($eq = $this->_defineEq($array)) {
                            foreach ($eq as $item) {
                                if ($item != $optimal) {
                                    $lt[] = array('value' => $item,
                                                    'dim' => $this->getDemLabel($dim));
                                }
                            }
                        }
                    }
                }
                
                $gt = isset($gt)?$gt:FALSE;
                $lt = isset($lt)?$lt:FALSE;
        }
        
        return array('optimal' => $optimal,
                          'gt' => $gt,
                          'lt' => $lt);
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
        //print_r($id);die;
        if (is_array($id)) {
            return Mage::getModel('sizes/standardsvalues')->getCollection()
                ->addFieldToFilter('value_id', $id)->getColumnValues('value');
        } else {
            return Mage::getModel('sizes/standardsvalues')->load($id)->getValue();
        }
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