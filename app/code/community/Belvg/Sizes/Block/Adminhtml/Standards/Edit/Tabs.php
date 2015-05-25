<?php

class Belvg_Sizes_Block_Adminhtml_Standards_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('design_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Sizes Standard Editor');
    }
    
    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('sizes')->__('Name and Values'),
            'title'     => Mage::helper('sizes')->__('Name and Values'),
            'content'   => $this->getLayout()->createBlock('sizes/adminhtml_standards_edit_tabs_general')->toHtml(),
        ));

        return parent::_prepareLayout();
    }
    
}