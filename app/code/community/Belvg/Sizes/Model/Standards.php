<?php
class Belvg_Sizes_Model_Standards extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/standards');
        $this->setIdFieldName('standard_id');
    }

}