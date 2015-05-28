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
class Belvg_Sizes_Block_Adminhtml_Categories_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $cat_id	= $this->getRequest()->getParam('cat_id');
        $fieldset = $form->addFieldset('code', array('legend'=>Mage::helper('sizes')->__('Code')));
        $fieldset2 = $form->addFieldset('labels', array('legend'=>Mage::helper('sizes')->__('Labels')));
        $collection = Mage::getModel('sizes/cat')->getCollection();
        $labels = array();
        $stores = Mage::getModel('core/store')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $stores->getSelect()
               ->joinLeft(array('websites' => $prefix . 'core_website'), 'websites.website_id = main_table.website_id', 'websites.name as website_name');
        $stores = $stores->getData();
        
        foreach ($stores as $store) {
            $collection->getSelect()
                       ->joinLeft(array('labels' . $store['store_id'] => $prefix . 'belvg_sizes_cat_labels'), 'labels' . $store['store_id'] . '.cat_id = main_table.cat_id AND labels' . $store['store_id'] . '.store_id=' . $store['store_id'], 'label as label' . $store['store_id']);
        }
        
        $script = "<script type='text/javascript'>
                      var sizesFieldsets = []; 
                      function saveAndContinueEdit(url) {
                          activeTab = $$('.tabs')[0].select('.active')[0].id;
                          editForm.submit(url+'tab/'+activeTab+'/');
                      }
                      function checkSizesValues() {
                          sizesFieldsets.each( function(id){
                              prev_val = 0;
                              $(id).select('input.input-text').each( function(i, n){ 
                                  current_val = parseInt(i.value);
                                  if ((current_val < parseInt(prev_val)) 
                                  || isNaN(current_val)) {
                                      i.value = parseInt(prev_val)+1;
                                      i.addClassName('changed')
                                  }
                                  prev_val = i.value;
                              });
                          }); 
                      };
                      
                      
                      document.observe('dom:loaded', function() {
                          $$('.fieldset input.input-text').each( function(el) {
                              el.observe('change', function() {
                                  el.addClassName('changed');
                              });
                          });
                          var tab = '" . $this->getRequest()->getParam('tab') . "';
                          if (tab) {
                              obj = $(tab);
                              design_tabsJsTabs.setSkipDisplayFirstTab();
                              design_tabsJsTabs.showTabContent(obj);
                          };
                          $$('.sizes-values input.input-text').each( function(el) {
                              el.observe('change', function() {
                                  checkSizesValues();
                              });
                          });
                          checkSizesValues();
                      });
                  </script>";
                  
        $labels = $collection->addFieldToFilter('main_table.cat_id', $cat_id)->getLastItem()->getData();
        if (isset($labels['cat_code'])) {
            $tmp_value = $labels['cat_code'];
        } else {
            $tmp_value = '';
        }

        $fieldset->addField('cat_code', 'text', array(
            'label'     => Mage::helper('sizes')->__('Category Code'),
            'class'     => 'validate-code',
            'required'  => TRUE,
            'name'      => 'cat_code',
            'onclick'   => '',
            'onchange'  => '',
            'style'     => '',
            'value'     => $tmp_value,
            'disabled'  => FALSE,
            'tabindex'  => 1,
            'width'    => '50px'
        ));

        if (isset($labels['sort_order'])) {
            $tmp_value = $labels['sort_order'];
        } else {
            $tmp_value = '';
        }
        
        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('sizes')->__('Sort Order'),
            'class'     => 'validate-digits',
            'required'  => FALSE,
            'name'      => 'sort_order',
            'onclick'   => '',
            'onchange'  => '',
            'style'     => '',
            'value'     => $tmp_value,
            'disabled'  => FALSE,
            'after_element_html' => $script,
            'tabindex'  => 1,
            'width'    => '50px'
        ));
        
        foreach ($stores as $store) {
            if ($store['store_id'] == 0) {
                $tmp = array( 'required' => TRUE, 'class' => 'required-entry');
            } else {
                $tmp = array( 'required' => FALSE, 'class' => '');
            }

            if (isset($labels['label' . $store['store_id']])) {
                $tmp_value = $labels['label' . $store['store_id']];
            } else {
                $tmp_value = '';
            }

            $fieldset2->addField('label' . $store['store_id'], 'text', array(
                'label'     => $store['website_name'] . ' / ' . $store['name'],
                'class'     => 'validate-no-html-tags ' . $tmp['class'],
                'required'  => $tmp['required'],
                'name'      => 'label[' . $store['store_id'] . ']',
                'onclick'   => '',
                'onchange'  => '',
                'style'     => '',
                'value'     => $tmp_value,
                'disabled'  => FALSE,
                'after_element_html' => '<small></small>',
                'tabindex'  => 1,
                'width'    => '50px'
            ));
        }
        
        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }
    
}