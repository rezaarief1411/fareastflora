define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/sidebar',
    'Smartosc_Checkout/js/model/step-navigator'
], function (Component, ko, $, sidebarModel, stepNavigator) {
    'use strict';

    return Component.extend({
        /**
         * @param {HTMLElement} element
         */
        setModalElement: function (element) {
            sidebarModel.setPopup($(element));
        },

        isProcessedPayment: function () {
            let itemClass = 'opc-sidebar-block';
            if (stepNavigator.getActiveItemIndex() == 3) {
                itemClass = 'opc-sidebar-block current-payment';
            }
            return itemClass;
        },
    });
});
