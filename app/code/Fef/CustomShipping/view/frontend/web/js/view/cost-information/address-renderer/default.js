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
    console.log('default-cost.js');
    return Component.extend({
        defaults: {
            template: 'Fef_CustomShipping/cost-information/address-renderer/default'
        },
    });
});
