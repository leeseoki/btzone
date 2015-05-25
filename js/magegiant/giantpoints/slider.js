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
/********** Giant Points Slider **********/
var GiantPointsSlider = Class.create(
    {
        initialize: function (pointEl, maxPointEl, pointCr, trackEl, handleEl, zoomOutEl, zoomInEl, pointLbl) {
            this.pointEl = $(pointEl);
            this.maxPointEl = $(maxPointEl);
            this.pointCr = pointCr;
            this.trackEl = $(trackEl);
            this.zoomOutEl = $(zoomOutEl);
            this.zoomInEl = $(zoomInEl);
            this.handleEl = $(handleEl);
            this.pointLbl = $(pointLbl);
            this.minPoints = 0;
            this.maxPoints = 1;
            this.pointStep = 1;
            this.slider = new Control.Slider(this.handleEl, this.trackEl, {
                axis: 'horizontal',
                range: $R(this.minPoints, this.maxPoints),
                values: this.availableValue(),
                onSlide: this.applyPoint.bind(this),
                onChange: this.applyPoint.bind(this),
            });
            this.applyPointCallback = function (v) {
            };
            this.init();
            this.initObserver();

        },
        init: function () {
            this.slider.value = this.pointCr;
        },
        initObserver: function () {
            Event.observe(this.pointEl, 'change', function () {
                this.manualChange(this.pointEl.value);
            }.bind(this));
            if (this.maxPointEl) {
                Event.observe(this.maxPointEl, 'change', function () {
                    if (this.maxPointEl.checked) {
                        this.manualChange(this.maxPoints);
                    } else {
                        this.manualChange(0);
                    }
                }.bind(this));
            }
            Event.observe(this.zoomOutEl, 'click', this.zoomOut.bind(this));
            Event.observe(this.zoomInEl, 'click', this.zoomIn.bind(this));
        },
        availableValue: function () {
            var values = [];
            for (var i = this.minPoints; i <= this.maxPoints; i += this.pointStep) {
                values.push(i);
            }
            return values;
        },
        applyOptions: function (options) {
            this.minPoints = options.minPoints || this.minPoints;
            this.maxPoints = options.maxPoints || this.maxPoints;
            this.pointStep = options.pointStep || this.pointStep;

            this.slider.range = $R(this.minPoints, this.maxPoints);
            this.slider.allowedValues = this.availableValue();
            this.manualChange(this.slider.value);
            this.isMaxPoint();

        },
        isMaxPoint: function () {
            if (this.maxPoints == this.slider.value) {
                if (this.maxPointEl)
                    this.maxPointEl.checked = true;
            }
        },
        applyMaxPoint: function (points) {
            this.maxPointEl.checked = true;
            this.manualChange(this.maxPoints);
        },
        applyPoint: function (points) {
            this.pointEl.value = points;
            if (this.pointLbl) {
                this.pointLbl.innerHTML = points;
            }
            if (typeof this.applyPointCallback == 'function') {
                this.applyPointCallback(points);
            }
        },
        zoomOut: function () {
            var curVal = this.slider.value - this.pointStep;
            if (curVal >= this.minPoints) {
                this.slider.value = curVal;
                this.slider.setValue(curVal);
                this.applyPoint(curVal);
            }
        },
        zoomIn: function () {
            var curVal = this.slider.value + this.pointStep;
            if (curVal <= this.maxPoints) {
                this.slider.value = curVal;
                this.slider.setValue(curVal);
                this.applyPoint(curVal);
            }
        },
        manualChange: function (points) {
            points = this.slider.getNearestValue(parseInt(points));
            this.slider.value = points;
            this.slider.setValue(points);
            this.applyPoint(points);
        },
        changeUseMaxpoint: function (event) {
            var checkEl = event.element();
            if (checkEl.checked) {
                this.manualChange(this.maxPoints);
            } else {
                this.manualChange(0);
            }
        },
        changeUseMaxpointEvent: function (checkEl) {
            Event.observe($(checkEl), 'click', this.changeUseMaxpoint.bind(this));
        },
        manualChangePoint: function (event) {
            var changeEl = event.element();
            this.manualChange(changeEl.value);
        },
        manualChangePointEvent: function (changeEl) {
            Event.observe($(changeEl), 'change', this.manualChangePoint.bind(this));
        }
    }
);
