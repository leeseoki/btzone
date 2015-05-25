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
class Magegiant_GiantPoints_Block_Adminhtml_Customer_Edit_Tabs_Giantpoints_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Default sort field
     *
     * @var string
     */
    protected $_defaultSort = 'change_date';

    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('pointsHistoryGrid');
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('giantpoints')->__('No Transactions Found'));
    }

    /**
     * Retrieve current customer object
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        if ($customerId = $this->getCustomerId()) {
            return Mage::getModel('customer/customer')->load($customerId);
        }

        return Mage::registry('current_customer');
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('giantpoints/customer')->getCustomerTransactions($this->_getCustomer());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'transaction_id',
            array(
                'header' => Mage::helper('giantpoints')->__('ID'),
                'index'  => 'transaction_id',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'store_id',
            array(
                'header'  => Mage::helper('giantpoints')->__('Store View'),
                'index'   => 'store_id',
                'type'    => 'options',
                'options' => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true),
            )
        );
        $this->addColumn(
            'point_amount',
            array(
                'header' => Mage::helper('giantpoints')->__('Points'),
                'index'  => 'point_amount',
                'type'   => 'number',
            )
        );
        $this->addColumn(
            'point_spent',
            array(
                'header' => Mage::helper('giantpoints')->__('Point Spent'),
                'index'  => 'point_spent',
                'type'   => 'number',
            )
        );

        $this->addColumn(
            'change_date',
            array(
                'header' => Mage::helper('giantpoints')->__('Date'),
                'index'  => 'change_date',
                'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'expiration_date',
            array(
                'header' => Mage::helper('giantpoints')->__('Date Expire'),
                'index'  => 'expiration_date',
                'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'comment',
            array(
                'header' => Mage::helper('giantpoints')->__('Comment'),
                'index'  => 'comment',
                'type'   => 'text',
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('giantpoints/adminhtml_transaction/transactionHistoryGrid', array('_current' => true));
    }
}