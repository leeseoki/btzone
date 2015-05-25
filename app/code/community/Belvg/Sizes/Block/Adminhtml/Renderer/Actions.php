<?php
class Belvg_Sizes_Block_Adminhtml_Renderer_Actions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
    public function render(Varien_Object $row)
    {
        return $this->getLayout()
            ->createBlock('sizes/adminhtml_categories_grid_actions')
            ->setTemplate('belvg/sizes/actions.phtml')
            ->setStandardId($row->getData('standard_id'))
            ->setCatId($row->getData('cat_id'))
            ->toHtml();
    }
    
}