/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Smartosc_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/totals'
], function (Component, stepNavigator, totals) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
        /**
         * @return {Boolean}
         */
        isVisibleCart: function () {
            if (stepNavigator.getActiveItemIndex() == 3) {
                return false
            } else
                return true;
        },
    });
});
