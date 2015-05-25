<?php
class Magestore_Inventorysupplyneeds_Model_Config {
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('inventorysupplyneeds')->__('EXPONENTIAL SMOOTHING')),
            array('value' => 2, 'label'=>Mage::helper('inventorysupplyneeds')->__('AVERAGE')),
        );
    }
}
?>
