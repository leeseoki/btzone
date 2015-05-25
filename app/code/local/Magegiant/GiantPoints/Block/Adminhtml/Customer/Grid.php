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
class Magegiant_GiantPoints_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
    }

    protected function _getPreparedCollection()
    {
        return Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->_getPreparedCollection());

        return parent::_prepareCollection();
    }

    protected function _getSelectedCustomers()
    {
        return $this->getRequest()->getPost('selected_customers', array());
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('customer')->__('ID'),
                'width'  => '50px',
                'index'  => 'entity_id',
                'type'   => 'number',
            )
        );
        $this->addColumn(
            'name',
            array(
                'header' => Mage::helper('customer')->__('Name'),
                'index'  => 'name',
            )
        );
        $this->addColumn(
            'email',
            array(
                'header' => Mage::helper('customer')->__('Email'),
                'width'  => '150',
                'index'  => 'email',
            )
        );

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'group',
            array(
                'header'  => Mage::helper('customer')->__('Group'),
                'width'   => '100',
                'index'   => 'group_id',
                'type'    => 'options',
                'options' => $groups,
            )
        );

        $this->addColumn(
            'Telephone',
            array(
                'header' => Mage::helper('customer')->__('Telephone'),
                'width'  => '100',
                'index'  => 'billing_telephone',
            )
        );

        $this->addColumn(
            'billing_postcode',
            array(
                'header' => Mage::helper('customer')->__('ZIP'),
                'width'  => '90',
                'index'  => 'billing_postcode',
            )
        );

        $this->addColumn(
            'billing_country_id',
            array(
                'header' => Mage::helper('customer')->__('Country'),
                'width'  => '100',
                'type'   => 'country',
                'index'  => 'billing_country_id',
            )
        );

        $this->addColumn(
            'billing_region',
            array(
                'header' => Mage::helper('customer')->__('State/Province'),
                'width'  => '100',
                'index'  => 'billing_region',
            )
        );

        $this->addColumn(
            'customer_since',
            array(
                'header'    => Mage::helper('customer')->__('Customer Since'),
                'type'      => 'datetime',
                'align'     => 'center',
                'index'     => 'created_at',
                'gmtoffset' => true,
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                array(
                    'header'  => Mage::helper('customer')->__('Website'),
                    'align'   => 'center',
                    'width'   => '80px',
                    'type'    => 'options',
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                    'index'   => 'website_id',
                )
            );
        }

        return parent::_prepareColumns();
    }

    public function getResetFilterButtonHtml()
    {
        $mssBlock = $this->getLayout()->getBlock('awMssGridBlock');
        if ($mssBlock) {
            $mssRules = $mssBlock->getResetFilterButtonHtml();
        } else {
            $mssRules = $this->__(
                'Customers can be filtered using Market Segmentation Suite rules. %s', $this->_getMssLink()
            );
        }

        return $mssRules . $this->getChildHtml('reset_filter_button');
    }

    protected function _getMssLink()
    {
        return "<a href='{$this->helper('giantpoints')->getMssLink()}' target='_blank'>{$this->__('Learn more')}</a>";
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('selected_customers_form');

        $this->getMassactionBlock()->addItem(
            'giantpoints_update_subscribe',
            array(
                'label' => Mage::helper('customer')->__('Subscribe to balance update'),
                'url'   => $this->getUrl('*/*/massSubscribe'),
            )
        );

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/customersGrid', array('_current' => true));
    }
}