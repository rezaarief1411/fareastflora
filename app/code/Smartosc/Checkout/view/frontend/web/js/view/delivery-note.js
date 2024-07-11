define(['uiComponent'], function (Component) {
    'use strict';
    console.log('Smartosc/Checkout/view/frontend/web/js/view/delivery-note.js');

    return Component.extend({
        defaults: {
            template: 'Smartosc_Checkout/shipping-address/shipping-method-note'
        },
        /**
         * Init component
         */
        initialize: function () {
            this._super();
        }
    });
});
