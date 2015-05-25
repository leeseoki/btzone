<?php
class Belvg_Sizes_Model_Standardsvalues extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/standardsvalues');
        $this->setIdFieldName('value_id');
    }

}
