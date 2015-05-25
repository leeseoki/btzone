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
 * @package     Magestore_Inventorysupplyneeds
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorysupplyneeds Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysupplyneeds
 * @author      Magestore Developer
 */
class Magestore_Inventorysupplyneeds_Adminhtml_InventorysupplyneedsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorysupplyneeds_Adminhtml_InventorysupplyneedsController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Items Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
        return $this;
    }
 
    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Manage Supply Needs'));
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * chart action - show history sales
     */
    public function chartAction() {
        $form_html = $this->getLayout()
            ->createBlock('inventorysupplyneeds/adminhtml_inventorysupplyneeds')
            ->setTemplate('inventorysupplyneeds/chart.phtml')
            ->toHtml();
        $this->getResponse()->setBody($form_html);
    }

    /**
     * create a purchase order from supply need
     */
    public function createpurchaseAction() {
        $data = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();   
            $warehouseId = $supplier = $currency = $changeRate = '';
            if (!isset($data['supplier_select']) || is_null($data['supplier_select']) || $data['supplier_select'] == '')
                return;      
            $supplierId = $data['supplier_select'];
            if(isset($data['warehouse_select']) && $data['warehouse_select'] != ''){                
                $warehouseId = $data['warehouse_select'];
                $firstWarehouseId = $data['warehouse_select'];
            }else{
                
                $warehouses = Mage::helper('inventorypurchasing/purchaseorder')->getWarehouseOption();
                zend_debug::dump($warehouses);
                $i = 0;
                foreach($warehouses as $warehouse){
                    if($i != 0){
                        $warehouseId .= ',';
                    }else{
                        $firstWarehouseId = $warehouse['value'];
                    }
                    $warehouseId .= $warehouse['value'];
                    $i++;
                }
            }
            if(isset($data['currency']))
                $currency = $data['currency'];
            if(isset($data['change_rate']))
                $changeRate = $data['change_rate'];
                                                  
            $productData = array();
            if (isset($data['product_list'])) {
                $list = array();
                $list = explode(';', $data['product_list']);                 
                foreach ($list as $productPurchase) {
                    $productPurchaseUse = explode('_',$productPurchase);
                    if(!isset($productPurchaseUse[1]))
                        continue;
                    $productPurchaseFinal = explode('=', $productPurchaseUse[1]);
                    $productId = $productPurchaseFinal[0];
                    $qty = $productPurchaseFinal[1];
                    
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $productInfo = Mage::getModel('inventorypurchasing/supplier_product')
                                        ->getCollection()
                                        ->addFieldToFilter('supplier_id', $supplierId)
                                        ->addFieldToFilter('product_id', $productId)
                                        ->getFirstItem();
                    if ($productInfo->getId()) {   
                        $productData[] = array(
                            'SKU' => $product->getSku(),
                            'COST' => $productInfo->getCost(),
                            'TAX' => $productInfo->getTax(),
                            'DISCOUNT' => $productInfo->getDiscount(),
                            'SUPPLIER_SKU' => $productInfo->getSupplierSku(),
                            'warehouse_'.$firstWarehouseId => $qty
                        );
                    }
                }                
            }
            if($productData)
                Mage::getModel('admin/session')->setData('purchaseorder_product_import', $productData);
            $this->_redirect('inventorypurchasingadmin/adminhtml_purchaseorders/new',array(
                                                                'supplier_id' => $supplierId,
                                                                'warehouse_ids' => $warehouseId,
                                                                'currency' => $currency,
                                                                'change_rate' => $changeRate
                                                            )
                    );
        }
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function exportPostAction($data) {
        $headers = new Varien_Object(array(
                'ID' => Mage::helper('inventorysupplyneeds')->__('ID'),
                'Name' => Mage::helper('inventorysupplyneeds')->__('Name'),
                'SKU' => Mage::helper('inventorysupplyneeds')->__('SKU'),
                'Cost' => Mage::helper('inventorysupplyneeds')->__('Cost'),
                'Price' => Mage::helper('inventorysupplyneeds')->__('Price'),
                'Warehouse' => Mage::helper('inventorysupplyneeds')->__('Warehouse'),
                'Supplyneeds' => Mage::helper('inventorysupplyneeds')->__('Supplyneeds'),
                'Supplier' => Mage::helper('inventorysupplyneeds')->__('Supplier')
            ));
        $template = '"{{ID}}","{{Name}}","{{SKU}}","{{Cost}}","{{Price}}","{{Supplyneeds}}","{{Warehouse}}","{{Supplier}}"';
        $content = $headers->toString($template);
        if (($data['product_list'])) {
            $info = array();
            $list = explode(';', $data['product_list']);
            $arr = Mage::helper('inventorysupplyneeds')->filterList($list);
            foreach($arr as $productId=>$qty){
                $product = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id', $productId)
                    ->addAttributeToSelect('*')
                    ->getFirstItem();
                $warehouse = Mage::getModel('inventoryplus/warehouse')
                    ->getCollection()
                    ->addFieldToFilter('warehouse_id', $data['warehouse_select'])
                    ->getFirstItem()
                    ->getName();
                $supplier = Mage::getModel('inventorypurchasing/supplier')
                    ->getCollection()
                    ->addFieldToFilter('supplier_id', $data['supplier_select'])
                    ->getFirstItem()
                    ->getName();
                $cost = Mage::getModel('inventoryplus/inventory')
                    ->getCollection()
                    ->addFieldToFilter('product_id',$productId)
                    ->getFirstItem()
                    ->getCostPrice();
                $info['ID'] = $productId;
                $info['Name'] = $product->getName();
                $info['SKU'] = $product->getSku();
                $info['Cost'] = $cost;
                $info['Price'] = $product->getPrice();
                $info['Supplyneeds'] = $qty;
                $info['Warehouse'] = $warehouse;
                $info['Supplier'] = $supplier;
                $csv_content = new Varien_Object($info);
                $content .= "\n";
                $content .= $csv_content->toString($template);
            }
        }
        $this->_prepareDownloadResponse('supplyneeds.csv', $content);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus');
    }
    
}