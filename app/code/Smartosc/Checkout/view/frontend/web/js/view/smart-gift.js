define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/url',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Ui/js/view/messages',
    'uiRegistry',
    'Magento_Checkout/js/model/quote'
], function (Component, ko, $, url, getTotalsAction, messages, registry, quote) {
    'use strict';

    var gifts = checkoutConfig.gift;
    var giftItems = JSON.parse(gifts);

    return Component.extend({
        defaults: {
            template: 'Smartosc_Checkout/smart-gift'
        },
        checkedGift: ko.observable(false),
        chosenGift: ko.observableArray(false),
        // items
        giftOptions: ko.observableArray(giftItems),

        /**
         * Init component
         */
        initialize: function () {
            var self = this;
            this._super();
            self.initializeChosenGift();
            self.chosenGift.subscribe(function(data) {
                // call ajax to update quote
                self.addOrRemoveGiftToCart(data);
            });
        },
        addOrRemoveGiftToCart: function(data) {
            var self = this,
                messageContainer = registry.get('checkout.errors').messageContainer;
            $.ajax({
                showLoader: true,
                url: url.build('smart_checkout/customer_ajax/addgifttocart'),
                data: {data: data},
                type: "POST"
            }).done(function(response) {
                if (!response.errors) {
                    // @todo: handle success
                    messageContainer.addSuccessMessage({
                        message: response.message
                    });
                } else {
                    // @todo: handle failure
                    messageContainer.addErrorMessage({
                        message: response.message
                    });
                }
            }).always(function() {
                 // The cart page totals summary block update
                var deferred = $.Deferred();
                getTotalsAction([], deferred);
            });
        },
        initializeChosenGift: function() {
            var self = this;
            var quoteItems = quote.getItems();
            quoteItems.forEach(quoteItem => {
                giftItems.forEach(giftItem => {
                    if (quoteItem.sku === giftItem.sku){
                        self.chosenGift.push(giftItem);
                    }
                })
            })
        }
    });
});
