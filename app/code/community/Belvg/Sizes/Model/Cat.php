<?php
class Belvg_Sizes_Model_Cat extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sizes/cat');
        $this->setIdFieldName('cat_id');
    }
    
    public function getCategories()
    {   
        $prefix = Mage::getConfig()->getTablePrefix();
        $collection = $this->getCollection();
        $collection->getSelect()
                   ->joinLeft(array('labels' => $prefix . 'belvg_sizes_cat_labels'), "labels.cat_id = main_table.cat_id AND labels.store_id=0");
        return $collection;
    }
    
    public function getCategoryLabel($catId)
    {
        if ($catId) {    
            $prefix = Mage::getConfig()->getTablePrefix();
            $store_id = Mage::app()->getStore()->getStoreId();
            $collection = $this->getCollection()->addFieldToFilter('main_table.cat_id', (int)$catId);
            $collection->getSelect()
                       ->joinLeft(array('labels' => $prefix . 'belvg_sizes_cat_labels'), "labels.cat_id = main_table.cat_id AND (labels.store_id=" . $store_id . " OR labels.store_id=0)", array('labels.label', 'labels.store_id'));
                       
            foreach ($collection->getData() as $item) {
                if ($item['store_id'] == $store_id) {
                    return htmlspecialchars($item['label'], ENT_QUOTES);
                } elseif ($item['store_id'] == 0) {
                    $label = $item['label'];
                }
            }
            
            return htmlspecialchars($label, ENT_QUOTES);
        }
        
        return FALSE;
    }
    
}