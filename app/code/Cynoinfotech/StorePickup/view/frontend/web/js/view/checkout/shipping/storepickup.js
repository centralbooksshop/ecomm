define([
    'jquery',
    "underscore",
    'ko',
    'Magento_Checkout/js/model/quote',
    'uiComponent',
    'mage/calendar',
    'Magento_Ui/js/modal/modal'
], function ($, _, ko, quote, Component, calendar, modal) {
    'use strict';
    var show_hide_custom_blockConfig = window.checkoutConfig.show_hide_custom_block;
    var storeStartTime = parseInt(window.checkoutConfig.hour_min) > 12 ? (parseInt(window.checkoutConfig.hour_min) - 12)+" PM" : parseInt(window.checkoutConfig.hour_min)+ " AM";
    var storeEndTime = parseInt(window.checkoutConfig.hour_max) > 12 ? (parseInt(window.checkoutConfig.hour_max) - 12)+" PM" : parseInt(window.checkoutConfig.hour_max)+ " AM";

    var map;
    function initMap()
    {
    
         map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: parseFloat(window.checkoutConfig.store_lat),
                lng: parseFloat(window.checkoutConfig.store_lng)
            },
            zoom: parseInt(window.checkoutConfig.zoom_level)
        });
        var latlng = new google.maps.LatLng(parseFloat(window.checkoutConfig.store_lat), parseFloat(window.checkoutConfig.store_lng));
    
        var infowindow =  new google.maps.InfoWindow({});
        var marker, count;
        for (count = 0; count < window.checkoutConfig.storepick_location.length; count++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(window.checkoutConfig.storepick_location[count][1], window.checkoutConfig.storepick_location[count][2]),
                map: map,
                title: window.checkoutConfig.storepick_location[count][0]
            });
        }
        map.panTo(marker.position);
        return map;
    }

    $(document).ready(function () {
        $(document).on('change','.storepickup-shipping-method select',function () {
            $('.store_info ul li').hide();
            if ($(this).val() != "") {
                $(document).find('.storepickup_checked').val('1');
                $('li.store_info_'+$(this).val()).show();
            } else {
                $(document).find('.storepickup_checked').val('0');
            }
        });
        
        $(document).on('click','#click-me',function () {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Stores in Map',
                buttons: [{
                    text:'Close',
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            
            var popup = modal(options, $('#ci-storepickup-popup-modal'));
            $("#ci-storepickup-popup-modal").modal("openModal");
            
            initMap();
            
            $('#map').css({"overflow":"visible","height":"500px"});
        });
    });
    
    return Component.extend({
        defaults: {
            formSelector: '#checkout-step-shipping_method button',
            template: 'Cynoinfotech_StorePickup/checkout/shipping/storepickup',
            storepickConfig: window.checkoutConfig.storepick_config,
            storepickConfigEncode: window.checkoutConfig.storepick_config_encode,
            storepickInfo: window.checkoutConfig.storepick_info,
            websiteCode: window.checkoutConfig.websiteCode,
        },
        
        initObservable: function () {
                this._super();
                this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);

            return this;
        },

        mobileNumberValidation: function()
        {
            var mobileNum = $("#pickup_person_id").val();
            var validateMobNum= /^\d*(?:\.\d{1,2})?$/;
            if (validateMobNum.test(mobileNum ) && mobileNum.length == 10) {
                $("#store-selector-phone-error").hide();
            }
            else {
                $("#store-selector-phone-error").show();
            }
        },
        
        initialize: function () {
            this._super();
            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    var format = 'yy-mm-dd';

                    //initialize datetimepicker with some optional options
                    var options = {
                        onSelect: function () {
                            var value = $('.ui_tpicker_hour_slider select.ui-timepicker-select').val(); 
                            if (value == parseInt(window.checkoutConfig.hour_max)) {
                                $('.ui_tpicker_minute_slider select.ui-timepicker-select').prop('disabled', true);
                            } else {
                                $('.ui_tpicker_minute_slider select.ui-timepicker-select').prop('disabled', false);
                            }
                        },
                        minDate: 1,
                        dateFormat:format,
                        minTime: window.checkoutConfig.hour_min,
                        maxTime: window.checkoutConfig.hour_max,
                        //beforeShowDay: function (date){var day = date.getDay(); return [(day > 0), '']; }
						beforeShowDay: function (date) {
                            var today = new Date();
							today.setHours(0,0,0,0);

							var thirdDay = new Date(today);
							thirdDay.setDate(today.getDate() + 3);

							var secondDay = new Date(thirdDay);
							secondDay.setDate(thirdDay.getDate() + 1);

							var nextDay = new Date(secondDay);
							nextDay.setDate(secondDay.getDate() + 1);

							function normalize(d) {
								d = new Date(d);
								d.setHours(0,0,0,0);
								return d.getTime();
							}

							var t = normalize(date);

							if (
								t === normalize(thirdDay) ||
								t === normalize(secondDay) ||
								t === normalize(nextDay)
							) {
								return [true, ''];
							}

							return [false, 'ui-state-disabled'];
						}

                    };
                    $el.datetimepicker(options);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));

                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    //when the view model is updated, update the widget
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };
            
            return this;
        },
        canVisibleBlock: show_hide_custom_blockConfig,
        getPickupMessage: ko.observable("Pickup is during store hours between "+ storeStartTime +" to "+ storeEndTime +" only")
    });

});
