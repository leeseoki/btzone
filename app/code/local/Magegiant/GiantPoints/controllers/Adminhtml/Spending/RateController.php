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
 * Giantpoints Adminhtml Controller
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Adminhtml_Spending_RateController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('giantpoints/spending/spending_rate')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Spending Rates'),
                Mage::helper('adminhtml')->__('Spending Rate')
            );

        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Spending Rate'));
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
        $rateId = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('giantpoints/rate')->load($rateId);

        if ($model->getId() || $rateId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('points_rate_data', $model);

            $this->loadLayout();

            $this->_setActiveMenu('giantpoints/spending');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Spending Rates'),
                Mage::helper('adminhtml')->__('Spending Rate')
            );
            $this->_title($this->__('Reward Points'))
                ->_title($this->__('Spending Rate'));
            if ($model->getId()) {
                $this->_title($this->__('Edit Spending Rate #%s', $model->getId()));
            } else {
                $this->_title($this->__('New Spending Rate'));
            }

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('giantpoints/adminhtml_spending_rate_edit'));
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

    /**
     * save item action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('giantpoints/rate');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('giantpoints')->__('Spending rate was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('giantpoints')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete action
     */
    public function massDeleteAction()
    {
        try {
            $rates = $this->getRequest()->getParam('rate_ids');
            foreach ($rates as $rate) {
                Mage::getModel('giantpoints/rate')->load($rate)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('giantpoints')->__('Total of %d record(s) were successfully removed', count($rates))
            );
        } catch (Exception $exc) {
            Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('giantpoints/rate');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Spending rate was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('giantpoints/spending/spending_rate');
    }
}