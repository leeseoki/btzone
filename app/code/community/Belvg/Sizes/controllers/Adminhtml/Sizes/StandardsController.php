<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /****************************************
 *    MAGENTO EDITION USAGE NOTICE       *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /****************************************
 *    DISCLAIMER                         *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    v1.0.0
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Adminhtml_Sizes_StandardsController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Check ACL rules
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/sizes/sizes_standards');
    }
    
    /**
     * Index action
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Standards'));
        $this->_addContent($this->getLayout()->createBlock('sizes/adminhtml_standards'));
        $this->renderLayout();
    }
    
    /**
     * New standard creation
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    /**
     * Editing existing category
     * @return void
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Standard Editor'));
        $this->renderLayout();
    }
    
    /**
     * Save operation
     * @return void
     */
    public function saveAction()
    {
        $standard_id = $this->getRequest()->getParam('standard_id');
        $redirect = FALSE;
        $back = $this->getRequest()->getParam('back');
        $params = $this->getRequest()->getParam('standard');
        
        //saving of standard name
        $data = array();
        if ($standard_id) {
            $data['standard_id'] = $standard_id;
        }
        
        $data['name'] = $params['standard_name'];
        try {
            Mage::getModel('sizes/standards')->setData($data)->save();            
        } catch (Exception $e) {
            $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/standards)');
            $redirect = TRUE;  
        }
        
        if (!$standard_id) {
            $standard_id = Mage::getModel('sizes/standards')->getCollection()->getLastItem()->getId();
        }
        
        //saving of standard values
        if (!$redirect) {
            $data = array();
            if (isset($params['values'])) {
                foreach ($params['values']['value'] as $key=>$item) {
                    if (isset($params['values']['delete'][$key])) {
                        try {
                            Mage::getModel('sizes/standardsvalues')->setId($key)->delete();
                        } catch (Exception $e) {
                            $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/standardsvalues)');
                            $redirect = TRUE;
                        }
                    } else {
                        if ($key > 0) {
                            $data['value_id'] = $key;
                        } else {
                            unset($data['value_id']);
                        }
                        
                        $data['standard_id'] = $standard_id;
                        $data['value'] = $item;
                        $data['sort_order'] = (int)$params['values']['sort_order'][$key];
                        try {
                            Mage::getModel('sizes/standardsvalues')->setData($data)->save();            
                        } catch (Exception $e) {
                            $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/standardsvalues)');
                            $redirect = TRUE;  
                        }
                    }
                }
            }
        }
     
        //final redirect
        if (!$redirect) {
            $this->_getSession()->addSuccess('Standard (' . $params['standard_name'] . ') has been saved successfuly');
            if ($back) {
                $this->_redirect('*/*/edit', array('standard_id' => $standard_id));
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/edit', array('standard_id' => $standard_id));
        }
    }
    
    /**
     * Deleting current element from db
     * @return void
     */
    public function deleteAction()
    {
        $standard_id = (int)$this->getRequest()->getParam('standard_id');
        if (!empty($standard_id)) {
            try {
                Mage::getModel('sizes/standards')->setId($standard_id)->delete();
                $this->_getSession()->addSuccess('Sizes standard has been deleted successfuly');
            } catch (Exception $e) {
                $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/standards)');
            }
        } else {
            $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/standards)');
        }
        
        $this->_redirect('*/*/');
    }
    
}