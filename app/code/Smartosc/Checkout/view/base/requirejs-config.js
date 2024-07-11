var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Smartosc_Checkout/js/view/shipping': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Smartosc_Checkout/js/mixin/setShippingInformationActionMixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Smartosc_Checkout/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'Smartosc_Checkout/js/mixin/cartItemsMixin': true
            }
        }
    }
};
