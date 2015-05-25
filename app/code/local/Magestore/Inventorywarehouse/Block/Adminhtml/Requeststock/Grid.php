<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Requeststock_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('requeststockGrid');
        $this->setDefaultSort('warehouse_requeststock_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('inventorywarehouse/requeststock')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('warehouse_requeststock_id', array(
            'header' => Mage::helper('inventorywarehouse')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'warehouse_requeststock_id',
//            'renderer' => 'inventoryplus/adminhtml_warehouse_edit_tab_renderer_increment'
        ));


        $this->addColumn('warehouse_name_from', array(
            'header' => Mage::helper('inventorywarehouse')->__('Source'),
            'align' => 'left',
//            'type' => 'options',
            'width' => '350px',
//            'options' => Mage::helper('inventorywarehouse')->getAllWarehouseRequeststockGrid(),
            'index' => 'warehouse_name_from',
//            'filter_condition_callback' => array($this, 'filterWarehouseTo')
        ));

        $this->addColumn('warehouse_name_to', array(
            'header' => Mage::helper('inventorywarehouse')->__('Destination Warehouse'),
            'align' => 'left',
            'index' => 'warehouse_name_to',
//            'type' => 'options',
            'width' => '350px',
//            'options' => Mage::helper('inventorywarehouse')->getWarehouseNames(),
        ));

        $this->addColumn('total_products', array(
            'header' => Mage::helper('inventorywarehouse')->__('Qty requested'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'total_products',
            'type' => 'number'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventorywarehouse')->__('Created On'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'date',
            'index' => 'created_at',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('inventorywarehouse')->__('Created by'),
            'width' => '80px',
            'align' => 'left',
            'index' => 'created_by'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inventorywarehouse')->__('Status'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('inventorywarehouse')->__('Completed'),
                2 => Mage::helper('inventorywarehouse')->__('Canceled')
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('sales')->__('Action'),
            'width' => '80px',
            'filter' => false,
            'align' => 'left',
            'sortable' => false,
            'is_system' => true,
            'renderer' => 'inventorywarehouse/adminhtml_requeststock_renderer_action'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventorywarehouse')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorywarehouse')->__('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

    public function filterCreatedOn($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['orig_from']) && $filter['orig_from']) {
			$filter['orig_from'] .= ' 00:00:00'; 
			$from = Mage::getModel('core/date')->date('Y-m-d H:i:s', Mage::getModel('core/date')->gmtTimestamp($filter['orig_from']));
            $collection->getSelect()->where('created_at >= ?', $from);
        }
        if (isset($filter['orig_to']) && $filter['orig_to']) {
            $filter['orig_to'] .= ' 23:59:59';
			$to = Mage::getModel('core/date')->date('Y-m-d H:i:s', Mage::getModel('core/date')->gmtTimestamp($filter['orig_to']));
            $collection->getSelect()->where('created_at <= ?', $to);
        }
    }

    public function filterWarehouseTo($collection, $column) {
        $value = $column->getFilter()->getValue();

        if (!is_null(@$value)) {
            if ($value == 'Others') {
                $collection->getSelect()->where('main_table.from_name like ?', $value);
            } else {
                $collection->getSelect()->where('main_table.from_name like ?', $value);
            }
        }
        return $this;
    }

}