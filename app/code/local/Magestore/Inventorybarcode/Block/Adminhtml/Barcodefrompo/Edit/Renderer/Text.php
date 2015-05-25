<?php

class Magestore_Inventorybarcode_Block_Adminhtml_Barcodefrompo_Edit_Renderer_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    /**
     * Format variables pattern
     *
     * @var string
     */
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';

    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function _getValue(Varien_Object $row)
    {
        $format = ( $this->getColumn()->getFormat() ) ? $this->getColumn()->getFormat() : null;
        $defaultValue = $this->getColumn()->getDefault();
        if (is_null($format)) {
            // If no format and it column not filtered specified return data as is.
            $data = parent::_getValue($row);
            $string = is_null($data) ? $defaultValue : $data;
            if($this->getColumn()->getId()=='purchaseorder_purchase_order_id'){
                $string = Mage::helper('inventorybarcode')->__('PO#').$string;
            }
            if($this->getColumn()->getId()=='supplier_supplier_id'){
                
                $string = Mage::getModel('inventorypurchasing/supplier')->load($string)->getSupplierName();
            }
            return $this->escapeHtml($string);
        }
        elseif (preg_match_all($this->_variablePattern, $format, $matches)) {
            // Parsing of format string
            $formattedString = $format;
            foreach ($matches[0] as $matchIndex=>$match) {
                $value = $row->getData($matches[1][$matchIndex]);
                $formattedString = str_replace($match, $value, $formattedString);
            }
            
            return $formattedString;
        } else {
            return $this->escapeHtml($format);
        }
    }
}
