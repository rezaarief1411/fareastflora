define([
        'Magento_Tax/js/view/checkout/summary/shipping',
        'Magento_Checkout/js/model/quote'
    ], function (Component, quote) {
        'use strict';

        var mixin = {
            getShippingMethodTitle: function () {
                this._super();
                var shippingMethod,
                    shippingMethodTitle = '';

                if (!this.isCalculated()) {
                    return '';
                }

                shippingMethod = quote.shippingMethod();

                if (!_.isArray(shippingMethod) && !_.isObject(shippingMethod)) {
                    return '';
                }

                if (typeof shippingMethod['method_title'] !== 'undefined') {
                    shippingMethodTitle = shippingMethod['method_title'] + ':';
                }

                return shippingMethodTitle ? shippingMethodTitle : '';
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
