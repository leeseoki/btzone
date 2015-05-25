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
 /****************************************
 *    MAGENTO EDITION USAGE NOTICE       *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /****************************************
 *    DISCLAIMER                         *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    v1.0.0
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Model_Source_Options
{
    
    public function toOptionArray()
    {
        $data = array();
        $productOptions = Mage::getModel('catalog/product_option')
                              ->getCollection()
                              ->addTitleToResult(0)
                              ->addPriceToResult(0)
                              ->setOrder('sort_order', 'asc')
                              ->setOrder('title', 'asc');
 
        foreach ($productOptions as $option) {
            array_push($data, array( 'value' => $option->getOptionId(), 
                                     'label' => $option->getTitle()
                                     ));
        }
        
        return $data;
    }
    
}