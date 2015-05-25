<?php
class Belvg_Sizes_Block_Adminhtml_Categories_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'cat_id';
        $this->_blockGroup = 'sizes';
        $this->_controller = 'adminhtml_categories';
        $this->_mode = 'edit';
        $cat_id = $this->getRequest()->getParam('cat_id');
        $this->_removeButton('save', 'label' );
        if (!$cat_id) {
            $this->_removeButton('reset', 'label' );
        }
        
        if ($cat_id) {
            $this->_addButton('delete', array(
                'label'     => Mage::helper('catalog')->__('Delete'),
                'onclick'   => "if(confirm('" . Mage::helper('sizes')->__('Do you really want to delete this item?') . "')) editForm.submit('" . $this->getUrl('*/*/delete', array('cat_id' => $this->getRequest()->getParam('cat_id'))) . "'); else return false;",
                'class' => 'delete'
            ), -1);
            $this->_addButton('add_new_dimension', array(
                'label'     => Mage::helper('sizes')->__('Add New Dimension'),
                'onclick'   => "setLocation('" . $this->getUrl('*/sizes_dimensions/new', array('cat_id' => $this->getRequest()->getParam('cat_id'))) . "');",
                'class' => 'add'
            ), -1);
        }
        
        $this->_addButton('save_and_edit_button', array(
            'label'     => Mage::helper('sizes')->__('Save and Continue'),
            'onclick'   => "saveAndContinueEdit('" . $this->getUrl('*/*/save', array('cat_id' => $this->getRequest()->getParam('cat_id'), 'back' => 'true')) . "');",
            'class' => 'save'
        ), -1);
        
        $this->_addButton('save', array(
            'label'     => Mage::helper('sizes')->__('Save'),
            'onclick'   => "editForm.submit('" . $this->getUrl('*/*/save', array('cat_id' => $this->getRequest()->getParam('cat_id'))) . "');",
            'class' => 'save'
        ), -1);
    }
    
    public function getHeaderText()
    {
        if ($cat_id = $this->getRequest()->getParam('cat_id')) {
            $tmp = $this->_getCatInfo($cat_id);
            return $tmp['label'] . ' (' . $tmp['code'] . ')';
        } else {
            return Mage::helper('sizes')->__('New Category');
        }
    }
    
    protected function _getCatInfo($cat_id)
    {
        
        return array( 'label' => Mage::getModel('sizes/catlabels')->getCollection()
                                                                  ->addFieldToFilter('cat_id', $cat_id)
                                                                  ->addFieldToFilter('store_id', 0)
                                                                  ->getLastItem()->prepareLabel(),
                       'code' => Mage::getModel('sizes/cat')->getCollection()
                                                            ->addFieldToFilter('cat_id', $cat_id)
                                                            ->getLastItem()->getCatCode() );                                                                  
    }
    
}