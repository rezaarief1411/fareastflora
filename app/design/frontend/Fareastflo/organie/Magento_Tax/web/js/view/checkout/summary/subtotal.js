/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;
    var baseOriginalSubtotal = window.checkoutConfig.base_original_subtotal;
    var totalSaving = window.checkoutConfig.total_saving;

    return Component.extend({
        defaults: {
            displaySubtotalMode: displaySubtotalMode,
            template: 'Magento_Tax/checkout/summary/subtotal',
        },
        totals: quote.getTotals(),
        baseOriginalSubtotal: baseOriginalSubtotal,
        totalSaving: totalSaving,

        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals().subtotal;
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displaySubtotalMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingTaxDisplayed: function () {
            return this.displaySubtotalMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|String}
         */
        getValueInclTax: function () {
            var price = 0;
            if (this.totals()) {
                this.totals()["items"].forEach(function (item) {
                    price += item["row_total_incl_tax"];
                });
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @returns {*|String|string}
         */
        getBaseOriginalSubtotal: function () {
            var originalSegments;

            if (!this.totals()) {
                return null;
            }

            originalSegments = this.totals()['total_segments'].filter(function (segment) {
                return segment.code.indexOf('base_original_subtotal') !== -1;
            });
            return originalSegments[0].value ? this.getFormattedPrice(originalSegments[0].value) : '';
        },

        /**
         * @returns {*|String|string}
         */
        getTotalSaving: function (){
            return totalSaving !== false?this.getFormattedPrice(totalSaving):'';
        }
    });
});
