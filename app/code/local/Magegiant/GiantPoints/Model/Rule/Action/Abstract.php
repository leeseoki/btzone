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
 * Giantpoints Model
 *
 * @category    MageGiant
 * @package     MageGiant_GiantPoints
 * @author      MageGiant Developer
 */
abstract class Magegiant_GiantPoints_Model_Rule_Action_Abstract extends Varien_Object
{

    /**
     * Add more Admin Condition
     *
     * @param $fieldset
     * @return $this
     */
    public function visitAdminConditions(&$fieldset)
    {
        return $this;
    }

    /**
     * Add more action earn point
     *
     * @param $fieldset
     * @return $this
     */
    public function visitAdminActions(&$fieldset)
    {
        return $this;
    }

    /**
     * @param $fieldset
     * @return $this
     */
    public function visitAdminTriggers(&$fieldset)
    {
        return $this;
    }

    /**
     * Add more script to admin form
     *
     * @return array
     */
    public function getAdminFormScripts()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getAdminFormInitScripts()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getNewActions()
    {
        return array();
    }

}
