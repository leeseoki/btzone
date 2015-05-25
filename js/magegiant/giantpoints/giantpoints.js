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

/********** Update JS Prototype - Fix for Magento 1.4 **********/
if (typeof Element.clone == 'undefined') {
    Element.clone = function (element, deep) {
        if (!(element = $(element)))
            return;
        var clone = element.cloneNode(deep);
        clone._prototypeUID = void 0;
        if (deep) {
            var descendants = Element.select(clone, '*'),
                i = descendants.length;
            while (i--) {
                descendants[i]._prototypeUID = void 0;
            }
        }
        return Element.extend(clone);
    };
}

/*Magegiant Core Update*/

var MagegiantGiantPointsCore = {
    initialize: function () {
        this.updater = MagegiantGiantPointsCoreUpdater;
        Ajax.Responders.register({
            onComplete: function (response) {
                if (response.transport.status === 403) {
                    document.location.reload();
                }
            }
        });
    },

    getCssLoaderClassForBlock: function (block, loaderConfig) {
        var blockHeight = this.getElementHeight(block);
        var classNameConfigKey = '16px';
        Object.keys(loaderConfig).each(function (key) {
            if (parseInt(key) < blockHeight && parseInt(key) > parseInt(classNameConfigKey)) {
                classNameConfigKey = key;
            }
        });

        return loaderConfig[classNameConfigKey];
    },

    addLoaderOnBlock: function (block, loaderConfig) {
        var cssClassName = this.getCssLoaderClassForBlock(block, loaderConfig);
        if (!cssClassName) {
            return;
        }
        var loader = new Element('div');
        loader.addClassName(cssClassName);
        block.insertBefore(loader, block.down());
    },

    removeLoaderFromBlock: function (block, loaderConfig) {
        Object.values(loaderConfig).each(function (cssClasses) {
            var selector = "." + cssClasses.split(" ").join(".");
            block.select(selector).each(function (el) {
                el.remove();
            });
        });
    },

    showMsg: function (msg, cssClass, targetBlock) {
        var me = this;
        if ((typeof(msg) === "object") && ("length" in msg)) {
            msg.each(function (msgItem) {
                me._addMsgToBlock(msgItem, cssClass, targetBlock);
            });
        } else if (typeof(msg) === "string") {
            this._addMsgToBlock(msg, cssClass, targetBlock);
        }
    },

    removeMsgFromBlock: function (block, className) {
        var blocks = block.select("." + className);
        blocks.each(function (el) {
            el.remove();
        });
    },

    _addMsgToBlock: function (msg, cssClass, parentContainer) {
        var targetBlock = null;
        var existsErrorBlocks = parentContainer.select("." + cssClass + " ul");
        if (existsErrorBlocks.length === 0) {
            var errorMsgBlock = new Element('div');
            errorMsgBlock.addClassName(cssClass);
            errorMsgBlock.appendChild(new Element('ul'));
            parentContainer.insertBefore(errorMsgBlock, parentContainer.down());
            targetBlock = errorMsgBlock.down();
        } else {
            targetBlock = existsErrorBlocks.first();
        }
        var newMsg = new Element('li');
        newMsg.update(msg);
        targetBlock.appendChild(newMsg);
    },

    getElementHeight: function (element) {
        var element = $(element);
        var origDimensions = element.getDimensions();
        var origHeight = element.style.height;
        var origDisplay = element.style.display;
        var origVisibility = element.style.visibility;
        element.setStyle({
            'height': '',
            'display': '',
            'visibility': 'hidden'
        });
        var height = Math.max(element.getDimensions()['height'], origDimensions['height']);
        element.setStyle({
            'height': origHeight,
            'display': origDisplay,
            'visibility': origVisibility
        });
        return height;
    },
    loadPaymentSpendingPointOnestep: function (block, elements) {
        block.insert({
            top: elements.html
        });
    },
    loadPaymentSpendingPoint: function (block, elements) {
        if (!this.isLoadedPaymentSpendingPoint) {
            block.insert({
                before: elements.html
            });
            this.isLoadedPaymentSpendingPoint = true;
        }
        if (this.reloadPaymentSpending) {
            this._removePaymentSpendingPoint();
            block.insert({
                before: elements.html
            });
            this.reloadPaymentSpending = false;
        }
        if ($('p_method_free')) {
            $('p_method_free').checked = true;
        }
    },
    _removePaymentSpendingPoint: function () {
        if ($('giantpoints-checkout-payment-method-load'))
            $('giantpoints-checkout-payment-method-load').remove();
    }
};

