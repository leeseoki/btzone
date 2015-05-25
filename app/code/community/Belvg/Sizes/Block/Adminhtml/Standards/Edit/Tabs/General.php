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
 /****************************************
 *    MAGENTO EDITION USAGE NOTICE       *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /****************************************
 *    DISCLAIMER                         *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    v1.0.0
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Block_Adminhtml_Standards_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $standard_id = $this->getRequest()->getParam('standard_id');
        $fieldset = $form->addFieldset('name', array('legend'=>Mage::helper('sizes')->__('Name')));
        $fieldset2 = $form->addFieldset('values', array('legend'=>Mage::helper('sizes')->__('Values')));
        $standard = Mage::getModel('sizes/standards')->load($standard_id);
        $values = Mage::getModel('sizes/standardsvalues')->getCollection()
                                                         ->addFieldToFilter('standard_id', $standard_id)
                                                         ->setOrder('sort_order', 'asc');
        
        $script = "<script type='text/javascript'>
                       document.observe('dom:loaded', function() { 
                           $$('.fieldset input').each( function(el) {
                               el.observe('change', function() {
                                   el.addClassName('changed');
                               });
                           });
                       });
                   </script>";
        
        $fieldset->addField('standard_name', 'text', array(
            'label'     => Mage::helper('sizes')->__('Standard Name'),
            'class'     => 'validate-alphanum-with-spaces',
            'required'  => TRUE,
            'name'      => 'standard_name',
            'value'     => $standard->getName(),
            'disabled'  => FALSE
        ));
        
        $fieldset2->addType('values', 'Belvg_Sizes_Block_Adminhtml_Form_Element_Values')
                  ->addField('standard_values', 'values', array(
            'label'     => '',
            'class'     => '',
            'required'  => FALSE,
            'name'      => 'values',
            'value'     => $values,
            'disabled'  => FALSE,
            'after_element_html' => $script
        ));
        
        $form->setFieldNameSuffix('standard');
        $this->setForm($form);
    }
    
}