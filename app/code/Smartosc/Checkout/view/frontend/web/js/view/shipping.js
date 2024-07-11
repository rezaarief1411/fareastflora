/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'js/plugins/slider/slick.min',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Smartosc_Checkout/js/model/step-navigator',
    'Smartosc_Checkout/js/model/validate-note-length',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/url',
    'mage/storage',
    'Magento_Ui/js/modal/modal',
    'uiLayout',
    'Smartosc_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'Magento_Checkout/js/action/get-totals',
    'mage/translate',
    'mage/calendar'
], function (
    $,
    slick,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    createBillingAddress,
    selectBillingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    validateNote,
    resourceUrlManager,
    urlBuilder,
    storage,
    modal,
    layout,
    checkoutDataResolver,
    checkoutData,
    registry,
    getTotalsAction,
    $t,
    calendar
) {
    'use strict';
    var popUp = null,
        storeOptions = JSON.parse(checkoutConfig.storesList),
        giftOptions = [
            {id: '1', img_url: '/media/2.jpg', title: 'Gift Card 1', price: 'FREE'},
            {id: '2', img_url: '/media/4.jpg', title: 'Gift Card 2', price: '$5'},
            {id: '3', img_url: '/media/2.jpg', title: 'Gift Card 3', price: '$10'},
            {id: '4', img_url: '/media/4.jpg', title: 'Ballon 1', price: '$1'},
            {id: '5', img_url: '/media/4.jpg', title: 'Ballon 2', price: '$15'},
        ];

    function setBillingAndShippingInformation() {
        stepNavigator.next();
    }

    return Component.extend({
        defaults: {
            template: 'Smartosc_Checkout/shipping',
            shippingFormTemplate: 'Smartosc_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),
        isAddressSameAsShipping: ko.observable(true),
        isShowBillingForm: ko.observable(false),
        storeOptions: storeOptions,
        giftOptions: ko.observableArray(giftOptions),
        selectedOptions: ko.observable(),
        selectedDate: ko.observable(),
        valueNote: ko.observable(),
        checkedGift: ko.observable(true),
        chosenGift: ko.observableArray(),
        pickupTab: ko.observable(),
        deliveryTab: ko.observable(),
        currentProfit: ko.observable(),
        isShippingAddresstTheSameBillingAddress: ko.observable('true'),
        pickupLocation: ko.observable(),

        initChildren: function () {
            this.messageContainer = new Messages();
            this.createMessagesComponent();
            return this;
        },

        createMessagesComponent: function () {
            var messagesComponent = {
                parent: this.name,
                name: this.name + '.messages',
                displayArea: 'messages',
                component: 'Magento_Ui/js/view/messages',
                config: {
                    messageContainer: this.messageContainer
                }
            };

            layout([messagesComponent]);

            return this;
        },


        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';
            this._super();
            if (!quote.isVirtual()) {
                this.visible(true);
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Billing Info / Delivery Info'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            }
            checkoutDataResolver.resolveBillingAddress();
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() === 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });


            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    checkoutData.setShippingAddressFromData(shippingAddrsData);
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            return this;
        },


        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super().observe(
                {
                    selectedStore: null,
                }
            );
            this.optionStores = ko.observableArray([]);
            this.optionStores(this.storeOptions);
            return this;
        },
        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },

        validateNoteLimit: function (element, viewModel) {

            var limit = Number(checkoutConfig.pickup_note_limit);

            $(element).keyup(function(event) {
                return validateNote.showErrorMessage(element,limit);
            });

            $(element).keypress(function (event) {
                return validateNote.showErrorMessage(element,limit);
            });
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            return true;
        },

        validateBillingInformation: function () {
            return true;
        },

        /**
         * @return {Boolean}
         */
        useShippingAddress: function () {
            if (this.isAddressSameAsShipping()) {
                this.isShowBillingForm(false);
            } else {
                this.isShowBillingForm(true);
            }
            return true;
        },

        setShippingInformationStorePickup: function () {
            var shippingAddress,
                shippingMethod,
                addressData,
                field,
                country = registry.get(this.parentName + '.shippingAddress.shipping-address-fieldset.country_id'),
                countryIndexedOptions = country.indexedOptions,
                shippingAddress = quote.shippingAddress();
            addressData = addressConverter.formAddressDataToQuoteAddress(
                this.selectedStore()
            );

            var $pickupNote = $('textarea[name="pickup_comments"]');
            var note = $pickupNote.val();

            if (note == '') {
                $pickupNote.parent().find('.field-error').remove();
            } else {
                var limit = Number(checkoutConfig.pickup_note_limit);

                if (note.length > limit) {
                    if ($pickupNote.parent().find('.field-error').length == 0) {
                        var errMessage = '<div class="field-error" style="margin-top: 0;"><span>' + $t('You enter exceed limit %1 characters.').replace('%1', limit) + '</span></div>';
                        $pickupNote.parent().append(errMessage);
                    }
                    $pickupNote.focus();
                    return false;
                } else {
                    $pickupNote.parent().find('.field-error').remove();
                }
            }

            //Copy form data to quote shipping address object
            for (field in addressData) {
                if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                    shippingAddress.hasOwnProperty(field) &&
                    typeof addressData[field] != 'function' &&
                    _.isEqual(shippingAddress[field], addressData[field])
                ) {
                    shippingAddress[field] = addressData[field];
                } else if (typeof addressData[field] != 'function' &&
                    !_.isEqual(shippingAddress[field], addressData[field])) {
                    shippingAddress = addressData;
                    break;
                }
            }
            var location = registry.get('checkout.steps.shipping-step.shippingAddress.delivery-extra-information.pickup_location');
            shippingAddress = this.formatShippingAddressFromStorePickup(location.getSelectedStore(), shippingAddress);
            selectShippingAddress(shippingAddress);
            if (this.validateShippingBillingInformation()) {
                shippingMethod = {
                    amount: 0,
                    available: true,
                    base_amount: 0,
                    carrier_code: "in_store_pickup",
                    carrier_title: "Store Pickup",
                    error_message: "",
                    method_code: "in_store_pickup",
                    method_title: "Free",
                    price_excl_tax: 0,
                    price_incl_tax: 0,
                };

                this.selectShippingMethod(shippingMethod);

                if (this.validateShippingInformationStorePickup()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.forceNavigateTo('payment');
                        }
                    );
                }
            }

            // reload totals model
            var deferred = $.Deferred();
            getTotalsAction([], deferred);

        },

        validateShippingInformationStorePickup: function () {
            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The delivery method is missing. Select the delivery method and try again.')
                );
                return false;
            }
            this.source.set('params.invalid', false);
            if (
                !quote.shippingMethod()['method_code'] ||
                !quote.shippingMethod()['carrier_code']
            ) {
                this.focusInvalid();
                return false;
            }
            return true;
        },
        /**
         *
         * @param storePickUpAddress
         * @param shippingAddress
         * @returns {*}
         */
        formatShippingAddressFromStorePickup: function (storePickUpAddress, shippingAddress) {
            var name = storePickUpAddress.name;
            var parts = name.split("@");

            function getLastName(words) {
                var n = words.split(" ");
                return n[n.length - 1];
            }

            function getFirstName(words) {
                var n = words.split(" ");
                delete n[n.length - 1];
                return n.join(' ');
            }

            if (parts.length === 2) {
                var firstName = parts[0].trimRight();
                var lastName = '@' + parts[1];
                shippingAddress.firstname = firstName;
                shippingAddress.lastname = lastName;
            } else {
                shippingAddress.firstname = getFirstName(name);
                shippingAddress.lastname = getLastName(name);
            }

            shippingAddress.telephone = storePickUpAddress.phone_number;

            shippingAddress.countryId = storePickUpAddress.country;
            shippingAddress.email = storePickUpAddress.email;
            shippingAddress.street = [];
            shippingAddress.street[0] = storePickUpAddress.street_address;
            shippingAddress.city = storePickUpAddress.city;
            shippingAddress.postcode = storePickUpAddress.zipcode;
            return shippingAddress;
        },
        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingBillingInformation()
                && this.validateShippingInformation()) {
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });

                var serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote);
                var address = quote.shippingAddress()
                var payload = JSON.stringify({
                    address: {
                        'street': address.street,
                        'city': address.city,
                        'region_id': address.region_id,
                        'country_id': address.countryId,
                        'postcode': address.postcode,
                        'email': address.email,
                        'customer_id': address.customer_id,
                        'firstname': address.firstname,
                        'lastname': address.lastname,
                        'middlename': address.middlename,
                        'prefix': address.prefix,
                        'suffix': address.suffix,
                        'vat_id': address.vat_id,
                        'company': address.company,
                        'telephone': address.telephone,
                        'fax': address.fax,
                        'custom_attributes': address.custom_attributes,
                        'save_in_address_book': address.save_in_address_book
                    }
                });
                var selectShippingMethod;
                storage.post(serviceUrl, payload, false).done(function(result) {
                    selectShippingMethod = result.filter(item => item.method_code !== 'in_store_pickup');
                }).fail(function(response) {
                    console.log(response)
                });

                // $.ajax({
                //     type : 'POST',
                //     url:  urlBuilder.build(serviceUrl),
                //     data: payload,
                //     async: false,
                //     done: function(result) {
                //         console.log(result)
                //         selectShippingMethod = result
                //     }
                // })

                // console.log(selectShippingMethod)
                // this.selectShippingMethod({
                //     "carrier_code": "owsh1",
                //     "method_code": "standard",
                //     "carrier_title": "Local Delivery",
                //     "method_title": "Standard Delivery",
                //     "amount": 35,
                //     "base_amount": 35,
                //     "available": true,
                //     "extension_attributes": {"custom": []},
                //     "error_message": "",
                //     "price_excl_tax": 32.71,
                //     "price_incl_tax": 35
                // });
                stepNavigator.next();
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingBillingInformation: function () {
            var shippingAddress,
                billingAddress,
                addressDataShipping,
                addressDataBilling,
                field,
                country = registry.get(this.parentName + '.shippingAddress.shipping-address-fieldset.country_id'),
                countryIndexedOptions = country.indexedOptions,
                option = countryIndexedOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer,
                self = this,
                addDiffBill = jQuery("#shipping-address-different-billing").is(':checked'),
                addSameBill = jQuery("#shipping-address-same-as-billing").is(':checked');

            this.source.set('params.invalid', false);
            this.triggerBillingDataValidateEvent();

            billingAddress = quote.billingAddress();
            var sourceBillingAddress = this.source.get('billingAddress');
            // set building, floor to custom attributes
            sourceBillingAddress['custom_attributes'] = {
                    'billing_building': sourceBillingAddress['building'],
                    'billing_floor': sourceBillingAddress['floor']
                };
            addressDataBilling = addressConverter.formAddressDataToQuoteAddress(
                sourceBillingAddress
            );

            //Copy form data to quote billing address object
            if (billingAddress !== null) {
                for (field in addressDataBilling) {
                    if (addressDataBilling.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        billingAddress.hasOwnProperty(field) &&
                        typeof addressDataBilling[field] != 'function' &&
                        _.isEqual(billingAddress[field], addressDataBilling[field])
                    ) {
                        billingAddress[field] = addressDataBilling[field];
                    } else if (typeof addressDataBilling[field] != 'function' &&
                        !_.isEqual(billingAddress[field], addressDataBilling[field])) {
                        billingAddress = addressDataBilling;
                        break;
                    }
                }
            }
            if (this.source.get('params.invalid')) {
                this.focusInvalid();
                return false;
            }

            billingAddress.email = this.source.get('billingAddress').billing_email;

            quote.guestEmail = this.source.get('billingAddress').billing_email;

            if (customer.isLoggedIn()) {
                billingAddress['save_in_address_book'] = 1;
            }

            if (billingAddress !== null) {
                if (billingAddress.customAttributes === undefined) {
                    billingAddress.customAttributes = {};
                }
                billingAddress.customAttributes['billing_floor'] = sourceBillingAddress.floor;
                billingAddress.customAttributes['billing_building'] = sourceBillingAddress.building;
                billingAddress.customAttributes['billing_email'] = sourceBillingAddress.billing_email;
                selectBillingAddress(billingAddress);
                // @todo: save billing address to local storage
            }
            if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });
                return false;
            }

            return true;
        },

        validateShippingInformation: function () {
            var billingAddress

            if (this.isShippingAddresstTheSameBillingAddress() == 'true') {
                billingAddress = quote.billingAddress();
                selectShippingAddress(billingAddress);
                return true;
            }

            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                country = registry.get(this.parentName + '.shippingAddress.shipping-address-fieldset.country_id'),
                countryIndexedOptions = country.indexedOptions,
                option = countryIndexedOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;
            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();
            shippingAddress = quote.shippingAddress();
            addressData = addressConverter.formAddressDataToQuoteAddress(
                this.source.get('shippingAddress')
            );
            //Copy form data to quote shipping address object
            for (field in addressData) {
                if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                    shippingAddress.hasOwnProperty(field) &&
                    typeof addressData[field] != 'function' &&
                    _.isEqual(shippingAddress[field], addressData[field])
                ) {
                    shippingAddress[field] = addressData[field];
                } else if (typeof addressData[field] != 'function' &&
                    !_.isEqual(shippingAddress[field], addressData[field])) {
                    shippingAddress = addressData;
                    break;
                }
            }

            // if (customer.isLoggedIn()) {
            //     shippingAddress['save_in_address_book'] = 1;
            // }
            shippingAddress.email = this.source.get('billingAddress').billing_email;
            selectShippingAddress(shippingAddress);
            if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });
                return false;
            }

            if (this.source.get('params.invalid')) {
                this.focusInvalid();
                return false;
            }

            var customAttributes = {};
            customAttributes = $.extend(customAttributes, shippingAddress.customAttributes);
            // update customAttributes for shippingAddress
            if (jQuery("#shipping-address-different-billing").is(':checked')) {
                customAttributes = $.extend(
                    customAttributes,
                    {
                        deliverynote : require("uiRegistry").get("checkout.steps.delivery-step.deliveryContent.delivery_note.delivery_note").value(),
                    }
                );
                customAttributes['billing_floor'] = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.floor").value();
                customAttributes['billing_building'] = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.building").value();

                var indexedOptions = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id").indexedOptions;

                var countryId = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id").value();


                var countryObject = require("uiRegistry").get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id").indexedOptions[countryId];

                var countryLabel = countryObject.label;
                shippingAddress.city = countryLabel;

            }
            shippingAddress.customAttributes = customAttributes;

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        },

        triggerBillingDataValidateEvent: function () {
            this.source.trigger('billingAddress.data.validate');

            if (this.source.get('billingAddress.custom_attributes')) {
                this.source.trigger('billingAddress.custom_attributes.data.validate');
            }
        },
        afterRenderContent: function () {
            window.pickupTab = false;
            window.deliveryTab = true;
            this.pickupTab(false);
            this.deliveryTab(true);
            this.currentProfit(50);
            mediaCheck({
                media: '(max-width: 991px)',
                entry: function () {
                    $('.gift-list').each(function() {
                        if($(this).children('.col-xs-4').length > 4) {
                            $(this).slick({
                                infinite: false,
                                slidesToShow: 4,
                                slidesToScroll: 4,
                                autoplay: true,
                                autoplaySpeed: 3000,
                                prevArrow: "<span class='arrow-prev fa fa-angle-left'>Prev</span>",
                                nextArrow: "<span class='arrow-next fa fa-angle-right'>Next</span>",
                                responsive: [{
                                    breakpoint: 450,
                                    settings: {
                                        slidesToShow: 3,
                                        slidesToScroll: 3
                                    }
                                }
                                ]
                            });
                        }
                    });
                },
                exit: function () {
                    $('.gift-list').each(function() {
                        if ($(this).hasClass('slick-initialized ')) {
                            $(this).slick('unslick');
                        }
                    });

                }
            });
            $('#shipping-address-same-as-billing').prop('checked', true).trigger('change');
        },

        /**
         *
         * @param pickupForm
         */
        pickUpStore: function (pickupForm) {
            var pickUpData = {},
                formDataArray = $(pickupForm).serializeArray();

            formDataArray.forEach(function (entry) {
                pickUpData[entry.name] = entry.value;
            });

        },

        /**
         *
         * @returns {*}
         */
        actionDeliveyTab: function () {
            window.pickupTab = false;
            window.deliveryTab = true;
            this.pickupTab(false);
            this.deliveryTab(true);
            this.currentProfit(50);
        },

        /**
         *
         * @returns {*}
         */
        autoChangeRadioShippingAsBillingAdd: function (element, vm) {
            $(element).change(function (event) {
                var countryId = $(this);
                if (countryId.val() && countryId.val() !== 'SG') {
                     $("input[id='shipping-address-different-billing']").click().click();
                }
            });
        },

        /**
         *
         * @returns {*}
         */
        autoSelectBillingCountry: function(element, vm) {
            setInterval(function () {
                $(element).parent().find('select[name="country_id"]').val('SG').change();
            }, 1500);
        },

        /**
         *
         * @returns {*}
         */
        actionPickupTab: function () {
            window.pickupTab = true;
            window.deliveryTab = false;
            this.pickupTab(true);
            this.deliveryTab(false);
            this.currentProfit(-50);
        }
    });
});
