<?php
class Belvg_Sizes_Model_Mysql4_Dem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('sizes/dem');
    }
    
}