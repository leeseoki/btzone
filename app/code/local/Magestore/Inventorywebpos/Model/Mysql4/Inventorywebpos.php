<?php

class Magestore_Inventorywebpos_Model_Mysql4_Inventorywebpos extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the inventorywebpos_id refers to the key field in your database table.
        $this->_init('inventorywebpos/inventorywebpos', 'inventorywebpos_id');
    }
}