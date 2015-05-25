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
class Magegiant_GiantPoints_Block_Adminhtml_Transaction_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'giantpoints';
        $this->_controller = 'adminhtml_transaction';
        $this->_removeButton('delete');

        $transaction = Mage::registry('transaction_data');
        if ($transaction && $transaction->getId()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            if ($transaction->getPointAmount() <= 0) {
                return;
            }
            $this->_formScripts[] = "
                function confirmSetLocation(url) {
                    if (confirm('{$this->jsQuoteEscape(
                $this->__('This action can not be restored. Are you sure?')
            )}')) {
                        setLocation(url);
                    }
                }
            ";

            if ($transaction->getStatus() <= Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED
                && $transaction->getExpirationDate()
                && strtotime($transaction->getExpirationDate()) < time()
                && $transaction->getPointAmount() > $transaction->getPointUsed()
            ) {
                $this->_addButton('expire_transaction', array(
                    'label'   => Mage::helper('giantpoints')->__('Expire'),
                    'onclick' => "confirmSetLocation('{$this->getUrl('*/*/expire', array(
                            'id' => $transaction->getId()
                        ))}')",
                    'class'   => 'delete',
                ));
            }
            if ($transaction->getStatus() < Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED) {
                $this->_addButton('cancel_transaction', array(
                    'label'   => Mage::helper('giantpoints')->__('Cancel'),
                    'onclick' => "confirmSetLocation('{$this->getUrl('*/*/cancel', array(
                            'id' => $transaction->getId()
                        ))}')",
                    'class'   => 'delete',
                ));
                $this->_addButton('complete_transaction', array(
                    'label'   => Mage::helper('giantpoints')->__('Complete'),
                    'onclick' => "confirmSetLocation('{$this->getUrl('*/*/complete', array(
                            'id' => $transaction->getId()
                        ))}')",
                    'class'   => 'save',
                ));

                return;
            }
            $rewardAccount = $transaction->getRewardCustomer();
            if ($transaction->getStatus() == Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED
                && $transaction->getPointAmount() <= $rewardAccount->getPointBalance()
            ) {
                $this->_addButton('cancel_transaction', array(
                    'label'   => Mage::helper('giantpoints')->__('Cancel'),
                    'onclick' => "confirmSetLocation('{$this->getUrl('*/*/cancel', array(
                            'id' => $transaction->getId()
                        ))}')",
                    'class'   => 'delete',
                ));
            }

            return;
        }

        $this->_updateButton('save', 'label', Mage::helper('giantpoints')->__('Save Transaction'));
        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('giantpoints')->__('Save And Continue View'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function showAllRewardCustomer() {
            var url= '" . $this->getUrl('*/*/customer', array('_current' => true)) . "';
                 new Ajax.Request(url, {
                            method: 'post',
                            parameters: {
                                form_key: FORM_KEY,
                                 selected_customer_id: $('customer_id').value || 0
                            },
                            evalScripts: true,
                           onSuccess: function(transport) {
                                TINY2.box.show({boxid:'customer_popup',fixed:false,width:650,height:450,animate:false})
                                $('customer_popup').update(transport.responseText);

                            }
                        });
           }
           document.observe('dom:loaded', function(){
              $('customer_email').observe('click',function(){showAllRewardCustomer()});
           });
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('transaction_data') && Mage::registry('transaction_data')->getId()) {
            return Mage::helper('giantpoints')->__("Transaction #%s", Mage::registry('transaction_data')->getId());
        }

        return Mage::helper('giantpoints')->__('Add Transaction');
    }
}
