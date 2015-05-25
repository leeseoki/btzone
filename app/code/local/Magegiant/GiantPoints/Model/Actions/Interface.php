<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Giantpoints Resource Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
interface Magegiant_GiantPoints_Model_Actions_Interface
{
    /**
     * Calculate and return point amount that action has
     * + point amount > 0 => action will add point to customer
     * + point amount < 0 => action will reduce point from customer
     * + point amount = 0 => take no action
     *
     * @return int
     */
    public function getPointAmount();

    /**
     * @return mixed
     */
    public function getComment();

    /**
     * @param null $transaction
     * @return mixed
     */
    public function getCommentHtml($transaction = null);

    /**
     * prepare transaction
     *
     * @return array
     */
    public function updateTransaction();

}
