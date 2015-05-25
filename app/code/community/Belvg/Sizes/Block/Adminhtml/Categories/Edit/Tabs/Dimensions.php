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
class Belvg_Sizes_Block_Adminhtml_Categories_Edit_Tabs_Dimensions extends Mage_Adminhtml_Block_Widget_Grid
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('statusGrid');
        $this->setDefaultSort('main_table.dem_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(TRUE);
        $this->setPagerVisibility(FALSE);
        $this->setFilterVisibility(FALSE);
        $this->setHeadersVisibility(TRUE);
        
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sizes/dem')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $collection->getSelect()
                   ->joinLeft(array('labels' => $prefix . 'belvg_sizes_dem_labels'), 'labels.dem_id = main_table.dem_id AND labels.store_id=0');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();	
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'       => 'checkbox',
            'values'     => $this->_getSelectedDem(), 
            'align'      => 'center',
            'index'      => 'dem_id',
            'field_name' => 'dem[]',
            'filter'     => FALSE,
            'sortable'   => FALSE
        ));
        
        $this->addColumn('dem_id', array(
            'header'    => $this->__('Id'),
            'align'     => 'center',
            'width'     => '40px',
            'index'     => 'dem_id',
            'filter_index' => 'main_table.dem_id',
            'sortable'   => FALSE
        ));
        
        $this->addColumn('dem_code', array(
            'header'    => $this->__('Dimension Code'),
            'align'     => 'left',			
            'index'     => 'dem_code',
            'sortable'   => FALSE
        ));
        
        $this->addColumn('label', array(
            'header'    => $this->__('Default Label'),
            'align'     => 'left',			
            'index'     => 'label',
            'sortable'   => FALSE
        ));
        
        $this->addColumn('sort_order', array(
            'header'    => $this->__('Sort Order'),
            'align'     => 'left',			
            'index'     => 'sort_order',
            'sortable'   => FALSE
        ));
        
        return parent::_prepareColumns();
    }
    
    protected function _getSelectedDem()
    {
        return explode(',', Mage::getModel('sizes/cat')->getCollection()
                                ->addFieldToFilter('cat_id', $this->getRequest()->getParam('cat_id'))
                                ->getLastItem()
                                ->getDimIds());
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/sizes_dimensions/edit', array('dem_id' => $row->getDemId(), 'cat_id' => $this->getRequest()->getParam('cat_id')));
        return FALSE;
    }
    
}