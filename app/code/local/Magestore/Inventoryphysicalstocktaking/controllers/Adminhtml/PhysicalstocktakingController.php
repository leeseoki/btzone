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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryphysicalstocktaking
 * @author      Magestore Developer
 */
class Magestore_Inventoryphysicalstocktaking_Adminhtml_PhysicalstocktakingController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('inventoryplus')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Physical Stocktaking'), Mage::helper('adminhtml')->__('Manage Physical Stocktaking')
        );
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Manage Physical Stocktaking'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function importproductAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $physicalstocktakingProduct = array();
                $physicalstocktakingProducts = array();
                $fields = array();
                $count = 0;
                $physicalstocktakingHelper = Mage::helper('inventoryphysicalstocktaking');
                if (count($dataFile))
                    foreach ($dataFile as $col => $row) {
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {
                            if (count($row))
                                foreach ($row as $index => $cell) {
                                    if (isset($fields[$index])) {
                                        $physicalstocktakingProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $physicalstocktakingProducts[] = $physicalstocktakingProduct;
                        }
                    }
                $physicalstocktakingHelper->importProduct($physicalstocktakingProducts);
            } catch (Exception $e) {
                
            }
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventory_listphysicalstocktaking_grid');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Physical Stocktaking'));
        if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
            $this->_redirect('*/*/prepare');
        } else {
            $this->loadLayout()->_setActiveMenu('inventoryplus');
            $this->renderLayout();
        }
    }

    public function editAction() {

        $adjustStockId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking')->load($adjustStockId);

        if (!$adjustStockId) {
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Add New Physical Stocktaking'));
        } else {
            $this->_title($this->__('Inventory'))
                    ->_title($this->__('Edit Physical Stocktaking'));
        }

        if ($model->getId() || $adjustStockId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('physicalstocktaking_data', $model);

            $this->loadLayout()->_setActiveMenu('inventoryplus');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                    ->removeItem('js', 'mage/adminhtml/grid.js')
                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
            $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit'))
                    ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryphysicalstocktaking')->__('Adjust Stock does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function prepareAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Add New Physical Stocktaking'));
        if ($data = $this->getRequest()->getPost() || !Mage::helper('inventoryplus')->isWarehouseEnabled() || $this->getRequest()->getParam('warehouse_id')) {
            if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('fileToUpload');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('csv'));
                    $uploader->setAllowRenameFiles(false);

                    $uploader->setFilesDispersion(false);

                    try {
                        $fileName = $_FILES['fileToUpload']['tmp_name'];
                        $Object = new Varien_File_Csv();
                        $dataFile = $Object->getData($fileName);
                        $adjustStockProduct = array();
                        $adjustStockProducts = array();
                        $fields = array();
                        if (count($dataFile))
                            foreach ($dataFile as $col => $row) {
                                if ($col == 0) {
                                    if (count($row))
                                        foreach ($row as $index => $cell)
                                            $fields[$index] = (string) $cell;
                                }elseif ($col > 0) {
                                    if (count($row))
                                        foreach ($row as $index => $cell) {
                                            if (isset($fields[$index])) {
                                                $adjustStockProduct[$fields[$index]] = $cell;
                                            }
                                        }
                                    $adjustStockProducts[] = $adjustStockProduct;
                                }
                            }
                        if (count($adjustStockProducts)) {
                            $adjustStockProducts['warehouse_id'] = $this->getRequest()->getPost('warehouse_id');
                            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', $adjustStockProducts);
                            $this->loadLayout()->_setActiveMenu('inventoryplus');

                            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                                    ->removeItem('js', 'mage/adminhtml/grid.js')
                                    ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                            $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit')->setPhysicalstocktakingProducts($adjustStockProducts))
                                    ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs')->setPhysicalstocktakingProducts($adjustStockProducts));
                            $this->renderLayout();
                        } else {
                            Mage::getSingleton('adminhtml/session')->addError(
                                    Mage::helper('inventoryphysicalstocktaking')->__('Unable to find item to save')
                            );
                            $this->_redirect('*/*/new');
                        }
                    } catch (Exception $e) {
                        
                    }
                } catch (Exception $e) {
                    
                }
            } else {


                if ($this->getRequest()->getPost('warehouse_id'))
                    $adjustStockProducts['warehouse_id'] = $this->getRequest()->getPost('warehouse_id');

                if (!Mage::helper('inventoryplus')->isWarehouseEnabled()) {
                    $adjustStockProducts['warehouse_id'] = Mage::getModel('inventoryplus/warehouse')->getCollection()
                                    ->getFirstItem()->getId();


                    $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
                    $canPhysicalAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                            ->addFieldToFilter('warehouse_id', $adjustStockProducts['warehouse_id'])
                            ->addFieldToFilter('admin_id', $adminId)
                            ->addFieldToFilter('can_physical', 1);
                    if (!$canPhysicalAdmins->getSize()) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('inventoryphysicalstocktaking')->__('You have not permission to physical stocktaking!'));
                        session_write_close();
                        $this->_redirect('*/*/');
                    }
                } else {

                    if (!$this->getRequest()->getPost('warehouse_id') && $this->getRequest()->getParam('warehouse_id'))
                        $adjustStockProducts['warehouse_id'] = $this->getRequest()->getParam('warehouse_id');
                }



                Mage::getModel('admin/session')->setData('physicalstocktaking_product_warehouse', $adjustStockProducts);
                $this->loadLayout()->_setActiveMenu('inventoryplus');

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                        ->removeItem('js', 'mage/adminhtml/grid.js')
                        ->addItem('js', 'magestore/adminhtml/inventory/grid.js');
                $this->_addContent($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit')->setPhysicalstocktakingProducts($adjustStockProducts))
                        ->_addLeft($this->getLayout()->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_edit_tabs')->setPhysicalstocktakingProducts($adjustStockProducts));
                $this->renderLayout();
            }
        }else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('inventoryphysicalstocktaking')->__('Unable to find item to save')
            );
            $this->_redirect('*/*/new');
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryphysicalstocktaking.physicalstocktaking.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('physicalstocktaking_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('physicalstocktaking_product_import')) {
            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', null);
        }
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('inventoryphysicalstocktaking.physicalstocktaking.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('physicalstocktaking_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('physicalstocktaking_product_import')) {
            Mage::getModel('admin/session')->setData('physicalstocktaking_product_import', null);
        }
    }

    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {
            $warehouse_id = $data['warehouse_id'];
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouse_id);
            try {
                if (!isset($data['physicalstocktaking_products']) || empty($data['physicalstocktaking_products'])) {
                    Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('inventoryphysicalstocktaking')->__('Cannot save physical stocktaking with no product')
                    );
                    if (!$this->getRequest()->getParam('id')) {
                        $this->_redirect('inventoryphysicalstocktakingadmin/adminhtml_physicalstocktaking/new');
                        return;
                    } else {
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }


                $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                $model = Mage::getModel('inventoryphysicalstocktaking/physicalstocktaking');
                //create new
                if ($this->getRequest()->getParam('id')) {
                    $model = $model->load($this->getRequest()->getParam('id'));
                } else {
                    $model->setWarehouseId($warehouse_id)
                            ->setWarehouseName($warehouse->getWarehouseName())
                            ->setCreatedAt(now())
                            ->setData('create_by', $admin)
                            ->setStatus(0);
                }
                $model->addData($data);
                //cancel
                if ($this->getRequest()->getParam('cancel')) {
                    $model->setStatus(2);
                    $model->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('inventoryplus')->__('The physical stocktaking has been successfully canceled.')
                    );
                    $this->_redirect('*/*/');
                    return;
                }
                //confirm
                if ($this->getRequest()->getParam('confirm')) {
                    $model->setData('confirm_by', $admin)
                            ->setData('confirm_at', now())
                            ->setStatus(1);
                }


                $model->save();


                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $readConnection = $resource->getConnection('core_read');
