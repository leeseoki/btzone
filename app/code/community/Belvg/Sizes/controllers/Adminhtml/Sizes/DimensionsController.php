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
class Belvg_Sizes_Adminhtml_Sizes_DimensionsController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Check ACL rules
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/sizes/sizes_dimensions');
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
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Dimensions'));
        $this->_addContent($this->getLayout()->createBlock('sizes/adminhtml_dimensions'));
        $this->renderLayout();
    }
    
    /**
     * New category creation
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
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Dimensions Editor'));
        $this->renderLayout();
    }
    
    /**
     * Save operation
     * @return void
     */
    public function saveAction()
    {
        $cat_id = $this->getRequest()->getParam('cat_id');
        $dem_id = $this->getRequest()->getParam('dem_id');
        $redirect = FALSE;
        $back = $this->getRequest()->getParam('back');
      
        //saving to sizes/dem
        $general = $this->getRequest()->getParam('general');
        $data = array();
        if ($dem_id) {
            $data['dem_id'] = $dem_id;
        }
        
        $data['dem_code'] = $general['dem_code'];
        $data['sort_order'] = (int)$general['sort_order'];
        try {
            Mage::getModel('sizes/dem')->setData($data)->save();            
        } catch (Exception $e) {
            $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/dem)');
            $redirect = TRUE;  
        }
        
        if (!$dem_id) {
            $dem_id = Mage::getModel('sizes/dem')->getCollection()->getLastItem()->getId();
        }
        
        //saving to sizes/dem_labels
        if (!$redirect) {
            $data = array();
            foreach ($general['label'] as $key=>$label) {
                $tmp = Mage::getModel('sizes/demlabels')->getCollection()
                                                        ->addFieldToFilter('dem_id', $dem_id)
                                                        ->addFieldToFilter('store_id', $key)
                                                        ->getLastItem()->getId();
                if ($tmp) {
                    $data['id'] = $tmp;
                } else {
                    unset($data['id']);
                }
                
                $data['dem_id'] = $dem_id; 
                $data['store_id'] = $key;
                $data['label'] = $label;
                if ($label) {
                    try {
                        Mage::getModel('sizes/demlabels')->setData($data)->save();
                    } catch (Exception $e) {
                        $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/dem_labels)');
                        $redirect = TRUE;
                    }
                } elseif ($tmp) {
                    try {
                        Mage::getModel('sizes/demlabels')->setId($tmp)->delete();
                    } catch (Exception $e) {
                        $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/dem_labels)');
                        $redirect = TRUE;
                    }
                }
            }
        }
        
        //final redirect
        if (!$redirect) {
            $this->_getSession()->addSuccess('Sizes dimension (' . $general['dem_code'] . ') has been saved successfuly');
            $this->_redirect('*/sizes_categories/edit', array('cat_id' => $cat_id, 'tab' => 'design_tabs_dimensions'));
        } else {
            $this->_redirect('*/*/edit', array('dem_id' => $dem_id, 'cat_id' => $cat_id));
        }
    }
    
    
    /**
     * Deleting current element from db
     * @return void
     */
    public function deleteAction()
    {
        $dem_id = (int)$this->getRequest()->getParam('dem_id');
        if (!empty($dem_id)) {
            try {
                Mage::getModel('sizes/dem')->setId($dem_id)->delete();
                $this->_getSession()->addSuccess('Sizes dimension has been deleted successfuly');
            } catch (Exception $e) {
                $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/dem)');
            }
        } else {
            $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/dem)');
        }
        
        $cat_id = (int)$this->getRequest()->getParam('cat_id');
        $this->_redirect('*/sizes_categories/edit', array('cat_id' => $this->getRequest()->getParam('cat_id'), 'tab' => 'design_tabs_dimensions'));
    }
    
}