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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorybarcode Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorybarcode
 * @author      Magestore Developer
 */
class Magestore_Inventorybarcode_Adminhtml_SearchbarcodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorybarcode_Adminhtml_SearchbarcodeController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus');
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Search Barcodes'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {       
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * search action
     */
    public function searchAction() {

        $items = array();

        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('barcode_query', '');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($query,'barcode');
        if($barcode->getId())
        {
            $result = array();
            $result['barcode_id'] = $barcode->getId();
            $result['show'] = true;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));            
        }else{
            $searchInstance = new Magestore_Inventorybarcode_Model_Search_Barcode();
            $results = $searchInstance->setStart($start)
                    ->setLimit($limit)
                    ->setQuery($query)
                    ->load()
                    ->getResults();
            $items = array_merge_recursive($items, $results);
        
            $totalCount = sizeof($items);

        
           
            $block = $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('inventorybarcode/search/autocomplete.phtml')
                    ->assign('items', $items);

            $this->getResponse()->setBody($block->toHtml());
        }
    }
    
    /**
     * showinformation action
     */
    public function showinformationAction() {

        $barcodeId = $this->getRequest()->getParam('barcode_id');
        
        $barcodeModel = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);
        
        $information = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/search/information/information.phtml')         
                ->assign('barcode', $barcodeModel);
        
                
        $barcode = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/search/information/barcode.phtml')
                ->assign('barcode', $barcodeModel);
        
        $productHtml = '';
        if($barcodeModel->getProductEntityId()){
            $productModel = Mage::getModel('catalog/product')->load($barcodeModel->getProductEntityId());


            $product = $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('inventorybarcode/search/information/product.phtml')
                    ->assign('qty', $barcodeModel->getQty())
                    ->assign('product', $productModel);

            $productHtml = $product->toHtml();
        }
        
        $warehouseHtml = '';        
        
        if($barcodeModel->getWarehouseWarehouseId()){
            $warehouseIds = explode(',', $barcodeModel->getWarehouseWarehouseId());
           
            foreach($warehouseIds as $key => $id){
                $warehouseModel = Mage::getModel('inventoryplus/warehouse')->load($id);

                $warehouse = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/warehouse.phtml')
                        ->assign('warehouse', $warehouseModel);
                
                $warehouseHtml .= $warehouse->toHtml();
            }
        }
        
        $supplierHtml = '';
        $purchaseorderHtml = '';
        
        if (Mage::helper('core')->isModuleEnabled('Magestore_Inventorypurchasing')) {
            
            if($barcodeModel->getSupplierSupplierId()){
                $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($barcodeModel->getSupplierSupplierId());


                $supplier = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/supplier.phtml')
                        ->assign('supplier', $supplierModel);

                $supplierHtml = $supplier->toHtml();
            }

            
            if($barcodeModel->getPurchaseorderPurchaseOrderId()){
                $purchaseorderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($barcodeModel->getPurchaseorderPurchaseOrderId());


                $purchaseorder = $this->getLayout()->createBlock('adminhtml/template')
                        ->setTemplate('inventorybarcode/search/information/purchaseorder.phtml')
                        ->assign('purchaseorder', $purchaseorderModel);

                $purchaseorderHtml = $purchaseorder->toHtml();
            }
        }
        
        $result = array();
        
        $result['general'] = $information->toHtml();
        $result['barcode'] = $barcode->toHtml();
        $result['product'] = $productHtml;
        $result['warehouse'] = $warehouseHtml;
        $result['supplier'] = $supplierHtml;
        $result['purchaseorder'] = $purchaseorderHtml;
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
       
    }

}
