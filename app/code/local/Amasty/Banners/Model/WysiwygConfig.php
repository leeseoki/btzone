<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Banners
 */

class Amasty_Banners_Model_WysiwygConfig extends Mage_Cms_Model_Wysiwyg_Config
{
    public function getConfig($data = array())
    {
        $adminUrl = Mage::getSingleton('adminhtml/url');
        $request = $adminUrl->getRequest();

        $oldName = $request->getRouteName();

        $request->setRouteName('adminhtml');
        $config = parent::getConfig($data);
        $request->setRouteName($oldName);

        return $config;
    }
}
