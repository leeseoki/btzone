<?php
class Magestore_Inventoryreports_Block_Adminhtml_Header extends Mage_Adminhtml_Block_Template{
    protected function _prepareLayout(){
        return parent::_prepareLayout();
    }
    
    public function getSubmitUrl(){
        return Mage::getUrl('inventoryreportsadmin/adminhtml_report/index/',array('type_id' => $this->getRequest()->getParam('type_id')));
    }
    
    public function getTypeId(){
        return $this->getRequest()->getParam('type_id');
    }
}

