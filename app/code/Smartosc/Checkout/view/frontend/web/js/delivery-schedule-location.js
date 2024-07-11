define(
    [ 'jquery',
        'uiComponent',
        'ko'
    ], function($, Component, ko) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Smartosc_Checkout/delivery-schedule-location',
                selectedStore : ko.observable(),
                seedData: JSON.parse(checkoutConfig.storesList)
            },
            /**
             * @return {exports}
             */
            initialize: function () {
                this._super();
                this.seedData = this.seedData.filter(item => item.status === "1");
                if(this.seedData.length) {
                    this.selectedStore(this.seedData[0]);
                }
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

            onStoreChange: function (store) {
                this.selectedStore(store);
            },
            getSelectedStore : function () {
                return this.selectedStore()
            } ,
            getOptionsStores : function () {
                return this.optionStores();
            },
        });
    }
);
