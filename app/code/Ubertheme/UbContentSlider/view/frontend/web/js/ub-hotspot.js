/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

define([
    'jquery',
    'mage/translate',
], function ($, $t) {
    'use strict';

    $.widget('mage.ubHotSpot', {
        options: {
            "baseUrl": "/",
            "hsData": [],
            "hsContainer": "",
            "mapperWidth": 24, //px
            "mapperHeight": 24, //px
            "hotSpotSelector": ".hot-spot",
            "mobileTimeout": 200, //1s
        },

        /**
         * Initialize widget
         */
        _create: function () {
            var self = this;
            self.genHotSpots();
            self.bindEvents();
        },

        genHotSpots: function () {
            var self = this;
            //generate current hotspots data
            if (self.options.hsData.length
                && $(self.options.hsContainer).length
                && $.isArray(self.options.hsData)) {
                $.each(self.options.hsData, function (index, data) {
                    if (index == 0) {
                        //update mapper size from first hotspot
                        self.options.mapperWidth = data.pos_w;
                        self.options.mapperHeight = data.pos_h;
                    }
                    $(self.options.hsContainer).append(self.genHotSpot(data));
                });
            }
        },

        genHotSpot: function (data) {
            var self = this;
            var hs = '';
            if (data) {
                //make hotspot's style
                var hsStyle = 'width:' + data.pos_w + 'px;height:' + data.pos_h + 'px;';
                hsStyle += 'left:' + data.pos_x + '%;top:' + data.pos_y + '%;';

                //make hotspot element
                hs += '<div class="hot-spot" data-sku="' + data.sku + '" '
                    + 'data-pos-width="' + data.pos_w + '" ' + 'data-pos-height="' + data.pos_h + '" '
                    + 'data-pos-left="' + data.pos_x + '" ' + 'data-pos-top="' + data.pos_y + '" '
                    + 'data-options="" style="' + hsStyle + '" >';

                hs += '<span class="hs-ico-tag"></span>';
                //make hotspot's content container
                hs += '<div class="hs-content" >';
                hs += '<div class="content-loaded"></div>';
                hs += '</div>';
                hs += '</div>';
            }

            return hs;
        },

        bindEvents: function () {
            var self = this;
            var isTouch = $('html').hasClass('touch');
            if (isTouch) {
                $(self.options.hsContainer + " " + self.options.hotSpotSelector).on('touchstart', function () {
                    self.loadHotSpot($(this));
                }).on('touchend', function () {
                    if (!$('html').hasClass('open-modal')) {
                        self.runQuickview($(this), self.options.mobileTimeout);
                    }
                });
            } else {
                $(self.options.hsContainer + " " + self.options.hotSpotSelector).on('mouseover', function () {
                    self.loadHotSpot($(this));
                    self.showHotSpot($(this));
                });
            }
        },

        loadHotSpot: function ($hotspot) {
            var self = this;
            //ajax preview hotspot's content if hasn't loaded yet
            var $hsContent = $hotspot.find(".hs-content");
            if (!$hsContent.hasClass('loaded')) {
                $.ajax({
                    url: self.options.baseUrl + 'ubslide/ajax/hotspotContent',
                    type: "GET",
                    dataType: "json",
                    data: {
                        product_sku: $hotspot.data('sku')
                    },
                    beforeSend: function () {
                        $hsContent.addClass('loading');
                    },
                    success: function (rs) {
                        $hsContent.removeClass('loading').addClass('loaded');
                        $hsContent.find('.content-loaded').first().html(rs.html);
                        //init quickview functions for hotspot's loaded content
                        if ($('body').UBQuickView && ubQuickViewOptions !== undefined) {
                            //init quickview function for hotspot
                            ubQuickViewOptions.group = false;
                            ubQuickViewOptions.itemClass = "#" + self.element.attr('id') + " .product-info";
                            ubQuickViewOptions.btnContainer = ".thumbnail";
                            ubQuickViewOptions.additionClass = "hs-mode";
                            $.ub.UBQuickView(ubQuickViewOptions);
                            if (!$('html').hasClass('touch')) {
                                //bind quickview function for product's name
                                $hsContent.delegate(".content-loaded .product-name", "click", function () {
                                    self.runQuickview($hotspot, 0);
                                });
                            } else {
                                self.runQuickview($hotspot, self.options.mobileTimeout);
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        $hsContent.removeClass('loading');
                    }
                });
            }
        },

        runQuickview: function ($hotspot, timeout) {
            setTimeout(function () {
                var $btnQuickview = $hotspot.find(".hs-content .content-loaded .product-info .thumbnail a.ub-quick-view-button");
                if ($btnQuickview.length) {
                    $btnQuickview.trigger("click");
                }
            }, timeout)
        },

        showHotSpot: function ($hotspot) {
            var self = this;
            var $hsContent = $hotspot.find(".hs-content");

            var marginX = "left",
                marginY = "top",
                hsClass = "hs-left",
                top = $hotspot.data("pos-top"),
                left = $hotspot.data("pos-left"),
                x = $hotspot.outerHeight() + 10,
                y = -($hsContent.outerHeight() / 2) + ($hotspot.outerHeight() / 2);

            if (left > 50) { //value by %
                marginX = "right";
                hsClass = "hs-right";
            }

            if ((left < 70 && left > 30)) {
                if (top < 30 || top > 70) {
                    if (top > 70) { //value by %
                        marginY = "bottom";
                        hsClass = "hs-bottom";
                    }
                    if (top < 30) { //value by %
                        marginY = "top";
                        hsClass = "hs-top";
                    }
                    x = -($hsContent.outerWidth() / 2) + ($hotspot.outerWidth() / 2);
                    y = $hotspot.outerHeight() + 10;
                }
            }

            if (hsClass == 'hs-left' || hsClass == 'hs-right') {
                if (top < 15 || top > 80) {
                    y = (top < 15) ? 0 : -($hsContent.outerHeight() - $hotspot.outerHeight());
                    hsClass += (top < 15) ? " top-bound" : " bottom-bound";
                }
            }

            $hotspot.removeAttr("class").addClass('hot-spot').addClass(hsClass);
            $hsContent.css({
                "left": "auto",
                "right": "auto",
                "top": "auto",
                "bottom": "auto"
            }).css(marginX, x).css(marginY, y);
        },

        toPercent: function (value, max) {
            var rs = Math.round((value / max) * 100);
            return rs;
        }

    });

    return $.mage.ubHotSpot;
});
