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
 * Giantpoints Edit Block
 *
 * @category     Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Earning_Rate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'giantpoints';
        $this->_controller = 'adminhtml_earning_rate';

        $this->_updateButton('save', 'label', Mage::helper('giantpoints')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('giantpoints')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('adminhtml')->__('Save And Continue'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('giantpoints_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'giantpoints_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'giantpoints_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('points_rate_data')
            && Mage::registry('points_rate_data')->getId()
        ) {
            return Mage::helper('giantpoints')->__("Edit Earning Rate #%s" .
                "",
                $this->htmlEscape(Mage::registry('points_rate_data')->getId())
            );
        }

        return Mage::helper('giantpoints')->__('Add Earning Rate');
    }
}