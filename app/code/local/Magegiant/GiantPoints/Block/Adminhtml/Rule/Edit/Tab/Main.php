<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Adminhtml Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Rule_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('behavior_rule_data');

        $form = new Varien_Data_Form ();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('giantpoints')->__('General Information')));

        if ($model->getId()) {
            $fieldset->addField('behavior_rule_id', 'hidden', array('name' => 'behavior_rule_id'));
        }

        $fieldset->addField('product_ids', 'hidden', array('name' => 'product_ids'));

        $fieldset->addField('name', 'text', array(
                'name'     => 'name',
                'label'    => Mage::helper('giantpoints')->__('Rule Name'),
                'title'    => Mage::helper('giantpoints')->__('Rule Name'),
                'required' => true
            )
        );

        $fieldset->addField('description', 'textarea', array(
                'name'  => 'description',
                'label' => Mage::helper('giantpoints')->__('Description'),
                'title' => Mage::helper('giantpoints')->__('Description'),
                'style' => 'height: 100px;'
            )
        );

        $fieldset->addField('is_active', 'select', array(
                'label'   => Mage::helper('giantpoints')->__('Status'),
                'title'   => Mage::helper('giantpoints')->__('Status'),
                'name'    => 'is_active', 'required' => true,
                'options' => array(
                    '1' => Mage::helper('giantpoints')->__('Active'),
                    '0' => Mage::helper('giantpoints')->__('Inactive')
                )
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'     => 'website_ids[]',
                'label'    => Mage::helper('giantpoints')->__('Websites'),
                'title'    => Mage::helper('giantpoints')->__('Websites'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray()));
        } else {
            $fieldset->addField('website_ids', 'hidden', array(
                    'name'  => 'website_ids[]',
                    'value' => Mage::app()->getStore(true)->getWebsiteId()
                )
            );
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }

        $element = $fieldset->addField('sort_order', 'text', array(
                'name'  => 'sort_order',
                'label' => Mage::helper('giantpoints')->__('Priority')
            )
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

}