var MagegiantGiantPointsCoreUpdater = {
    setConfig: function (config) {
        this.currentRequest = null;
        this.requestQueue = [];
        this.queue = {};
        this.blocks = {};

        var me = this;
        if (config.blocks) {
            config.blocks.each(function (block) {
                me._registerBlockNameForUpdate(block[0], block[1]);
            });
        }
        this.loaderConfig = config.loaderConfig;
        this.loaderToBlockCssClass = config.loaderToBlockCssClass;
        this.map = config.map;
    },

    startRequest: function (url, options) {
        var action = this._getActionFromUrl(url);
        this.addActionBlocksToQueue(action);
        if (this.currentRequest === null) {
            this.runRequest(url, options);
        } else {
            this.requestQueue.push([url, options]);
        }
    },

    addActionBlocksToQueue: function (action) {
        if (!action || !this.map[action]) {
            return;
        }
        var me = this;
        this.map[action].each(function (blockName) {
            if (typeof(me.queue[blockName]) === 'undefined') {
                me.queue[blockName] = 0;
            }
            if (!me.blocks[blockName]) {
                return;
            }
            if (me.queue[blockName] === 0) {
                var targetBlockForAddLoader = me.blocks[blockName].select('.' + me.loaderToBlockCssClass).first();
                if (!targetBlockForAddLoader) {
                    targetBlockForAddLoader = me.blocks[blockName];
                }
                if ("addActionBlocksToQueueBeforeFn" in me.blocks[blockName]) {
                    me.blocks[blockName].addActionBlocksToQueueBeforeFn();
                }
                MagegiantGiantPointsCore.addLoaderOnBlock(targetBlockForAddLoader, me.loaderConfig);
                if ("addActionBlocksToQueueAfterFn" in me.blocks[blockName]) {
                    me.blocks[blockName].addActionBlocksToQueueAfterFn();
                }
            }
            me.queue[blockName]++;
        });
    },

    removeActionBlocksFromQueue: function (action, response) {
        if (!action || !this.map[action]) {
            return;
        }
        var response = response || {};
        var blocksNewHtml = response.blocks || {};
        var me = this;
        this.map[action].each(function (blockName) {
            if (!me.blocks[blockName]) {
                return;
            }
            me.queue[blockName]--;
            if (me.queue[blockName] === 0) {
                if (blocksNewHtml[blockName]) {
                    me.blocks[blockName].update(blocksNewHtml[blockName]);
                }
                if ("removeActionBlocksFromQueueBeforeFn" in me.blocks[blockName]) {
                    me.blocks[blockName].removeActionBlocksFromQueueBeforeFn(response);
                }
                MagegiantGiantPointsCore.removeLoaderFromBlock(me.blocks[blockName], me.loaderConfig);
                if ("removeActionBlocksFromQueueAfterFn" in me.blocks[blockName]) {
                    me.blocks[blockName].removeActionBlocksFromQueueAfterFn(response);
                }
            }
        });
    },

    runRequest: function (url, options) {
        var options = options || {};
        var me = this;
        var ajaxRequestOptions = Object.extend({}, options);
        ajaxRequestOptions = Object.extend(ajaxRequestOptions, {
            onComplete: function (transport) {
                me.onRequestCompleteFn(transport);
                if (options.onComplete) {
                    options.onComplete(transport);
                }
            }
        });
        this.currentRequest = new Ajax.Request(url, ajaxRequestOptions);
    },

    onRequestCompleteFn: function (transport) {
        try {
            eval("var response = " + transport.responseText);
        } catch (e) {
            //error
            var response = {
                blocks: {}
            };
        }
        var action = this._getActionFromUrl(transport.request.url);
        this.removeActionBlocksFromQueue(action, response);
        this.currentRequest = null;
        if (this.requestQueue.length > 0) {
            this._clearQueue();
            var args = this.requestQueue.shift();
            this.runRequest(args[0], args[1]);
        }
        if (response.reload) {
            window.location.reload();
        }
        if (response.error) {
            console.log(response.error);
        }
    },

    _registerBlockNameForUpdate: function (name, selector) {
        var element = $$(selector).first();
        if (!element) {
            return;
        }
        if (typeof(this.blocks[name]) != 'undefined') {
            return;
        }
        this.blocks[name] = element;
    },

    _getActionFromUrl: function (url) {
        var matches = url.match(/giantpoints\/ajax\/([^\/]+)\//);
        if (!matches || !matches[1]) {
            return null;
        }
        return matches[1];
    },

    _clearQueue: function () {
        var me = this;
        var foundedActions = [];
        var requestToRemove = [];
        this.requestQueue.reverse().each(function (args, key) {
            var url = args[0];
            var action = me._getActionFromUrl(url);
            if (foundedActions.indexOf(action) === -1) {
                foundedActions.push(action);
            } else {
                requestToRemove.push(key);
            }
        });
        var newQueue = [];
        this.requestQueue.each(function (args, key) {
            if (requestToRemove.indexOf(key) === -1) {
                newQueue.push(args);
            } else {
                var action = me._getActionFromUrl(args[0]);
                me.removeActionBlocksFromQueue(action);
            }
        })
        this.requestQueue = newQueue.reverse();
    }
};
MagegiantGiantPointsCore.initialize();
/*\Magegiant Core Update*/
