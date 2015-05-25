<?php

class Magestore_Inventorywarehouse_Block_Adminhtml_Warehouse_Edit_Tab_Renderer_Transactionobject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
		$sender = $row->getWarehouseNameFrom();
		$recipient = $row->getWarehouseNameTo();
        $html = $sender."/".$recipient;
        return $html;
    }

}

?>
