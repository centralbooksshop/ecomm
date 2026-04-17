define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/validation'
], function ($, Component, ko, quote, checkoutData) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();
            this.initAutocomplete();
            return this;
        },

        initAutocomplete: function () {
            var autocompleteInstance = null;

            $(document).on('focus', 'input[name="street[0]"]', function () {

                // Prevent re-initializing
                if (autocompleteInstance) return;

                // Google API not loaded
                if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
                    console.warn('Google Places API not loaded.');
                    return;
                }

                var handlerId = $(this).attr('id');

                // Initialize Autocomplete restricted to India only
                autocompleteInstance = new google.maps.places.Autocomplete(
                    document.getElementById(handlerId),
                    {
                        types: ['geocode'],
                        componentRestrictions: { country: "IN" }
                    }
                );

                // When user selects an address suggestion
                google.maps.event.addListener(autocompleteInstance, 'place_changed', function () {
                    var place = autocompleteInstance.getPlace();

                    if (!place.address_components) {
                        return;
                    }

                    // Extract Address Components
                    var components = {};
                    place.address_components.forEach(function (item) {
                        var type = item.types[0];
                        components[type] = {
                            long_name: item.long_name,
                            short_name: item.short_name
                        };
                    });

                    // Fill Street[0] with full formatted address
                    var fullAddress = place.formatted_address;

                    $('input[name="street[0]"]')
                        .val(fullAddress)
                        .trigger('keyup');

                    // Fill City
                    if (components.locality) {
                        $('input[name="city"]')
                            .val(components.locality.long_name)
                            .trigger('keyup');
                    }

                    
					var postcode = components.postal_code
						? components.postal_code.long_name
						: '';

					$('input[name="postcode"]')
						.val(postcode)
						.trigger('keyup');

                    // Set Country as India
                    $('select[name="country_id"]')
                        .val("IN")
                        .trigger('change');

                    // Fill Region or State
                    if (components.administrative_area_level_1) {
                        var regionName = components.administrative_area_level_1.long_name;
                        var matched = false;

                        // Try selecting state from dropdown
                        $('[name="region_id"] option').each(function () {
                            if ($(this).text() === regionName) {
                                $(this).prop('selected', true).trigger('change');
                                matched = true;
                                return false;
                            }
                        });

                        // If not matched in dropdown, set as text
                        if (!matched) {
                            $('[name="region"]')
                                .val(regionName)
                                .trigger('keyup');
                        }
                    }

                }); // place_changed end
            }); // focus event end
        }
    });
});
