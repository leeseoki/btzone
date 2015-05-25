<?php

class Magestore_Inventoryreports_Block_Adminhtml_Reportcontent_Renderer_Ordertimerange extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = '';
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('top_filter'));
        switch ($requestData['report_radio_select']){
            case 'hours_of_day':
                $html = $row->getTimeRange().':00' .' - '. $row->getTimeRange().':59';
                break;
            case 'days_of_week':
                $daysofweek = Mage::helper('inventoryreports')->getDaysOfWeek();
                $html .= $daysofweek[$row->getTimeRange()];
                break;
        }
        return $html;
    }
}

