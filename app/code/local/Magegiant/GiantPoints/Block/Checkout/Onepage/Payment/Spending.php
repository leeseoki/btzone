<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
* https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/**
 * GiantPoints Checkout Payment Method Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Checkout_Onepage_Payment_Spending extends Magegiant_GiantPoints_Block_Abstract
{
    protected static $_spendingPaymentNodes = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setName('checkout.payment.spending');
    }

    /**
     * get giant points spending block helper
     *
     * @return Magegiant_GiantPoints_Helper_Block_Spend
     */
    public function getSpendHelper()
    {
        return Mage::helper('giantpoints/spending_point');
    }

    /**
     * @return string
     */
    public function getSpendingChildHtml()
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
        $flyweight_nodes = $this->_getSpendingPaymentNodes();
        // if no flyweight were found, return back.
        if (!is_array($flyweight_nodes) || sizeof($flyweight_nodes) <= 0) return array();
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
                ->setTemplate((string)$child_elem['template']);

            $blocks[] = $block;

        }

        return $blocks;
    }

    /**
     * Loads flyweight nodes from the layout XML or from the memory if it was loaded before
     * return array()
     */
    protected function _getSpendingPaymentNodes()
    {
        if (self::$_spendingPaymentNodes !== null) {
            return self::$_spendingPaymentNodes;
        }

        self::$_spendingPaymentNodes = $this->getLayout()->getXpath("//reference[@name='checkout.payment.spending']");

        // Make sure we don't reset it to null so it doesn't try to load it again since null assumes that we have not loaded it yet.
        if (self::$_spendingPaymentNodes === null) {
            self::$_spendingPaymentNodes = array();
        }

        return self::$_spendingPaymentNodes;
    }

    /**
     * call method that defined from block helper
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $helper = $this->getSpendHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
        }

        return parent::__call($method, $args);
    }
}
