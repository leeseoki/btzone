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
class Magegiant_GiantPoints_Block_Adminhtml_Customer_Edit_Tabs_Giantpoints_Notifications extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setData('customer', Mage::registry('current_customer'));
        $this->addData(
            Mage::getModel('giantpoints/customer')->getAccountByCustomer($this->getCustomer())->getData()
        );
    }

    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_giantpoints_notification');

        $fieldset = $form->addFieldset(
            'notification_fieldset', array('legend' => Mage::helper('giantpoints')->__('Reward Points Notification'))
        );

        $fieldset->addField(
            'notification_update',
            'checkbox',
            array(
                'label'   => Mage::helper('giantpoints')->__('Subscribe to balance update'),
                'name'    => 'notification_update',
                'id'      => 'notification_update',
                'value'   => 1,
                'checked' => (bool)(int)$this->getNotificationUpdate(),
            )
        );

        $fieldset->addField(
            'notification_expire',
            'checkbox',
            array(
                'label'   => Mage::helper('giantpoints')->__('Subscribe to points expiration notification'),
                'name'    => 'notification_expire',
                'id'      => 'notification_expire',
                'value'   => 1,
                'checked' => (bool)(int)$this->getNotificationExpire(),
            )
        );

        $this->setForm($form);

        return $this;
    }
}