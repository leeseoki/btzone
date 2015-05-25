<?php
class Belvg_Sizes_Block_Adminhtml_Renderer_Values extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
    public function render(Varien_Object $row)
    {
        return $this->getLayout()
            ->createBlock('sizes/adminhtml_standards_grid_values')
            ->setTemplate('belvg/sizes/values.phtml')
            ->setStandardId($row->getData('standard_id'))
            ->toHtml();
    }
    
}