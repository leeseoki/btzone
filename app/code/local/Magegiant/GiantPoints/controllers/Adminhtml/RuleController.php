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
class Magegiant_GiantPoints_Adminhtml_RuleController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        if (!$this->_isEnabled()) {
            $this->_redirect('adminhtml/system_config/edit/', array('section' => 'giantpoints'));

            return;
        }
        $this->loadLayout()
            ->_setActiveMenu('giantpoints/earning/behavior/special_rule')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Behavior Rules'),
                Mage::helper('adminhtml')->__('Behavior Rules')
            );

        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Behavior Rules'));
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
        if (!$this->_isEnabled()) {
            $this->_redirect('adminhtml/system_config/edit/', array('section' => 'giantpoints'));

            return;
        }
        $rateId = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('giantpoints/rule')->load($rateId);

        if ($model->getId() || $rateId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('behavior_rule_data', $model);

            $this->loadLayout();

            $this->_setActiveMenu('giantpoints/earning');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Earning Rates'),
                Mage::helper('adminhtml')->__('Earning Rate')
            );
            $this->_title($this->__('Reward Points'))
                ->_title($this->__('Behavior Rule'));
            if ($model->getId()) {
                $this->_title($this->__('Edit Rule #%s', $model->getId()));
            } else {
                $this->_title($this->__('New Rule'));
            }

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('giantpoints/adminhtml_rule_edit'))
                ->_addLeft($this->getLayout()->createBlock('giantpoints/adminhtml_rule_edit_tabs'));;
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
            $model = Mage::getModel('giantpoints/rule');
            if (is_array($data ['customer_group_ids'])) {
                $data ['customer_group_ids'] = implode(',', $data ['customer_group_ids']);
            }
            if (is_array($data ['website_ids'])) {
                $data ['website_ids'] = implode(',', $data ['website_ids']);
            }
            if (!$data['is_onhold_enabled']) {
                $data['onhold_duration'] = 0;
            }
            if (isset ($data ['rule'] ['actions'])) {
                $data ['actions'] = $data ['rule'] ['actions'];
            }
            if (array_key_exists('simple_action', $data) && $data['simple_action'] != 'by_percent') {
                $data['point_amount'] = floor($data['point_amount']);
            }
            try {
                Mage::dispatchEvent('giantpoints_behavior_rule_controller_save_before', array(
                    'rule' => $data
                ));
                $model->loadPost($data);
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $model->save();
                $data['behavior_rule_id'] = $model->getId();
                Mage::dispatchEvent('giantpoints_behavior_rule_controller_save_after', array(
                    'rule' => $data
                ));
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('giantpoints')->__('Behavior earning rule was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array(
                            'id'       => $model->getId(),
                            '_current' => true
                        )
                    );

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
            $rules = $this->getRequest()->getParam('rule', array());
            foreach ($rules as $rule) {
                Mage::getModel('giantpoints/rule')->load($rule)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('giantpoints')->__('Total of %d record(s) were successfully removed', count($rules))
            );
        } catch (Exception $exc) {
            Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function massActivateAction()
    {
        try {
            $rulesToDelete = $this->getRequest()->getParam('rule', array());
            foreach ($rulesToDelete as $ruleToDelete) {
                Mage::getModel('giantpoints/rule')->load($ruleToDelete)->setData('is_active', 1)->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Successfully changed'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('advancednewsletter')->__('Unable to find item to save')
            );
        }
        $this->_redirect('*/*/index');
    }

    public function massInactivateAction()
    {
        try {
            $rulesToDelete = $this->getRequest()->getParam('rule');
            foreach ($rulesToDelete as $ruleToDelete) {
                Mage::getModel('giantpoints/rule')->load($ruleToDelete)->setData('is_active', 0)->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Successfully changed'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('advancednewsletter')->__('Unable to find item to save')
            );
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
                $model = Mage::getModel('giantpoints/rule');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Earning rate was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsBehavior')
        || Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsMilestone');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('giantpoints/earning/earning/behavior/specialrule');
    }
}