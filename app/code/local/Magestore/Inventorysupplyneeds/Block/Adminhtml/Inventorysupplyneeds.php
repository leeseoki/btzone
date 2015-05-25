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

/**
 * Inventorysupplyneeds Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Block_Adminhtml_Inventorysupplyneeds extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_inventorysupplyneeds';
        $this->_blockGroup = 'inventorysupplyneeds';
        $this->_headerText = Mage::helper('inventorysupplyneeds')->__('Supply Needs Manager');
        $this->_addButtonLabel = Mage::helper('inventorysupplyneeds')->__('Add Item');
        parent::__construct();
        if (Mage::helper('inventorypurchasing/purchaseorder')->getWarehouseOption())
            $this->_addButton('purchase_order', array(
                'label' => Mage::helper('inventorysupplyneeds')->__('Create New Purchase Order'),
                'onclick' => 'createPurchaseOrder()',
                'class' => 'add',
                    ), 0);
        $this->_addButton('fill_max', array(
            'label' => Mage::helper('inventorysupplyneeds')->__('Fill All To Purchase'),
            'onclick' => 'fillAllMax()',
            'class' => 'save',
                ), 0);
        $this->_addButton('cancel_max', array(
            'label' => Mage::helper('inventorysupplyneeds')->__('Cancel All'),
            'onclick' => 'cancelAll()',
            'class' => 'save',
                ), 0);
        $this->setTemplate('inventorysupplyneeds/content-header.phtml');
        $this->_removeButton('add');
    }

}
