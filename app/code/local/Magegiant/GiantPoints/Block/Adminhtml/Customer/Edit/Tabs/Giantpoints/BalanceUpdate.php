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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Customer_Edit_Tabs_Giantpoints_BalanceUpdate extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_giantpoints');

        $fieldset = $form->addFieldset(
            'giantpoints_fieldset', array('legend' => Mage::helper('giantpoints')->__('Update Reward Points Balance'))
        );

        $fieldset->addField(
            'change_balance',
            'text',
            array(
                'label' => Mage::helper('giantpoints')->__('Update Points'),
                'name'  => 'change_balance',
                'note'  => Mage::helper('giantpoints')->__('Add or subtract customer\'s balance. For ex: 99 or -99 points.'),
                'class' => 'validate-number',
            )
        );

        $fieldset->addField(
            'giantpoints_comment',
            'text',
            array(
                'label' => Mage::helper('giantpoints')->__('Comment'),
                'name'  => 'giantpoints_comment',
            )
        );
        $fieldset->addField('giantpoints_expiration_day', 'text', array(
            'label' => Mage::helper('giantpoints')->__('Points expire after'),
            'title' => Mage::helper('giantpoints')->__('Points expire after'),
            'name'  => 'giantpoints_expiration_day',
            'class' => 'validate-zero-or-greater',
            'note'  => Mage::helper('giantpoints')->__('day(s) since the transaction date. If empty or zero, there is no limitation.')
        ));
        $this->setForm($form);

        return $this;
    }
}