<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Banners
 */

class Amasty_Banners_Block_Adminhtml_Rule_Grid_Renderer_Position extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    public function render(Varien_Object $row)
    {
        $hlp = Mage::helper('ambanners');
        $position = $row->getData('banner_position');
        $position = trim($position, ',');
        if (!$position) {
            return $hlp->__('No Position');
        }
        $position = explode(',', $position);

        $html = '';

        foreach($hlp->getPosition() as $posId => $row)
        {
            if (in_array($posId, $position)) {
                $html .= $row . "<br />";
            }
        }
        return $html;
    }

}