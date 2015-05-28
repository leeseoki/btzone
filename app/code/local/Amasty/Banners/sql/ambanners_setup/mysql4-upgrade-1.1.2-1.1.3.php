<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Banners
 */

$this->startSetup();

$this->run("
 	ALTER TABLE `{$this->getTable('ambanners/rule')}` CHANGE COLUMN `banner_position`
 	`banner_position` VARCHAR(255) DEFAULT NULL
");
$this->endSetup();
