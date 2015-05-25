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

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create inventoryreports table
 */
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('erp_inventory_report_chart_type')};
	CREATE TABLE {$this->getTable('erp_inventory_report_chart_type')} (
		`report_chart_type_id` int(11) unsigned NOT NULL auto_increment,
		`report_type_id` int(11) unsigned NOT NULL,
		`chart_type` varchar(255) default '',
		PRIMARY KEY  (`report_type_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('1','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('2','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('3','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('4','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('5','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('6','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('7','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('8','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('9','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('10','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('11','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('12','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('13','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('14','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('15','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('16','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('17','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('18','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('19','chart_pie,chart_table');
        insert into {$this->getTable('erp_inventory_report_chart_type')}(`report_type_id`,`chart_type`) values ('20','chart_pie,chart_table');
");
$installer->endSetup();


