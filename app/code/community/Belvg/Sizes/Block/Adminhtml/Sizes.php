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
class Belvg_Sizes_Block_Adminhtml_Sizes extends  Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('belvg/sizes/product_tab_form.phtml');
    }
    
    public function getTabLabel()
    {
        return $this->__('Sizes');
    }
    
    public function getTabTitle()
    {
        return $this->__('Sizes');
    }
    
    public function canShowTab()
    {
        if ($this->getRequest()->getParam('type')
        || ($id = $this->getRequest()->getParam('id'))) {
            /*if ($id && Mage::getModel('catalog/product')->load($id)->getVisibility() == 1) {
                return FALSE;
            }*/        
        
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function isHidden()
    {
        return FALSE;
    }
    
    public function getOptions()
    {   
        $product_id = $this->getRequest()->getParam('id');
        if ($product_id) {
            $tmp = Mage::getModel('sizes/products')->getCollection()
                                                   ->addFieldToFilter('product_id', $product_id)
                                                   ->getLastItem();
            $cat_id = $tmp->getCatId();
            $standard_id = $tmp->getStandardId();
        } else {
            $cat_id = 0;
            $standard_id = 0;
        }
        
        $result = array();
        //getting category options
        $cats = Mage::getModel('sizes/cat')->getCategories();
        $result['cats'] = '<option ';
        if ($cat_id == 0) {
            $result['cats'] .= 'selected="selected"';
        }
        
        $result['cats'] .= ' value="0">Disable</option>';
        foreach ($cats as $cat) {
            $result['cats'] .= '<option ';
            if ($cat_id == $cat->getCatId()) {
                $result['cats'] .= 'selected="selected"';
            }
            
            $result['cats'] .= ' value="' . $cat->getCatId() . '">' . $cat->getLabel() . '</option>';
        }
        
        //getting standard options
        $result['standards'] = '';
        $standards = Mage::getModel('sizes/standards')->getCollection();
        foreach ($standards as $standard) {
            $result['standards'] .= '<option ';
            if ($standard_id == $standard->getStandardId()) {
                $result['standards'] .= 'selected="selected"';
            }
            
            $result['standards'] .= ' value="' . $standard->getStandardId() . '">' . $standard->getName() . '</option>';
        }
        
        return $result;
    }
    
}