//                $installer = Mage::getModel('core/resource_setup');
                $sqlNews = array();
                $sqlOlds = '';
                $sqlOldsAvailable = '';
                $sqlUpdateAdjustProduct = '';
                $sqlAdjustNew = array();
                $countSqlOlds = 0;
                $countUpdateadjustProduct = 0;

                if (isset($data['physicalstocktaking_products']) && !empty($data['physicalstocktaking_products'])) {
                    $physicalstocktakingProducts = array();
                    $physicalstocktakingProductsExplodes = explode('&', urldecode($data['physicalstocktaking_products']));
                    if (count($physicalstocktakingProductsExplodes) <= 900) {
                        parse_str(urldecode($data['physicalstocktaking_products']), $physicalstocktakingProducts);
                    } else {
                        foreach ($physicalstocktakingProductsExplodes as $physicalstocktakingProductsExplode) {
                            $physicalstocktakingProduct = '';
                            parse_str($physicalstocktakingProductsExplode, $physicalstocktakingProduct);
                            $physicalstocktakingProducts = $physicalstocktakingProducts + $physicalstocktakingProduct;
                        }
                    }

                    if (count($physicalstocktakingProducts) > 0) {
                        $productIds = array();
                        $qtys = '';
                        $count = 0;
                        foreach ($physicalstocktakingProducts as $pId => $enCoded) {
                            $productIds[] = $pId;
                            $codeArr = array();
                            parse_str(base64_decode($enCoded), $codeArr);
                            $warehouseProductItem = Mage::getModel('inventoryplus/warehouse_product')
                                    ->getCollection()
                                    ->addFieldToFilter('warehouse_id', $warehouse_id)
                                    ->addFieldToFilter('product_id', $pId)
                                    ->getFirstItem();
                            $qtyAddMore = 0;
                            $qtyAddMore = $codeArr['adjust_qty'];
                            $oldQty = $warehouseProductItem->getTotalQty() ? $warehouseProductItem->getTotalQty() : 0;
                            $newQty = $codeArr['adjust_qty'];
                            $newQtyAvailable = $codeArr['adjust_qty'];
                            $sqlNews[] = array(
                                'product_id' => $pId,
                                'warehouse_id' => $warehouse_id,
                                'total_qty' => $codeArr['adjust_qty'],
                                'available_qty' => $newQtyAvailable
                            );
                            if ($this->getRequest()->getParam('id')) {
                                $sqlAdjustProduct = "Select * from " . $resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product') . " WHERE (physicalstocktaking_id = " . $this->getRequest()->getParam('id') . ") AND (product_id = " . $pId . ")";

                                $adjustProduct = $readConnection->fetchRow($sqlAdjustProduct);

                                if ($adjustProduct) {
                                    $countUpdateadjustProduct ++;
                                    $sqlUpdateAdjustProduct .= 'UPDATE ' . $resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product') . ' SET adjust_qty = ' . $newQty . ' WHERE (physicalstocktakingproduct_id = ' . $adjustProduct['physicalstocktakingproduct_id'] . ');';

                                    if ($countUpdateadjustProduct == 900) {
                                        $writeConnection->query($sqlUpdateAdjustProduct);
                                        $sqlUpdateAdjustProduct = '';
                                        $countUpdateadjustProduct = 0;
                                    }
                                } else {
                                    $sqlAdjustNew[] = array(
                                        'physicalstocktaking_id' => $model->getId(),
                                        'product_id' => $pId,
                                        'old_qty' => $oldQty,
                                        'adjust_qty' => $newQty
                                    );
                                    if (count($sqlAdjustNew) == 1000) {
                                        $writeConnection->insertMultiple($resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product'), $sqlAdjustNew);
                                        $sqlAdjustNew = array();
                                    }
                                }
                            } else {
                                $sqlAdjustNew[] = array(
                                    'physicalstocktaking_id' => $model->getId(),
                                    'product_id' => $pId,
                                    'old_qty' => $oldQty,
                                    'adjust_qty' => $newQty
                                );
                                if (count($sqlAdjustNew) == 1000) {
                                    $writeConnection->insertMultiple($resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product'), $sqlAdjustNew);
                                    $sqlAdjustNew = array();
                                }
                            }
                        }


                        if ($this->getRequest()->getParam('id')) {
                            if (!empty($countUpdateadjustProduct)) {
                                $writeConnection->query($sqlUpdateAdjustProduct);
                            }
                        }

                        if (!empty($sqlAdjustNew)) {
                            $writeConnection->insertMultiple($resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product'), $sqlAdjustNew);
                        }
                    } else {
                        $deleteSql = 'DELETE FROM ' . $resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product') . ' WHERE `physicalstocktaking_id` = ' . $this->getRequest()->getParam('id');
                        $writeConnection->query($deleteSql);
                    }

                    if (count($productIds) > 0 && $this->getRequest()->getParam('id')) {
                        $productIds = implode(',', $productIds);
                        $deleteSql = 'DELETE FROM ' . $resource->getTableName('inventoryphysicalstocktaking/physicalstocktaking_product') . ' WHERE `physicalstocktaking_id` = ' . $this->getRequest()->getParam('id') . ' AND `product_id` NOT IN (' . $productIds . ')';
                        $writeConnection->query($deleteSql);
                    }
                    $writeConnection->commit();
                    if ($this->getRequest()->getParam('confirm') || $this->getRequest()->getParam('confirmadjust')) {
                        $confirmAdjust = 0;
                        $adjuststock_data['warehouse_id'] = $warehouse->getWarehouseId();
                        $adjuststock_data['warehouse_name'] = $warehouse->getWarehouseName();
                        $adjuststock_data['file_path'] = $model->getFilePath();
                        $adjuststock_data['created_at'] = now();
                        $adjuststock_data['created_by'] = $admin;
                        $adjuststock_data['reason'] = $data['reason'];
                        $adjuststock_data['status'] = 0;
                        $adjuststock_data['adjuststock_products'] = $data['physicalstocktaking_products'];

                        if ($this->getRequest()->getParam('confirmadjust')) {
                            $confirmAdjust = $this->getRequest()->getParam('confirmadjust');
                            $adjuststock_data['status'] = 1;
                        }

                        $adjust_model = Mage::getModel('inventoryplus/adjuststock');
                        $adjust_model->setData($adjuststock_data);
                        try {
                            $adjust_model->save();
                            Mage::helper('inventoryplus/adjuststock')->adjustStockData($adjuststock_data, $warehouse_id, $adjust_model, $confirmAdjust);
                            if ($confirmAdjust) {
                                $url = Mage::helper('adminhtml')->getUrl('inventoryplusadmin/adminhtml_adjuststock/edit', array('id' => $adjust_model->getId()));
                                Mage::getSingleton('adminhtml/session')->addSuccess(
                                        Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been confirmed.')
                                );
                                Mage::getSingleton('adminhtml/session')->addSuccess(
                                        Mage::helper('inventoryphysicalstocktaking')->__('A stock adjustment(Id %s) has already created and been completed automatically. You can <a href="%s"/>Click here</a> to view stock adjustment.', $adjust_model->getId(), $url)
                                );
                            } else {
                                $url = Mage::helper('adminhtml')->getUrl('inventoryplusadmin/adminhtml_adjuststock/edit', array('id' => $adjust_model->getId()));
                                Mage::getSingleton('adminhtml/session')->addSuccess(
                                        Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been confirmed. A pending stock adjustment(Id %s) has been successfully created. Now you can <a href="%s"/>Click here</a> to update stock of the warehouse.', $adjust_model->getId(), $url)
                                );
                            }
                        } catch (Exception $e) {
                            
                        }
                    }
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('inventoryphysicalstocktaking')->__('The physical stocktaking has been saved successfully.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventoryphysicalstocktaking')->__('Unable to find physical stocktaking to save.')
        );
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'physicalstocktaking.csv';
        $content = $this->getLayout()
                ->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_listphysicalstocktaking_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'physicalstocktaking.xml';
        $content = $this->getLayout()
                ->createBlock('inventoryphysicalstocktaking/adminhtml_physicalstocktaking_listphysicalstocktaking_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

}
