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
 * @package     Magestore_Inventorydashboard
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydashboard Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydashboard
 * @author      Magestore Developer
 */
class Magestore_Inventorydashboard_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getGroupType() {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $result = '';
        $sql = "SELECT distinct type, title from " . $resource->getTableName('erp_inventory_dashboard_report_type') . " where type != 'unknown'";
        $result = $readConnection->fetchAll($sql);
        return $result;
    }

    public function getReportType($type) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $result = '';
        $sql = 'SELECT * from ' . $resource->getTableName('erp_inventory_dashboard_report_type') . ' where `type` = "' . $type . '"';
        $result = $readConnection->fetchAll($sql);
        return $result;
    }

    public function getDefaultChartType($chartCode = null) {
        $response = '';
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('read_write');
        $sql = "Select distinct(`chart_code`) from " . $resource->getTableName('erp_inventory_dashboard_chart_report') . " where `report_code` = 'hours_of_day'";
        $results = $readConnection->query($sql);
        $i = 1;
        foreach ($results as $result) {
            if ($chartCode == $result['chart_code']) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            $image = Mage::getBaseUrl('media') . 'inventorydashboard/charttype/' . $result['chart_code'] . '.png';
            $response .= '<li style="float:left;margin-right:15px">
                <input type="radio" id="default_chart_type" class="radio validate-one-required-by-name validation-passed" value="' . $result['chart_code'] . '" name="chart_type" title="' . $result['chart_code'] . '" ' . $checked . '/>
                <label for="chart_pie"><img src ="' . $image . '" title="' . $result['chart_code'] . '" /></label>
                </li>';
            $i++;
        }
        return $response;
    }

    public function getItemColumn($tab_id) {
        $itemCol1 = Mage::getModel('inventorydashboard/items')->getCollection()->addFieldToFilter('tab_id', $tab_id)->addFieldToFilter('item_column', 1);
        $itemCol2 = Mage::getModel('inventorydashboard/items')->getCollection()->addFieldToFilter('tab_id', $tab_id)->addFieldToFilter('item_column', 2);
        $item_column = 1;
        if (count($itemCol1) != 0 && count($itemCol2) == 0) {
            $item_column = 2;
        }
        if (count($itemCol2) > 0 && count($itemCol1) > count($itemCol2)) {
            $item_column = 2;
        }
        return $item_column;
    }

    public function getChartData($data) {
        $results = array();
        switch ($data['group_type']) {
            case 'sales':
                $name = $data['chart_name'];
                if ($data['sales_report'] == 'order_attribute') {
                    $reportType = $data['attribute_sales_report'];
                } else {
                    $reportType = $data['sales_report'];
                }
                $chartType = $data['chart_type'];
                break;
            case 'warehouse':
                $name = $data['chart_name'];
                $reportType = $data['warehouse_report'];
                $chartType = $data['chart_type'];
                break;
            case 'product':
                $name = $data['chart_name'];
                $reportType = $data['product_report'];
                $chartType = $data['chart_type'];
                break;
            case 'supplier':
                $name = $data['chart_name'];
                $reportType = $data['supplier_report'];
                $chartType = $data['chart_type'];
                break;
            case 'unknown':
                $name = $data['chart_name'];
                $reportType = $data['report_type'];
                $chartType = $data['chart_type'];
                break;
        }
        $results['name'] = $name;
        $results['reportType'] = $reportType;
        $results['chartType'] = $chartType;
        return $results;
    }

    public function getChartDataEdit($data) {
        $results = array();
        switch ($data['group_type']) {
            case 'sales':
                $name = $data['chart_name'];
                if ($data['sales_report_edit'] == 'order_attribute') {
                    $reportType = $data['attribute_sales_report_edit'];
                } else {
                    $reportType = $data['sales_report_edit'];
                }
                $chartType = $data['chart_type'];
                break;
            case 'warehouse':
                $name = $data['chart_name'];
                $reportType = $data['warehouse_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'product':
                $name = $data['chart_name'];
                $reportType = $data['product_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'supplier':
                $name = $data['chart_name'];
                $reportType = $data['supplier_report_edit'];
                $chartType = $data['chart_type'];
                break;
            case 'unknown':
                $name = $data['chart_name'];
                $reportType = $data['report_type_edit'];
                $chartType = $data['chart_type'];
                break;
        }
        $results['name'] = $name;
        $results['reportType'] = $reportType;
        $results['chartType'] = $chartType;
        return $results;
    }

    public function getChartColumnData($collection, $requestData) {
        $reportcode = $requestData['report_radio_select'];
        $attribute = '';
        if ($reportcode == 'order_attribute') {
            $attribute = $requestData['attribute_select'];
        }
        $series = array();
        $categories = '[';
        $series['inventory_order']['name'] = $this->__('Sales Report By Order');
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
                if (strlen($col->getData('att_' . $attribute)) > 20) {
                    $categories .= '\'' . substr($col->getData('att_' . $attribute), 0, 20) . '\'';
                } else {
                    $categories .= '\'' . $col->getData('att_' . $attribute) . '\'';
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
                }
                $series .= '{name:\'' . $alias . '( ' . $totalInventories . ' )\',y:' . $totalInventories . '}';
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

    //getDateRangeByDay
    public function getDateRangeByDay($days) {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);
        $dateStart->subDay($days - 1);
        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');
        return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
    }

}
