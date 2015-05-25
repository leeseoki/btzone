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
class Belvg_Sizes_Block_Adminhtml_Standards_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'standard_id';
        $this->_blockGroup = 'sizes';
        $this->_controller = 'adminhtml_standards';
        $this->_mode = 'edit';
        $standard_id = $this->getRequest()->getParam('standard_id');
        $this->_removeButton('save', 'label' );
        if (!$standard_id) {
            $this->_removeButton('reset', 'label' );
        }
        
        if ($standard_id) {
            $this->_addButton('delete', array(
                'label'     => Mage::helper('catalog')->__('Delete'),
                'onclick'   => "if(confirm('" . Mage::helper('sizes')->__('Do you really want to delete this item?') . "')) editForm.submit('" . $this->getUrl('*/*/delete', array('standard_id' => $this->getRequest()->getParam('standard_id'))) . "'); else return false;",
                'class' => 'delete'
            ), -1);
        }
        
        $this->_addButton('save_and_edit_button', array(
            'label'     => Mage::helper('sizes')->__('Save and Continue'),
            'onclick'   => "editForm.submit('" . $this->getUrl('*/*/save', array('standard_id' => $this->getRequest()->getParam('standard_id'), 'back' => 'true')) . "');",
            'class' => 'save'
        ), -1);
        $this->_addButton('save', array(
            'label'     => Mage::helper('sizes')->__('Save'),
            'onclick'   => "editForm.submit('" . $this->getUrl('*/*/save', array('standard_id' => $this->getRequest()->getParam('standard_id'))) . "');",
            'class' => 'save'
        ), -1);
    }
    
    public function getHeaderText()
    {
        if ($standard_id = $this->getRequest()->getParam('standard_id')) {
            return Mage::getModel('sizes/standards')->load($standard_id)->getName();
        } else {
            return Mage::helper('sizes')->__('New Standard');
        }
    }
    
}