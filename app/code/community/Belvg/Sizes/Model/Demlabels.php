<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 *******************************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
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
    
    public function getPreparedLabel()
    {
        return $this->prepareLabel() . ' (' . Mage::helper('sizes')->getUnitSizes() . ')';
    }
    
}