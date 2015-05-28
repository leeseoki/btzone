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
class Belvg_Sizes_Block_Popup_Form extends Belvg_Sizes_Block_Popup
{

    public function getRanges($dem_id)
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $filter = Mage::getModel('sizes/standardsvalues')->getCollection()
            ->addFieldToFilter('standard_id', $this->getStandardId())->getColumnValues('value_id');
            
        $ranges = Mage::getModel('sizes/main')->getCollection()
            ->addFieldToFilter('dem_id', $dem_id)
            ->addFieldToFIlter('main_table.value_id', $filter);
            
        $ranges->getSelect()
            ->joinLeft(
                array('values' => $prefix . 'belvg_sizes_standards_values'),
               'main_table.value_id=values.value_id',
                array('value')
            );

        $min = min($ranges->getColumnValues('min'));
        $max = max($ranges->getColumnValues('max'));
        $full_r = $max - $min;
        
        $result = array();
        foreach ($ranges as $range) {
            if (isset($lastRange)) {
                if (($lastRange->getMax() == $range->getMax()) 
                && ($lastRange->getMin() == $range->getMin())) {
                    end($result);
                    $lastKey = key($result);
                    $result[$lastKey . '/' . $range->getValue()] = $result[$lastKey];
                    unset($result[$lastKey]);
                    $lastRange = $range;
                    continue;
                }
            }
            
            $r = $range->getMax() - $range->getMin();
            $percent = $r / $full_r * 100;
            $result[$range->getValue()] = $percent;
            $lastRange = $range;
        }
        
        return $result;
    }
    
}