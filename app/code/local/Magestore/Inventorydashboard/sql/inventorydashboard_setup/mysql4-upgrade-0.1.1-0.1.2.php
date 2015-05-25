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

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
//add new dashboard tab
$dashboardTab = Mage::getModel('inventorydashboard/tabs')
        ->setData('name','3D Charts')
        ->setData('position','0')
        ->save();
$tabId = $dashboardTab->getId();
/**
 * create inventorydashboard table
 */
$installer->run("

INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Supplier - PO', '2', '1', 'purchase_order_to_supplier', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Product - Best Seller', '1', '1', 'best_seller', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Warehouse - Qty Adjuststock', '2', '0', 'total_qty_adjuststock', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_item')} (tab_id, name, item_column, item_row, report_code, chart_code) VALUES ($tabId, 'Sales - Daily Hours', '1', '0', 'hours_of_day', 'chart_column');  


");

$installer->endSetup();

