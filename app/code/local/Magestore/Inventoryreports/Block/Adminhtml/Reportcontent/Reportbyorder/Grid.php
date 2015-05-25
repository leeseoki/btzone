<?php

class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyorder_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_requestData = null;
    protected $_filter = null;

    public function __construct() {
        parent::__construct();
        $this->setId('reportorderGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        //set request data
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        $requestData['type_id'] = $this->getRequest()->getParam('type_id');
        $this->_requestData = $requestData;
    }

    protected function _prepareCollection() {
        $data = Mage::helper('inventoryreports/order')->getOrderReportCollection($this->_requestData);
        if(is_array($data)){
            $collection = $data['collection'];
            $this->_filter = $data['filter'];
        } else {
            $collection = $data;
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        if ($this->_requestData['report_radio_select'] != 'order_attribute') {
            $this->addColumn('time_range', array(
                'header' => Mage::helper('inventoryreports')->__('Time'),
                'align' => 'left',
                'index' => 'time_range',
                'width' => '100px',
                'filter' => false,
                'renderer' => 'inventoryreports/adminhtml_reportcontent_renderer_ordertimerange'
            ));
        } else {
            $attribute = $this->_requestData['attribute_select'];
            $this->addColumn('att_' . $attribute, array(
                'header' => Mage::helper('inventoryreports')->__(ucwords(str_replace('_', ' ', $attribute))),
                'align' => 'left',
                'index' => 'att_' . $attribute,
                'width' => '100px',
                'filter' => false,
                    //'renderer' => 'inventoryreports/adminhtml_reportcontent_renderer_ordertimerange'
            ));
        }
        
        $this->addColumn('count_entity_id', array(
            'header' => Mage::helper('inventoryreports')->__('No. of Orders '),
            'align' => 'right',
            'index' => 'count_entity_id',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));
        
        $this->addColumn('count_item_id', array(
            'header' => Mage::helper('inventoryreports')->__('Total Item(s) Sold'),
            'align' => 'right',
            'index' => 'count_item_id',
            'type' => 'number',
            'width' => '50px',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));
        
        $this->addColumn('sum_base_subtotal', array(
            'header' => Mage::helper('inventoryreports')->__('Subtotal (Base)'),
            'align' => 'right',
            'index' => 'sum_base_subtotal',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_subtotal', array(
            'header' => Mage::helper('inventoryreports')->__('Subtotal (Purchased)'),
            'align' => 'right',
            'index' => 'sum_subtotal',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_base_tax_amount', array(
            'header' => Mage::helper('inventoryreports')->__('Tax (Base)'),
            'align' => 'right',
            'index' => 'sum_base_tax_amount',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_tax_amount', array(
            'header' => Mage::helper('sales')->__('Tax (Purchased)'),
            'align' => 'right',
            'index' => 'sum_tax_amount',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_base_grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('G.T. (Base)'),
            'align' => 'right',
            'index' => 'sum_base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));

        $this->addColumn('sum_grand_total', array(
            'header' => Mage::helper('inventoryreports')->__('G.T. (Purchased)'),
            'align' => 'right',
            'index' => 'sum_grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
            'filter_condition_callback' => array($this, '_filterCallback'),
        ));
        return parent::_prepareColumns();
    }
    
    protected function _filterCallback($collection, $column){
        $filter = $column->getFilter()->getValue();
        $filterData = $this->getFilterData();
        $arr = array();
        foreach ($collection as $item) {
            $fieldValue = $item->getData($column->getId());
            $pass = TRUE;
            if (isset($filter['from']) && $filter['from'] >= 0) {
                if (floatval($fieldValue) < floatval($filter['from'])) {
                    $pass = FALSE;
                }
            }
            if ($pass) {
                if (isset($filter['to']) && $filter['to'] >= 0) {
                    if (floatval($fieldValue) > floatval($filter['to'])) {
                        $pass = FALSE;
                    }
                }
            }
            if ($pass) {
                $item->setData($column->getId(),$fieldValue);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

    public function getGridUrl() {
        return $this->getUrl('*/adminhtml_report/reportordergrid', array('type_id' => 'sales', 'top_filter' => $this->getRequest()->getParam('top_filter')));
    }

}
