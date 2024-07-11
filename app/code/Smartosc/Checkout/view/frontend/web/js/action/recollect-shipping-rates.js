/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (quote, selectShippingAddress, rateRegistry) {
    'use strict';

    return function () {
        var shippingAddress = null;

        if (!quote.isVirtual()) {
            console.log("shippingAddress");
            console.log(shippingAddress);
            shippingAddress = quote.shippingAddress();

            rateRegistry.set(shippingAddress.getCacheKey(), null);
            selectShippingAddress(shippingAddress);
        }
    };
});
