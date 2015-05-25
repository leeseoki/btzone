<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
* https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */


class Magegiant_GiantPoints_Block_Adminhtml_System_Config_Form_Field_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl("giantpoints/adminhtml_transaction/resetTransactions");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(Mage::helper('giantpoints')->__('Reset all transactions'))
            ->setOnClick("return conformation();")
            ->toHtml()
        ;

        $html .= "<p class='note'>";
        $html .= "<span style='color:#E02525;'>";
        $html .= Mage::helper('giantpoints')->__(
            "This action is unrecoverable and will set all customers' points balance to 0"
        );
        $html .= "</span>";
        $html .= "</p>";
        $html .= "
            <script type='text/javascript'>
                function conformation (){
                    if (confirm('" . $this->__('Are you sure to reset all transactions?') . "')) {
                        var url ='{$url}';
                        new Ajax.Request(url, {
                            parameters: {
                                         form_key: FORM_KEY,
                                         },
                            evalScripts: true,
                            onSuccess: function(transport) {
                                if(transport.responseText =='success'){
                                 alert('".$this->__('All transaction removed successfully')."');
                                }
                            }
                        });
                    }
                }
            </script>
        ";
        return $html;
    }
}