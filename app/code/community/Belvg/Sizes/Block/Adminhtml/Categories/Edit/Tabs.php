<?php

class Belvg_Sizes_Block_Adminhtml_Categories_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('design_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Sizes Category Editor');
    }
    
    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('sizes')->__('Code and Labels'),
            'title'     => Mage::helper('sizes')->__('Code and Labels'),
            'content'   => $this->getLayout()->createBlock('sizes/adminhtml_categories_edit_tabs_general')->toHtml(),
        ));
        
        $this->addTab('image', array(
            'label'     => Mage::helper('sizes')->__('Image'),
            'title'     => Mage::helper('sizes')->__('Image'),
            'content'   => $this->getLayout()->createBlock('sizes/adminhtml_categories_edit_tabs_image')->toHtml(),
        ));
        
        $this->addTab('dimensions', array(
            'label'     => Mage::helper('sizes')->__('Dimensions'),
            'title'     => Mage::helper('sizes')->__('Dimensions'),
            'content'   => $this->getLayout()->createBlock('sizes/adminhtml_categories_edit_tabs_dimensions')->toHtml(),
        ));
        
        if ($this->_isDims()) {
            $this->addTab('values', array(
                'label'     => Mage::helper('sizes')->__('Sizes Values'),
                'title'     => Mage::helper('sizes')->__('Sizes Values'),
                'content'   => $this->getLayout()->createBlock('sizes/adminhtml_categories_edit_tabs_values')->toHtml(),
            ));
        }

        return parent::_prepareLayout();
    }
    
    protected function _isDims()
    {
        $cat_id = $this->getRequest()->getParam('cat_id');
        return (boolean)Mage::getModel('sizes/cat')->getCollection()
                                                   ->addFieldToFilter('cat_id', $cat_id)
                                                   ->getLastItem()
                                                   ->getDimIds();
    }
    
}