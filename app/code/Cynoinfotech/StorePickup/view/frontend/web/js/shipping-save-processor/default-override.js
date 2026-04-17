define(
    [
        'jquery',
        'underscore',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function (
        $,
        _,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                this.extendPayload(payload);

                fullScreenLoader.startLoader();

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            },

            extendPayload: function (payload) {
                quote.cistorepickup = [];
                quote.cistorepickup.store_pickup = quote.cistorepickupStorePickup || $('[name="store_pickup"]').val();
                quote.cistorepickup.storepickup_shipping_checked = $('[name="storepickup_shipping_checked"]').val();
                quote.cistorepickup.calendar_inputField = $('[name="calendar_inputField"]').val();
                quote.cistorepickup.pickup_person_name = $('[name="pickup_person_name"]').val();
                quote.cistorepickup.pickup_person_id = $('[name="pickup_person_id"]').val();
               

                var storeData = {
                    store_pickup:quote.cistorepickup.store_pickup,
                    storepickup_shipping_checked: quote.cistorepickup.storepickup_shipping_checked,
                    calendar_inputField: quote.cistorepickup.calendar_inputField,
                    pickup_person_name: quote.cistorepickup.pickup_person_name,
                    pickup_person_id: quote.cistorepickup.pickup_person_id
                };

                if (!payload.addressInformation.hasOwnProperty('extension_attributes')) {
                    payload.addressInformation.extension_attributes = {};
                }
                console.log('carrier_code '+ quote.shippingMethod().carrier_code + ' carrier_code '+ quote.shippingMethod().method_code);
				
                if (quote.shippingMethod().method_code == 'cistorepickup') {
                    console.log(storeData);
                    payload.addressInformation.extension_attributes = _.extend(
                        payload.addressInformation.extension_attributes,
                        storeData
                    )
                }

                
            }
        };
    }
);

