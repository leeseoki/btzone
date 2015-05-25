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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Model_Observer {

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Inventorywarehouse_Model_Observer
     */
    public function inventorywarehouse_menu($observer) {
        $menu = $observer->getEvent()->getMenus()->getMenu();

        $menu['warehouses'] = array('label' => Mage::helper('inventorywarehouse')->__('Warehouses'),
            'sort_order' => 100,
            'url' => '',
            'active' => (Mage::app()->getRequest()->getControllerName() == 'adminhtml_warehouse' || Mage::app()->getRequest()->getControllerName() == 'adminhtml_sendstock' || Mage::app()->getRequest()->getControllerName() == 'adminhtml_requeststock') ? true : false,
            'level' => 0,
            'children' => array(
                'warehouse' => array('label' => Mage::helper('inventorywarehouse')->__('Manage Warehouses'),
                    'sort_order' => 100,
                    'url' => Mage::helper("adminhtml")->getUrl("inventoryplusadmin/adminhtml_warehouse/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => false,
                    'level' => 1),
                'sendstock' => array('label' => Mage::helper('inventorywarehouse')->__('Send Stock'),
                    'sort_order' => 110,
                    'url' => Mage::helper("adminhtml")->getUrl("inventorywarehouseadmin/adminhtml_sendstock/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => false,
                    'level' => 1
                ),
                'requeststock' => array('label' => Mage::helper('inventorywarehouse')->__('Request Stock'),
                    'sort_order' => 120,
                    'url' => Mage::helper("adminhtml")->getUrl("inventorywarehouseadmin/adminhtml_requeststock/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => false,
                    'level' => 1)
            )
        );
        $observer->getEvent()->getMenus()->setData('menu', $menu);
    }

    public function add_more_permission($observer) {
        $assignment = $observer->getEvent()->getPermission();
        $data = $observer->getEvent()->getData();
        $adminId = $observer->getEvent()->getAdminId();
        $changePermissions = $observer->getEvent()->getChangePermssions();

        $transfers = array();
        if (isset($data['data']['transfer']) && is_array($data['data']['transfer'])) {
            $transfers = $data['data']['transfer'];
        }

        if ($assignment->getId()) {
            $oldTransfer = $assignment->getCanSendRequestStock();
        }

        if (in_array($adminId, $transfers)) {
            if ($assignment->getId()) {
                if ($oldTransfer != 1) {
                    $changePermissions[$adminId]['old_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
                    $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_transfer'] = '';
                $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
            }
            $assignment->setData('can_send_request_stock', 1);
        } else {
            if ($assignment->getId()) {
                if ($oldTransfer != 0) {
                    $changePermissions[$adminId]['old_transfer'] = Mage::helper('inventoryplus')->__('Can transfer Warehouse');
                    $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_transfer'] = '';
                $changePermissions[$adminId]['new_transfer'] = Mage::helper('inventoryplus')->__('Cannot transfer Warehouse');
            }
            $assignment->setData('can_send_request_stock', 0);
        }
    }

    public function column_permission_grid($observer) {
        $columns = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $columns->addColumn('can_send_request_stock', array(
            'header' => Mage::helper('inventorywarehouse')->__('Send/Request Stock'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'transfer[]',
            'values' => $this->_getSelectedCanTransferAdmins()
        ));
    }

    protected function _getSelectedCanTransferAdmins() {
        $warehouse = $this->getWarehouse();

        if ($warehouse->getId())
            $canSendRequestAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->getAllCanSendRequestAdmins();
        else {
            $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
            $canSendRequestAdmins = array($adminId);
        }

        return $canSendRequestAdmins;
    }

    public function getWarehouse() {
        return Mage::getModel('inventoryplus/warehouse')
                        ->load(Mage::app()->getRequest()->getParam('id'));
    }

    /**
     * set template for adjust stock add new
     *
     */
    public function adjust_stock_html($observer) {
        $block = $observer->getEvent()->getBlock();
        $block->setTemplate('inventorywarehouse/adjuststock/new.phtml');
    }

    /**
     * Select warehouse to subtract stock when customers create order
     *
     */
    public function salesOrderPlaceAfter($observer) {

        if (Mage::registry('INVENTORY_CORE_ORDER_PLACE'))
            return;
        Mage::register('INVENTORY_CORE_ORDER_PLACE', true);
        $order = $observer->getOrder();
        $items = $order->getAllItems();
        $warehouseIds = null;
        $selectWarehouse = Mage::getStoreConfig('inventoryplus/general/select_warehouse');
        $ShippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        foreach ($items as $item) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
            $manageStock = $stockItem->getManageStock();
            if ($stockItem->getUseConfigManageStock()) {
                $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
            }
            if (!$manageStock) {
                continue;
            }

            $warehouseId = Mage::helper('inventorywarehouse/warehouse')->getQtyProductWarehouse($item->getProductId(), $selectWarehouse, $ShippingAddress, $billingAddress);

            if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped')))
                continue;


            if (!$warehouseId) {
                Mage::log($observer->getOrder() . ' ------- ' . $item->getProductId(), null, 'inventory_management.log');
                continue;
            }
            $qtyOrdered = 0;
            if (!$item->getQtyOrdered() || $item->getQtyOrdered() == 0) {
                if ($item->getParentItemId()) {
                    $qtyOrdered = Mage::getModel('sales/order_item')->load($item->getParentItemId())->getQtyOrdered();
                }
            } else {
                $qtyOrdered = $item->getQtyOrdered();
            }

            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouseId)
                    ->addFieldToFilter('product_id', $item->getProductId())
                    ->getFirstItem();
            $currentQty = $warehouseProduct->getAvailableQty() - $qtyOrdered;
            try {
                $warehouseProduct->setAvailableQty($currentQty)
                        ->save();
                Mage::getModel('inventoryplus/warehouse_order')->setOrderId($order->getId())
                        ->setWarehouseId($warehouseId)
                        ->setProductId($item->getProductId())
                        ->setItemId($item->getId())
                        ->setQty($qtyOrdered)
                        ->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'inventory_management.log');
            }
        }
    }

    
    public function salesOrderShipmentSaveBefore($observer){
        $data = Mage::app()->getRequest()->getParams();           
        if($data['invoice']['do_shipment'] == 1){
            $items = $data['invoice']['items'];
            $check = Mage::helper('inventorywarehouse')->checkShipment($items);            
            if($check == 0) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryplus')->__('Can\'t create shipment , there are no warehouse enough stock'));
                throw new Exception("No warehouse enough stock");
            } 
        }        
    }

    /*
     * catch event sales order shipment save after
     */   


    public function salesOrderShipmentSaveAfter($observer) {
        try {
            if (Mage::registry('INVENTORY_WAREHOUSE_ORDER_SHIPMENT'))
                return;
            Mage::register('INVENTORY_WAREHOUSE_ORDER_SHIPMENT', true);
            $inventoryShipmentData = array();
            $data = Mage::app()->getRequest()->getParams();
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $orderId = $data['order_id'];
            $shipmentId = $shipment->getId();
            $customerId = $order->getCustomerId();
            $customerName = Mage::getModel('customer/customer')->load($customerId)->getName();
            $createdAt = date('Y-m-d', strtotime(now()));
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();    
            $reason = Mage::helper('inventoryplus')->__("Shipment for order #%s", $order->getIncrementId());
            
        //IF CREATE SHIPMENT FROM INVOICE    
            if(isset($data['invoice']['do_shipment']) &&  $data['invoice']['do_shipment'] == 1){
                $items = $data['invoice']['items'];
                $productData = array();
                //substract total qty warehouse
                foreach($items as $item_id => $qty){
                    $item = Mage::getModel('sales/order_item')->load($item_id);
                    $basePrice = $item->getBasePrice();
                    $product_id = $item->getProductId();                    
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                    $manageStock = $stockItem->getManageStock();
                    if ($stockItem->getUseConfigManageStock()) {
                        $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
                    }
                    if (!$manageStock) {
                        continue;
                    }
                    if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped', 'virtual', 'downloadable')))
                        continue;
                    
                    $warehouse_id = Mage::helper('inventorywarehouse')->selectWarehouseToShip($product_id,$qty);
                    $warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                            ->getCollection()
                            ->addFieldToFilter('warehouse_id', $warehouse_id)
                            ->addFieldToFilter('product_id',$product_id)
                            ->getFirstItem();
                    $changed_qty = $warehouse_product->getTotalQty() - $qty;     
                    $warehouse_product->setTotalQty($changed_qty);
                    $warehouse_product->save();  
                    
                    $productData[$warehouse_id][$product_id] = $qty;                    
                    $warehouseName = Mage::getModel('inventoryplus/warehouse')
                            ->load($warehouse_id)
                            ->getWarehouseName();
                    
                    $inventoryOrderModel = Mage::getModel('inventoryplus/warehouse_order')
                            ->getCollection()
                            ->addFieldToFilter('order_id',$orderId)
                            ->addFieldToFilter('product_id',$product_id)
                            ->getFirstItem();
                            
                    if($inventoryOrderModel->getId()){
                        $changedOnHoldQty = $inventoryOrderModel->getQty() - $qty;
                        $inventoryOrderModel->setData('qty',$changedOnHoldQty);
                        $inventoryOrderModel->save();
                        if($inventoryOrderModel->getWarehouseId() != $warehouse_id){
                            $subtracted_avail = $warehouse_product->getAvailableQty() - $qty;
                            $warehouse_product->setAvailableQty($subtracted_avail)
                                    ->save();
                            $ordered_warehouse_product = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id',$inventoryOrderModel->getWarehouseId())
                                    ->addFieldToFilter('product_id',$product_id)
                                    ->getFirstItem();
                            $return_avail = $ordered_warehouse_product->getAvailableQty() + $qty;
                            $ordered_warehouse_product->setAvailableQty($return_avail)
                                            ->save();                            
                        }
                    }
                            
                    $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
                    $inventoryShipmentModel->setItemId($item->getItemId())
                            ->setProductId($product_id)
                            ->setOrderId($orderId)
                            ->setWarehouseId($warehouse_id)
                            ->setWarehouseName($warehouseName)
                            ->setShipmentId($shipmentId)
                            ->setQtyShipped($qty)
                            ->setSubtotalShipped($basePrice * $qty)
                            ->save();                            
                                                  
                }
                //SAVE TRANSACTION
                
                foreach($productData as $warehouse_id => $items){
                    $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
                    $transactionSendData = array();
                    $totalQty = 0;
                    $transactionSendData['type'] = '5';
                    $transactionSendData['warehouse_id_from'] = $warehouse_id;
                    $transactionSendData['warehouse_name_from'] = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($warehouse_id);
                    $transactionSendData['warehouse_id_to'] = $customerId;
                    $transactionSendData['warehouse_name_to'] = $customerName;
                    $transactionSendData['created_at'] = $createdAt;
                    $transactionSendData['created_by'] = $admin;
                    $transactionSendData['reason'] = $reason;
                    $transactionSendModel->addData($transactionSendData);
                    $transactionSendModel->save();
                   
                    foreach ($items as $productId => $qty) {
                        $product = Mage::getModel('catalog/product')->load($productId);
                        Mage::getModel('inventorywarehouse/transaction_product')
                                ->setWarehouseTransactionId($transactionSendModel->getId())
                                ->setProductId($productId)
                                ->setProductSku($product->getSku())
                                ->setProductName($product->getName())
                                ->setQty(-$qty)
                                ->save();
                        $totalQty += $qty;
                    }
                    $transactionSendModel->setTotalProducts(-$totalQty)->save();
                }
        // NORMAL SHIPMENT
            } else {
                if (isset($data['echeck_dropship']) && $data['echeck_dropship'] == 1)
                return;
              
                $shippingMethod = $order->getShippingMethod();
                
                $total_qty_order = $order->getTotalQtyOrdered();
                $total_qty_shipped = array();
                $total_shipped = array();

                foreach ($order->getAllItems() as $item) {
                    $basePrice = $item->getBasePrice();
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());

                    $manageStock = $stockItem->getManageStock();
                    if ($stockItem->getUseConfigManageStock()) {
                        $manageStock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', Mage::app()->getStore()->getStoreId());
                    }

                    if (!$manageStock) {
                        continue;
                    }

                    if (in_array($item->getProductType(), array('configurable', 'bundle', 'grouped', 'virtual', 'downloadable')))
                        continue;


                    //row_total_incl_tax       
                    $inventoryShipmentData[$item->getItemId()]['qty'] = '';
                    $inventoryShipmentData[$item->getItemId()]['warehouse'] = '';
                    if ($item->getParentItemId()) {

                        if (isset($data['shipment']['items'][$item->getParentItemId()])) {
                            $item_parrent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                            $options = $item->getProductOptions();

                            if (isset($options['bundle_selection_attributes'])) {
                                $option = unserialize($options['bundle_selection_attributes']);

                                $parentQty = $data['shipment']['items'][$item->getParentItemId()];

                                $itemQty = (int) $option['qty'] * (int) $parentQty;

                                $inventoryShipmentData[$item->getItemId()]['qty'] = $itemQty;
                                $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;

                                if (isset($data['warehouse-shipment']['items'][$item->getParentItemId()]) && $data['warehouse-shipment']['items'][$item->getParentItemId()] != '') {
                                    $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getParentItemId()];
                                } else {
                                    if (!isset($data['warehouse-shipment']['items'][$item->getItemId()]) || (isset($data['warehouse-shipment']['items'][$item->getItemId()]) && $data['warehouse-shipment']['items'][$item->getItemId()] == '')) {
                                        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                                ->getCollection()
                                                ->addFieldToFilter('product_id', $item->getProductId())
                                                ->addFieldToFilter('total_qty', array('gteq' => $inventoryShipmentData[$item->getItemId()]['qty']))
                                                ->getFirstItem();
                                        $inventoryShipmentData[$item->getItemId()]['warehouse'] = $warehouseProduct->getWarehouseId();
                                    } else {
                                        $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                                    }
                                }
                                $inventoryShipmentData[$item->getItemId()]['product_id'] = $item->getProductId();
                                $inventoryShipmentData[$item->getItemId()]['price_incl_tax'] = $item->getPriceInclTax();
                            } else {

                                $inventoryShipmentData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getParentItemId()];
                                $inventoryShipmentData[$item->getItemId()]['price'] = $item_parrent->getBasePrice();
                                if (isset($data['warehouse-shipment']['items'][$item->getParentItemId()]) && $data['warehouse-shipment']['items'][$item->getParentItemId()] != '') {
                                    $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getParentItemId()];
                                } else {
                                    if (!isset($data['warehouse-shipment']['items'][$item->getItemId()]) || (isset($data['warehouse-shipment']['items'][$item->getItemId()]) && $data['warehouse-shipment']['items'][$item->getItemId()] == '')) {
                                        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                                ->getCollection()
                                                ->addFieldToFilter('product_id', $item->getProductId())
                                                ->addFieldToFilter('total_qty', array('gteq' => $inventoryShipmentData[$item->getItemId()]['qty']))
                                                ->getFirstItem();
                                        $inventoryShipmentData[$item->getItemId()]['warehouse'] = $warehouseProduct->getWarehouseId();
                                    } else {
                                        $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                                    }
                                }
                                $inventoryShipmentData[$item->getItemId()]['product_id'] = $item->getProductId();
                                $inventoryShipmentData[$item->getItemId()]['price_incl_tax'] = $item->getPriceInclTax();
                            }
                        } else {
                            if (!isset($data['warehouse-shipment']['items'][$item->getItemId()]) || (isset($data['warehouse-shipment']['items'][$item->getItemId()]) && $data['warehouse-shipment']['items'][$item->getItemId()] == '')) {
                                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->addFieldToFilter('total_qty', array('gteq' => $inventoryShipmentData[$item->getItemId()]['qty']))
                                        ->getFirstItem();
                                $inventoryShipmentData[$item->getItemId()]['warehouse'] = $warehouseProduct->getWarehouseId();
                            } else {
                                $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                            }
                            $inventoryShipmentData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getItemId()];
                            $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;
                            $inventoryShipmentData[$item->getItemId()]['product_id'] = $item->getProductId();
                            $inventoryShipmentData[$item->getItemId()]['price_incl_tax'] = $item->getPriceInclTax();
                        }
                    } else {

                        if (!$item->getHasChildren()) {
                            if (isset($data['shipment']['items'][$item->getItemId()])) {
                                $inventoryShipmentData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getItemId()];
                                $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;
                            } elseif (isset($data['shipment']['items'][$item->getParentItemId()])) {
                                $inventoryShipmentData[$item->getItemId()]['qty'] = $data['shipment']['items'][$item->getParentItemId()];
                                $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;
                            }
                            if (isset($data['warehouse-shipment']['items'][$item->getItemId()])) {
                                $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getItemId()];
                            } elseif (isset($data['warehouse-shipment']['items'][$item->getParentItemId()])) {
                                $inventoryShipmentData[$item->getItemId()]['warehouse'] = $data['warehouse-shipment']['items'][$item->getParentItemId()];
                            } else {
                                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->addFieldToFilter('total_qty', array('gteq' => $inventoryShipmentData[$item->getItemId()]['qty']))
                                        ->getFirstItem();
                                $inventoryShipmentData[$item->getItemId()]['warehouse'] = $warehouseProduct->getWarehouseId();
                            }
                            $inventoryShipmentData[$item->getItemId()]['product_id'] = $item->getProductId();
                            $inventoryShipmentData[$item->getItemId()]['price_incl_tax'] = $item->getPriceInclTax();
                        } else {
                            if (!isset($data['warehouse-shipment']['items'][$item->getItemId()]) || (isset($data['warehouse-shipment']['items'][$item->getItemId()]) && $data['warehouse-shipment']['items'][$item->getItemId()] == '')) {
                                $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->addFieldToFilter('total_qty', array('gteq' => $inventoryShipmentData[$item->getItemId()]['qty']))
                                        ->getFirstItem();
                                $data['warehouse-shipment']['items'][$item->getItemId()] = $warehouseProduct->getWarehouseId();
                            }
                            $warehouseName = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($data['warehouse-shipment']['items'][$item->getItemId()]);
                            $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
                            $inventoryShipmentModel->setItemId($item->getItemId())
                                    ->setProductId($item->getProductId())
                                    ->setOrderId($orderId)
                                    ->setWarehouseId($data['warehouse-shipment']['items'][$item->getItemId()])
                                    ->setWarehouseName($warehouseName)
                                    ->setShipmentId($shipmentId)
                                    ->setQtyShipped(0)
                                    ->save();
                        }
                    }
                    if ($inventoryShipmentData[$item->getItemId()]['qty'] > ($item->getQtyOrdered() - $item->getQtyRefunded())) {
                        $inventoryShipmentData[$item->getItemId()]['qty'] = ($item->getQtyOrdered() - $item->getQtyRefunded());
                        $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;
                    }
                }

                //get total qty shipped
                $order_item_collection = Mage::getModel('sales/order_item')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $order->getEntityId());
                $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                        ->addFieldToFilter('order_id', $order->getId());
                foreach ($order_item_collection as $order_item) {

                    if (!$order_item->getParentItemId()) {
                        if ($order_item->getProductType() == 'virtual' || $order_item->getProductType() == 'downloadable') {
                            $total_qty_order += -(int) $order_item->getQtyOrdered();
                        }
                        $shipment_item = Mage::getModel('sales/order_shipment_item')
                                ->getCollection()
                                ->addFieldToFilter('order_item_id', $order_item->getItemId());
                        foreach ($shipment_item as $i) {
                            $qty_shipped = $i->getQty();
                            $total_shipped[] = (int) $qty_shipped;
                        }
                    }
                    if ($order_item->getProductType() == 'virtual' || $order_item->getProductType() == 'downloadable') {
                        $datavisual = $warehouseOrder->addFieldToFilter('product_id', $item->getProductId())->getFirstItem();

                        $inventoryShipmentData[$item->getItemId()]['qty'] = $item->getQtyOrdered();
                        $inventoryShipmentData[$item->getItemId()]['price'] = $basePrice;
                        $inventoryShipmentData[$item->getItemId()]['warehouse'] = $datavisual->getWarehouseId();
                        $inventoryShipmentData[$item->getItemId()]['product_id'] = $item->getProductId();
                        $inventoryShipmentData[$item->getItemId()]['price_incl_tax'] = $item->getPriceInclTax();
                    }
                }
                $total_products_shipped = array_sum($total_shipped);
                //end get total qty shipped
                //set status for shipment
                if ($total_qty_order == 0) {
                    $shipping_progress = 2;
                } else {
                    if ((int) $total_products_shipped == 0) {
                        $shipping_progress = 0;
                    } elseif ((int) $total_products_shipped < (int) $total_qty_order) {
                        $shipping_progress = 1;
                    } elseif ((int) $total_products_shipped == (int) $total_qty_order) {
                        $shipping_progress = 2;
                    }
                }
                $order->setShippingProgress($shipping_progress);
                //end set status

                try {
                    $mailData = array();
                    $transactionData = array();
                    $qty_notice = Mage::getStoreConfig('inventoryplus/notice/qty_notice');
                    $i = 0;

                    $warehouseorders = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                            ->addFieldToFilter('order_id', $order->getId());
                    $warehouseorderId = array();

                    foreach ($inventoryShipmentData as $key => $dataArray) {
                        //add data to create transaction

                        $transactionData[$dataArray['warehouse']][$dataArray['product_id']] = $dataArray['qty'];
                        $warehouseName = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($dataArray['warehouse']);
                        $inventoryShipmentModel = Mage::getModel('inventoryplus/warehouse_shipment');
                        $inventoryShipmentModel->setItemId($key)
                                ->setProductId($dataArray['product_id'])
                                ->setOrderId($orderId)
                                ->setWarehouseId($dataArray['warehouse'])
                                ->setWarehouseName($warehouseName)
                                ->setShipmentId($shipmentId)
                                ->setQtyShipped($dataArray['qty'])
                                ->setSubtotalShipped($dataArray['price'] * $dataArray['qty'])
                                ->save();

                        $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')
                                ->getCollection()
                                ->addFieldToFilter('warehouse_id', $dataArray['warehouse'])
                                ->addFieldToFilter('product_id', $dataArray['product_id'])
                                ->getFirstItem();
                        $oldQty = $warehouseProduct->getTotalQty();
                        $newQty = $oldQty - $dataArray['qty'];


                        if ($dataArray['qty'] != 0 && ($newQty < $oldQty)) {
                            $warehouseOr = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                                    ->addFieldToFilter('order_id', $order->getId())
                                    ->addFieldToFilter('warehouse_id', $dataArray['warehouse'])
                                    ->addFieldToFilter('product_id', $dataArray['product_id'])
                                    ->getFirstItem();
                            if (!$warehouseOr->getId()) {
                                $newQtyAvailable = $warehouseProduct->getAvailableQty() - $dataArray['qty'];
                                $warehouseProduct->setAvailableQty($newQtyAvailable);
                            }
                            $warehouseProduct->setTotalQty($newQty);
    //                        $warehouseProduct->setUpdatedAt(now());
                            $warehouseProduct->save();

                            $warehouseorderId[$dataArray['product_id']] = array('qty' => $dataArray['qty'], 'warehouse_id' => $dataArray['warehouse']);
                        }
    //                    else {
    //                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventorywarehouse')->__('QTY of product in warehouse %s is not avaiable', $warehouseName));
    //                    }

                        if ($newQty < $qty_notice && $oldQty >= $qty_notice) {
                            if ($mailData[$dataArray['warehouse']]) {
                                $mailData[$dataArray['warehouse']] = $mailData[$dataArray['warehouse']] . ',' . $dataArray['product_id'];
                            } else {
                                $mailData[$dataArray['warehouse']] = $dataArray['product_id'];
                            }
                        }
                    }

                    foreach ($warehouseorders as $warehouseorder) {
                        if (isset($warehouseorderId[$warehouseorder->getProductId()])) {
                            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                                    ->addFieldToFilter('warehouse_id', $warehouseorder->getWarehouseId())
                                    ->addFieldToFilter('product_id', $warehouseorder->getProductId())
                                    ->getFirstItem();

                            $currentQtyOrder = $warehouseorder->getQty() - $warehouseorderId[$warehouseorder->getProductId()]['qty'];
                            if ($warehouseorder->getWarehouseId() != $warehouseorderId[$warehouseorder->getProductId()]['warehouse_id']) {
                                $currentQty = $warehouseProduct->getAvailableQty() + $warehouseorderId[$warehouseorder->getProductId()]['qty'];
                                $warehouseProduct->setAvailableQty($currentQty)
                                        ->save();
                            }
                            $warehouseorder->setQty($currentQtyOrder)
                                    ->save();
                        }
                    }
                    //create send transaction
                    
                    
                    foreach ($transactionData as $warehouseId => $productData) {
                        $transactionSendModel = Mage::getModel('inventorywarehouse/transaction');
                        $transactionSendData = array();
                        $totalQty = 0;
                        $transactionSendData['type'] = '5';
                        $transactionSendData['warehouse_id_from'] = $warehouseId;
                        $transactionSendData['warehouse_name_from'] = Mage::helper('inventorywarehouse/warehouse')->getWarehouseNameByWarehouseId($warehouseId);
                        $transactionSendData['warehouse_id_to'] = $customerId;
                        $transactionSendData['warehouse_name_to'] = $customerName;
                        $transactionSendData['created_at'] = $createdAt;
                        $transactionSendData['created_by'] = $admin;
                        $transactionSendData['reason'] = $reason;
                        $transactionSendModel->addData($transactionSendData);
                        try {
                            $transactionSendModel->save();
                            //save product for transaction
                            foreach ($productData as $productId => $qty) {
                                $product = Mage::getModel('catalog/product')->load($productId);
                                Mage::getModel('inventorywarehouse/transaction_product')
                                        ->setWarehouseTransactionId($transactionSendModel->getId())
                                        ->setProductId($productId)
                                        ->setProductSku($product->getSku())
                                        ->setProductName($product->getName())
                                        ->setQty(-$qty)
                                        ->save()
                                ;
                                $totalQty += $qty;
                            }
                            $transactionSendModel->setTotalProducts(-$totalQty)->save();
                        } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        }
                    }
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }                        
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    //add more transaction warehouse tab
    public function addWarehouseTab($observer) {
        $warehouseId = $observer->getWarehouseId();
        if (!$warehouseId)
            return;
        $tab = $observer->getTab();
        $tab->addTab('transaction_section', array(
            'label' => Mage::helper('inventoryplus')->__('Stock Movements'),
            'title' => Mage::helper('inventoryplus')->__('Stock Movements'),
            'url' => $tab->getUrl('inventorywarehouseadmin/adminhtml_warehouse/transaction', array(
                '_current' => true,
                'id' => $warehouseId,
            )),
            'class' => 'ajax'
        ));
    }

    public function warehouse_controller_index($observer) {

        $controller = $observer->getEvent()->getWarehouseControler();

        $controller->getLayout()->getBlock('head')->addJs('magestore/inventorywarehouse/warehouse.js');
    }

    public function warehouse_add_new_product($observer) {

        $data = $observer->getEvent()->getData();
        $warehouse = $observer->getEvent()->getWarehouse();

        if (isset($data['data']['warehouse_add_products'])) {
            $warehousenewProducts = array();
            $warehousenewProductsExplodes = explode('&', urldecode($data['data']['warehouse_add_products']));
            if (count($warehousenewProductsExplodes) <= 900) {
                parse_str(urldecode($data['data']['warehouse_add_products']), $warehousenewProducts);
            } else {
                foreach ($warehousenewProductsExplodes as $warehouseProductsExplode) {
                    $warehouseProduct = '';
                    parse_str($warehousenewProductsExplodes, $warehouseProduct);
                    $warehousenewProducts = $warehousenewProducts + $warehouseProduct;
                }
            }

            if (count($warehousenewProducts)) {

                $productIds = '';
                foreach ($warehousenewProducts as $pId => $enCoded) {
                    $codeArr = array();
                    parse_str(base64_decode($enCoded), $codeArr);
                    try {
                        $warehouseProductsItem = Mage::getModel('inventoryplus/warehouse_product');
                        $warehouseProductsItem->setData('warehouse_id', $warehouse->getId())
                                ->setData('product_id', $pId)
                                ->setData('total_qty', 0)
                                ->setData('available_qty', 0)
                                ->setData('created_at', now())
//                                ->setData('updated_at', now())
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
    }

    /* add warehouse and supplier when create product */

    public function catalogProductSaveAfterEvent($observer) {
        if (Mage::registry('INVENTORY_WAREHOUSE_CREATE_PRODUCT'))
            return;
        Mage::register('INVENTORY_WAREHOUSE_CREATE_PRODUCT', true);
		
		//if import dataflow
		if(Mage::app()->getRequest()->getActionName() == 'batchRun'){
			return;
		}
		
        $product = $observer->getProduct();
        $productId = $product->getId();
        if (in_array($product->getTypeId(), array('configurable', 'bundle', 'grouped')))
            return;
        if (Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate')) {
            $currentProductId = Mage::getModel('admin/session')->getData('inventory_catalog_product_duplicate');
            $warehouseProducts = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $currentProductId);
            foreach ($warehouseProducts as $warehouseProduct) {
                $newWarehouseProduct = Mage::getModel('inventoryplus/warehouse_product');
                $newWarehouseProduct->setData('product_id', $productId)
                        ->setData('warehouse_id', $warehouseProduct->getWarehouseId())
                        ->save();
            }
            Mage::getModel('admin/session')->setData('inventory_catalog_product_duplicate', false);
        }

        $post = Mage::app()->getRequest()->getPost();
        $isInStock = 0;
        if (isset($post['product']['stock_data']['is_in_stock']))
            $isInStock = $post['product']['stock_data']['is_in_stock'];
        if (isset($post['simple_product']))
            if (isset($post['simple_product']['stock_data']['is_in_stock']))
                $isInStock = $post['simple_product']['stock_data']['is_in_stock'];
        if (isset($post['inventory_select_warehouse'])) {
            $warehouses = $post['inventory_select_warehouse'];
        } else {
            $warehouseProduct = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->getFirstItem();
            if ($warehouseProduct->getId())
                return;
            $firstWarehouse = Mage::getModel('inventoryplus/warehouse')->getCollection()->getFirstItem();
            if ($firstWarehouse->getId()) {
                $warehouses[] = array('warehouse_id' => $firstWarehouse->getId(), 'qty' => 0);
            }
        }
        try {
            $totalQty = 0;
            foreach ($warehouses as $warehouse) {
                $totalQty += $warehouse['qty'];
                $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product');
                $warehouseProductModel->setWarehouseId($warehouse['warehouse_id'])
                        ->setProductId($productId)
                        ->setTotalQty($warehouse['qty'])
                        ->setAvailableQty($warehouse['qty'])
//                        ->setUpdatedAt(now())
                        ->save();
            }
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $stockItem = $product->getStockItem();
            $stockItem->setData('qty', $totalQty);
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->save();
            $product->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'inventory_management.log');
        }
    }

    public function catalogModelProductDuplicate($observer) {
        $currentProduct = $observer->getCurrentProduct();
        $currentProductId = $currentProduct->getId();
        Mage::getModel('admin/session')->setData('inventory_catalog_product_duplicate', $currentProductId);
    }
    
    
}
