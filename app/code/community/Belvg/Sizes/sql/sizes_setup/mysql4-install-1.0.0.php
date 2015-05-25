<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    1.0.0
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
 
$installer = $this;

$installer->startSetup();

$installer->run("
 
CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_cat')} (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_code` varchar(100) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  `dim_ids` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_cat')}
VALUES (1, 'men_shirts', 10, '1,2'),
       (2, 'men_jackets', 20, '1,2'),
       (3, 'men_shorts', 30, '2'),
       (4, 'men_jeans', 40, '2,3'),
       (5, 'men_pants', 50, '2,3'),
       (6, 'women_shirts', 60, '1,2'),
       (7, 'women_jackets', 70, '1,2'),
       (8, 'women_dresses', 80, '1,2'),
       (9, 'women_skirts', 90, '2'),
       (10, 'women_jeans', 110, '2,3'),
       (11, 'women_shorts', 120, '2'),
       (12, 'women_pants', 130, '2,3');

CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_cat_labels')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_cat_labels')}
VALUES (1, 1, 0, 'Men Shirts'),
       (2, 2, 0, 'Men Jackets'),
       (3, 3, 0, 'Men Shorts'),
       (4, 4, 0, 'Men Jeans'),
       (5, 5, 0, 'Men Pants'),
       (6, 6, 0, 'Women Shirts'),
       (7, 7, 0, 'Women Jackets'),
       (8, 8, 0, 'Women Dresses'),
       (9, 9, 0, 'Women Skirts'),
       (10, 10, 0, 'Women Jeans'),
       (11, 11, 0, 'Women Shorts'),
       (12, 12, 0, 'Women Pants');

CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_dem')} (
  `dem_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dem_code` varchar(100) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`dem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_dem')}
VALUES (1, 'chest', 5),
       (2, 'waist', 10),
       (3, 'inseam', 15),
       (4, 'hips', 20),
       (5, 'cup_size', 25),
       (6, 'height', 30),
       (7, 'weight', 35),
       (8, 'length', 40),
       (9, 'neck', 45);

CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_dem_labels')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dem_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_dem_labels')}
VALUES (1, 1, 0, 'Chest'),
       (2, 2, 0, 'Waist'),
       (3, 3, 0, 'Inseam'),
       (4, 4, 0, 'Hips'),
       (5, 5, 0, 'Cup Size'),
       (6, 6, 0, 'Height'),
       (7, 7, 0, 'Weight'),
       (8, 8, 0, 'Length'),
       (9, 9, 0, 'Neck');
       
CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_standards')} (
  `standard_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`standard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_standards')}
VALUES (1, 'International');

CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_standards_values')} (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `standard_id` int(10) unsigned NOT NULL,
  `value` varchar(100) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('belvg_sizes_standards_values')}
VALUES (1, 1, 'XS', 5),
       (2, 1, 'S', 10),
       (3, 1, 'M', 15),
       (4, 1, 'L', 20),
       (5, 1, 'XL', 25),
       (6, 1, 'XXL', 30),
       (7, 1, 'XXXL', 35);
       
CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_main')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(10) unsigned NOT NULL,
  `dem_id` int(10) unsigned NOT NULL,
  `min` int(10) unsigned NOT NULL,
  `max` int(10) unsigned NOT NULL,
  `value_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1; 
       
CREATE TABLE IF NOT EXISTS {$this->getTable('belvg_sizes_products')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `standard_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;        

ALTER TABLE `{$this->getTable('belvg_sizes_cat_labels')}`
  ADD CONSTRAINT `belvg_sizes_cat_id_fk` FOREIGN KEY (`cat_id`) REFERENCES `{$this->getTable('belvg_sizes_cat')}` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `belvg_sizes_cat_store_fk` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE;
  
ALTER TABLE `{$this->getTable('belvg_sizes_dem_labels')}`
  ADD CONSTRAINT `belvg_sizes_dem_id_fk` FOREIGN KEY (`dem_id`) REFERENCES `{$this->getTable('belvg_sizes_dem')}` (`dem_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `belvg_sizes_dem_store_fk` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE;

ALTER TABLE `{$this->getTable('belvg_sizes_main')}`
  ADD CONSTRAINT `belvg_sizes_main_cat_fk` FOREIGN KEY (`cat_id`) REFERENCES `{$this->getTable('belvg_sizes_cat')}` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `belvg_sizes_main_dem_fk` FOREIGN KEY (`dem_id`) REFERENCES `{$this->getTable('belvg_sizes_dem')}` (`dem_id`) ON DELETE CASCADE, 
  ADD CONSTRAINT `belvg_sizes_main_value_fk` FOREIGN KEY (`value_id`) REFERENCES `{$this->getTable('belvg_sizes_standards_values')}` (`value_id`) ON DELETE CASCADE; 
  
ALTER TABLE `{$this->getTable('belvg_sizes_standards_values')}`
  ADD CONSTRAINT `belvg_sizes_dem_standards_id_fk` FOREIGN KEY (`standard_id`) REFERENCES `{$this->getTable('belvg_sizes_standards')}` (`standard_id`) ON DELETE CASCADE;

");

$installer->endSetup();