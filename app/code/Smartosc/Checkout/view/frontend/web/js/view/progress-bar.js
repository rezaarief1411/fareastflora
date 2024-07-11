/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Smartosc_Checkout/js/model/step-navigator',
], function ($, _, ko, Component, stepNavigator) {
    'use strict';

    var steps = stepNavigator.steps;

    return Component.extend({
        defaults: {
            template: 'Smartosc_Checkout/progress-bar',
            visible: true,
        },
        steps: steps,
        dataStep: ko.observable(''),

        /** @inheritdoc */
        initialize: function () {
            var stepsValue;
            let self = this;
            self.dataStep(window.location.hash.replace('#', ''));
            this._super();
            window.addEventListener('hashchange', _.bind(stepNavigator.handleHash, stepNavigator));

            if (!window.location.hash) {
                stepsValue = stepNavigator.steps();

                if (stepsValue.length) {
                    if(window.isCustomerLoggedIn) {
                        stepNavigator.setHash('shipping');
                    }else {
                        stepNavigator.setHash(stepsValue.sort(stepNavigator.sortItems)[0].code);
                    }
                }
            }

            stepNavigator.handleHash();
        },

        /**
         * @param {*} itemOne
         * @param {*} itemTwo
         * @return {*|Number}
         */
        sortItems: function (itemOne, itemTwo) {
            return stepNavigator.sortItems(itemOne, itemTwo);
        },

        /**
         * @param {Object} step
         */
        navigateTo: function (step) {
            $('.opc-progress-bar').attr('data-step', step.code);
            if(step.code!='payment'){
                $('#checkout').removeClass('checkout-payment');
            }else{
                $('#checkout').addClass('checkout-payment');
            }
            if(window.isCustomerLoggedIn && step.code == 'step_one_login') {
                return false;
            }
            /**
             * Denied to go back step delivery:
             * if user has chosen store pickup, he is on payment step and
             * clicks on delivery step, he will be force gone back to shipping
             */
            if(window.pickupTab && step.code == 'delivery' && window.location.hash == '#payment') {
                stepNavigator.forceNavigateTo('shipping');
            }
            stepNavigator.navigateTo(step.code);
        },

        /**
         * @param {Object} item
         * @return {*|Boolean}
         */
        isProcessed: function (item) {
            return stepNavigator.isProcessed(item.code);
        },

        isProcessedPayment: function () {
            return stepNavigator.getActiveItemIndex() == 3;
        }
    });
});
