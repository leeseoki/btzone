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
class Magegiant_GiantPoints_Block_Adminhtml_Rule_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('behavior_rule_data');
        $form  = new Varien_Data_Form ();
        $form->setHtmlIdPrefix('rule_');
        Mage::dispatchEvent('giantpoints_rule_condition_from_prepare_before',
            array(
                'form' => $form
            )
        );
        $fieldset = $form->addFieldset('triggers_fieldset', array('legend' => Mage::helper('giantpoints')->__('Events')));
        $ruleElem = $points_conditions_field = $fieldset->addField('points_conditions', 'select', array(
            'label'    => Mage::helper('giantpoints')->__('Customer Action or Event'),
            'name'     => 'points_conditions',
            'options'  => Mage::getSingleton('giantpoints/rule_action')->getOptionsArray(),
            'required' => true,
            'onchange' => 'showNote(this);',
        ));
        $ruleElem->setAfterElementHtml(
            "<script>
                function showNote(elem) {
                   if($('note_points_conditions') != null) {
                      $('note_points_conditions').remove();
                   }
                 }
           </script>"
        );
        Mage::getSingleton('giantpoints/rule_action')->visitAdminTriggers($fieldset);
        $fieldset       = $form->addFieldset('conditions_fieldset', array('legend' => Mage::helper('giantpoints')->__('Conditions')));
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        foreach ($customerGroups as $group) {
            if ($group ['value'] == 0) {
                //Removes the "Not Logged In" option, becasue its redundant for special rules
                array_shift($customerGroups);
            }
        }
        $fieldset->addField('customer_group_ids', 'multiselect', array('name' => 'customer_group_ids[]', 'label' => Mage::helper('giantpoints')->__('Customer Group Is'), 'title' => Mage::helper('giantpoints')->__('Customer Group Is'), 'required' => true, 'values' => $customerGroups));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array('name' => 'from_date', 'label' => Mage::helper('giantpoints')->__('From Date'), 'title' => Mage::helper('giantpoints')->__('From Date'), 'image' => $this->getSkinUrl('images/grid-cal.gif'), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso));
        $fieldset->addField('to_date', 'date', array('name' => 'to_date', 'label' => Mage::helper('giantpoints')->__('To Date'), 'title' => Mage::helper('giantpoints')->__('To Date'), 'image' => $this->getSkinUrl('images/grid-cal.gif'), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso));
        Mage::getSingleton('giantpoints/rule_action')->visitAdminConditions($fieldset);
        $form->setValues($model->getData());
        $this->setForm($form);
        Mage::dispatchEvent('giantpoints_rule_condition_from_prepare_after',
            array(
                'form' => $form
            )
        );

        return parent::_prepareForm();
    }

}