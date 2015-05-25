<?php

$installer = $this;

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('giantpoints/rule')};
CREATE TABLE {$this->getTable('giantpoints/rule')} (
    `behavior_rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `from_date` DATE,
    `to_date` DATE,
    `customer_group_ids` VARCHAR(255) NOT NULL,
    `customer_group_id` SMALLINT(5) UNSIGNED NOT NULL,
    `is_active` TINYTEXT NOT NULL,
    `conditions_serialized` MEDIUMTEXT NOT NULL,
    `point_action` VARCHAR(25),
    `point_amount` INT(11),
    `website_ids` TEXT,
    `sort_order` INT(10) NOT NULL DEFAULT '0',
    `simple_action` VARCHAR(32) NOT NULL DEFAULT 'by_percent',
    `onhold_duration` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`behavior_rule_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");
try {
    $installer->getConnection()->addColumn(
        $this->getTable('giantpoints/transaction'),
        'onhold_date',
        'datetime NULL');
} catch (Exception $e) {
}
$installer->endSetup();