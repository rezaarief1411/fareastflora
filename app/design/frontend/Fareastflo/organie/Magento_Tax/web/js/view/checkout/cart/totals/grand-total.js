/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/grand-total',
    'jquery',
    'dropdownDialog'
], function (Component, $, dropdownDialog) {
    'use strict';
    var totalSaving = window.checkoutConfig.total_saving;
    return Component.extend({
        /**
         * @override
         */
        isDisplayed: function () {
            return true;
        },
        totalSaving: totalSaving,
        /**
         *
         */
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
        },
        /**
         * @returns {*|String|string}
         */
        getTotalSaving: function () {
            var totalSavingSegments;

            if (!this.totals()) {
                return null;
            }

            totalSavingSegments = this.totals()['total_segments'].filter(function (segment) {
                return segment.code.indexOf('total_saving') !== -1;
            });
            return totalSavingSegments[0].value ? this.getFormattedPrice(totalSavingSegments[0].value) : '';
        },
    });
});
