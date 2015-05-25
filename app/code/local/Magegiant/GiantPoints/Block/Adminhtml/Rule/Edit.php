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
 * Giantpoints Adminhtml Block
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @author      Magegiant Developer
 */
class Magegiant_GiantPoints_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    const PAGE_TABS_BLOCK_ID = 'manage_behavior_rule_tabs';

    public function __construct()
    {
        parent::__construct();
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'giantpoints';

        $this->_updateButton('save', 'label', Mage::helper('giantpoints')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('giantpoints')->__('Delete Rule'));

        $this->_addButton('saveandcontinue', array(
                'label'   => Mage::helper('salesrule')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class'   => 'save'
            ),
            -100
        );

        $this->_formScripts [] = "
            function saveAndContinueEdit(urlTemplate){
                 var urlTemplateSyntax = /(^|.|\\r|\\n)({{(\\w+)}})/;
                var template = new Template(urlTemplate, urlTemplateSyntax);
                var url = template.evaluate({tab_id:" . self::PAGE_TABS_BLOCK_ID . "JsTabs.activeTab.id});
                editForm.submit(url);
            }

        ";
        try {
            $additional_scripts = Mage::getSingleton('giantpoints/rule_action')->getAdminFormScripts();
            $this->_formScripts = array_merge($this->_formScripts, $additional_scripts);

            $additional_init_scripts = Mage::getSingleton('giantpoints/rule_action')->getAdminFormInitScripts();
            $this->_formInitScripts  = array_merge($this->_formInitScripts, $additional_init_scripts);
        } catch (Exception $e) {
        }

        $this->_formInitScripts [] = <<<TOGGLE_ONHOLD
            function toggleOnholdEnabled(isEnabled) {
                var rule_onhold_duration = $('rule_onhold_duration').up().up();
                if (isEnabled == 1) {
                    rule_onhold_duration.show();
                    $('rule_onhold_duration').addClassName('validate-notzero')
                        .addClassName('validate-not-negative-number');
                } else {
                    rule_onhold_duration.hide();
                    $('rule_onhold_duration').removeClassName('validate-notzero')
                        .removeClassName('validate-not-negative-number');
                }
            }
TOGGLE_ONHOLD;
        $this->_formInitScripts [] = "toggleOnholdEnabled($('rule_is_onhold_enabled').value)";

        try {
            $behavior_checks = array();
            $behaviors       = Mage::getSingleton('giantpoints/rule_action')->getProbationalBehaviors();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        if (count($behaviors) > 0) {
            foreach ($behaviors as $key) {
                $behavior_checks[] = "
                    if (action == '{$key}') {
                        rule_is_onhold_enabled.show();
                    }";
            }
        } else {
            // If there are no behavior checks, just make an empty IF statement that does nothing.
            $behavior_checks[] = "if(true) { }";
        }

        $jsIfBehaviorIsProbational = implode(" else ", $behavior_checks);

        $this->_formInitScripts [] = <<<ONHOLD_AVAILABLE
            function toggleActionsSelect(action) {
                var rule_is_onhold_enabled = $('rule_is_onhold_enabled').up().up();
                {$jsIfBehaviorIsProbational}
                else {
                    $('rule_is_onhold_enabled').value = 0;
                    toggleOnholdEnabled(0);
                    rule_is_onhold_enabled.hide();
                }
            }

            // update the onchange events for the rule_points_conditions field.
            document.observe('dom:loaded', function() {
                Event.observe('rule_points_conditions', 'change', function() {
                    toggleActionsSelect(this.value);
                });
            });

ONHOLD_AVAILABLE;
        $this->_formInitScripts [] = "toggleActionsSelect($('rule_points_conditions').value)";

        $defaultCustomerGroupLabel   = $this->__('Customer Group Is');
        $referredCustomerGroupLabel  = $this->__('Referred Customer Group Is');
        $affiliateCustomerGroupLabel = $this->__('Affiliate Customer Group Is');
        $this->_formInitScripts[]    = <<<STINIT
            var MagegiantGiantPoints = MagegiantGiantPoints || {};

            document.observe('dom:loaded', function() {
                MagegiantGiantPoints.init();
            });

            MagegiantGiantPoints.init = function()
            {
                var self = this;
                var conditionsField = $('rule_points_conditions');
                conditionsField.observe('change', function() {
                    self.conditionsListener(this);
                });

                var initalCondition = conditionsField.value;
                this.refreshCustomerGroupCondition(initalCondition);

                return self;
            };

            MagegiantGiantPoints.conditionsListener = function(element)
            {
                var condition = element.value;
                this.refreshCustomerGroupCondition(condition);
                return this;
            };

            MagegiantGiantPoints.refreshCustomerGroupCondition = function(condition)
            {
                var customerGroupConditionField = $('rule_customer_group_ids');
                if (!customerGroupConditionField) {
                    return this;
                }
                var oldLabelHtml = customerGroupConditionField.up('tr').down('label'),
                    newLabelHtml = '';
                if (!condition.indexOf('giantpoints_milestone_referral')) {
                    newLabelHtml = '{$affiliateCustomerGroupLabel} <span class="required">*</span>';
                } else if (!condition.indexOf('giantpoints_customer_referral')) {
                    newLabelHtml = '{$referredCustomerGroupLabel} <span class="required">*</span>';

                } else {
                    newLabelHtml =  '{$defaultCustomerGroupLabel} <span class="required">*</span>';
                }
                oldLabelHtml.update(newLabelHtml);

                return this;
            };
STINIT;
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab'        => '{{tab_id}}',
            'active_tab' => null
        ));
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('behavior_rule_data');
        if ($rule->getId()) {
            return Mage::helper('giantpoints')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        } else {
            return Mage::helper('giantpoints')->__('New Rule');
        }
    }

}
