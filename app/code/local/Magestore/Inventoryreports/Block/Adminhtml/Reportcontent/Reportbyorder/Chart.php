<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryreports Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Chart extends Mage_Adminhtml_Block_Widget {

    public function getChartColumnData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $attribute = '';
        if ($reportcode == 'order_attribute') {
            $attribute = $requestData['attribute_select'];
        }
        $series = array();
        $categories = '[';
        $series['inventory_order']['name'] = $this->__('Base Grandtotal');
        $series['inventory_order']['data'] = '[';
        $i = 0;
        $total_order = 0;
        $total_invoice = 0;
        $total_refund = 0;
        foreach ($collection as $col) {
            if ($i != 0) {
                $categories .= ',';
                $series['inventory_order']['data'] .= ',';
            }
            if ($attribute) {
                if ($attribute == 'shipping_method' && !$col->getData('att_' . $attribute)) {
                    $categories .= '\'' . $this->__('No shipping') . '\'';
                } else {
                    $categories .= '\'' . ucwords($col->getData('att_' . $attribute)) . '\'';
                }
            } else if ($reportcode == 'days_of_week') {
                $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                $categories .= '\'' . $daysofweek[$col->getTimeRange()] . '\'';
            } else if ($reportcode == 'hours_of_day') {
                $categories .= '\'' . $col->getTimeRange() . ':00' . ' - ' . $col->getTimeRange() . ':59' . '\'';
            }
            if ($reportcode == 'invoice') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_invoice += round($col->getData('sum_base_grand_total_invoiced'), 2);
            } else if ($reportcode == 'refund') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_refund += round($col->getData('sum_base_grand_total_refunded'), 2);
            } else {
                $series['inventory_order']['data'] .= round($col->getData('sum_base_grand_total'), 2);
            }
            $i++;
        }
        if ($total_order) {
            $categories = '[' . '\'' . 'Total Ordered' . '\'';
            $series['inventory_order']['data'] = '[' . $total_order;
            if ($reportcode == 'invoice') {
                $categories .= ',' . '\'' . 'Total Invoiced' . '\'';
                $series['inventory_order']['data'] .= ',' . $total_invoice;
            } else {
                $categories .= ',' . '\'' . 'Total Refunded' . '\'';
                $series['inventory_order']['data'] .= ',' . $total_refund;
            }
        }
        $categories .= ']';
        $series['inventory_order']['data'] .= ']';
        $data['categories'] = $categories;
        //$series['inventory_order']['data'] = Mage::helper('inventoryreports')->checkNullDataChart($series['inventory_order']['data']);
        $data['series'] = $series; 
        return $data;
    }

    public function getChartPieData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $attribute = '';
        if ($reportcode == 'order_attribute') {
            $attribute = $requestData['attribute_select'];
        }
        $series = '';
        $i = 0;
        $j = 0;
        $total_order = 0;
        $total_invoice = 0;
        $total_refund = 0;
        foreach ($collection as $col) {
            $totalInventories = 0;
            if ($col->getData('sum_base_grand_total'))
                $totalInventories = round($col->getData('sum_base_grand_total'), 2);
            if ($i != 0)
                $series .= ',';
            if ($attribute) {
                if ($attribute == 'shipping_method' && !$col->getData('att_' . $attribute)) {
                    $alias = $this->__('No shipping');
                } else {
                    $alias = ucwords($col->getData('att_' . $attribute));
                }
            } else if ($reportcode == 'days_of_week') {
                $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                $alias = $daysofweek[$col->getTimeRange()];
            } else if ($reportcode == 'hours_of_day') {
                $alias = $col->getTimeRange() . ':00' . ' - ' . $col->getTimeRange() . ':59';
            }
            if ($reportcode == 'invoice') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_invoice += round($col->getData('sum_base_grand_total_invoiced'), 2);
            } else if ($reportcode == 'refund') {
                $total_order += round($col->getData('sum_base_grand_total'), 2);
                $total_refund += round($col->getData('sum_base_grand_total_refunded'), 2);
            } else {
                if ($totalInventories > 0) {
                    $j++;
                    $series .= '{name:\'' . $alias . '( ' . $totalInventories . ' )\',y:' . $totalInventories . '}';
                }
                
            }
            $i++;
        }
        if ($reportcode != 'invoice' && $reportcode != 'refund' && $j == 0) {
            return false;
        }
        if ($total_order && $total_order > 0) {
            $series = '';
            if ($reportcode == 'invoice') {
                $series .= '{name:\'' . 'Total Invoiced' . '(' . round(($total_invoice / $total_order) * 100, 2) . '%)\',y:' . $total_invoice . '}';
                if (($total_order - $total_invoice) > 0) {
                    $series .= ',';
                    $series .= '{name:\'' . 'Total Not Invoiced' . '( ' . (100 - round(($total_invoice / $total_order) * 100, 2)) . '%)\',y:' . ($total_order - $total_invoice) . '}';
                }
            } else {
                $series .= '{name:\'' . 'Total Refunded' . '( ' . round(($total_refund / $total_order) * 100, 2) . '%)\',y:' . $total_refund . '}';
                if (($total_order - $total_refund) > 0) {
                    $series .= ',';
                    $series .= '{name:\'' . 'Total Not Refunded' . '( ' . (100 - round(($total_refund / $total_order) * 100, 2)) . '%)\',y:' . ($total_order - $total_refund) . '}';
                }
            }
        }
        $series = Mage::helper('inventoryreports')->checkNullDataChart($series);
        $data['series'] = $series;
        return $data;
    }

}
