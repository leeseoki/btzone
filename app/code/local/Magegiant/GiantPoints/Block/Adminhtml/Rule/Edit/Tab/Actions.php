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
class Magegiant_GiantPoints_Block_Adminhtml_Rule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('behavior_rule_data');

        $form = new Varien_Data_Form ();

        $form->setHtmlIdPrefix('rule_');

        // Dispatch event
        Mage::dispatchEvent('giantpoints_block_earning_behavior_rule_before', array(
            'actions_block' => $this,
            'rule'          => $model,
            'form'          => $form
        ));

        $fieldset = $form->addFieldset('action_fieldset', array('legend' => Mage::helper('giantpoints')->__('Actions to take')));

        $fieldset->addField('point_action', 'select', array(
            'name'     => 'point_action',
            'label'    => Mage::helper('giantpoints')->__('Action'),
            'required' => true,
            'options'  => Mage::getSingleton('giantpoints/rule_action')->getActionOptionsArray()
        ));

        $fieldset->addField('point_amount', 'text', array(
                'name'     => 'point_amount',
                'required' => true,
                'class'    => 'validate-greater-than-zero',
                'label'    => Mage::helper('salesrule')->__('Fixed Amount')
            )
        );
        $isOnholdEnabledField = $fieldset->addField('is_onhold_enabled', 'select', array(
            'name'               => 'is_onhold_enabled',
            'label'              => $this->__("Keep this transaction is On hold"),
            'after_element_html' => '',
            'options'            => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No')),
            'onchange'           => 'toggleOnholdEnabled(this.value)'
        ));

        $onholdDurationField = $fieldset->addField('onhold_duration', 'text', array(
            'name'  => 'onhold_duration',
            'label' => $this->__("Number of days for transfers to be on hold"),
        ));
        Mage::getSingleton('giantpoints/rule_action')->visitAdminActions($fieldset);
        // Dispatch event
        Mage::dispatchEvent('giantpoints_block_earning_behavior_rule_after', array(
            'actions_block' => $this,
            'rule'          => $model,
            'form'          => $form
        ));


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}