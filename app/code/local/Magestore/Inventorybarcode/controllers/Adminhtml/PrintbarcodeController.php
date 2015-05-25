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
class Magestore_Inventorybarcode_Adminhtml_PrintbarcodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * select template to print barcode
     *
     * @return Magestore_Inventory_Adminhtml_InventoryController
     */
    public function selecttemplateAction() {

        $function = Mage::getModel('inventorybarcode/printbarcode_function');
        echo $this->getLayout()->createBlock('inventorybarcode/adminhtml_printbarcode')->setTemplate('inventorybarcode/printbarcode/selecttemplate.phtml')->toHtml();
    }


    public function getimageAction() {

        $params = $this->getRequest()->getParams();

        $type = $params['type'];
        $code = $params['text'];

        if (isset($params['customize']) && $params['customize']) {
            $heigth = $params['heigth_barcode'];
            $barcodeOptions = array('text' => $code,
                'barHeight' => $heigth,
                'fontSize' => $params['font_size'],
                'withQuietZones' => true
            );
        } else {
            $barcodeOptions = array('text' => $code,
                'fontSize' => $params['font_size'],
                'withQuietZones' => true
            );
        }


        // No required options
        $rendererOptions = array();

        // Draw the barcode in a new image,
        // send the headers and the image
        $imageResource = Zend_Barcode::factory(
                        $type, 'image', $barcodeOptions, $rendererOptions
        );
        imagepng($imageResource->draw(), Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'inventorybarcode' . DS . 'images' . DS . 'barcode' . DS . 'barcode.png');
        $imageResource->render();
    }

    public function printBarcodeAction() {
        $params = $this->getRequest()->getParams();
		/* Added by Magnus 08/04/2015 (File pdf hien thi cac ki tu dc biet-ko hieu dc) */
		$this->loadLayout();
        $this->renderLayout();
		return;
		/* End Magnus 08/04/2015 */
        $contents = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/printbarcode/printbarcode.phtml')
                ->assign('barcodeId', $params['barcodeId'])
                ->assign('qty', $params['number_of_barcode'])
                ->assign('barcodeTemplate', $params['barcode_template'])
                ->assign('fontSize', $params['font_size'])
                ->assign('imageWidth', $params['image_width']);
        if (isset($params['border'])) {
            $contents->assign('border', $params['border']);
        } else {
            $contents->assign('border', 0);
        }
       
        include("lib/Magestore/Mpdf/mpdf.php");
        $top = '10';
        $bottom = '10';
        $left = '10';
        $right = '10';
       
        $mpdf = new mPDF('', $params['printing_format'], 8, '', $left, $right, $top, $bottom);

        $mpdf->WriteHTML($contents->toHtml());

        echo $mpdf->Output();
		die;
    }
    
    public function massprintBarcodeAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

}
