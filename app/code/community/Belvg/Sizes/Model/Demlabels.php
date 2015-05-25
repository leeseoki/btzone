<?php
class Belvg_Sizes_Model_Demlabels extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/demlabels');
        $this->setIdFieldName('id');
    }
    
    public function prepareLabel()
    {
        return htmlspecialchars($this->getLabel(), ENT_QUOTES);
    }
    
}