<?php
class Belvg_Sizes_Block_Adminhtml_Dimensions_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'dem_id';
        $this->_blockGroup = 'sizes';
        $this->_controller = 'adminhtml_dimensions';
        $this->_mode = 'edit';
        $cat_id = $this->getRequest()->getParam('cat_id');
        $dem_id = $this->getRequest()->getParam('dem_id');
        $this->_removeButton('save', 'label' );
        if (!$dem_id) {
            $this->_removeButton('reset', 'label' );
        }
        
        if ($dem_id) {
            $this->_addButton('delete', array(
                'label'     => Mage::helper('catalog')->__('Delete'),
                'onclick'   => "if(confirm('" . Mage::helper('sizes')->__('Do you really want to delete this item?') . "')) editForm.submit('" . $this->getUrl('*/*/delete', array('cat_id' => $this->getRequest()->getParam('cat_id'), 'dem_id' => $this->getRequest()->getParam('dem_id'))) . "'); else return false;",
                'class' => 'delete'
            ), -1);
        }
        
        $this->_addButton('save', array(
            'label'     => Mage::helper('sizes')->__('Save'),
            'onclick'   => "editForm.submit('" . $this->getUrl('*/*/save', array('cat_id' => $this->getRequest()->getParam('cat_id'), 'dem_id' => $this->getRequest()->getParam('dem_id'))) . "');",
            'class' => 'save'
        ), -1);
    }
    
    public function getHeaderText()
    {
        if ($dem_id = $this->getRequest()->getParam('dem_id')) {
            $tmp = $this->_getDemInfo($dem_id);
            return $tmp['label'] . ' (' . $tmp['code'] . ')';
        } else {
            return Mage::helper('sizes')->__('New Dimension');
        }
    }
    
    protected function _getDemInfo($dem_id)
    {
        
        return array( 'label' => Mage::getModel('sizes/demlabels')->getCollection()
                                                                  ->addFieldToFilter('dem_id', $dem_id)
                                                                  ->addFieldToFilter('store_id', 0)
                                                                  ->getLastItem()->prepareLabel(),
                       'code' => Mage::getModel('sizes/dem')->getCollection()
                                                            ->addFieldToFilter('dem_id', $dem_id)
                                                            ->getLastItem()->getDemCode() );                                                                  
    }
    
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/sizes_categories/edit', array('cat_id' => $this->getRequest()->getParam('cat_id'), 'tab' => 'design_tabs_dimensions'));
    }


    
}