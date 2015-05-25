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
class Magegiant_GiantPoints_Model_Rule_Action extends Varien_Object
{
    const ACTION_SIGN_UP       = 'giantpoints_customer_signup';
    const ACTION_NEWSLETTER    = 'giantpoints_customer_signup_newsletter';
    const ACTION_WRITES_REVIEW = 'giantpoints_customer_writes_review';
    const ACTION_TAG           = 'giantpoints_customer_tags_product';
    const ACTION_POLL          = 'giantpoints_customer_take_poll';
    const ACTION_SEND_FRIEND   = 'giantpoints_customer_send_friend';
    const ACTION_BIRTHDAY      = 'giantpoints_customer_birthday';
    const ACTION_LIKE_FACEBOOK = 'giantpoints_customer_like_facebook';
    const ACTION_GOOGLE_PLUS   = 'giantpoints_customer_google_plus';
    protected $behaviorRules = array();
    protected $hasBehaviorRules = false;

    public function _construct()
    {
        parent::_construct();
    }

    public function getActionArray()
    {
        $options = $this->getOptionsArray();
        unset ($options ['']);

        return $options;
    }

    public function getOptionsArray()
    {
        if (!Mage::helper('core')->isModuleEnabled('Magegiant_GiantPointsBehavior')) {
            $rules = array('' => '', //include the null option so the user can pick nothing
            );
        } else {
            $rules = array(''                         => '', //include the null option so the user can pick nothing
                           self::ACTION_SIGN_UP       => Mage::helper('giantpoints')->__('Signs up'),
                           self::ACTION_NEWSLETTER    => Mage::helper('giantpoints')->__('Signs up for a newsletter'),
                           self::ACTION_WRITES_REVIEW => Mage::helper('giantpoints')->__('Writes a review'),
                           self::ACTION_TAG           => Mage::helper('giantpoints')->__('Tags a product'),
                           self::ACTION_POLL          => Mage::helper('giantpoints')->__('Votes in poll'),
                           self::ACTION_SEND_FRIEND   => Mage::helper('giantpoints')->__('Send email to friends'),
            );
        }
        foreach ($this->getBehaviorRules() as $rule) {
            $rules += $rule->getCustomerConditions();
        }

        return $rules;
    }

    public function getActionOptionsArray()
    {
        $base_actions = array(
            'grant_points' => Mage::helper('giantpoints')->__('Give points to the customer')
        );
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $base_actions = array_merge($base_actions, $rule->getNewActions());
        }

        return $base_actions;
    }

    public function getAdminFormScripts()
    {
        $base_scripts = array();
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $base_scripts = array_merge($base_scripts, $rule->getAdminFormScripts());
        }

        return $base_scripts;
    }

    public function getAdminFormInitScripts()
    {
        $base_scripts = array();
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $base_scripts = array_merge($base_scripts, $rule->getAdminFormInitScripts());
        }

        return $base_scripts;
    }

    public function getBehaviorRules()
    {
        if ($this->hasBehaviorRules)
            return $this->behaviorRules;
        $special_rule = Mage::getConfig()->getNode('global/giantpoints/behavior/rule');
        $rules        = array();
        if ($special_rule) {
            $code_nodes = (array)$special_rule->children();
            usort($code_nodes, array($this, "sortRules"));
            foreach ($code_nodes as $code => $special) {
                $special = (array)$special;
                if (isset ($special['class'])) {
                    $model_code = $special['class'];
                } else {
                    throw new Exception ("Action model for special rule code '$code' is not specified.");
                }
                $config_model = Mage::getModel($model_code);
                if (!($config_model instanceof Magegiant_GiantPoints_Model_Rule_Action_Abstract)) {
                    throw new Exception ("Config model for special rule code '$code' should extend Magegiant_GiantPoints_Model_Rule_Action_Abstract but it doesn't . ");
                }
                $rules[$code] = $config_model;
            }
        }
        $this->behaviorRules    = $rules;
        $this->hasBehaviorRules = true;

        return $this->behaviorRules;
    }

    public function sortRules($a, $b)
    {
        $a = (array)$a;
        $b = (array)$b;
        $a = isset($a['sort_order']) ? $a['sort_order'] : 0;
        $b = isset($b['sort_order']) ? $b['sort_order'] : 0;
        if ($a == $b)
            return 0;

        return ($a > $b) ? 1 : -1;
    }

    public function visitAdminTriggers(&$fieldset)
    {
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $rule->visitAdminTriggers($fieldset);
        }

        return $this;
    }

    public function visitAdminActions(&$fieldset)
    {
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $rule->visitAdminActions($fieldset);
        }

        return $this;
    }

    public function visitAdminConditions(&$fieldset)
    {
        foreach ($this->getBehaviorRules() as $code => $rule) {
            $rule->visitAdminConditions($fieldset);
        }

        return $this;
    }

    public function getProbationalBehaviors()
    {
        $rules = array();
        if (Mage::getConfig()->getNode('global/giantpoints/behavior/probational') != null) {
            $code_nodes = Mage::getConfig()->getNode('global/giantpoints/behavior/probational')->children();
            foreach ($code_nodes as $code => $sansData) {
                $rules[$code] = $code;
            }
        }

        return $rules;
    }
}
