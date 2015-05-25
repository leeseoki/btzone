<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Catalog_Product_List_Points extends Magegiant_GiantPoints_Block_Catalog_Product_List_Abstract
{
    protected static $_productListPointsNodes = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/giantpoints/catalog/product/list/points.phtml');
        $this->setName("giantpoints_catalog_product_list_points");
    }

    public function setProduct($product)
    {
        parent::setProduct($product);

        return $this;
    }

    /**
     * @return string
     */
    public function getPointsHtml()
    {
        $html = "";

        $blocks = $this->_getBlocksChildren();

        foreach ($blocks as $block) {
            $block_html = $block->toHtml();

            $html .= $block_html;
        }

        return $html;

    }

    protected function _getBlocksChildren()
    {

        // Load the flyweight block classes from the global layout.
        $flyweight_nodes = $this->_getProductListPointsNodes();
        // if no flyweight were found, return back.
        if (sizeof($flyweight_nodes) <= 0) return array();
        // Find and sort the children nodes
        $listPoint_children = array();
        $last_priority      = 0;
        foreach ($flyweight_nodes as $flyweight_node) {
            foreach ($flyweight_node->children() as $child) {
                if (!isset($child['priority'])) $child['priority'] = $last_priority;

                $priority                      = (int)$child['priority'];
                $child['name']                 = $child['name'] . $last_priority;
                $listPoint_children[$priority] = $child;
                $last_priority                 = $priority + 1;
            }
        }

        // Sort by priority (index of the array).
        ksort($listPoint_children);

        // Generate the blocks from this block's layout system.
        $blocks = array();
        foreach ($listPoint_children as $child_elem) {
            if (!isset($child_elem['type'])) continue;
            if (!isset($child_elem['name'])) continue;
            if (!isset($child_elem['template'])) continue;

            $block = $this->getLayout()
                ->createBlock((string)$child_elem['type'], (string)$child_elem['name'])
                ->setTemplate((string)$child_elem['template'])
                ->setProduct($this->getProduct());

            $blocks[] = $block;

        }

        return $blocks;
    }

    /**
     * Loads flyweight nodes from the layout XML or from the memory if it was loaded before
     * return array()
     */
    protected function _getProductListPointsNodes()
    {
        if (self::$_productListPointsNodes !== null) {
            return self::$_productListPointsNodes;
        }

        self::$_productListPointsNodes = $this->getLayout()->getXpath("//reference[@name='giantpoints_catalog_product_list_points']");

        // Make sure we don't reset it to null so it doesn't try to load it again since null assumes that we have not loaded it yet.
        if (self::$_productListPointsNodes === null) {
            self::$_productListPointsNodes = array();
        }

        return self::$_productListPointsNodes;
    }
}