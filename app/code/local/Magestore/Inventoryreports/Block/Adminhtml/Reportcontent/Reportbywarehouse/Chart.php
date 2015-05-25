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
class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Reportbywarehouse_Chart extends Mage_Adminhtml_Block_Widget {
    
    protected function _prepareLayout() {
        $filterData = new Varien_Object();
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        if(empty($requestData)){
            $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_qty_adjust.phtml');
        }
        if(!empty($requestData)){
            switch ($requestData['report_radio_select']) {
                case 'total_qty_adjuststock':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_qty_adjust.phtml');
                    break;
                case 'number_of_product_adjuststock':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/number_of_product_adjuststock.phtml');
                    break;
                case 'total_order_by_warehouse':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_order_by_warehouse.phtml');
                    break;
                case 'sales_by_warehouse_revenue':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/sales_by_warehouse_revenue.phtml');
                    break;
                case 'sales_by_warehouse_item_shipped':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/sales_by_warehouse_item_shipped.phtml');
                    break;
                case 'total_stock_transfer_send_stock':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_stock_transfer_send_stock.phtml');
                    break;
                case 'total_stock_transfer_request_stock':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_stock_transfer_request_stock.phtml');
                    break;
                case 'supply_needs_by_warehouse_products':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/supply_needs_by_warehouse_products.phtml');
                    break;
                case 'total_stock_different_when_physical_stocktaking_by_warehouse':
                    $this->setTemplate('inventoryreports/content/chart/chart-content/warehouse/total_stock_different_when_physical_stocktaking_by_warehouse.phtml');
                    break;
            }
        }
                return parent::_prepareLayout();
    }
    
    protected function _getAttributeTableAlias($attributeCode)
    {
        return 'at_' . $attributeCode;
    }

}