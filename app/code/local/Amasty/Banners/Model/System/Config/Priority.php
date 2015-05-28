<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Banners
 */

class Amasty_Banners_Model_System_Config_Priority extends Varien_Object
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('ambase');
            return array(
                    array('value' => 0, 'label' => $hlp->__('Show all, sorted by priority')),
                    array('value' => 1, 'label' => $hlp->__('Show only one with the highest priority')),

            );
    }
}
?>