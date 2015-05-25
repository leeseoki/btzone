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
class Magegiant_GiantPoints_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('giantpoints/transaction')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header' => Mage::helper('giantpoints')->__('ID'),
            'align'  => 'right',
            'width'  => '10px',
            'index'  => 'transaction_id',
            'type'   => 'number',
        ));


        $this->addColumn('customer_email', array(
            'header'   => Mage::helper('giantpoints')->__('Customer'),
            'align'    => 'left',
            'index'    => 'customer_email',
            'width'  => '50px',
            'renderer' => 'giantpoints/adminhtml_transaction_renderer_customer',
        ));

        $this->addColumn('comment', array(
            'header' => Mage::helper('giantpoints')->__('Comment'),
            'align'  => 'left',
            'index'  => 'comment',
            'width'  => '120px',
        ));
        $this->addColumn('action_code', array(
            'header' => Mage::helper('giantpoints')->__('Action'),
            'align'  => 'left',
            'index'  => 'action_code',
            'width'  => '10px',
        ));

        $this->addColumn('point_amount', array(
            'header' => Mage::helper('giantpoints')->__('Points'),
            'align'  => 'right',
            'index'  => 'point_amount',
            'type'   => 'number',
        ));

        $this->addColumn('point_spent', array(
            'header' => Mage::helper('giantpoints')->__('Point Spent'),
            'align'  => 'right',
            'index'  => 'point_spent',
            'type'   => 'number',
        ));

        $this->addColumn('change_date', array(
            'header' => Mage::helper('giantpoints')->__('Date'),
            'index'  => 'change_date',
            'type'   => 'datetime',
        ));

        $this->addColumn('expiration_date', array(
            'header' => Mage::helper('giantpoints')->__('Expire On'),
            'index'  => 'expiration_date',
            'type'   => 'datetime',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('giantpoints')->__('Status'),
            'align'   => 'left',
            'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getSingleton('giantpoints/transaction')->getStatusHash(),
        ));

        $this->addColumn('store_id', array(
            'header'  => Mage::helper('giantpoints')->__('Store View'),
            'align'   => 'left',
            'index'   => 'store_id',
            'type'    => 'options',
            'options' => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true),
        ));

        $this->addColumn('view_action', array(
            'header'    => Mage::helper('giantpoints')->__('View'),
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('giantpoints')->__('View'),
                    'url'     => array('base' => '*/*/edit'),
                    'field'   => 'id'
                )),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('giantpoints')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('giantpoints')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magegiant_GiantPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('transaction_id');
        $this->getMassactionBlock()->setFormFieldName('transactions');

        $this->getMassactionBlock()->addItem('complete', array(
            'label'   => Mage::helper('giantpoints')->__('Complete'),
            'url'     => $this->getUrl('*/*/massComplete'),
            'confirm' => Mage::helper('giantpoints')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('cancel', array(
            'label'   => Mage::helper('giantpoints')->__('Cancel'),
            'url'     => $this->getUrl('*/*/massCancel'),
            'confirm' => Mage::helper('giantpoints')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('expire', array(
            'label'   => Mage::helper('giantpoints')->__('Expire'),
            'url'     => $this->getUrl('*/*/massExpire'),
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
     * get grid url (use for ajax load)
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
