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
class Magegiant_GiantPoints_Block_Adminhtml_Transaction_Edit_Customer_Popup
    extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magegiant/giantpoints/transaction/customer/popup.phtml');

        return $this;
    }

    /**
     * init serializer block, called from layout
     *
     * @param string $gridName
     * @param string $hiddenInputName
     */
    public function initSerializerBlock($gridName, $hiddenInputName)
    {
        $grid = $this->getLayout()->getBlock($gridName);
        $this->setGridBlock($grid)
            ->setInputElementName($hiddenInputName);
    }
}
