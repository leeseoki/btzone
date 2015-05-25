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
 * @package     Magestore_Inventorywarehouse
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorywarehouse Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorywarehouse
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Model_Purchaseorder_Refund extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'inventorypurchasing_purchaseorder_refund';
    protected $_eventObject = 'inventorypurchasing_purchaseorder_refund';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('inventorypurchasing/purchaseorder_refund');
    }
}