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
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Transaction_Edit_Customer_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('giantpointsCustomerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        if ($this->_getSelectedCustomer()) {
            $this->setDefaultFilter(array('in_customers' => 1));
        }
        $this->setDefaultLimit(10);
    }

    /**
     * prepare collection for block to display
     *
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group_id');

        // Join to get Points
        $collection->getSelect()
            ->joinLeft(array('gp' => $collection->getTable('giantpoints/customer')),
                'e.entity_id = gp.customer_id',
                array('point_balance')
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_customers') {
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', $column->getFilter()->getValue());
            } elseif ($this->_getSelectedCustomer()) {
                $this->getCollection()->addFieldToFilter('entity_id', array(
                    'neq' => $this->_getSelectedCustomer()
                ));
            }
        } elseif ($column->getId() == 'point_balance') {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond['from'])) {
                $this->getCollection()->getSelect()
                    ->where('gp.point_balance >= ?', $cond['from']);
            }
            if (isset($cond['to'])) {
                $this->getCollection()->getSelect()
                    ->where('gp.point_balance <= ?', $cond['to']);
            }
        } else {
            return parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare columns for this grid
     *
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_customers', array(
            'header'           => Mage::helper('giantpoints')->__('Select'),
            'header_css_class' => 'a-center',
            'type'             => 'radio',
            'html_name'        => 'in_customers',
            'align'            => 'center',
            'index'            => 'entity_id',
            'values'           => array($this->_getSelectedCustomer()),
            'filter'           => false,
            'sortable'         => false,
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('giantpoints')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'entity_id',
            'type'   => 'number',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('giantpoints')->__('Name'),
            'index'  => 'name',
        ));

        $this->addColumn('email', array(
            'header'   => Mage::helper('giantpoints')->__('Email'),
            'index'    => 'email',
            'renderer' => 'giantpoints/adminhtml_transaction_edit_customer_renderer_email',
        ));

        $this->addColumn('point_balance', array(
            'header'   => Mage::helper('giantpoints')->__('Point Balance'),
            'align'    => 'right',
            'index'    => 'point_balance',
            'type'     => 'number',
            'renderer' => 'giantpoints/adminhtml_transaction_edit_customer_renderer_point',
        ));

        $this->addColumn('group', array(
            'header'  => Mage::helper('giantpoints')->__('Group'),
            'width'   => '100',
            'index'   => 'group_id',
            'type'    => 'options',
            'options' => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gt' => 0))
                    ->load()
                    ->toOptionHash(),
        ));


        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getId()));
    }

    /**
     * get grid url (use for ajax load)
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerGrid', array('_current' => true));
    }

    protected function _getSelectedCustomer()
    {
        return $this->getRequest()->getParam('selected_customer_id', '0');
    }

    public function getSelectedCustomer()
    {
        return $this->_getSelectedCustomer();
    }
}
