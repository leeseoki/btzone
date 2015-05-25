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
 * Inventoryreports Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryreports
 * @author      Magestore Developer
 */
class Magestore_Inventoryreports_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action {

    public function _initAction() {
        $this->_title($this->__('Inventory'))
                ->_title($this->__('Reports'));
        switch ($this->getRequest()->getParam('type_id')) {
            case 'sales':
                $this->_title($this->__('Order Reports'));
                break;
            case 'warehouse':
                $this->_title($this->__('Warehouse Reports'));
                break;
            case 'product':
                $this->_title($this->__('Product Reports'));
                break;
            case 'supplier':
                $this->_title($this->__('Supplier Reports'));
                break;
        }
        $this->loadLayout();
//                ->_setActiveMenu('inventoryplus');
        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function reportordergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function reportinvoicegridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function reportcreditmemogridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function inventorybysuppliergridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalqtyadjuststockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }

    public function numberofproductadjuststockgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function totalorderbywarehousegridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function salesbywarehouserevenuegridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function salesbywarehouseitemshippedgridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function totalstocktransfersendstockgridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function totalstocktransferrequeststockgridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function supplyneedsbywarehouseproductsgridAction(){
        $this->loadLayout()
                ->renderLayout();
    }

    public function totalstockdifferentwhenphysicalstocktakingbywarehousegridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function bestsellergridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function moststockremaingridAction(){
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function warehousingtimelongestgridAction() {
        $this->loadLayout()
                ->renderLayout();
    }
    
    public function timedeliverybysupplierAction()
    {            
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybysupplier.phtml')->toHtml();
    }
    
    public function timedeliverybywarehouseAction()
    {            
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybywarehouse.phtml')->toHtml();
    }
    
    public function timedeliverybyproductAction()
    {            
        echo $this->getLayout()->createBlock('adminhtml/template')->setTemplate('inventoryreports/content/grid/product/grid/product/timedeliverybyproduct.phtml')->toHtml();
    }
}
