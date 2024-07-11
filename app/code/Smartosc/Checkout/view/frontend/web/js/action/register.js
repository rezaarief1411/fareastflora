define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'Smartosc_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, storage, globalMessageList, customerData, $t, stepNavigator, fullScreenLoader) {
    'use strict';

    var callbacks = [],

        /**
         * @param {Object} registerData
         * @param {String} redirectUrl
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (registerData, redirectUrl, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;

            return $.ajax({
                url: redirectUrl,
                data: registerData,
                type: 'post',
                dataType: 'json'
            }).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(registerData);
                    });
                } else {
                    callbacks.forEach(function (callback) {
                        callback(registerData);
                    });
                    customerData.invalidate(['customer']);
                    fullScreenLoader.startLoader();
                    stepNavigator.next();
                    location.reload();
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(registerData);
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
