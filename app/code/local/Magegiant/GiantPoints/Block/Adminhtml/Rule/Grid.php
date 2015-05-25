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
 * Giantpoints Adminhtml Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giantpoint_behavior_rule_grid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('giantpoints/rule')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('behavior_rule_id',
            array(
                'header' => Mage::helper('giantpoints')->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'behavior_rule_id'
            )
        );

        $this->addColumn('name',
            array(
                'header' => Mage::helper('giantpoints')->__('Rule Name'),
                'align'  => 'left',
                'index'  => 'name'
            )
        );

        $this->addColumn('from_date',
            array(
                'header' => Mage::helper('giantpoints')->__('Date Start'),
                'align'  => 'left',
                'width'  => '120px',
                'type'   => 'date',
                'index'  => 'from_date'
            )
        );

        $this->addColumn('to_date',
            array(
                'header' => Mage::helper('giantpoints')->__('Date Expire'),
                'align'  => 'left',
                'width'  => '120px',
                'type'   => 'date',
                'index'  => 'to_date'
            )
        );
        $this->addColumn('point_amount',
            array(
                'header' => Mage::helper('giantpoints')->__('Point Amount'),
                'align'  => 'left',
                'width'  => '120px',
                'type'   => 'number',
                'index'  => 'point_amount'
            )
        );
        $this->addColumn(
            'is_active',
            array(
                'header'  => Mage::helper('giantpoints')->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => array(
                    1 => $this->__('Active'),
                    0 => $this->__('Inactive')
                )
            )
        );

        $this->addColumn('sort_order',
            array(
                'header' => Mage::helper('giantpoints')->__('Priority'),
                'align'  => 'right',
                'index'  => 'sort_order'
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => Mage::helper('giantpoints')->__('Delete'),
                'url'   => $this->getUrl('*/*/massDelete'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'activate',
            array(
                'label' => Mage::helper('giantpoints')->__('Activate'),
                'url'   => $this->getUrl('*/*/massActivate'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'deactivate',
            array(
                'label' => Mage::helper('giantpoints')->__('Inactivate'),
                'url'   => $this->getUrl('*/*/massInactivate'),
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
