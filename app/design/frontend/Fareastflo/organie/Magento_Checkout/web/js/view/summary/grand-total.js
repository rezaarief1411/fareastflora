/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'dropdownDialog'
], function (Component, quote, $, dropdownDialog) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/grand-total'
        },

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

        /**
         * Get pure value.
         */
        getPureValue: function () {
            var totals = quote.getTotals()();

            if (totals) {
                return totals['base_grand_total'];
            }

            return quote['base_grand_total'];
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        /**
         *
         * @returns {*}
         */
        getGrandTotalExclTax: function () {
            var totals = quote.getTotals()();

            if (!totals) {
                return 0;
            }

            return this.getFormattedPrice(totals['grand_total']);
        },

        /**
         *
         * @returns {*}
         */
        getTaxMount: function () {
            var totals = quote.getTotals()();

            if (!totals) {
                return 0;
            }

            return this.getFormattedPrice(totals['tax_amount']);
        },

        loadDropdown: function () {
            $(".excl").dropdownDialog({
                appendTo: "[data-block=dropdownTotal]",
                triggerTarget:"[data-trigger=triggerTotal]",
                autoOpen: false,
                timeout: 2000,
                closeOnMouseLeave: false,
                closeOnEscape: false,
                closeOnClickOutside: false,
                triggerClass: "active",
                parentClass: "active",
                buttons: []
            });
        }
    });
});
