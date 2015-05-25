<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /****************************************
 *    MAGENTO EDITION USAGE NOTICE       *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /****************************************
 *    DISCLAIMER                         *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @version    v1.0.0
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Block_Adminhtml_Form_Element_Values extends Varien_Data_Form_Element_Abstract
{
    
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }
    
    public function getElementHtml()
    {
        $gallery = $this->getValue();
        
        $html = '<table id="gallery" class="gallery" border="0" cellspacing="3" cellpadding="0">';
        $html .= '<thead id="gallery_thead" class="gallery"><tr class="gallery"><td class="gallery" valign="middle" align="center">Value</td><td class="gallery" valign="middle" align="center">Sort Order</td><td class="gallery" valign="middle" align="center">Delete</td></tr></thead>';
        $widgetButton = $this->getForm()->getParent()->getLayout();
        $buttonHtml = $widgetButton->createBlock('adminhtml/widget_button')
                                   ->setData( array( 'label' => 'Add New Value',
                                                 'onclick'   => 'addNewRow()',
                                                 'class'     => 'add',
                                                 'style'     => 'width:100%;'))
                                   ->toHtml();
        $html .= '<tfoot class="gallery">';
        $html .= '<tr class="gallery">';
        $html .= '<td class="gallery" valign="middle" align="left" colspan="5">' . $buttonHtml . '</td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        
        $html .= '<tbody class="gallery">';
        
        $i = 0;
        
        if (!is_null($this->getValue())) {
            foreach ($this->getValue() as $item) {
                $i++;
                $html .= '<tr class="gallery">';
                $html .= '<td class="gallery" align="center" style="vertical-align:bottom;"><input class="input-text validate-alphanum-with-spaces values-value required-entry" type="input" name="' . parent::getName() . '[value][' . $item->getValueId() . ']" value="' . $item->getValue() . '" id="' . $this->getHtmlId() . '_position_' . $item->getValueId() . '" size="9"/></td>';
                $html .= '<td class="gallery" align="center" style="vertical-align:bottom;"><input class="input-text values-sort-order validate-digits" type="input" name="' . parent::getName() . '[sort_order][' . $item->getValueId() . ']" value="' . $item->getSortOrder() . '" id="' . $this->getHtmlId() . '_position_' . $item->getValueId() . '" size="5"/></td>';
                $html .= '<td class="gallery" align="center" style="vertical-align:bottom;"><input type="checkbox" name="' . parent::getName() . '[delete][' . $item->getValueId() . ']" value="' . $item->getValueId() . '" id="' . $this->getHtmlId() . '_delete_' . $item->getValueId() . '"/></td>';
                $html .= '</tr>';
            }
        }
        
        if ($i==0) {
            $html .= '<script type="text/javascript">document.getElementById("gallery_thead").style.visibility="hidden";</script>';
        }
        
        $html .= '</tbody></table>';
        
        $name = $this->getName();
        $parentName = parent::getName();
        
        $html .= <<<EndSCRIPT
        
        <script language="javascript">
        id = 0;
        
        function addNewRow(){
            
            document.getElementById("gallery_thead").style.visibility="visible";
            
            id--;
            //Value input
            var new_value_input = document.createElement( 'input' );
            new_value_input.type = 'text';
            new_value_input.name = '{$parentName}[value]['+id+']';
            new_value_input.size = '9';
            new_value_input.value = '';
            new_value_input.addClassName('input-text validate-alphanum-with-spaces values-value new required-entry');
            
            
            // Sort order input
            var new_row_input = document.createElement( 'input' );
            new_row_input.type = 'text';
            new_row_input.name = '{$parentName}[sort_order]['+id+']';
            new_row_input.size = '5';
            new_row_input.value = '0';
            new_row_input.addClassName('input-text values-sort-order new validate-digits');
            
            // Delete button
            var new_row_button = document.createElement( 'input' );
            new_row_button.type = 'checkbox';
            new_row_button.value = 'Delete';
            
            table = document.getElementById( "gallery" );
            
            // no of rows in the table:
            noOfRows = table.rows.length;
            
            // no of columns in the pre-last row:
            noOfCols = table.rows[noOfRows-2].cells.length;
            
            // insert row at pre-last:
            var x=table.insertRow(noOfRows-1);
            
            // insert cells in row.
            for (var j = 0; j < noOfCols; j++) {
                
                newCell = x.insertCell(j);
                newCell.align = "center";
                newCell.valign = "middle";
                
                if (j==1) {
                    newCell.appendChild( new_row_input );
                }
                else if (j==2) {
                    newCell.appendChild( new_row_button );
                }
                else {
                    newCell.appendChild( new_value_input );
                }
                
            }
            
            // Delete function
            new_row_button.onclick= function(){
                
                this.parentNode.parentNode.parentNode.removeChild( this.parentNode.parentNode );
                
                // Appease Safari
                //    without it Safari wants to reload the browser window
                //    which nixes your already queued uploads
                return false;
            };
            
        }
        </script>
        
EndSCRIPT;
        $html.= $this->getAfterElementHtml();
        return $html;
    }
    
    public function getName()
    {
        return $this->getData('name');
    }
    
    public function getParentName()
    {
        return parent::getName();
    }
    
}