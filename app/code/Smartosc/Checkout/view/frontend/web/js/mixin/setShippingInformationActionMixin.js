define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Smartosc_Checkout/js/helper/data'
], function ($, wrapper, quote, dataHelper) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress, deliveryDate, deliveryNote;

            shippingAddress = quote.shippingAddress();
            deliveryDate = dataHelper.getObservableValue('checkout.steps.delivery-step.deliveryContent.delivery_date.delivery_date');
            deliveryNote = dataHelper.getObservableValue('checkout.steps.delivery-step.deliveryContent.delivery_note.delivery_note');

            if (shippingAddress['customAttributes'] === undefined) {
                shippingAddress['customAttributes'] = {};
            }

            if (deliveryDate) {
                shippingAddress['customAttributes']['delivery_date'] = dataHelper.displayDate(deliveryDate);
            }

            if (deliveryNote) {
                shippingAddress['customAttributes']['delivery_note'] = deliveryNote;
            }

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if ($('.shipping-address-container .gift-card-container #gift-card').is(':checked')) {
                shippingAddress['customAttributes']['gift_message'] = $('.shipping-address-container .smart-gift #gift-message').val();
            }

            if ($('.checkout-shipping-method #shipping-method-checkbox').is(':checked')) {
                shippingAddress['customAttributes']['authorize_message'] = window.authorizeMessage;
            }

            if(window.pickupTab) {
                var pickupDate = $("input[name='pickup_date']").val();
                pickupDate = dataHelper.formatDate(pickupDate);
                shippingAddress['extension_attributes']['pickup_date'] = pickupDate;
                shippingAddress['extension_attributes']['pickup_time'] = $("span[name='pickup_time']").html();
                shippingAddress['extension_attributes']['pickup_comments'] = $("textarea[name='pickup_comments']").val();
                shippingAddress['extension_attributes']['pickup_store_name'] = $(".store-name").text();
                shippingAddress['extension_attributes']['pickup_store_address'] = $(".store-address").text();
                shippingAddress['extension_attributes']['pickup_store_state'] = $(".store-state").text();
                shippingAddress['extension_attributes']['pickup_store_zip'] = $(".store-zip").text();
                shippingAddress['extension_attributes']['billing_floor'] = dataHelper.getObservableValue('checkout.steps.shipping-step.shippingAddress.billingAddress.address-fieldset.floor');
                shippingAddress['extension_attributes']['billing_building'] = dataHelper.getObservableValue('checkout.steps.shipping-step.shippingAddress.billingAddress.address-fieldset.building');
                shippingAddress['customAttributes']['pickup_time'] = $("span[name='pickup_time']").html();
                if ($('.store-container .gift-card-container #gift-card').is(':checked')) {
                    shippingAddress['customAttributes']['gift_message'] = $('.store-container .smart-gift #gift-message').val();
                }
            } else {
                if (!$("#shipping-address-same-as-billing").is(":checked")) {
                    shippingAddress['prefix'] = $('select[name="prefix"]', "#shipping-new-address-form").val();
                }
            }

            return originalAction();
        });
    };
});
