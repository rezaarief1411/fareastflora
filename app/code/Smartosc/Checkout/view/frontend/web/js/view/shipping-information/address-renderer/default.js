/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Smartosc_Checkout/js/helper/data'
], function (Component, _, customerData, dataHelper) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },

        /**
         * @return {String}
         */
        pickupDate: function() {
            var pickupDate = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.delivery-extra-information.pickup_date").value();

            if (pickupDate)
                return dataHelper.displayDate(pickupDate);

            return '';
        },

        /**
         * @return {String}
         */
        pickupNote: function() {
            var pickupNote = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.delivery-extra-information.pickup_comments").value();

            if (pickupNote)
                return pickupNote;

            return '';
        },

        /**
         * @param {*} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * Get customer attribute label
         *
         * @param {*} attribute
         * @returns {*}
         */
        getCustomAttributeLabel: function (attribute) {
            var resultAttribute;

            if (typeof attribute === 'string') {
                return attribute;
            }

            if (attribute.label) {
                return attribute.label;
            }

            if (typeof this.source.get('customAttributes') !== 'undefined') {
                resultAttribute = _.findWhere(this.source.get('customAttributes')[attribute['attribute_code']], {
                    value: attribute.value
                });
            }

            return resultAttribute && resultAttribute.label || attribute.value;
        }
    });
});
