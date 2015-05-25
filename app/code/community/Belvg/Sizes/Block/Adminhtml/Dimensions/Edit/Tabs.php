<?php

class Belvg_Sizes_Block_Adminhtml_Dimensions_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('design_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Sizes Dimensions Editor');
    }
    
    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('sizes')->__('Code and Labels'),
            'title'     => Mage::helper('sizes')->__('Code and Labels'),
            'content'   => $this->getLayout()->createBlock('sizes/adminhtml_dimensions_edit_tabs_general')->toHtml(),
        ));

        return parent::_prepareLayout();
    }
    
}