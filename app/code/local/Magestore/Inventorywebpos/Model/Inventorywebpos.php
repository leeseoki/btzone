<?php

class Magestore_Inventorywebpos_Model_Inventorywebpos extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventorywebpos/inventorywebpos');
    }
}