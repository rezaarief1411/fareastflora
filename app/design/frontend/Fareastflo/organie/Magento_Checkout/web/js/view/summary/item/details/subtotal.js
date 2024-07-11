/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total'
], function (viewModel) {
    'use strict';

    return viewModel.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Magento_Checkout/summary/item/details/subtotal'
        },

        /**
         * @param {Object} quoteItem
         * @return {*|String}
         */
        getValue: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['base_row_total_incl_tax']);
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getPriceValue: function (quoteItem) {
            return this.getFormattedPrice(quoteItem['base_price_incl_tax']);
        }
    });
});
