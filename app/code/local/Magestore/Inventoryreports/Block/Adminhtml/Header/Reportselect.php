<?php
class Magestore_Inventoryreports_Block_Adminhtml_Header_Reportselect extends Mage_Core_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Inventoryreports_Block_Inventoryreports
     */
    public function _prepareLayout()
    {
        $this->setTemplate('inventoryreports/header/report_select.phtml');
        return parent::_prepareLayout();
    }
    
    public function getReportType(){
        $type = $this->getRequest()->getParam('type_id');
        return Mage::getModel('inventoryreports/reporttype')->getReportTypeData($type);
    }
    
    public function getOrderAttributeOptions(){
        $options = array();
        //index will use in join table in helper/Data.php
        //index must be table_alias / field
        $options[0] = $this->__('Attributes');
        $options['shipping_method'] = $this->__('Shipping Method');
        $options['payment_method'] = $this->__('Payment Method');
        $options['status'] = $this->__('Status');
        $options['tax_code'] = $this->__('Tax');
        return $options;
    }
}


