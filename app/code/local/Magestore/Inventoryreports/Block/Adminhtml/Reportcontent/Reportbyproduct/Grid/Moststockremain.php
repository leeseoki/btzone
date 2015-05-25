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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbyproduct_Grid_Moststockremain extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('moststockremainGrid');
        $this->setDefaultSort('total_remain');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
//        Zend_Debug::dump($requestData);
        $gettime = Mage::Helper('inventoryreports')->getTimeSelected($requestData);



        $collection = Mage::getModel('inventoryplus/warehouse_product')->getCollection();
        $collection->getSelect()
                ->join(array('flat' => 'catalog_product_flat_1'), 'main_table.product_id = flat.entity_id', array('flat.name', 'SUM(main_table.total_qty) AS total_remain', 'SUM(main_table.available_qty) AS available'))
                ->where('main_table.created_at BETWEEN "' . $gettime['date_from'] . '" AND "' . $gettime['date_to'] . '"')
                ->group('main_table.product_id')
        ;
        $collection->setOrder('total_remain', 'DESC');
        $checkResult = 0;
        $limitCollection = new Varien_Data_Collection();
        foreach ($collection as $c) {
            if ($checkResult == 7) {
                break;
            }
            $limitCollection->addItem($c);
            $checkResult++;
        }
        $this->setCollection($limitCollection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventory_Block_Adminhtml_Inventory_Grid
     */
    protected function _prepareColumns() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));

        $this->addColumn('name', array(
            'header' => Mage::helper('inventoryreports')->__('Product Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        $this->addColumn('total_remain', array(
            'header' => Mage::helper('inventoryreports')->__('Qty. Remaining'),
            'align' => 'right',
            'index' => 'total_remain',
            'type' => 'number',
            'width' => '100px',
            'filter_index' => 'SUM(main_table.total_qty)',
            'filter_condition_callback' => array($this, '_filterTotalRemainCallback'),
        ));

//        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryreports')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('inventoryreports')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
//        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/adminhtml_report/moststockremaingrid', array('top_filter' => $this->getRequest()->getParam('top_filter')));
    }

    public function _filterTotalRemainCallback($collection, $column) {
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
                $item->setData($column->getId(), $fieldValue);
                $arr[] = $item;
            }
        }
        $temp = Mage::helper('inventoryreports')->_tempCollection(); // A blank collection 
        for ($i = 0; $i < count($arr); $i++) {
            $temp->addItem($arr[$i]);
        }
        $this->setCollection($temp);
    }

}
