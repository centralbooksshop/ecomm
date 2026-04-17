var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-billing-address': {
                'Centralbooks_ExtraCheckoutAddressFields/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Centralbooks_ExtraCheckoutAddressFields/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Centralbooks_ExtraCheckoutAddressFields/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Centralbooks_ExtraCheckoutAddressFields/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Centralbooks_ExtraCheckoutAddressFields/js/action/set-billing-address-mixin': true
            }
        }
    }
};