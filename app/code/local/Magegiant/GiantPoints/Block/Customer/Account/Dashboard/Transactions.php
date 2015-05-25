<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Customer_Account_Dashboard_Transactions extends Magegiant_GiantPoints_Block_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $collection = Mage::getResourceModel('giantpoints/transaction_collection')
            ->addFieldToFilter('customer_id', $customerId);
        $collection->getSelect()->order('change_date DESC');
        $this->setCollection($collection);
    }

    /**
     * @return $this|Mage_Core_Block_Abstract
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'transactions_pager')
            ->setCollection($this->getCollection());
        $this->setChild('transactions_pager', $pager);

        return $this;
    }

    /**
     * get pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('transactions_pager');
    }
}
