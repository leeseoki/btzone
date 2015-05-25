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
 * Inventoryreports Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Helper_Data extends Mage_Core_Helper_Abstract {

    //get all supplier name
    public function getAllSupplierName() {
        $suppliers = array();
        $model = Mage::getModel('inventorypurchasing/supplier');
        $collection = $model->getCollection();
        foreach ($collection as $supplier) {
            $suppliers[$supplier->getId()] = $supplier->getSupplierName();
        }
        return $suppliers;
    }

    //get all warehouse name
    public function getAllWarehouseName() {
        $warehouses = array();
        $model = Mage::getModel('inventoryplus/warehouse');
        $collection = $model->getCollection();
        foreach ($collection as $warehouse) {
            $warehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
        }
        return $warehouses;
    }

    //get time inventory for each product
    public function getTimeInventory($item, $filterData) {
        $time = '';
        $count = 0;
        $totalTime = 0;
        $now = time(); // or your date as well

        $block = new Magestore_Inventoryreports_Block_Adminhtml_Supplier_Product_Grid;
        $filter = $block->getParam($block->getVarNameFilter(), null);
        $condorder = '';
        if ($filter) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'supplier_id') {
                    $condorder = $key;
                }
            }
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorybarcode')) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $results = '';
            $purchaseOrderIds = array();
            if ($condorder) {
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM ' . $resource->getTableName('inventorybarcode/barcode') . ' where (`product_entity_id` = ' . $item->getId() .
                        ') and (`supplier_supplier_id` = ' . $condorder . ') and (`qty` > ' . 0 . ')';
            } else {
                $sql = 'SELECT distinct(`purchaseorder_purchase_order_id`) FROM ' . $resource->getTableName('inventorybarcode/barcode') . ' where (`product_entity_id` = ' . $item->getId() .
                        ') and (`qty` > ' . 0 . ')';
            }
            $results = $readConnection->query($sql);
            if ($results) {
                foreach ($results as $result) {
                    $purchaseOrderIds[] = $result['purchaseorder_purchase_order_id'];
                }
            }
            $purchaseOrders = Mage::getModel('inventorypurchasing/purchaseorder')
                    ->getCollection()
                    ->addFieldToFilter('purchase_order_id', array('in' => $purchaseOrderIds));
            $count += $purchaseOrders->getSize();
            $notPurchases = Mage::getModel('inventorybarcode/barcode')
                    ->getCollection()
                    ->addFieldToFilter('purchaseorder_purchase_order_id', '')
                    ->addFieldToFilter('qty', array('gt' => 0));
            $count += $notPurchases->getSize();
            foreach ($purchaseOrders as $purchaseOrder) {
                $your_date = strtotime($purchaseOrder->getPurchaseOn());
                $datediff = $now - $your_date;
                $totalTime += floor($datediff / (60 * 60 * 24));
                $time = 1;
            }

            if ($time == '') {
                return '';
            }
            $time = round($totalTime / $count, 1);
            return $time;
        }
        $deliveries = Mage::getModel('inventorypurchasing/purchaseorder_delivery')
                ->getCollection()
                ->addFieldToFilter('product_id', $row->getId());
        foreach ($deliveries as $delivery) {
            $count++;
            $your_date = strtotime($delivery->getDeliveryDate());
            $datediff = $now - $your_date;
            $time = 1;
            $totalTime += floor($datediff / (60 * 60 * 24));
        }
        if ($time == '') {
            return 'N/A';
        }
        $time = round($totalTime / $count, 1);
        return $time;
    }

    //reset collection
    public function _tempCollection() {
        $collection = new Varien_Data_Collection();
        return $collection;
    }

    //get days of week
    public function getDaysOfWeek() {
        return array(
            '1' => $this->__('Sunday'),
            '2' => $this->__('Monday'),
            '3' => $this->__('Tuesday'),
            '4' => $this->__('Wednesday'),
            '5' => $this->__('Thusday'),
            '6' => $this->__('Friday'),
            '7' => $this->__('Saturday'),
        );
    }

    //get period for reports
    public function getPeriodOptions() {
        $options = array();
        $options = array(
            '1' => $this->__('Day'),
            '2' => $this->__('Month'),
            '3' => $this->__('Year'),
        );
        return $options;
    }

    public function checkDisplay($report_radio_select) {
        if ($report_radio_select == 'warehousing_time_longest') {
            return 0;
        }
        if ($report_radio_select == 'total_stock_different_when_physical_stocktaking_by_warehouse') {
            return 2;
        } else {
            return 1;
        }
    }

    public function getTimeSelected($time_request) {
        $result_time = array();
        $number = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $first_day = date('Y-m-01 00:00:00');
        $last_day = date('Y-m-' . $number . ' 23:59:59');
        $start_day = date('Y-m-d 00:00:00');
        $end_day = date('Y-m-d 23:59:59');
        if (isset($time_request['select_time'])) {
            $select_time = $time_request['select_time'];
            switch ($select_time) {
                default:
                    $time = 30;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "last_7_days":
                    $time = 7;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "last_30_days":
                    $time = 30;
                    $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
                    $to = $end_day;
                    break;
                case "next_7_days":
                    $time = 7;
                    $from = $start_day;
                    $to = strftime('%Y-%m-%d 23:59:59', strtotime(date("Y-m-d", strtotime($start_day)) . " +$time day"));
                    break;
                case "next_30_days":
                    $time = 30;
                    $from = $start_day;
                    $to = strftime('%Y-%m-%d 23:59:59', strtotime(date("Y-m-d", strtotime($start_day)) . " +$time day"));
                    break;
                case "this_week":
                    $day = date('w');
                    $week_start = date('Y-m-d 00:00:00', strtotime('-' . $day . ' days'));
                    $week_end = date('Y-m-d 23:59:59', strtotime('+' . (6 - $day) . ' days'));
                    $from = $week_start;
                    $to = $week_end;
                    break;
                case "this_month":
                    $from = $first_day;
                    $to = $last_day;
                    break;
                case "range":
                    $from = $time_request['date_from'] . ' 00:00:00';
                    $to = $time_request['date_to'] . ' 23:59:59';
                    break;
            }
        } else {
            $time = 30;
            $from = strftime('%Y-%m-%d 00:00:00', strtotime(date("Y-m-d", strtotime($start_day)) . " -$time day"));
            $to = $end_day;
        }
        $result_time['date_from'] = $from;
        $result_time['date_to'] = $to;
        return $result_time;
    }

    public function getHeaderText() {
        $requestData = Mage::helper('adminhtml')->prepareFilterString(Mage::app()->getRequest()->getParam('top_filter'));
        $reportcode = $requestData['report_radio_select'];
        $type_id = Mage::app()->getRequest()->getParam('type_id');
        switch ($reportcode) {
            case 'hours_of_day':
                return $this->__(ucwords($type_id) . ' Report By ' . 'Hour Of Day');
            case 'days_of_week':
                return $this->__(ucwords($type_id) . ' Report By ' . 'Day Of Week');
            case 'order_attribute':
                $attribute = $requestData['attribute_select'];
                return $this->__(ucwords($type_id) . ' Report By ' . ucwords(str_replace('_', ' ', $attribute)));
            case 'invoice':
                return $this->__(ucwords($type_id) . ' Report By ' . 'Invoices');
            case 'refund':
                return $this->__(ucwords($type_id) . ' Report By ' . 'Refunds');
            case 'total_qty_adjuststock':
                return $this->__('Report on Total Adjusted Qty by Warehouse');
            case 'number_of_product_adjuststock':
                return $this->__('Report on Number of Products Being Adjusted Qty by Warehouse');
            case 'total_order_by_warehouse':
                return $this->__('Report on Total Sales Orders by Warehouse');
            case 'sales_by_warehouse_revenue':
                return $this->__('Warehouse Revenue Report');
            case 'sales_by_warehouse_item_shipped':
                return $this->__('Report on Number of Items Shipped by Warehouse');
            case 'total_stock_transfer_send_stock':
                return $this->__('Report on Total Qty Sent by Warehouse');
            case 'total_stock_transfer_request_stock':
                return $this->__('Report on Total Qty Requested by Warehouse');
            case 'supply_needs_by_warehouse_products':
                return $this->__('Report on Supply Needs by Warehouse');
            case 'total_stock_different_when_physical_stocktaking_by_warehouse':
                return $this->__('Report on Stocktake Qty Variances by Warehouse');
            case 'best_seller':
                return $this->__('Bestseller Report');
            case 'most_stock_remain':
                return $this->__('Report on Qty. Remaining by Product');
            case 'warehousing_time_longest':
                return $this->__('Report on Warehousing Time by Product');
            case 'purchase_order_to_supplier':
                return $this->__('Report on Total Qty Purchased by Supplier');
        }
    }

    public function getSupplierName($id) {
        return Mage::getModel('inventorypurchasing/supplier')->load($id)->getSupplierName();
    }

    public function getSupplierReportCollection($requestData) {
        if ($requestData['select_time']) {
            $gettime = $this->getTimeSelected($requestData);
            $purchase_ids = array();
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $installer = Mage::getModel('core/resource');
            $sql = 'SELECT distinct(`purchase_order_id`) from ' . $installer->getTableName("erp_inventory_purchase_order") . ' WHERE (purchase_on BETWEEN "' . $gettime['date_from'] . '" and "' . $gettime['date_to'] . '")';
            $results = $readConnection->fetchAll($sql);
            foreach ($results as $result) {
                $purchase_ids[] = $result['purchase_order_id'];
            }
            $ids = join(',', $purchase_ids);
            if (isset($requestData['supplier_select']) && $supplierId = $requestData['supplier_select']) {
                $productAttribute = $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');
                $resource = Mage::getSingleton('core/resource');
                $collection = Mage::getModel('inventorypurchasing/supplier_product')
                        ->getCollection()
                        ->addFieldToFilter('supplier_id', $supplierId);
                if ($ids) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.product_id=barcode.product_entity_id and barcode.qty > 0 and barcode.supplier_supplier_id = ' . $supplierId . ' and barcode.purchaseorder_purchase_order_id IN (' . $ids . ')', //.' and barcode.purchaseorder_purchase_order_id IN '.$stringpurchase
                                    array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->joinLeft(array('product_attribute' => $resource->getTableName('catalog_product_entity_' . $productAttribute->getData('backend_type'))), 'main_table.product_id=product_attribute.entity_id and product_attribute.attribute_id = ' . $productAttribute->getData('attribute_id'), array('product_name' => 'product_attribute.value')
                            )
                            ->group(array('main_table.product_id'));
                } else {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.product_id=barcode.product_entity_id and barcode.qty > 0 and barcode.supplier_supplier_id = ' . $supplierId . ' and barcode.purchaseorder_purchase_order_id IN (0)', //.' and barcode.purchaseorder_purchase_order_id IN '.$stringpurchase
                                    array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->joinLeft(array('product_attribute' => $resource->getTableName('catalog_product_entity_' . $productAttribute->getData('backend_type'))), 'main_table.product_id=product_attribute.entity_id and product_attribute.attribute_id = ' . $productAttribute->getData('attribute_id'), array('product_name' => 'product_attribute.value')
                            )
                            ->group(array('main_table.product_id'));
                }
                $collection->setIsGroupCountSql(true);
            } else {
                $collection = Mage::getModel('inventorypurchasing/supplier')
                        ->getCollection();
                if (!empty($ids)) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.supplier_id=barcode.supplier_supplier_id and barcode.qty > 0 and barcode.purchaseorder_purchase_order_id IN (' . $ids . ')', array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->group(array('main_table.supplier_id'));
                }
                if (empty($ids)) {
                    $collection->getSelect()
                            ->joinLeft(array('barcode' => $collection->getTable('inventorybarcode/barcode')), 'main_table.supplier_id=barcode.supplier_supplier_id and barcode.qty > 0 and barcode.purchaseorder_purchase_order_id IN (0)', array('total_inventory' => 'sum(barcode.qty)')
                            )
                            ->group(array('main_table.supplier_id'));
                }
            }
        }
        //$collection->addFieldToFilter('total_inventory',array('gt'=>0));
        return $collection;
    }

    public function getDefaultOptionsWarehouse() {
        $options = array();
        $options["select_time"] = "last_30_days";
        $options["report_radio_select"] = "total_qty_adjuststock";
        $options["select_warehouse"] = "0";
        return $options;
    }

    public function getDefaultOptionsSupplier() {
        $options = array();
        $options["select_time"] = "last_30_days";
        $options["report_radio_select"] = "purchase_order_to_supplier";
        $options["select_warehouse"] = "0";
        return $options;
    }

    public function getWarehouseName($id) {
        return Mage::getModel('inventoryplus/warehouse')->load($id)->getWarehouseName();
    }

    public function checkProductInWarehouse($data, $warehouse_id) {
        $product_ids = array();
        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->addFieldToFilter('warehouse_id', $warehouse_id);
        $collection->addFieldToFilter('product_id', array('in' => $data));
        foreach ($collection as $value) {
            $product_ids[] = $value->getProductId();
        }
        return $product_ids;
    }

    public function checkNullDataChart($series) {
        $seriesCheckNull = explode(',', $series);
        $seriesCheckNull = array_filter($seriesCheckNull);
        $newSeries = implode(',', $seriesCheckNull);
        return $newSeries;
    }

}
