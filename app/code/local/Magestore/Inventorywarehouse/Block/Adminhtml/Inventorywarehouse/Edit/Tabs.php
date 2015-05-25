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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorywarehouse_Block_Adminhtml_Inventorywarehouse_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('inventorywarehouse_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventorywarehouse')->__('Item Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventorywarehouse_Block_Adminhtml_Inventorywarehouse_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('inventorywarehouse')->__('Item Information'),
            'title'     => Mage::helper('inventorywarehouse')->__('Item Information'),
            'content'   => $this->getLayout()
                                ->createBlock('inventorywarehouse/adminhtml_inventorywarehouse_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}