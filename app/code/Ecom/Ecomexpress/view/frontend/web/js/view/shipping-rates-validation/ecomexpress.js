/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/ecomexpress',
    '../../model/shipping-rates-validation-rules/ecomexpress'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    ecomexpressShippingRatesValidator,
    ecomexpressShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('ecomexpress', ecomexpressShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('ecomexpress', ecomexpressShippingRatesValidationRules);

    return Component;
});