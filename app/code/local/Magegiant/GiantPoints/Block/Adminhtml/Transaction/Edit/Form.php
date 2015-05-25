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
class Magegiant_GiantPoints_Block_Adminhtml_Transaction_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', array(
                    'id' => $this->getRequest()->getParam('id'),
                )),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getGiantPointsData()) {
            $model = new Varien_Object(Mage::getSingleton('adminhtml/session')->getGiantPointsData());
            Mage::getSingleton('adminhtml/session')->setGiantPointsData(null);
        } elseif (Mage::registry('transaction_data')) {
            $model = Mage::registry('transaction_data');
        }
        $fieldset = $form->addFieldset('transaction_form', array(
            'legend' => Mage::helper('giantpoints')->__('Transaction information')
        ));

        if ($model->getId()) {
            $fieldset->addField('comment', 'note', array(
                'label' => Mage::helper('giantpoints')->__('Comment'),
                'text'  => $model->getComment(),
            ));

            $fieldset->addField('customer_email', 'note', array(
                'label' => Mage::helper('giantpoints')->__('Customer Email'),
                'text'  => sprintf('<a target="_blank" href="%s">%s</a>',
                    $this->getUrl('adminhtml/customer/edit', array('id' => $model->getCustomerId())),
                    $model->getCustomerEmail()
                ),
            ));

            try {
                $notice = $model->getNotice();
            } catch (Exception $e) {
                Mage::helper('giantpoints')->log('Exception: ' . $e->getMessage() . ' in ' . __CLASS__ . ' on line ' . __LINE__);
            }
            if ($notice) {
                $fieldset->addField('notice', 'note', array(
                    'label' => Mage::helper('giantpoints')->__('Notice'),
                    'text'  => $notice,
                ));
            }
            $statusHash = $model->getStatusHash();
            $fieldset->addField('status', 'note', array(
                'label' => Mage::helper('giantpoints')->__('Status'),
                'text'  => isset($statusHash[$model->getStatus()])
                        ? '<strong>' . $statusHash[$model->getStatus()] . '</strong>' : '',
            ));
            if ($model->getPointAmount()) {
                $fieldset->addField('point_amount', 'note', array(
                    'label' => Mage::helper('giantpoints')->__('Points'),
                    'text'  => '<strong>' . Mage::helper('giantpoints')->addLabelForPoint(
                            $model->getPointAmount(),
                            $model->getStoreId()
                        ) . '</strong>',
                ));
            }
            if ($model->getPointSpent()) {
                $fieldset->addField('point_spent', 'note', array(
                    'label' => Mage::helper('giantpoints')->__('Point Spent'),
                    'text'  => Mage::helper('giantpoints')->addLabelForPoint(
                            $model->getPointSpend(),
                            $model->getStoreId()
                        ),
                ));
            }
            $fieldset->addField('change_date', 'note', array(
                'label' => Mage::helper('giantpoints')->__('Created time'),
                'text'  => $this->formatTime($model->getCreatedTime(),
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                        true
                    ),
            ));
            if ($model->getExpirationDate()) {
                $fieldset->addField('expiration_date', 'note', array(
                    'label' => Mage::helper('giantpoints')->__('Expire On'),
                    'text'  => $model->getExpirationDate(),
                ));
            }

            $fieldset->addField('store_id', 'note', array(
                'label' => Mage::helper('giantpoints')->__('Store View'),
                'text'  => Mage::app()->getStore($model->getStoreId())->getName(),
            ));

            return parent::_prepareForm();
        }

        $fieldset->addField('customer_email', 'text', array(
            'label'              => Mage::helper('giantpoints')->__('Customer'),
            'title'              => Mage::helper('giantpoints')->__('Customer'),
            'class'              => 'required-entry',
            'required'           => true,
            'style'              => 'cursor:pointer',
            'name'               => 'customer_email',
            'readonly'           => true,
            'after_element_html' => '</td>
            <td class="label">
            <img id="select_customer" src="' . $this->getSkinUrl('css/magegiant/images/icon-select.png') . '" onclick="showAllRewardCustomer()" title="' . $this->__('Click here to select customer') . '" />'

        ));

        $fieldset->addField('customer_id', 'hidden', array('name' => 'customer_id'));

        $fieldset->addField('point_amount', 'text', array(
            'label'    => Mage::helper('giantpoints')->__('Points'),
            'title'    => Mage::helper('giantpoints')->__('Points'),
            'name'     => 'point_amount',
            'required' => true,
            'class'    => 'validate-number'
        ));

        $fieldset->addField('comment', 'textarea', array(
            'label' => Mage::helper('giantpoints')->__('Comment'),
            'title' => Mage::helper('giantpoints')->__('Comment'),
            'name'  => 'comment',
            'style' => 'height: 5em;'
        ));

        $fieldset->addField('expiration_day', 'text', array(
            'label' => Mage::helper('giantpoints')->__('Points expire after'),
            'title' => Mage::helper('giantpoints')->__('Points expire after'),
            'name'  => 'expiration_day',
            'class' => 'validate-zero-or-greater',
            'note'  => Mage::helper('giantpoints')->__('day(s) since the transaction date. If empty or zero, there is no limitation.')
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);

        return parent::_prepareForm();
    }
}
