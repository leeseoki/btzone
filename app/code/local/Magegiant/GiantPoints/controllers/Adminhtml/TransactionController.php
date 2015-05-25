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
 * GiantPoints Transaction Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('giantpoints/transaction')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Transactions Manager'),
                Mage::helper('adminhtml')->__('Transaction Manager')
            );

        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Transaction Manager'));
        $this->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction()
    {
        $transactionId = $this->getRequest()->getParam('id');
        $model         = Mage::getModel('giantpoints/transaction')->load($transactionId);

        if ($model->getId() || $transactionId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('transaction_data', $model);

            $this->loadLayout();

            $this->_setActiveMenu('giantpoints/transaction');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Transactions Manager'),
                Mage::helper('adminhtml')->__('Transaction Manager')
            );

            $this->_title($this->__('Reward Points'))
                ->_title($this->__('Transaction Manager'));
            if ($model->getId()) {
                $this->_title($this->__('Transaction #%s', $model->getId()));
            } else {
                $this->_title($this->__('New Transaction'));
            }

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('giantpoints/adminhtml_transaction_edit'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('giantpoints')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function customerAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    public function customerGridAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * save item action
     */
    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $request  = $this->getRequest();
                $customer = Mage::getModel('customer/customer')->load($request->getPost('customer_id'));
                if (!$customer->getId()) {
                    throw new Exception($this->__('Not found customer to create transaction.'));
                }
                $obj            = new Varien_Object(array(
                    'point_amount'   => $request->getPost('point_amount'),
                    'comment'        => $request->getPost('comment'),
                    'expiration_day' => (int)$request->getPost('expiration_day'),
                ));
                $additionalData = array(
                    'customer'      => $customer,
                    'action_object' => $obj,
                    'notice'        => null,


                );
                $transaction    = Mage::helper('giantpoints/action')->createTransaction(
                    'change_by_admin', $additionalData
                );

                if (!$transaction->getId()) {
                    throw new Exception(
                        $this->__('Cannot create transaction, please recheck form information.')
                    );
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Transaction has been created successfully.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $transaction->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($request->getPost());
                $this->_redirect('*/*/edit', array('id' => $request->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('giantpoints')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * complete giant points transaction
     */
    public function completeAction()
    {
        $transactionId = $this->getRequest()->getParam('id');
        $transaction   = Mage::getModel('giantpoints/transaction')->load($transactionId);
        try {
            $transaction->completeTransaction();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Transaction has been completed successfully.')
            );
            $this->_redirect('*/*/edit', array('id' => $transaction->getId()));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * cancel giant points transaction
     */
    public function cancelAction()
    {
        $transactionId = $this->getRequest()->getParam('id');
        $transaction   = Mage::getModel('giantpoints/transaction')->load($transactionId);
        try {
            $transaction->cancelTransaction();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Transaction has been canceled successfully.')
            );
            $this->_redirect('*/*/edit', array('id' => $transaction->getId()));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * expire giant points transaction
     */
    public function expireAction()
    {
        $transactionId = $this->getRequest()->getParam('id');
        $transaction   = Mage::getModel('giantpoints/transaction')->load($transactionId);
        try {
            $transaction->expireTransaction();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Transaction has been expired successfully.')
            );
            $this->_redirect('*/*/edit', array('id' => $transaction->getId()));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass complete transaction(s) action
     */
    public function massCompleteAction()
    {
        $tranIds = $this->getRequest()->getParam('transactions');
        if (!is_array($tranIds) || !count($tranIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            $collection = Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('point_amount', array('gt' => 0))
                ->addFieldToFilter('status', array(
                    'lt' => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED
                ))
                ->addFieldToFilter('transaction_id', array('in' => $tranIds));
            $total      = 0;
            foreach ($collection as $transaction) {
                try {
                    $transaction->completeTransaction();
                    $total++;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            if ($total > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d transaction(s) were successfully completed', $total)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('No transaction was completed')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass cancel transaction(s) action
     */
    public function massCancelAction()
    {
        $tranIds = $this->getRequest()->getParam('transactions');
        if (!is_array($tranIds) || !count($tranIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            $collection = Mage::getResourceModel('giantpoints/transaction_collection')
                ->addFieldToFilter('point_amount', array('gt' => 0))
                ->addFieldToFilter('status', array(
                    'lteq' => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED
                ))
                ->addFieldToFilter('transaction_id', array('in' => $tranIds));
            $total      = 0;
            foreach ($collection as $transaction) {
                try {
                    $transaction->cancelTransaction();
                    $total++;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            if ($total > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d transaction(s) were successfully canceled', $total)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('No transaction was canceled')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass expire selected transaction(s)
     */
    public function massExpireAction()
    {
        $tranIds = $this->getRequest()->getParam('transactions');
        if (!is_array($tranIds) || !count($tranIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            $curZendDate = new Zend_Date();
            $curDateTime = $curZendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $collection  = Mage::getResourceModel('giantpoints/transaction_collection')
                ->addAvailableBalanceFilter()
                ->addFieldToFilter('status', array(
                    'lteq' => Magegiant_GiantPoints_Model_Transaction::STATUS_COMPLETED
                ))
                ->addFieldToFilter('expiration_date', array('notnull' => true))
                ->addFieldToFilter('expiration_date', array('lteq' => $curDateTime))
                ->addFieldToFilter('transaction_id', array('in' => $tranIds));
            $total       = 0;
            foreach ($collection as $transaction) {
                try {
                    $transaction->expireTransaction();
                    $total++;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            if ($total > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d transaction(s) were successfully expired', $total)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('No transaction was expired')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Reset all transaction
     */
    public function resetTransactionsAction()
    {
        try {

            $transactionTableName = Mage::getSingleton('core/resource')->getTableName('giantpoints/transaction');
            $customerTableName    = Mage::getSingleton('core/resource')->getTableName('giantpoints/customer');
            $write                = Mage::getSingleton('core/resource')->getConnection('core_write');

            $write->truncate($transactionTableName);
            $write->exec("UPDATE `{$customerTableName}` SET `point_balance`=0,`point_spent`=0 WHERE 1");
            echo 'success';
        } catch (Exception $e) {
            echo 'error';
        }
        return $this;
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        $fileName = 'giantpoints_transaction.csv';
        $content  = $this->getLayout()
            ->createBlock('giantpoints/adminhtml_transaction_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * transaction history grid action
     */
    public function transactionHistoryGridAction()
    {
        $customerId   = $this->getRequest()->getParam('id', 0);
        $historyTable = $this->getLayout()
            ->createBlock(
                'giantpoints/adminhtml_customer_edit_tabs_giantpoints_history_grid', '', array('customer_id' => $customerId)
            );
        $this->getResponse()->setBody($historyTable->toHtml());
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        $fileName = 'giantpoints_transaction.xml';
        $content  = $this->getLayout()
            ->createBlock('giantpoints/adminhtml_transaction_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('giantpoints/transaction');
    }
}
