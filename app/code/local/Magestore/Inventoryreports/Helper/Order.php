<?php

class Magestore_Inventoryreports_Helper_Order extends Mage_Core_Helper_Abstract {

    public function getHoursofdayReportCollection($datefrom, $dateto) {
        $arrayCollection = array();
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id ', array('count_item_id' => 'IFNULL(COUNT( DISTINCT orderitem.item_id),0)')
        );
        $collection->getSelect()->group('hour(main_table.created_at)');
        $collection->getSelect()->columns(array(
            'time_range' => 'hour(main_table.created_at)',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)'
        ));
        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
            'count_item_id' => 'orderitem',
        );
        return $arrayCollection;
    }

    public function getDaysofweekReportCollection($datefrom, $dateto) {
        $arrayCollection = array();
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFieldToFilter('main_table.created_at', array(
            'from' => $datefrom,
            'to' => $dateto,
            'date' => true,
        ));
        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id ', array('orderitem.item_id', 'count_item_id' => 'IFNULL(COUNT( DISTINCT orderitem.item_id),0)')
        );

        $collection->getSelect()->group('dayofweek(main_table.created_at)');
        $collection->getSelect()->columns(array(
            'time_range' => 'dayofweek(main_table.created_at)',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)'
        ));
        $arrayCollection['collection'] = $collection;
        $arrayCollection['filter'] = array(
            'default' => 'main_table',
            'count_item_id' => 'orderitem',
        );
        return $arrayCollection;
    }

    public function getInvoiceReportCollection($datefrom, $dateto) {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
                ->addFieldToFilter('main_table.created_at', array(
                    'from' => $datefrom,
                    'to' => $dateto,
                    'date' => true,
                ))
                ->addFieldToFilter('base_subtotal_invoiced', array('gt' => 0));


        $collection->getSelect()->joinLeft(array(
            'orderinvoice' => $collection->getTable('sales/invoice')), 'main_table.entity_id = orderinvoice.order_id', array('count_invoice_id' => 'IFNULL(COUNT( DISTINCT orderinvoice.entity_id),0)')
        );

        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id AND orderitem.parent_item_id IS NULL', array('orderitem.item_id')
        );

        $collection->getSelect()->joinLeft(array(
            'invoiceitem' => $collection->getTable('sales/invoice_item')), 'orderinvoice.entity_id = invoiceitem.parent_id AND orderitem.item_id = invoiceitem.order_item_id', array('sum_invoice_item_qty' => 'IFNULL(SUM(invoiceitem.qty),0)')
        );

        $collection->getSelect()->group('main_table.entity_id');

        $collection->getSelect()->columns(array(
            'order_id' => 'main_table.increment_id',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount_invoiced' => 'IFNULL(SUM(main_table.base_tax_invoiced),0)',
            'sum_tax_amount_invoiced' => 'IFNULL(SUM(main_table.tax_invoiced),0)',
            'sum_subtotal_invoiced' => 'IFNULL(SUM(main_table.subtotal_invoiced),0)',
            'sum_base_subtotal_invoiced' => 'IFNULL(SUM(main_table.base_subtotal_invoiced),0)',
            'sum_grand_total_invoiced' => 'IFNULL(SUM(main_table.total_invoiced),0)',
            'sum_base_grand_total_invoiced' => 'IFNULL(SUM(main_table.base_total_invoiced),0)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)'
        ));

        return $collection;
    }

    public function getCreditmemoReportCollection($datefrom, $dateto) {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
                ->addFieldToFilter('main_table.created_at', array(
                    'from' => $datefrom,
                    'to' => $dateto,
                    'date' => true,
                ))
                ->addFieldToFilter('base_subtotal_refunded', array('gt' => 0));


        $collection->getSelect()->joinLeft(array(
            'ordercreditmemo' => $collection->getTable('sales/creditmemo')), 'main_table.entity_id = ordercreditmemo.order_id', array('count_creditmemo_id' => 'IFNULL(COUNT( DISTINCT ordercreditmemo.entity_id),0)')
        );

        $collection->getSelect()->joinLeft(array(
            'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id AND orderitem.parent_item_id IS NULL', array('orderitem.item_id')
        );

        $collection->getSelect()->joinLeft(array(
            'creditmemoitem' => $collection->getTable('sales/creditmemo_item')), 'ordercreditmemo.entity_id = creditmemoitem.parent_id AND orderitem.item_id = creditmemoitem.order_item_id', array('sum_creditmemo_item_qty' => 'IFNULL(SUM(creditmemoitem.qty),0)')
        );

        $collection->getSelect()->group('main_table.entity_id');

        $collection->getSelect()->columns(array(
            'order_id' => 'main_table.increment_id',
            'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
            'sum_base_tax_amount_refunded' => 'IFNULL(SUM(main_table.base_tax_refunded),0)',
            'sum_tax_amount_refunded' => 'IFNULL(SUM(main_table.tax_refunded),0)',
            'sum_subtotal_refunded' => 'IFNULL(SUM(main_table.subtotal_refunded),0)',
            'sum_base_subtotal_refunded' => 'IFNULL(SUM(main_table.base_subtotal_refunded),0)',
            'sum_grand_total_refunded' => 'IFNULL(SUM(main_table.total_refunded),0)',
            'sum_base_grand_total_refunded' => 'IFNULL(SUM(main_table.base_total_refunded),0)',
            'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
            'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
            'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)'
        ));
//        Zend_Debug::Dump(count($collection));
//        die();
        return $collection;
    }

    //get sales order report collection for table data
    public function getOrderReportCollection($requestData) {
        //variable request data
        $time_request = $requestData['select_time'];
        $report_type = $requestData['report_radio_select'];
        $arrayCollection = array();
        $datefrom = '';
        $dateto = '';
        //get time range
        if ($time_request == 'range') {
            if (isset($requestData['date_from'])) {
                $datefrom = $requestData['date_from'];
            } else {
                $now = now();
                $datefrom = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            if (isset($requestData['date_to'])) {
                $dateto = $requestData['date_to'];
            } else {
                $now = now();
                $dateto = date("Y-m-d", Mage::getModel('core/date')->timestamp($now));
            }
            $datefrom = $datefrom . ' 00:00:00';
            $dateto = $dateto . ' 23:59:59';
        } else {
            $time_range = Mage::helper('inventoryreports')->getTimeSelected($requestData);
            if (isset($time_range['date_from']) && isset($time_range['date_to'])) {
                $datefrom = $time_range['date_from'];
                $dateto = $time_range['date_to'];
            }
        }
        /* Prepare Collection */
        //switch report type
        switch ($report_type) {
            case 'hours_of_day':
                return $this->getHoursofdayReportCollection($datefrom, $dateto);
            case 'days_of_week':
                return $this->getDaysofweekReportCollection($datefrom, $dateto);
            case 'invoice':
                return $this->getInvoiceReportCollection($datefrom, $dateto);
            case 'refund':
                return $this->getCreditmemoReportCollection($datefrom, $dateto);
            case 'order_attribute':
                $collection = Mage::getModel('sales/order')->getCollection();
                $attribute = $requestData['attribute_select'];
                $cData = clone $collection;
                $cData = $cData->getFirstItem()->getData();
                if (!isset($cData[$attribute])) {
                    $collection = $this->prepareOrderAttributeCollection($attribute, $datefrom, $dateto);
                    $arrayCollection['collection'] = $collection;
                    $arrayCollection['filter'] = array(
                        'default' => 'main_table',
                        'count_item_id' => 'orderitem',
                        'count_entity_id' => 'order',
                    );
                    return $arrayCollection;
                } else {
                    $collection->addFieldToFilter('main_table.created_at', array(
                        'from' => $datefrom,
                        'to' => $dateto,
                        'date' => true,
                    ));
                    $collection->getSelect()->joinLeft(array(
                        'orderitem' => $collection->getTable('sales/order_item')), 'main_table.entity_id = orderitem.order_id', array('orderitem.item_id', 'count_item_id' => 'IFNULL(COUNT( DISTINCT orderitem.item_id),0)')
                    );
                    //echo $collection->getSelect()->__toString();die();
                    $collection->getSelect()->columns(array(
                        'att_' . $attribute => $attribute,
                        'att_shipping_method' => 'IFNULL(main_table.shipping_description,"No Shipping")',
                        'count_entity_id' => 'COUNT(DISTINCT main_table.entity_id)',
                        'sum_base_tax_amount' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
                        'sum_tax_amount' => 'IFNULL(SUM(main_table.tax_amount),0)',
                        'sum_subtotal' => 'IFNULL(SUM(main_table.subtotal),0)',
                        'sum_base_subtotal' => 'IFNULL(SUM(main_table.base_subtotal),0)',
                        'sum_grand_total' => 'IFNULL(SUM(main_table.grand_total),0)',
                        'sum_base_grand_total' => 'IFNULL(SUM(main_table.base_grand_total),0)'
                    ));
                    $collection->getSelect()->group('main_table.' . $attribute);
                    $arrayCollection['collection'] = $collection;
                    $arrayCollection['filter'] = array(
                        'default' => 'main_table',
                        'count_item_id' => 'orderitem',
                    );
//                    echo $collection->getSelect()->__toString();die();
                    return $arrayCollection;
                }
        }
        /* end Prepare Collection */
    }

    public function getOrderAttributeClass($class) {
        return 'sales/order_' . $class;
    }

    //collection for order attribute
    public function prepareOrderAttributeCollection($attribute, $dateFrom, $dateTo) {
        $elements = explode('_', $attribute, 2);
        $class = $elements[0];
        $field = $elements[1];
        $resource = $this->getOrderAttributeClass($class);
        $collection = Mage::getModel($resource)->getCollection();
        $orderField = 'order_id';
        if ($class == 'payment') {
            $orderField = 'parent_id';
            $collection->getSelect()->join(
                    array('core_config' => $collection->getTable('core/config_data')), 'core_config.path LIKE CONCAT("payment/",`main_table`.`method`,"/title")', array('att_payment_method' => 'core_config.value')
            );
        }
        if ($class != 'payment') {
            $collection->getSelect()->columns(array(
                'att_' . $attribute => "main_table.$field",));
        }
        $collection->getSelect()
                ->joinLeft(array(
                    'order' => $collection->getTable('sales/order')), '`order`.`created_at` >= \'' . $dateFrom . '\' and `order`.`created_at` <= \'' . $dateTo . '\' and `main_table`.`' . $orderField . '` = `order`.`entity_id`', array("*")
                )
                ->joinLeft(array(
                    'orderitem' => $collection->getTable('sales/order_item')), 'order.entity_id = orderitem.order_id', array('count_item_id' => 'IFNULL(COUNT( DISTINCT orderitem.item_id),0)')
                )
                ->group('main_table.' . $field)
        ;

        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $collection->getSelect()->columns(array(
            'count_entity_id' => 'IFNULL(COUNT(DISTINCT `order`.`entity_id`),0)',
            'sum_base_tax_amount' => 'IFNULL(SUM(`order`.`base_tax_amount`),0)',
            'sum_tax_amount' => 'IFNULL(SUM(`order`.`tax_amount`),0)',
            'sum_subtotal' => 'IFNULL(SUM(`order`.`subtotal`),0)',
            'sum_base_subtotal' => 'IFNULL(SUM(`order`.`base_subtotal`),0)',
            'sum_grand_total' => 'IFNULL(SUM(`order`.`grand_total`),0)',
            'sum_base_grand_total' => 'IFNULL(SUM(`order`.`base_grand_total`),0)',
            'base_currency_code' => "IFNULL(`order`.`base_currency_code`,'" . $currencyCode . "')",
            'order_currency_code' => "IFNULL(`order`.`order_currency_code`,'" . $currencyCode . "')"
        ));
        return $collection;
    }

}
