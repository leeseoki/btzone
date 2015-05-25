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

/**
 * create inventorydashboard table
 */
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('erp_inventory_dashboard_report_type')};
CREATE TABLE {$this->getTable('erp_inventory_dashboard_report_type')} (
        `report_type_id` int(11) unsigned NOT NULL auto_increment,
        `type` varchar(255) default '',
        `title` varchar(255) default'',
        `report_code` varchar(255) default '',
        `name` varchar(255) default'',
        `default_time_range` varchar(255) default 'last_30_days',
        PRIMARY KEY  (`report_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('sales','Sales Report','hours_of_day','Daily Hours');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('sales','Sales Report','days_of_week','Weekly Days');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('sales','Sales Report','order_attribute','Order Attributes');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('sales','Sales Report','invoice','Invoice');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('sales','Sales Report','refund','Refund');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','total_qty_adjuststock','Total Adjusted Qty');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','number_of_product_adjuststock','Product Adjuststock');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','total_order_by_warehouse','Total Order');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','sales_by_warehouse_revenue','Revenue');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','sales_by_warehouse_item_shipped','Item Shipped');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','total_stock_transfer_send_stock','Send Stock');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','total_stock_transfer_request_stock','Request Stock');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','supply_needs_by_warehouse_products','Supply Needs');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('warehouse','Warehouse Report','total_stock_different_when_physical_stocktaking_by_warehouse','Stocktaking Variance');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('product','Product Report','best_seller','Best Seller');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('product','Product Report','most_stock_remain','Qty. Remaining');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('product','Product Report','warehousing_time_longest','Warehousing Time');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('supplier','Supplier Report','purchase_order_to_supplier','Purchase Order');

INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('unknown','Unknown','order_in_10days','Sales orders in the last 10 days');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('unknown','Unknown','warehouse_shipment','Items shipped from warehouse(s)');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('unknown','Unknown','supplier_purchase_order','Purchase Orders sent to supplier(s)');
INSERT INTO {$this->getTable('erp_inventory_dashboard_report_type')}(`type`,`title`,`report_code`,`name`) VALUES ('unknown','Unknown','last_10_adjuststock','The last 10 stock adjustments');

UPDATE {$this->getTable('erp_inventory_dashboard_report_type')} SET `default_time_range` = 'next_30_days' WHERE `report_code` = 'supply_needs_by_warehouse_products';

INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('hours_of_day', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('hours_of_day', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('days_of_week', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('days_of_week', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('order_attribute', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('order_attribute', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('invoice', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('invoice', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('refund', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('refund', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_qty_adjuststock', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_qty_adjuststock', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('number_of_product_adjuststock', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('number_of_product_adjuststock', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_order_by_warehouse', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_order_by_warehouse', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('sales_by_warehouse_revenue', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('sales_by_warehouse_revenue', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('sales_by_warehouse_item_shipped', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('sales_by_warehouse_item_shipped', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_transfer_send_stock', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_transfer_send_stock', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_transfer_request_stock', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_transfer_request_stock', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('supply_needs_by_warehouse_products', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('supply_needs_by_warehouse_products', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_different_when_physical_stocktaking_by_warehouse', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('total_stock_different_when_physical_stocktaking_by_warehouse', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('best_seller', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('best_seller', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('most_stock_remain', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('most_stock_remain', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('warehousing_time_longest', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('warehousing_time_longest', 'chart_pie');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('purchase_order_to_supplier', 'chart_column');
INSERT INTO {$this->getTable('erp_inventory_dashboard_chart_report')} (report_code, chart_code) VALUES ('purchase_order_to_supplier', 'chart_pie');


");

$installer->endSetup();

