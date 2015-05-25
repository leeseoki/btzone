<?php

class Magestore_Inventorywebpos_Model_Observer
{
    /**
     * process controller_action_predispatch_webpos_index_productsearch event
     *
     * @return Magestore_Inventorywebpos_Model_Observer
     */
    public function initProductSearch($observer)
    {
        $keyword = Mage::app()->getRequest()->getPost('keyword');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($keyword,'barcode');
        $result = array();
        $storeId = Mage::app()->getStore()->getStoreId();
	$showOutofstock = Mage::getStoreConfig('webpos/general/show_product_outofstock',$storeId);
        $productBlock = Mage::getBlockSingleton('catalog/product_list');
        if($barcode->getId())
        {
            $productId = $barcode->getProductEntityId();
            $product = Mage::getModel('catalog/product')->load($productId);
                $addToCart = $productBlock->getAddToCartUrl($product).'tempadd/1';
                $result[] = $productId;
                $html = '';
                $html .= '<ul>';
                $html .= '<li id="sku_only" url="' . $addToCart . '" onclick="setLocation(\'' . $addToCart . '\')">';
                $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
				if($showOutofstock){
					$html .= '<br />';
					if($product->isAvailable()){
						$html .= '<p class="availability in-stock">'.Mage::helper('inventorywebpos')->__('Availability:').'<span>'.Mage::helper('inventorywebpos')->__('In stock').'</span></p><div style="clear:both"></div>';
					}else{
						$html .= '<p class="availability out-of-stock">'.Mage::helper('inventorywebpos')->__('Availability:').'<span>'.Mage::helper('inventorywebpos')->__('Out of stock').'</span></p><div style="clear:both"></div>';
					}
				}
                $html .= '</li>';
                $html .= '</ul>';
                echo $html;
                return;
        }else{
            $searchInstance = new Magestore_Inventorywebpos_Model_Search_Barcode();
            $results = $searchInstance->setStart(1)
                    ->setLimit(10)
                    ->setQuery($keyword)
                    ->load()
                    ->getResults();
            
           if(count($results)){   
                $html = '';
                $html .= '<ul>';
                foreach($results as $item){
                    $productId = $item['product_id'];
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $addToCart = $productBlock->getAddToCartUrl($product).'tempadd/1';
                    $result[] = $product->getId();
                    $html .= '<li onclick="setLocation(\'' . $addToCart . '\')">';
                    $html .= '<strong>' . Mage::getBlockSingleton('core/template')->htmlEscape($product->getName()) . '</strong>-' . Mage::helper('core')->currency($product->getFinalPrice());
                    $html .= '<br /><strong>SKU: </strong>' . $product->getSku();
					if($showOutofstock){
						$html .= '<br />';
						if($product->isAvailable()){
							$html .= '<p class="availability in-stock">'.Mage::helper('inventorywebpos')->__('Availability:').'<span>'.Mage::helper('inventorywebpos')->__('In stock').'</span></p><div style="clear:both"></div>';
						}else{
							$html .= '<p class="availability out-of-stock">'.Mage::helper('inventorywebpos')->__('Availability:').'<span>'.Mage::helper('inventorywebpos')->__('Out of stock').'</span></p><div style="clear:both"></div>';
						}
					}
                    $html .= '</li>';
                }
                $html .= '</ul>';                
                echo $html;                
                return;
           }
        }
    }
}