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
 * Giantpoints Grid Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Earning_Rate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('earningrateGrid');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Giantpoints_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('giantpoints/rate_collection')
            ->addFieldToFilter('direction', Magegiant_GiantPoints_Model_Rate::MONEY_TO_POINT);
        $this->setCollection($collection);

        parent::_prepareCollection();
        // Prepare website, customer group for grid
        foreach ($this->getCollection() as $rate) {
            $rate->setData('website_ids', explode(',', $rate->getData('website_ids')));
            $rate->setData('customer_group_ids', explode(',', $rate->getData('customer_group_ids')));
        }

        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Giantpoints_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rate_id', array(
            'header' => Mage::helper('giantpoints')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'rate_id',
            'type'   => 'number',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header'                    => Mage::helper('giantpoints')->__('Website'),
                'align'                     => 'left',
                'width'                     => '200px',
                'type'                      => 'options',
                'options'                   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'index'                     => 'website_ids',
                'filter_condition_callback' => array($this, 'filterCallback'),
                'sortable'                  => false,
            ));
        }

        $this->addColumn('customer_group_ids', array(
            'header'                    => Mage::helper('giantpoints')->__('Customer Groups'),
            'align'                     => 'left',
            'index'                     => 'customer_group_ids',
            'type'                      => 'options',
            'width'                     => '200px',
            'sortable'                  => false,
            'options'                   => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gt' => 0))
                    ->load()
                    ->toOptionHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));
        $this->addColumn('money', array(
            'header'   => Mage::helper('giantpoints')->__('Money spent'),
            'align'    => 'right',
            'index'    => 'money',
            'type'     => 'number',
            'renderer' => 'giantpoints/adminhtml_rate_renderer_grid_money',
        ));

        $this->addColumn('points', array(
            'header' => Mage::helper('giantpoints')->__('Earning Point(s)'),
            'align'  => 'left',
            'index'  => 'points',
            'type'   => 'number',
        ));


        $this->addColumn('priority', array(
            'header' => Mage::helper('giantpoints')->__('Priority'),
            'align'  => 'right',
            'index'  => 'priority'
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('giantpoints')->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('giantpoints')->__('Edit'),
                    'url'     => array('base' => '*/*/edit'),
                    'field'   => 'id'
                )),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));

        Mage::dispatchEvent('giantpoints_adminhtml_earning_rate_grid', array('grid' => $this));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Giantpoints_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rate_id');
        $this->getMassactionBlock()->setFormFieldName('rate_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('giantpoints')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('giantpoints')->__('Are you sure?')
        ));

        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * process filter call back
     *
     * @param $collection
     * @param $column
     */
    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
        }
    }
}