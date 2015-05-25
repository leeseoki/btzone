<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create giantpoints table
 */
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('giantpoints/customer')};
CREATE TABLE {$this->getTable('giantpoints/customer')} (
  `reward_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) NOT NULL,
  `point_balance` int(11) NOT NULL,
  `point_spent` int(11) NOT NULL default '0',
  `notification_update` TINYINT(1) DEFAULT NULL,
  `notification_expire` TINYINT(1) DEFAULT NULL,
  `subscription_earned` TINYINT(1) DEFAULT 0,
  `registration_earned` TINYINT(1) DEFAULT 0,
  `tags_earned` TEXT,
  `last_birthday` DATETIME DEFAULT NULL,
  PRIMARY KEY (`reward_id`),
  UNIQUE KEY `GIANT_POINTS_CUSTOMER_ID` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('giantpoints/transaction')};
CREATE TABLE {$this->getTable('giantpoints/transaction')} (
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `reward_id` int(10) unsigned NULL,
  `customer_id` int(10) unsigned NULL,
  `customer_email` varchar(255) NOT NULL,
  `action_code` varchar(63) NOT NULL,
  `action_type` smallint(5) NOT NULL default '0',
  `store_id` smallint(5) NOT NULL,
  `point_amount` int(11) NOT NULL default '0',
  `point_spent` int(11) NOT NULL default '0',
  `point_balance` int(11) NOT NULL default '0',
  `status` smallint(5) NOT NULL,
  `change_date` datetime NULL,
  `is_locked` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `lock_changed_date` DATETIME DEFAULT NULL,
  `expiration_date` datetime NULL,
  `expire_email_sent` smallint(5) NOT NULL default '0',
  `order_id` int(10) unsigned NULL,
  `order_increment_id` varchar(63) NULL,
  `order_base_amount` decimal(12,4) NULL,
  `order_amount` decimal(12,4) NULL,
  `base_discount` decimal(12,4) NULL,
  `discount` decimal(12,4) NULL,
  `comment` varchar(255) NOT NULL,
  `notice` text NULL,
  INDEX (`is_locked`),
  INDEX (`lock_changed_date`),
  PRIMARY KEY (`transaction_id`),
  KEY `FK_GIANTPOINTS_TRANS_REWARD_ID` (`reward_id`),
  CONSTRAINT `FK_GIANTPOINTS_TRANS_REWARD_ID` FOREIGN KEY (`reward_id`) REFERENCES {$this->getTable('giantpoints/customer')} (`reward_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS {$this->getTable('giantpoints/rate')};
CREATE TABLE {$this->getTable('giantpoints/rate')} (
  `rate_id` int(10) unsigned NOT NULL auto_increment,
  `website_ids` text NULL,
  `customer_group_ids` text NULL,
  `direction` smallint(5) NOT NULL,
  `points` int(11) NOT NULL default '0',
  `money` decimal(12,4) NOT NULL default '0',
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
try { /*Add column to sales_order table*/
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'giantpoints_spent', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
    /*Add column to sales_order_item table*/
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_spent', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_base_discount_invoiced', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_discount_invoiced', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_discount_refunded', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'giantpoints_base_discount_refunded', 'decimal(12,4) NOT NULL default 0');
    /*Add column to sales_invoice table*/
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
    /* add column into sales_flat_invoice_item table */
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'giantpoints_spent', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
    /*Add column to sales_creditmemo table*/
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
    /* add column into sales_flat_invoice_item table */
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'giantpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'giantpoints_spent', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'giantpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'giantpoints_discount', 'decimal(12,4) NOT NULL default 0');
} catch (Exception $e) {
}
/*Update birthday configuration*/

$installer->updateBirthdayConfig();

$installer->endSetup();

