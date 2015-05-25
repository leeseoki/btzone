<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryphysicalstocktaking Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Model_Observer {

    /**
     * process inventory_adminhtml_add_column_permission_grid event
     *
     * @return Magestore_Inventoryphysicalstocktaking_Model_Observer
     */
    public function addColumnPermission($observer) {
        $grid = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $grid->addColumn('can_physical', array(
            'header' => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'physical[]',
            'values' => $this->_getSelectedCanPhysicalAdmins($grid)
        ));
    }

    /**
     * process inventory_adminhtml_add_more_permission event
     *
     * @return Magestore_Inventoryphysicalstocktaking_Model_Observer
     */
    public function addMorePermission($observer) {
        $event = $observer->getEvent();
        $assignment = $event->getPermission();
        $datas = $event->getData();
        $data = $datas['data'];
        $adminId = $event->getAdminId();
        $changePermissions = $observer->getEvent()->getChangePermssions();
        $physicals = array();
        if (isset($data['physical']) && is_array($data['physical'])) {
            $physicals = $data['physical'];
        }
        if ($assignment->getId()) {
            $oldPhysical = $assignment->getCanPhysical();
        }

        if (in_array($adminId, $physicals)) {
            if ($assignment->getId()) {
                if ($oldPhysical != 1) {
                    $changePermissions[$adminId]['old_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
                    $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_physical'] = '';
                $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
            }
            $assignment->setData('can_physical', 1);
        } else {
            if ($assignment->getId()) {
                if ($oldPhysical != 0) {
                    $changePermissions[$adminId]['old_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Can physical stocktaking Warehouse');
                    $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
                }
            } else {
                $changePermissions[$adminId]['old_physical'] = '';
                $changePermissions[$adminId]['new_physical'] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot physical stocktaking Warehouse');
            }
            $assignment->setData('can_physical', 0);
        }
    }

    protected function _getSelectedCanPhysicalAdmins($grid) {
        $warehouse = $grid->getWarehouse();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $array = array();
        if ($warehouse->getId()) {
            $canPhysicalAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->addFieldToFilter('can_physical', 1);
            foreach ($canPhysicalAdmins as $canPhysicalAdmin) {
                $array[] = $canPhysicalAdmin->getAdminId();
            }
        } else {
            $array = array($adminId);
        }


        return $array;
    }

    public function inventoryMenu($observer) {
        $menu = $observer->getEvent()->getMenus()->getMenu();

        $menu['adjuststock'] = array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Stock Adjustment'),
            'sort_order' => 300,
            'url' => '',
            'active' => (Mage::app()->getRequest()->getControllerName() == 'adminhtml_adjuststock' || Mage::app()->getRequest()->getControllerName() == 'adminhtml_physicalstocktaking') ? true : false,
            'level' => 0,
            'children' => array(
                'adjust_stock' => array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Adjust Stock'),
                    'sort_order' => 110,
                    'url' => Mage::helper("adminhtml")->getUrl("inventoryplusadmin/adminhtml_adjuststock/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => false,
                    'level' => 1),
                'physical_stock' => array('label' => Mage::helper('inventoryphysicalstocktaking')->__('Physical Stocktaking'),
                    'sort_order' => 100,
                    'url' => Mage::helper("adminhtml")->getUrl("inventoryphysicalstocktakingadmin/adminhtml_physicalstocktaking/", array("_secure" => Mage::app()->getStore()->isCurrentlySecure())),
                    'active' => false,
                    'level' => 1
                )
            )
        );
        $observer->getEvent()->getMenus()->setData('menu', $menu);
    }

}
