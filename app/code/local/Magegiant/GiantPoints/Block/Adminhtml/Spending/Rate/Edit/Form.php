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
 * @category     Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright     Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Edit Form Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Spending_Rate_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare form's information for block
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Giantpoints_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('giantpoints');
        $rate   = Mage::registry('points_rate_data');

        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('_current' => true)),
                'method' => 'post',
            )
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array(
                'legend' => $helper->__('Spending Rate Information'),
            )
        );

        $fieldset->addField(
            'website_ids',
            'multiselect',
            array(
                'name'               => 'website_ids',
                'title'              => $helper->__('Website'),
                'label'              => $helper->__('Website'),
                'values'             => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
                'value'              => $rate->getWebsiteIds(),
                'required'           => true,
                'after_element_html' => $helper->addSelectAll('website_ids'),
            )
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionArray();

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            array(
                'name'               => 'customer_group_ids',
                'title'              => $helper->__('Customer Group'),
                'label'              => $helper->__('Customer Group'),
                'values'             => $groups,
                'value'              => $rate->getCustomerGroupIds(),
                'required'           => true,
                'after_element_html' => $helper->addSelectAll('customer_group_ids'),
            )
        );

        $fieldset->addField(
            'direction',
            'hidden',
            array(
                'name'  => 'direction',
                'value' => $this->_getDirection(),
            )
        );

        $ratesRenderer = $this->getLayout()
            ->createBlock('giantpoints/adminhtml_rate_renderer_form_direction')
            ->setDirection($this->_getDirection())
            ->setRate($rate);

        $fieldset->addField(
            'rate_to_currency',
            'note',
            array(
                'title' => $helper->__('Rate'),
                'label' => $helper->__('Rate'),
            )
        )->setRenderer($ratesRenderer);
        $fieldset->addField(
            'priority',
            'text',
            array(
                'name'  => 'priority',
                'title' => $helper->__('Priority'),
                'label' => $helper->__('Priority'),
                'class' => 'validate-zero-or-greater',
                'note'  => $helper->__('Higher priority Rate will be applied first'),
                'value' => $rate->getPriority(),
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getDirection()
    {
        return Magegiant_GiantPoints_Model_Rate::POINT_TO_MONEY;
    }

}