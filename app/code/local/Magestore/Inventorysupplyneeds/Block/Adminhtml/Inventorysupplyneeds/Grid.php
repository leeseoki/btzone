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
 * Inventorysupplyneeds Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('inventorysupplyneedsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');        
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        if (!$this->getFilterData())
            $this->setFilterData(new Varien_Object());
    }

     public static function cmpAscOutstockDate($a, $b) {
        return strtotime($a->getOutstockDate()) > strtotime($b->getOutstockDate());
    }

    public static function cmpDescOutstockDate($a, $b) {
        return strtotime($a->getOutstockDate()) < strtotime($b->getOutstockDate());
    }

    protected function _prepareLayout() {
        $this->setChild('export_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Export'),
                            'onclick' => 'exportCsv()',
                            'class' => 'task'
                        ))
        );

        $this->setChild('reset_filter_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Reset Filter'),
                            'onclick' => $this->getJsObjectName() . '.resetFilter()',
                        ))
        );
        $this->setChild('search_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('adminhtml')->__('Search'),
                            'onclick' => $this->getJsObjectName() . '.doFilter()',
                            'class' => 'task'
                        ))
        );
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid
     */
    protected function _prepareCollection() {
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $warehouse = $datefrom = $supplier = $dateto = '';
        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if ($requestData && isset($requestData['supplier_select']))
            $supplier = $requestData['supplier_select'];
        $supplierModel = Mage::getModel('inventorypurchasing/supplier');
        $warehouseModel = Mage::getModel('inventoryplus/warehouse');
        if ($requestData && isset($requestData['date_from']))
            $datefrom = $requestData['date_from'];
        if ($requestData && isset($requestData['date_to']))
            $dateto = $requestData['date_to'];
        if (!$datefrom) {
            $now = now();            
            $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        if (!$dateto) {
            $now = now();            
            $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
        }
        $datefrom = $datefrom . ' 00:00:00';
        $dateto = $dateto . ' 23:59:59';
        $collection = Mage::getResourceModel('inventorysupplyneeds/order_item_collection')
                ->addFieldToFilter('product_type', array('nin' => array('configurable', 'bundle', 'grouped')));
        ;
        $collection->getSelect()->group('main_table.product_id');        
        if ($warehouse && !$supplier) {
            $collection->getSelect()
                    ->join(
                            array('warehouse_product' => $warehouseModel->getCollection()->getTable('inventoryplus/warehouse_product')), "main_table.product_id = warehouse_product.product_id and warehouse_product.warehouse_id = '$warehouse'", array('qty_warehouse' => 'warehouse_product.available_qty'));
            if ($datefrom && $dateto) {
                if (strtotime($datefrom) <= strtotime($dateto)) {
                    $range = (strtotime($dateto) - strtotime($datefrom)) / (3600 * 24);
                    $x = 0;
                    $y = round($range * 10);
                    $today = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$x day"));
                    $lastperiod = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$y days"));
                    $resource = Mage::getSingleton('core/resource');        
                    $readConnection = $resource->getConnection('core_read');
                    $results = '';
                    $purchaseOrderIds = array();
                    $sql = 'SELECT distinct(`entity_id`) FROM '.$resource->getTableName('sales/order').' where (`created_at` >= \''. $lastperiod .'\') and (`created_at` <= \''. $today .'\')';             
                    $results = $readConnection->fetchAll($sql);                    
                    $collection->addFieldToFilter('order_id', array('in' => $results));  
                }
            }
        } elseif ($supplier && !$warehouse) {

            $collection->getSelect()
                    ->join(
                            array('supplier_product' => $supplierModel->getCollection()->getTable('inventorypurchasing/supplier_product')), "main_table.product_id = supplier_product.product_id and supplier_product.supplier_id = $supplier", array()
                    )
                    ->join(
                            array('catalog_inventory' => Mage::getModel('cataloginventory/stock_item')->getCollection()->getTable('cataloginventory/stock_item')), "main_table.product_id = catalog_inventory.product_id", array('qty_warehouse' => 'catalog_inventory.qty')
                    )
            ;
            if ($datefrom && $dateto) {
                if (strtotime($datefrom) < strtotime($dateto)) {
                    $range = (strtotime($dateto) - strtotime($datefrom)) / (3600 * 24);
                    $x = 0;
                    $y = round($range * 10);
                    $today = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$x day"));
                    $lastperiod = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$y days"));
                    $resource = Mage::getSingleton('core/resource');        
                    $readConnection = $resource->getConnection('core_read');
                    $results = '';
                    $purchaseOrderIds = array();
                    $sql = 'SELECT distinct(`entity_id`) FROM '.$resource->getTableName('sales/order').' where (`created_at` >= \''. $lastperiod .'\') and (`created_at` <= \''. $today .'\')';             
                    $results = $readConnection->fetchAll($sql);                    
                    $collection->addFieldToFilter('order_id', array('in' => $results));  
                }
            }
        } elseif ($supplier && $warehouse) {
            $collection->getSelect()
                    ->join(
                            array('warehouse_product' => $warehouseModel->getCollection()->getTable('inventoryplus/warehouse_product')), "main_table.product_id = warehouse_product.product_id and warehouse_product.warehouse_id = '$warehouse'", array('qty_warehouse' => 'warehouse_product.available_qty'));
            $collection->getSelect()->join(
                    array('supplier_product' => $supplierModel->getCollection()->getTable('inventorypurchasing/supplier_product')), "main_table.product_id = supplier_product.product_id and supplier_product.supplier_id = $supplier", array()
            );

            if ($datefrom && $dateto) {
                if (strtotime($datefrom) < strtotime($dateto)) {
                    $range = (strtotime($dateto) - strtotime($datefrom)) / (3600 * 24);
                    $x = 0;
                    $y = round($range * 10);
                    $today = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$x day"));
                    $lastperiod = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$y days"));
                    $resource = Mage::getSingleton('core/resource');        
                    $readConnection = $resource->getConnection('core_read');
                    $results = '';
                    $purchaseOrderIds = array();
                    $sql = 'SELECT distinct(`entity_id`) FROM '.$resource->getTableName('sales/order').' where (`created_at` >= \''. $lastperiod .'\') and (`created_at` <= \''. $today .'\')';             
                    $results = $readConnection->fetchAll($sql);                    
                    $collection->addFieldToFilter('order_id', array('in' => $results));  
                }
            }
        } elseif (!$supplier && !$warehouse) {
            $collection->getSelect()->join(
                    array('catalog_inventory' => Mage::getModel('cataloginventory/stock_item')->getCollection()->getTable('cataloginventory/stock_item')), "main_table.product_id = catalog_inventory.product_id", array('qty_warehouse' => 'catalog_inventory.qty')
            );

            if ($datefrom && $dateto) {
                if (strtotime($datefrom) < strtotime($dateto)) {
                    $range = (strtotime($dateto) - strtotime($datefrom)) / (3600 * 24);
                    $x = 0;
                    $y = round($range * 10);
                    $today = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$x day"));
                    $lastperiod = strftime('%Y-%m-%d', strtotime(date("Y-m-d", strtotime($datefrom)) . " -$y days"));
                    $orderIds = array();
                    $resource = Mage::getSingleton('core/resource');        
                    $readConnection = $resource->getConnection('core_read');
                    $results = '';
                    $purchaseOrderIds = array();
                    $sql = 'SELECT distinct(`entity_id`) FROM '.$resource->getTableName('sales/order').' where (`created_at` >= \''. $lastperiod .'\') and (`created_at` <= \''. $today .'\')';             
                    $results = $readConnection->fetchAll($sql);                    
                    $collection->addFieldToFilter('order_id', array('in' => $results));                    
                }
            }
        }
        $collection->getSelect()->columns(array('total_order' => 'SUM(`main_table`.`qty_ordered`)'));
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $condorder = '';
        if ($filter) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'product_id') {
                    $condorder = $key;
                }
            }
        }
        if ($condorder) {
            $from = $condorder['from'];
            $to = $condorder['to'];
            if ($from) {
                $collection->getSelect()
                        ->where('main_table.product_id >= \'' . $from . '\'')
                ;
            }
            if ($to) {
                $collection->getSelect()
                        ->where('main_table.product_id <= \'' . $to . '\'')
                ;
            }
        }

        $sort = $this->getRequest()->getParam('sort');
        $collection->setIsGroupCountSql(true);
        if (!Mage::registry('supplyneeds_collection'))
            Mage::register('supplyneeds_collection', $collection);
        $page = $this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1;
        $limit = $this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 20;
        $collection
            ->setPageSize($limit)
            ->setCurPage($page);
        $this->setCollection($collection);
        //return parent::_prepareCollection();
    }

    public function addExportType($url, $label) {
        $this->_exportTypes[] = new Varien_Object(
                        array(
                            'url' => $this->getUrl($url, array(
                                '_current' => true,
                                'filter' => $this->getParam($this->getVarNameFilter(), null)
                                    )
                            ),
                            'label' => $label
                        )
        );
        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
//            'sortable' => true,
            'width' => '30px',
            'index' => 'product_id',
            'type' => 'number',
            'filter_condition_callback' => array($this, 'filter_custom_column_callback'),
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Product Name'),
            'align' => 'left',
            'index' => 'name',
            'width' => '280px',
            'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_productname',
//            'filter_condition_callback' => array($this, 'filter_product_name_callback'),
        ));

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage',
            'index' => 'product_image',
            'filter' => false
        ));
        
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $warehouse = '';
        if ($requestData && isset($requestData['warehouse_select']))
            $warehouse = $requestData['warehouse_select'];
        if($warehouse){
            $this->addColumn('qty_warehouse', array(
                'header' => Mage::helper('catalog')->__('Total Avail. Qty in warehouse'),
                'width' => '80px',
                'index' => 'qty_warehouse',
                'type' => 'number',
                'filter_index' => 'warehouse_product.available_qty',
                'filter_condition_callback' => array($this, 'filter_custom_qty_callback'),
            ));
        }else{
            $this->addColumn('qty_warehouse', array(
                'header' => Mage::helper('catalog')->__('Total Avail. Qty in warehouse'),
                'width' => '80px',
                'index' => 'qty_warehouse',
                'type' => 'number',
                'filter_index' => 'SUM(warehouse_product.available_qty)',
                'filter_condition_callback' => array($this, 'filter_custom_qty_callback'),
            ));
        }

        $this->addColumn('supplier', array(
            'header' => Mage::helper('catalog')->__('Supplier'),
            'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_supplier',
            'width' => '200px',
            'sortable' => false,
            'filter' => false,
        ));

        $this->addColumn('outstock_date', array(
            'header' => Mage::helper('catalog')->__('Stock-out Date'),
            'width' => '80px',
            'type' => 'date',
            'align' => 'right',
            'index' => 'qty_warehouse',
            'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_outstockdate',
            'filter_condition_callback' => array($this, 'filter_outstock_date'),
        ));

        $this->addColumn('qty_for_supply', array(
            'header' => Mage::helper('catalog')->__('Total Qty. Needed Purchasing'),
            'width' => '80px',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_qtyforsupply'
        ));

        $this->addColumn('supply_needs', array(
            'header' => Mage::helper('catalog')->__('Qty to Purchase More'),
            'width' => '80px',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'inventorysupplyneeds/adminhtml_inventorysupplyneeds_renderer_supplyneeds',
        ));
        $this->addExportType('/*/*/', Mage::helper('inventorysupplyneeds')->__('CSV'));
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        
    }

    public function filter_outstock_date($collection, $column){
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();

        //process require information to calculate outstock date
        $data = Mage::helper('inventorysupplyneeds')->processFilterData();
        $dateto = $data['date_to'];
        $datefrom = $data['date_from'];
        $warehouse = $data['warehouse'];

        foreach ($collection as $item) {
            $product_id = $item->getProductId();
            $outstock_date = Mage::helper('inventorysupplyneeds')->getOutstockDate($product_id,$datefrom,$dateto,$warehouse,$item);
            $pass = TRUE;
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (strtotime($outstock_date) < strtotime($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (strtotime($outstock_date) > strtotime($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setOutstockDate($outstock_date);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventorysupplyneeds')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

    protected function _setCollectionOrder($column) {
        $filterData = $this->getFilterData();
        $collection = $this->getCollection();

         //process require information to calculate outstock date
        $data = Mage::helper('inventorysupplyneeds')->processFilterData();
        $dateto = $data['date_to'];
        $datefrom = $data['date_from'];
        $warehouse = $data['warehouse'];

        if ($collection) {
            switch ($column->getId()) {
                case 'outstock_date':
                    $arr = array();
                    foreach ($collection as $item) {
                        $product_id = $item->getProductId();
                        $outstock_date = Mage::helper('inventorysupplyneeds')->getOutstockDate($product_id,$datefrom,$dateto,$warehouse,$item);
                        $item->setOutstockDate($outstock_date);
                        $arr[] = $item;
                    }
                    if ($column->getDir() == 'asc') {
                        $sorted = usort($arr, array($this, 'cmpAscOutstockDate'));
                    } else {
                        $sorted = usort($arr, array($this, 'cmpDescOutstockDate'));
                    }
                    $temp = Mage::helper('inventorysupplyneeds')->_tempCollection(); // A blank collection 
                    for ($i = 0; $i < count($arr); $i++) {
                        $temp->addItem($arr[$i]);
                    }
                    $this->setCollection($temp);
                    break;
                default:
                    $filter = $column->getIndex();
                    if ($column->getFilterIndex())
                        $filter = $column->getFilterIndex();
                    if ($column->getDir() == 'asc') {
                        $collection->setOrder($filter, 'ASC');
                    } else {
                        $collection->setOrder($filter, 'DESC');
                    }
                    break;
            }
        }
    }

    protected function filter_custom_column_callback($collection, $column) {
        return $this;
    }

    protected function filter_product_name_callback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->getSelect()->where('main_table.product_name like ?', '%' . $value . '%');
        }
        return $this;
    }

    protected function filter_custom_qty_callback($collection, $column) {
        if($column->getIndex() == 'qty_warehouse'){
//            echo $collection->getSelect();die();
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
            $warehouse = '';
            if ($requestData && isset($requestData['warehouse_select']))
                $warehouse = $requestData['warehouse_select'];
            $from = $column->getFilter()->getValue('from');
            $to = $column->getFilter()->getValue('to');
            if($from){
                if($warehouse){
                    $collection->getSelect()->where("`warehouse_product`.`available_qty` >= $from");
                }else{
                    $collection->getSelect()->where("catalog_inventory.qty >= $from");                    
                }
            }
            if($to){
                if($warehouse){
                    $collection->getSelect()->where("`warehouse_product`.`available_qty` <= $to");
                }else{
                    $collection->getSelect()->where("catalog_inventory.qty <= $to");
                }
            }
        }
        return $this;
    }
}