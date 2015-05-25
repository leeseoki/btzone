<?php

class Magestore_Inventorywarehouse_Model_Inventoryselectwarehouse {
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the largest product Qty.')),
            array('value' => 2, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the smallest product Qty.')),
            array('value' => 3, 'label'=>Mage::helper('inventorywarehouse')->__('Warehouse with the minimum distance to customer’s shipping address')),
        );
    }
}

?>
