<?php

class Belvg_Sizes_Block_Adminhtml_Dimensions_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form',
                                       'action' => $this->getUrl('*/*/save', array('dem_id' => $this->getRequest()->getParam('dem_id'))),
                                       'method' => 'post',
                                      'enctype' => 'multipart/form-data'));
        $form->setUseContainer(TRUE);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
}