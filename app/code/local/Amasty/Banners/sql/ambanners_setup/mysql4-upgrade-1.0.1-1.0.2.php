<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Banners
 */
$this->startSetup();

$this->run("
 	ALTER TABLE `{$this->getTable('ambanners/rule')}` ADD COLUMN `show_products` tinyint DEFAULT 0
");
$this->endSetup();
