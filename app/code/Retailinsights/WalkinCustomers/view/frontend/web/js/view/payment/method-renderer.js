define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'receivedpaymentcard',
                component: 'Retailinsights_WalkinCustomers/js/view/payment/method-renderer/receivedpaymentcard'
            }
        );
        rendererList.push(
            {
                type: 'receivedpaymentcash',
                component: 'Retailinsights_WalkinCustomers/js/view/payment/method-renderer/receivedpaymentcash'
            }
        );
        return Component.extend({});
    }
);