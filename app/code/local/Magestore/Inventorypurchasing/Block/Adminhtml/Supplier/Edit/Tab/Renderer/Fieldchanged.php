<?php 
class Magestore_Inventorypurchasing_Block_Adminhtml_Supplier_Edit_Tab_Renderer_Fieldchanged
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $supplierHistoryId = $row->getSupplierHistoryId();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $sql = 'SELECT distinct(`field_name`) from ' . $resource->getTableName("erp_inventory_supplier_history_content") . ' WHERE (supplier_history_id = '.$supplierHistoryId.')';
        $results = $readConnection->fetchAll($sql);
        $content = '';
        foreach ($results as $result) {
            $content .= $result['field_name'].'<br />';
        }
        return $content;
    }
}