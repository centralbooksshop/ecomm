/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
	'Magento_Customer/js/model/customer'
], function ($, authenticationPopup, customerData, customerinfo) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

			var customerlogin = customerinfo.isLoggedIn();
            //if(customerlogin) {}
            //alert(customerlogin); 
	             
            console.log(customerlogin); 

            event.preventDefault();

           // if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
			if (!customerlogin && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
