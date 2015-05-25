<?php
class Magestore_Inventoryreports_Block_Adminhtml_Header_Others extends Magestore_Inventoryreports_Block_Adminhtml_Header
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Inventoryreports_Block_Inventoryreports
     */
    public function _prepareLayout()
    {
        $this->setTemplate('inventoryreports/header/others.phtml');
        return parent::_prepareLayout();
    }
    
}   
