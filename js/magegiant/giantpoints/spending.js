/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
* https://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_GiantPoints
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */
MagegiantGiantPointsSliderSpending = Class.create();
MagegiantGiantPointsSliderSpending.prototype = {
    initialize: function (config) {
        this.containerSelector = $$(config.containerSelector).first();
        this.rewardRuleContainer = $$(config.rewardRuleContainer).first();
        this.usePointCheckbox = $(config.usePointCheckbox);
        // init urls
        this.applyPointsUrl = config.applyPointsUrl;
        this.initObserver();
    },
    initObserver: function () {
        if (this.usePointCheckbox) {
            this.usePointCheckbox.observe('change', this.applyPoint.bind(this));
        }
    },
    initRewardRule: function () {
        if (this.usePointCheckbox) {
            if (this.usePointCheckbox.checked) {
                this.rewardRuleContainer.show();
            }
            else {
                this.rewardRuleContainer.hide();
            }
        }
    },
    applyPoint: function () {
        var me = this;
        if (this.usePointCheckbox) {
            if (this.usePointCheckbox.checked) {
                this.showRewardRule();
            }
            else {
                this.hideRewardRule();
            }
        }
        var params = Form.serializeElements(this.containerSelector.select('input, select, textarea'));
        var requestOptions = {
            method: 'post',
            parameters: params,
            postBody: params,

        };
        MagegiantGiantPointsCore.updater.startRequest(this.applyPointsUrl, requestOptions);

    },
    showRewardRule: function () {
        var me = this;
        var newHeight = MagegiantGiantPointsCore.getElementHeight(this.rewardRuleContainer);
        this.rewardRuleContainer.setStyle({'display': ''});
        this._applyEffect(this.rewardRuleContainer, newHeight, 0.5, function () {
            me.rewardRuleContainer.setStyle({'height': ''});
        });
    },
    hideRewardRule: function () {
        var me = this;
        this._applyEffect(me.rewardRuleContainer, 0, 0.3, function () {
            me.rewardRuleContainer.setStyle({'display': 'none'});
        });
    },
    _applyEffect: function (element, newHeight, duration, afterFinish) {
        if (element.effect) {
            element.effect.cancel();
        }
        var afterFinishFn = afterFinish || Prototype.emptyFunction;
        element.effect = new Effect.Morph(element, {
            style: {
                'height': newHeight + 'px'
            },
            duration: duration,
            afterFinish: function () {
                delete element.effect;
                afterFinishFn();
            }
        });
    },
}
;
