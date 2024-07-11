/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'jquery',
    'dropdownDialog'
], function (Component, quote, priceUtils, totals, $, dropdownDialog) {
    'use strict';

    var totalSaving = window.checkoutConfig.total_saving;

    return Component.extend({
        defaults: {
            isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
            template: 'Magento_Tax/checkout/summary/grand-total'
        },
        totals: quote.getTotals(),
        products: quote.getItems(),
        isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;
            if (this.totals()) {
                this.totals()["items"].forEach(function (item) {
                    price += item["row_total_incl_tax"];
                });

                if (this.totals()["shipping_incl_tax"] != undefined) {
                    price += this.totals()["shipping_incl_tax"];
                }
            }
            // if (this.totals()["discount_amount"] != undefined) {
            //     price += this.totals()["discount_amount"];
            // }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*|String}
         */
        getBaseValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['base_grand_total'];
            }

            return priceUtils.formatPrice(price, quote.getBasePriceFormat());
        },

        /**
         * @return {*}
         */
        getGrandTotalExclTax: function () {
            var total = this.totals();

            if (!total) {
                return 0;
            }

            return this.getFormattedPrice(total['grand_total']);
        },

        getTaxPercent: function () {
            var taxPercent = 0;
            var products = this.products;

            if (products.length > 0) {
                taxPercent = +products[0].tax_percent;
            }

            return `GST (${taxPercent}%)`;
        },

        /**
         * @return {*}
         */
        getTaxMount: function () {
            var total = this.totals();

            if (!total) {
                return 0;
            }

            return this.getFormattedPrice(total['tax_amount']);
        },

        /**
         * @return {Boolean}
         */
        isBaseGrandTotalDisplayNeeded: function () {
            var total = this.totals();

            if (!total) {
                return false;
            }

            return total['base_currency_code'] != total['quote_currency_code']; //eslint-disable-line eqeqeq
        },

        loadDropdownTotal: function () {
            $(".checkout-payment-method .order.totals .excl").dropdownDialog({
                appendTo: ".checkout-payment-method [data-block=dropdownTotal]",
                triggerTarget: ".checkout-payment-method [data-trigger=triggerTotal]",
                autoOpen: false,
                timeout: 2000,
                closeOnMouseLeave: false,
                closeOnEscape: false,
                closeOnClickOutside: false,
                triggerClass: "active",
                parentClass: "active",
                buttons: []
            });
            $(".opc-sidebar .order.totals .excl").dropdownDialog({
                appendTo: ".opc-sidebar [data-block=dropdownTotal]",
                triggerTarget: ".opc-sidebar [data-trigger=triggerTotal]",
                autoOpen: false,
                timeout: 2000,
                closeOnMouseLeave: false,
                closeOnEscape: false,
                closeOnClickOutside: false,
                triggerClass: "active",
                parentClass: "active",
                buttons: []
            });
        },

        /**
         * @returns {*|String|string}
         */
        getTotalSaving: function () {
            return totalSaving !== false ? this.getFormattedPrice(totalSaving) : '';
        }
    });
});
