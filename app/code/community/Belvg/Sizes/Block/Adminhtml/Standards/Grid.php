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
class Belvg_Sizes_Block_Adminhtml_Standards_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('sizesGrid');
        $this->setDefaultSort('standard_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(TRUE);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sizes/standards')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('standard_id', array(
            'header'    => $this->__('Id'),
            'align'     => 'center',
            'width'     => '100px',
            'index'     => 'standard_id'
        ));
        $this->addColumn('standard_name', array(
            'header'    => $this->__('Standard Name'),
            'align'     => 'left',
            'width'     => 'auto',
            'index'     => 'name'
        ));
        $this->addColumn('values', array(
            'header'    => $this->__('Values'),
            'align'     => 'left',
            'renderer'  => 'Belvg_Sizes_Block_Adminhtml_Renderer_Values',
            'filter'    => FALSE,
            'width'     => 'auto'
        ));
        $this->addColumn('actions', array(
            'header'    => $this->__('Actions'),
            'align'     => 'center',
            'renderer'  => 'Belvg_Sizes_Block_Adminhtml_Renderer_Actions',
            'filter'    => FALSE,
            'width'     => '100px'
        ));
        
        return parent::_prepareColumns();
    }
    
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('standard_id' => $row->getStandardId()));
    }
    
}