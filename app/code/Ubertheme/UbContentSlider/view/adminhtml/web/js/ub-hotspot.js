/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

define([
    'jquery',
    'jquery/ui',
    'Ubertheme_Base/js/jquery.ui.touch-punch.min',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/confirm'
], function ($, ui, uiTouch, $t, modal, confirmation) {
    'use strict';

    $.widget('mage.ubHotSpot', {
        options: {
            "baseUrl": "/",
            "dataPlaceholder": "",
            "dataAdded": [],
            "imgMapSelector": "#image-map",
            "mapperSelector": "#mapper",
            "mapperWidth": 24, //px
            "mapperHeight": 24, //px
            "mapperBorderWidth": 2, //px
            "mapperBorderStyle": "solid",
            "mapperBorderColor": "#fff",
            "mapperBgColor": "#ff4141",
            "mapperOpacity": 0.8,
            "hsFormSelector": "#hs-form",
            "addedHSSelector": "#added-hot-spots",
            "hotSpotSelector": ".hot-spot",
            "skuValidateUrl": "",
        },

        /**
         * Initialize widget
         */
        _create: function () {
            var self = this;
            //generate added hotspots
            self.generateHotSpots();
            //bind needed events
            self.bindEvents();
        },

        generateHotSpots: function () {
            var self = this;
            //generate current hotspots data
            if (self.options.dataAdded.length && $.isArray(self.options.dataAdded)) {
                $.each(self.options.dataAdded, function( index, data ) {
                    if (index == 0) {
                        //update mapper size from first hotspot
                        self.options.mapperWidth = data.pos_w;
                        self.options.mapperHeight = data.pos_h;
                    }
                    $(self.options.addedHSSelector).append(self.generateHotSpot(data));
                });
            }
        },

        generateHotSpot: function (data) {
            var self = this;
            var hs = '';
            if (data) {
                //make hotspot's style
                var hsStyle = 'width:' + data.pos_w + 'px;height:' + data.pos_h + 'px;';
                    hsStyle += 'left:' + data.pos_x + '%;top:' + data.pos_y + '%;';
                    hsStyle += 'border:' + self.getBorderStyle() + ';opacity:' + self.options.mapperOpacity + ';';
                    hsStyle += 'background:' + self.options.mapperBgColor + ' no-repeat fixed center;';

                //make hotspot element
                hs += '<div class="hot-spot" '
                    + 'data-sku="' + data.sku + '" '
                    + 'data-pos-width="' + data.pos_w + '" ' + 'data-pos-height="' + data.pos_h + '" '
                    + 'data-pos-left="' + data.pos_x + '" ' + 'data-pos-top="' + data.pos_y + '" '
                    + 'data-options="" '
                    + 'title="' + $t("Click & drag to a new position.") + '" style="' + hsStyle + '" >';

                //make hotspot's content container
                hs += '<span class="hs-ico-tag"></span>';
                hs += '<div class="hs-content" style="display:none;" >';
                    hs  += '<div class="content-loaded"></div>';
                    hs  += '<div class="hs-actions">';
                    hs  += '<a href="javascript:void(0);" title="' + $t("Remove this HotSpot") + '" class="action delete">'+ $t("Remove") +'</a>';
                    hs  += '</div>';
                hs  += '</div>';

                hs += '</div>';
            }

            return hs;
        },

        bindEvents: function () {
            var self = this;
            //slider to change mapper size
            $('#hs-slider').slider({
                min: 22,
                max: 75,
                value: self.options.mapperWidth,
                slide: function( event, ui ) {
                    self.hideHSForm();

                    self.options.mapperWidth = ui.value;
                    self.options.mapperHeight = ui.value;

                    //update for added hotspots
                    $(self.options.addedHSSelector).children('.hot-spot').each(function (i, item) {
                        $(item).data('pos-width', ui.value).data('pos-height', ui.value);
                        $(item).css({"width": ui.value + "px", "height": ui.value + "px"});
                    });

                    //update placeholder data
                    $(self.options.dataPlaceholder).val(self.getHotSpotsData());
                }
            });

            //handle click event on hotspot image area
            $(self.options.imgMapSelector).click(function (e) {
                var imageMapLeft = $(this).offset().left;
                var clickLeft = e.pageX;
                var leftDistance = clickLeft - imageMapLeft;

                var imageMapTop = $(this).offset().top;
                var clickTop = e.pageY;
                var topDistance = clickTop - imageMapTop;

                var mapperWidth = $(self.options.mapperSelector).width();
                var mapperHeight = $(self.options.mapperSelector).height();

                var imageMapWidth = $(self.options.imgMapSelector).width();
                var imageMapHeight = $(self.options.imgMapSelector).height();

                var top = topDistance,
                    left = leftDistance,
                    width = self.options.mapperWidth + "px",
                    height = self.options.mapperHeight + "px",
                    border = self.getBorderStyle();
                if ((topDistance + mapperHeight > imageMapHeight)
                    && (leftDistance + mapperWidth > imageMapWidth)) {
                    left = (clickLeft - mapperWidth - imageMapLeft);
                    top = (clickTop - mapperHeight - imageMapTop);
                } else if (leftDistance + mapperWidth > imageMapWidth) {
                    left = (clickLeft - mapperWidth - imageMapLeft);
                    top = topDistance;
                } else if (topDistance + mapperHeight > imageMapHeight) {
                    left = leftDistance;
                    top = (clickTop - mapperHeight - imageMapTop);
                } else {
                    left = leftDistance;
                    top = topDistance;
                }

                //convert top and left of the mapper element to percent:
                left = self.toPercent(left - (self.options.mapperWidth/2), imageMapWidth) + '%';
                top = self.toPercent(top - (self.options.mapperHeight/2), imageMapHeight) + '%';

                //show mapper
                $(self.options.mapperSelector).css({
                    "background": self.options.mapperBgColor + ' no-repeat fixed center',
                    "left": left,
                    "top": top,
                    "width": width,
                    "height": height,
                    "border": border,
                    "opacity": 0,
                }).show().animate({
                    "opacity": self.options.mapperOpacity,
                }, 'fast', "linear", function () {
                    //allow drag/drop on mapper
                    $(self.options.mapperSelector).draggable({
                        containment: "parent",
                        start: function(e, ui) {
                            ui.helper.addClass("dragging");
                        },
                        stop: function(e, ui) {
                            ui.helper.removeClass("dragging");
                            self.showHSForm();
                        }
                    });
                    //show hot spot form to add new
                    self.showHSForm();
                });
            });

            $("#hs-editor").delegate(self.options.hotSpotSelector, "mouseover", function() {
                self.loadHotSpot($(this));
                self.showHotSpot($(this));
            });

            //handle click event on a hotspot
            $("#hs-editor").delegate(self.options.hotSpotSelector, "click", function() {
                //off dragable on all current hotspots has enabled
                $(self.options.hotSpotSelector + ".ui-draggable").each(function (i, el) {
                    $(el).draggable('disable').removeClass("ui-draggable").children().hide();
                });

                var imgScopeTop = $(self.options.imgMapSelector).offset().top + $(self.options.imgMapSelector).height() - $(this).data('pos-height');
                var imgScopeLeft = $(self.options.imgMapSelector).offset().left + $(self.options.imgMapSelector).width() - $(this).data('pos-width');
                $(this).draggable({
                    containment: [
                        $(self.options.imgMapSelector).offset().left,
                        $(self.options.imgMapSelector).offset().top,
                        imgScopeLeft,
                        imgScopeTop
                    ],
                    start: function(e, ui) {
                        ui.helper.addClass("dragging");
                    },
                    stop: function(e, ui) {
                        ui.helper.removeClass("dragging");
                        $(this).draggable('disable').removeClass("ui-draggable");

                        //update current hotspot's data
                        self.updateHotSpot(this);

                        //update placeholder hotspots data
                        $(self.options.dataPlaceholder).val(self.getHotSpotsData());
                    }
                });

                //enable dragable on current hotspot
                $(this).draggable('enable').addClass("ui-draggable");
            });

            //trigger addHotSpot when press enter key
            $("#product-sku").on('keypress',function(e) {
                if(e.which == 13) {
                    if (self.validateHSForm()) {
                        self.addHotSpot();
                    }
                }
            });
            $("#btn-save-hot-spot").on('click', function () {
                if (self.validateHSForm()) {
                    self.addHotSpot();
                }
            });

            $(self.options.addedHSSelector).delegate(".hs-actions .action.delete", "click", function(e) {
                var obj = this;
                confirmation({
                    title: $t('Delete'),
                    content: $t('Are you sure you want to delete this hotspot?'),
                    actions: {
                        confirm: function() {
                            self.deleteHotSpot(obj);
                        },
                        /*cancel: function() {},
                        always: function() {}*/
                    }
                });
            });

            $("#btn-cancel-hot-spot").click(function () {
               self.hideHSForm();
            });
        },

        showHSForm: function () {
            var self = this;
            var $mapperPos = this.getMapperPosition();

            var x = ($mapperPos.pos_x + self.options.mapperWidth + self.options.mapperBorderWidth + 10),
                y = ($mapperPos.pos_y - ($(self.options.hsFormSelector).outerHeight()/2)) + ((self.options.mapperHeight + self.options.mapperBorderWidth)/2),
                frmClass = "form-right";
            var attr = {
                "left": x + "px",
                "top": y + "px"
            };
            $(self.options.hsFormSelector).css("right", "auto");

            if ($mapperPos.pos_x > ($(self.options.imgMapSelector).width()/2)) {
                x = ($(self.options.imgMapSelector).width() - $mapperPos.pos_x) + self.options.mapperBorderWidth + 10;
                frmClass = "form-left";
                attr = {
                    "right": x + "px",
                    "top": y + "px"
                };
                $(self.options.hsFormSelector).css("left", "auto");
            }

            $(self.options.hsFormSelector).removeAttr("class").addClass('hot-spot-form').addClass(frmClass);
            $(self.options.hsFormSelector).show().animate(attr, 'fast', "linear", function () {
                var $sku = $("#product-sku");
                if ($sku.hasClass('mage-error')) {
                    $sku.siblings('.mage-error').remove();
                    $sku.removeClass('mage-error');
                }
                $sku.focus();

                //handle click event to open the products chooser
                $("#btn-search-sku").on('click', function () {
                    var $chooser = $('#product-chooser-grid');
                    $chooser.modal({
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        title: $t('Search Product'),
                        buttons: [],
                        opened: function() {
                            //update tooltip for row
                            $("#product-chooser-grid").attr("title", $t("Click the product item you want to tag in the hotspot."));
                            $("#product-chooser-grid").delegate("tr._clickable", "hover", function() {
                                $(this).attr("title", $t("Click the product item you want to tag in the hotspot."));
                            });
                            //handle click on a row
                            $("#product-chooser-grid").delegate("tr._clickable", "click", function() {
                                //update sku value to field in form
                                var $skuCol = $(this).find("td.col-chooser_sku");
                                $sku.val($.trim($skuCol.text()));
                                //close modal
                                $chooser.modal('closeModal');
                            });
                        }
                    });
                    $chooser.modal('openModal');
                });
            });
        },

        hideHSForm: function () {
            $(this.options.hsFormSelector).fadeOut();
            $(this.options.mapperSelector).fadeOut();
            //reset hotspot form fields
            $("#product-sku").val('');
        },

        validateHSForm: function() {
            var self = this;
            var rs = false;

            //validate SKU
            var $sku = $("#product-sku");
            if (!$sku.val().length) {
                if (!$sku.hasClass('mage-error')) {
                    $sku.addClass('mage-error');
                } else {
                    $sku.siblings('.mage-error').remove();
                }
                $('<label class="mage-error">'+ $t('Entering SKU is required.') +'</label>').insertAfter($sku);
            } else {
                //check exits of SKU
                if (self.options.skuValidateUrl.length) {
                    $.ajax({
                        type: "GET",
                        url: self.options.skuValidateUrl,
                        data: {
                            product_sku : $sku.val(),
                            form_key: window.FORM_KEY
                        },
                        cache : false,
                        async: false,
                        //beforeSend: function() {},
                        success: function(res) {
                            if (res.found) {
                                $sku.removeClass('mage-error');
                                $sku.siblings('.mage-error').remove();
                                rs = true;
                            } else {
                                if (!$sku.hasClass('mage-error')) {
                                    $sku.addClass('mage-error');
                                } else {
                                    $sku.siblings('.mage-error').remove();
                                }
                                $('<label class="mage-error">'+ res.message +'</label>').insertAfter($sku);
                            }
                        },
                        //error: function(xhr, status, error) {}
                    });
                }
            }

            return rs;
        },

        addHotSpot: function () {
            var self = this;
            var data = this.getMapperPosition();
            data.sku = $("#product-sku").val();

            //convert top,left values from px to percent
            var imgW = $(self.options.imgMapSelector).width(),
                imgH = $(self.options.imgMapSelector).height();
            data.pos_x = self.toPercent(data.pos_x, imgW),
            data.pos_y = self.toPercent(data.pos_y, imgH);

            //update added hotspot container
            $(self.options.addedHSSelector).append(self.generateHotSpot(data));

            //update placeholder data
            $(self.options.dataPlaceholder).val(self.getHotSpotsData());

            self.hideHSForm();
        },

        loadHotSpot: function($hotspot) {
            var self = this;

            //ajax preview hotspot's content if hasn't loaded yet
            var $hsContent = $hotspot.find(".hs-content");
            if ($hotspot.data('sku').length) {
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
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            $hsContent.removeClass('loading');
                        }
                    });
                }
            }
        },

        showHotSpot: function($hotspot) {
            var self = this;
            var $hsContent = $hotspot.find(".hs-content");

            var marginX = "left",
                marginY = "top",
                hsClass = "hs-left",
                top = $hotspot.data("pos-top"),
                left = $hotspot.data("pos-left"),
                x = $hotspot.outerHeight() + 10,
                y = -($hsContent.outerHeight()/2) + ($hotspot.outerHeight()/2);

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
                    x = -($hsContent.outerWidth()/2) + ($hotspot.outerWidth()/2);
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
            $hsContent.css({"left": "auto", "right": "auto", "top": "auto", "bottom": "auto"}).css(marginX, x).css(marginY, y);
        },

        getMapperPosition: function () {
            var self = this;
            var position = $(self.options.mapperSelector).position();
            return {
                "pos_x": parseNumber(position.left),
                "pos_y": parseNumber(position.top),
                "pos_w": self.options.mapperWidth,
                "pos_h": self.options.mapperHeight
            };
        },

        getBorderStyle: function () {
            var style = this.options.mapperBorderWidth
                + "px " + this.options.mapperBorderStyle
                + " " + this.options.mapperBorderColor;

            return style;
        },

        getHotSpotsData: function () {
            var rs = [];
            $(this.options.addedHSSelector).children('.hot-spot').each(function (i, item) {
                var data = {};
                data.sku = $(item).data('sku');
                data.pos_w = parseNumber($(item).data('pos-width'));
                data.pos_h = parseNumber($(item).data('pos-height'));
                data.pos_x = parseNumber($(item).data('pos-left'));
                data.pos_y = parseNumber($(item).data('pos-top'));
                data.options = $(item).data('options');
                rs.push(data);
            });
            rs = JSON.stringify(rs);

            return rs;
        },

        updateHotSpot: function (obj) {
            var self = this;
            var newTop = $(obj).css("top").replace("px", ""),
                newLeft = $(obj).css("left").replace("px", "");

            //convert top,left values from px to percent
            var imgW = $(self.options.imgMapSelector).width(),
                imgH = $(self.options.imgMapSelector).height();
            newTop = self.toPercent(newTop, imgH),
            newLeft = self.toPercent(newLeft, imgW);

            $(obj).data('pos-left', newLeft);
            $(obj).data('pos-top', newTop);
            $(obj).css({
               "left": newLeft + "%",
               "top": newTop + "%"
            });
        },

        deleteHotSpot: function (obj) {
            $(obj).closest('.hot-spot').remove();
            //update placeholder data
            $(this.options.dataPlaceholder).val(this.getHotSpotsData());
        },

        toPercent: function(value, max) {
            var rs = Math.round((value/max) * 100);
            return rs;
        }

    });

    return $.mage.ubHotSpot;
});
