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
 * Inventorysupplyneeds Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getOrderInPeriod($datefrom, $dateto) {
        //Khoang thoi gian tinh tu thoi diem hien tai den thoi gian da chon
        $range = ceil((strtotime($dateto) - strtotime($datefrom)) / (3600 * 24));        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $allOrder = array();
        for ($i = 1; $i <= 10; $i++) {
            $orderIds = array();
            $j = $i - 1;
            $x = $range * $j;
            $y = $range * $i;
            $today = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$x day"));
            $lastperiod = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$y day"));            
            $orders = $readConnection->fetchAll("SELECT `entity_id` FROM `" . $resource->getTableName('sales/order') . "` WHERE (created_at >= '$lastperiod' AND created_at <= '$today')");
            foreach ($orders as $order) {
                array_push($orderIds, $order['entity_id']);
            }
            $string_orderIds = implode("','", $orderIds);
            array_push($allOrder, $string_orderIds);
        }
        return $allOrder;
    }

    //Tinh so luong san pham can thiet min
    public function calMin($product_id, $warehouse) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        if (!$warehouse) {
            $stockCollection = $readConnection->fetchAll("SELECT * FROM `" . $resource->getTableName('cataloginventory/stock_item') . "` WHERE (product_id = $product_id)");
            $stock = $stockCollection[0]['qty'];
            $min_needs = - (int) $stock;
        } else {
            $order_items = $readConnection->fetchAll("SELECT `parent_item_id`,`qty_shipped`,`qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = $product_id)");
            $warehouse_product = $readConnection->fetchAll("SELECT * FROM `" . $resource->getTableName('inventoryplus/warehouse_product') . "` WHERE (product_id = $product_id) AND (warehouse_id = $warehouse)");
            $qty_warehouse = $warehouse_product[0]['total_qty'];
            $qty_ordered = array();
            $qty_shipped = array();
            $qty_canceled = array();
            foreach ($order_items as $order_item) {
                $shipped = $order_item['qty_shipped'];
                $ordered = $order_item['qty_ordered'];
                $canceled = $order_item['qty_canceled'];
                if(($canceled == 0 || $shipped == 0) && $order_item['parent_item_id']){                                        
                    $parentId = $order_item['parent_item_id'];
                    $parents = $readConnection->fetchAll("SELECT `product_type`,`qty_shipped`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (item_id = $parentId)");                    
                    foreach($parents as $parent)
                        if($parent['product_type'] == 'configurable'){
                            $shipped = $parent['qty_shipped'];
                            $canceled = $parent['qty_canceled'];
                        }
                }
                $qty_ordered[] = $ordered;
                $qty_shipped[] = $shipped;
                $qty_canceled[] = $canceled;
            }
            $min_needs = (int) array_sum($qty_ordered) - (int) array_sum($qty_shipped) - (int) $qty_warehouse - (int) array_sum($qty_canceled);
        }
        return $min_needs;
    }

    //Tinh so luong san pham can thiet max Exponential
    public function calMaxExponential($product_id, $datefrom, $dateto, $warehouse) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $a = 0.1; //Bien a va bien b se duoc nhap vao tu tren form, de toi uu thi $a nen nam trong khoang 0.1 - 0.4
        $b = 0.5;
        $D = array(); //Doanh so thuc te
        $F = array(); //Du bao
        $T = array(); //Dinh huong
        $FIT = array(); //Du bao co dinh huong = date('Y-m-d');
        //Lay min
        $min_needs = $this->calMin($product_id, $warehouse);
        //Lay order
        $orders = $this->getOrderInPeriod($datefrom, $dateto);        
        if (!$warehouse) {
            $canceled_qty = array();
            if ($orders) {
                ////////////////////////////////////////////////////////////
                //Xac dinh so luong san pham tuong lai                
                foreach ($orders as $order) {
                    $period_qty = array();
                    $order_items = $readConnection->fetchAll("SELECT `parent_item_id`,`qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$order'))");
                    foreach ($order_items as $item) {
                        $qtyOrdered = $item['qty_ordered'];
                        $qtyCanceled = $item['qty_canceled'];
                        if($item['qty_ordered'] == 0 && $order_item['parent_item_id']){                                        
                            $parentId = $order_item['parent_item_id'];
                            $parents = $readConnection->fetchAll("SELECT `product_type`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (item_id = $parentId)");                    
                            foreach($parents as $parent)
                                if($parent['product_type'] == 'configurable'){                                    
                                    $qtyCanceled = $parent['qty_canceled'];
                                }
                        }
                        array_push($period_qty, $qtyOrdered);
                        array_push($canceled_qty, $qtyCanceled);
                    }
                    array_push($D, array_sum($period_qty));
                }
            } else {
                array_push($D, 0);
                array_push($canceled_qty, 0);
            }
        } else {
            if ($orders) {
                $canceled_qty = array();
                ////////////////////////////////////////////////////////////
                //Xac dinh so luong san pham tuong lai
                foreach ($orders as $order) {
                    $warehouse_order_ids = array();
                    $period_qty = array();
                    $warehouse_orders = $readConnection->fetchAll("SELECT `order_id` FROM `" . $resource->getTableName('inventoryplus/warehouse_shipment') . "` WHERE (product_id = '$product_id') AND (warehouse_id = '$warehouse') AND (order_id IN ('$order'))");
                    foreach ($warehouse_orders as $warehouse_order) {
                        $warehouse_order_ids[] = $warehouse_order['order_id'];
                    }
                    $warehouse_order_ids_unique = array_unique($warehouse_order_ids);
                    $string_warehouse_orders = implode("','", $warehouse_order_ids_unique);
                    $order_items = $readConnection->fetchAll("SELECT `qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$string_warehouse_orders'))");
                    foreach ($order_items as $item) {
                        array_push($period_qty, $item['qty_ordered']);
                    }
                    $order_items_cancel = $readConnection->fetchAll("SELECT `qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$order'))");
                    foreach ($order_items_cancel as $item_cancel) {
                        array_push($canceled_qty, $item_cancel['qty_canceled']);
                    }
                    array_push($D, array_sum($period_qty));
                }
            } else {
                array_push($D, 0);
                array_push($canceled_qty, 0);
            }
        }
        ////////////////////////////////////////////////////////////
        //Lay so luong san pham canceled trong ki        
        $D = array_reverse($D);        
        $F[0] = $D[0];
        $T[0] = 0;
        $FIT[0] = $F[0] + $T[0];
        for ($i = 1; $i < count($D) + 1; $i++) {
            $F[$i] = $a * $D[$i - 1] + (1 - $a) * $F[$i - 1];
            $T[$i] = $T[$i - 1] + $b * ($F[$i] - $F[$i - 1]);
            $FIT[$i] = $F[$i] + $T[$i];
        }
        $future_qty = end($FIT);
        $future_qty = ceil($future_qty);
        
        //////////////////////////////////////////////////////////
        //Tinh nhu cau max trong tuong lai
        $max_needs = (int) $min_needs + (int) array_sum($canceled_qty) + (int) $future_qty;
        return $max_needs;
    }
    
    
    //Tinh so luong san pham can thiet max average
    public function calMaxAverage($product_id, $datefrom, $dateto, $warehouse) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');      
        $D = array(); //Doanh so thuc te
        $F = array(); //Du bao
        $T = array(); //Dinh huong
        $FIT = array(); //Du bao co dinh huong = date('Y-m-d');
        //Lay min
        $min_needs = $this->calMin($product_id, $warehouse);
        //Lay order
        $orders = $this->getOrderInPeriod($datefrom, $dateto);        
        if (!$warehouse) {
            $canceled_qty = array();
            if ($orders) {
                ////////////////////////////////////////////////////////////
                //Xac dinh so luong san pham tuong lai                
                foreach ($orders as $order) {
                    $period_qty = array();
                    $order_items = $readConnection->fetchAll("SELECT `parent_item_id`,`qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$order'))");
                    foreach ($order_items as $item) {
                        $qtyOrdered = $item['qty_ordered'];
                        $qtyCanceled = $item['qty_canceled'];
                        if($item['qty_ordered'] == 0 && $order_item['parent_item_id']){                                        
                            $parentId = $order_item['parent_item_id'];
                            $parents = $readConnection->fetchAll("SELECT `product_type`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (item_id = $parentId)");                    
                            foreach($parents as $parent)
                                if($parent['product_type'] == 'configurable'){                                    
                                    $qtyCanceled = $parent['qty_canceled'];
                                }
                        }
                        array_push($period_qty, $qtyOrdered);
                        array_push($canceled_qty, $qtyCanceled);
                    }
                    array_push($D, array_sum($period_qty));
                }
            } else {
                array_push($D, 0);
                array_push($canceled_qty, 0);
            }
        } else {
            if ($orders) {
                $canceled_qty = array();
                ////////////////////////////////////////////////////////////
                //Xac dinh so luong san pham tuong lai
                foreach ($orders as $order) {
                    $warehouse_order_ids = array();
                    $period_qty = array();
                    $warehouse_orders = $readConnection->fetchAll("SELECT `order_id` FROM `" . $resource->getTableName('inventoryplus/warehouse_shipment') . "` WHERE (product_id = '$product_id') AND (warehouse_id = '$warehouse') AND (order_id IN ('$order'))");
                    foreach ($warehouse_orders as $warehouse_order) {
                        $warehouse_order_ids[] = $warehouse_order['order_id'];
                    }
                    $warehouse_order_ids_unique = array_unique($warehouse_order_ids);
                    $string_warehouse_orders = implode("','", $warehouse_order_ids_unique);
                    $order_items = $readConnection->fetchAll("SELECT `qty_ordered`,`qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$string_warehouse_orders'))");
                    foreach ($order_items as $item) {
                        array_push($period_qty, $item['qty_ordered']);
                    }
                    $order_items_cancel = $readConnection->fetchAll("SELECT `qty_canceled` FROM `" . $resource->getTableName('sales/order_item') . "` WHERE (product_id = '$product_id') AND (order_id IN ('$order'))");
                    foreach ($order_items_cancel as $item_cancel) {
                        array_push($canceled_qty, $item_cancel['qty_canceled']);
                    }
                    array_push($D, array_sum($period_qty));
                }
            } else {
                array_push($D, 0);
                array_push($canceled_qty, 0);
            }
        }
        ////////////////////////////////////////////////////////////
        //Lay so luong san pham canceled trong ki        
        $D = array_reverse($D);
        $future_qty = 0;
        $future_qty = ceil((array_sum($D)+ array_sum($canceled_qty))/10);
      
        //////////////////////////////////////////////////////////
        //Tinh nhu cau max trong tuong lai
        $max_needs = (int) $min_needs  + (int) $future_qty;
        return $max_needs;
    }

    public function _tempCollection(){
        $collection = new Varien_Data_Collection();
        return $collection;
    }

    public function processFilterData(){
        $data = array();
        $requestData = Mage::helper('adminhtml')->prepareFilterString(Mage::app()->getRequest()->getParam('top_filter'));
        $warehouse = $datefrom = $dateto = '';
        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if ($requestData && isset($requestData['date_from']))
            $datefrom = $requestData['date_from'];
        if (!$datefrom) {
            $now = now();            
            $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if ($requestData && isset($requestData['date_to']))
            $dateto = $requestData['date_to'];
        if (!$dateto) {
            $now = now();            
            $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if($datefrom)
            $datefrom = $datefrom . ' 00:00:00';
        if($dateto)
            $dateto = $dateto . ' 23:59:59';

        $data['warehouse'] = $warehouse;
        $data['date_to'] = $dateto;
        $data['date_from'] = $datefrom;

        return $data;
    }

    public function getOutstockDate($product_id, $datefrom, $dateto, $warehouse,$row){
        //average qty sell per period
        $max_needs = $this->calMaxAverage($product_id, $datefrom, $dateto, $warehouse);
        $min_needs = $this->calMin($product_id, $warehouse);
        $total_qty_per_period = $max_needs - $min_needs;     
        //range of period (day)
        $range = ceil((strtotime($dateto) - strtotime($datefrom)) / (3600 * 24));
        //average item sell per day
        $soldperday = $total_qty_per_period / $range;
        //qty in warehouse
        $qty_in_stock = $row->getQtyWarehouse();
        //time to reach outstock (day)
        $time_to_outstock = (int) $qty_in_stock / (int) $soldperday;
        //calculate outstock date
        $outstock_date = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " +$time_to_outstock day"));
        $outstock_date = Mage::helper('core')->formatDate($outstock_date, 'short', $showTime=false);
        return $outstock_date;
    }

    public function filterList($list) {
        $result = array();
        foreach ($list as $item) {
            $p = explode('=', $item);
            if (isset($p[0]) && isset($p[1])) {
                $qty = explode('_', $p[0]);
                $result[$qty[1]] = $p[1];
            }
        }
        return $result;
    }

    public function getWarehousesCanPurchase() {
        $collection = Mage::getModel('inventoryplus/warehouse')
                ->getCollection()
                ->addFieldToFilter('status', 1);
        $ids = array();
        if ($collection->getSize()) {
            $ids = $collection->getAllIds();
        }
        return $ids;
    }

}