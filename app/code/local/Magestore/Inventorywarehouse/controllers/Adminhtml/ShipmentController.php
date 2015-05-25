<?php

class Magestore_Inventorywarehouse_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_action {

    public function checkavailablebyeventAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $productId = $this->getRequest()->getParam('product_id');
        $qty = $this->getRequest()->getParam('qty');
        $orderItemId = $this->getRequest()->getParam('order_item_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $totalQtyOfProductRequest = $this->getRequest()->getParam('total_qty');
        $availableProduct = Mage::helper('inventorywarehouse/warehouse')
                ->checkWarehouseAvailableProduct($warehouseId, $productId, $totalQtyOfProductRequest);
        if ($availableProduct == true || $qty == 0) {
            $this->getResponse()->setBody("available");
        } else {            
            $this->getResponse()->setBody("notavailable");            
        }
    }

}
