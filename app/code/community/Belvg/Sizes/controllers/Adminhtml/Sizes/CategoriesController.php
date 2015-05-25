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
class Belvg_Sizes_Adminhtml_Sizes_CategoriesController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Check ACL rules
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/sizes/sizes_categories');
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
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Categories'));
        $this->_addContent($this->getLayout()->createBlock('sizes/adminhtml_categories'));
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
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('sizes')->__('Sizes - Categories Editor'));
        $this->renderLayout();
    }
    
    /**
     * Save operation
     * @return void
     */
    public function saveAction()
    {
        $helper = Mage::helper('sizes');
        $cat_id = $this->getRequest()->getParam('cat_id');
        $redirect = FALSE;
        $back = $this->getRequest()->getParam('back');
      
        //saving to sizes/cat
        $general = $this->getRequest()->getParam('general');
        $data = array();
        if ($cat_id) {
            $data['cat_id'] = $cat_id;
        }
        
        $data['cat_code'] = $general['cat_code'];
        $data['sort_order'] = (int)$general['sort_order'];
        if ($dem = $this->getRequest()->getParam('dem')) {
            foreach ($dem as $key=>$item) { 
                if (!(int)$item) {
                    unset($dem[$key]);
                }
            }
            
            $data['dim_ids'] = implode(',', $dem);
        } else {
            $data['dim_ids'] = FALSE;
        }
        
        try {
            Mage::getModel('sizes/cat')->setData($data)->save();            
        } catch (Exception $e) {
            $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/cat)');
            $redirect = TRUE;  
        }
        
        if (!$cat_id) {
            $cat_id = Mage::getModel('sizes/cat')->getCollection()->getLastItem()->getId();
        }
        
        //saving to sizes/cat_labels
        if (!$redirect) {
            $data = array();
            foreach ($general['label'] as $key=>$label) {
                $tmp = Mage::getModel('sizes/catlabels')->getCollection()
                                                        ->addFieldToFilter('cat_id', $cat_id)
                                                        ->addFieldToFilter('store_id', $key)
                                                        ->getLastItem()->getId();
                if ($tmp) {
                    $data['id'] = $tmp;
                } else {
                    unset($data['id']);
                }
                
                $data['cat_id'] = $cat_id; 
                $data['store_id'] = $key;
                $data['label'] = $label;
                if ($label) {
                    try {
                        Mage::getModel('sizes/catlabels')->setData($data)->save();
                    } catch (Exception $e) {
                        $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/cat_labels)');
                        $redirect = TRUE;
                    }
                } elseif ($tmp) {
                    try {
                        Mage::getModel('sizes/catlabels')->setId($tmp)->delete();
                    } catch (Exception $e) {
                        $this->_getSession()->addError('There are an errors during saving of data to DB (sizes/cat_labels)');
                        $redirect = TRUE;
                    }
                }
            }
        }
        
        //saving to sizes/main
        $main = $this->getRequest()->getParam('main');
        if ($main) {
            if (!$redirect) {
                $data = array();
                $data['cat_id'] = $cat_id;
                foreach ($main as $key1=>$item1) {
                    $data['dem_id'] = $key1;
                    foreach ($item1 as $key2=>$item2) {
                        $data['value_id'] = $key2; 
                        $data['min'] = (int)$item2[0];
                        $data['max'] = (int)$item2[1];
                        $tmp = Mage::getModel('sizes/main')->getCollection()
                                                           ->addFieldToFilter('cat_id', $cat_id)
                                                           ->addFieldToFilter('dem_id', $key1)
                                                           ->addFieldToFilter('value_id', $key2)
                                                           ->getLastItem()->getId();
                        if ($tmp) {
                            $data['id'] = $tmp;
                        } else {
                            unset($data['id']);
                        }
                        
                        if ($data['min'] || $data['max']) {
                            try {
                                Mage::getModel('sizes/main')->setData($data)->save();                    
                            } catch (Exception $e) {
                                $this->_getSession()->addError($helper->__('There are an errors during saving of data to DB (sizes/main)'));
                                $redirect = TRUE;
                            }
                        }
                    }
                }
            }
        }
        
        //image uploading
        if (isset($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $key=>$file) {
                $extensions = $helper->getImageExtensions();
                if (isset($file['name']) && $redirect == FALSE) {
                    if (!empty($file['name'])) { 
                        $tmp = explode('.', $file['name']);
                        $name = $cat_id . '-' . $key . '.' . $tmp[count($tmp)-1];
                        $uploader = new Varien_File_Uploader($key);
                        $uploader->setAllowedExtensions($extensions);
                        $path = Mage::getBaseDir('media') . DS . $helper->getMediaDir();
                        foreach ($extensions as $ext) {
                            if (is_file($tmp = $path . '/' . $cat_id . '-' . $key . '.' . $ext)) {
                                unlink($tmp); //print_r($tmp);
                            }
                        }

                        try {
                            $uploader->save($path, $name);	
                        } catch (Exception $e) {
                            $this->_getSession()->addError($helper->__('There are an errors during saving of image'));
                            $redirect = TRUE;					
                        };
                    }
                };
            }
        }
        
        //final redirect
        if (!$redirect) {
            $this->_getSession()->addSuccess('Sizes category (' . $general['cat_code'] . ') has been saved successfuly');
            if ($back) {
                $tab = $this->getRequest()->getParam('tab');
                $this->_redirect('*/*/edit', array('cat_id' => $cat_id, 'tab' => $tab));
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/edit', array('cat_id' => $cat_id));
        }
    }
    
    
    /**
     * Deleting current element from db
     * @return void
     */
    public function deleteAction()
    {
        $cat_id = (int)$this->getRequest()->getParam('cat_id');
        if (!empty($cat_id)) {
            try {
                Mage::getModel('sizes/cat')->setId($cat_id)->delete();
                $this->_getSession()->addSuccess('Sizes category has been deleted successfuly');
            } catch (Exception $e) {
                $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/main)');
            }
        } else {
            $this->_getSession()->addError('There are an errors during deleting of item from DB (sizes/main)');
        }
        
        $this->_redirect('*/*/');
    }
    
}