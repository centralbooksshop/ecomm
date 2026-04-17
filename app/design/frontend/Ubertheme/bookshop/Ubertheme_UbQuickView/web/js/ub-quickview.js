(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery",
            "mage/translate",
            'mage/validation/validation',
            "colorBox"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, $t) {
    "use strict";

    $.widget('ub.UBQuickView', {
        options: {
            baseUrl: '/',
            showPopupTitle: true,
            popupTitle: $t('UB Quick View'),
            currentText: $t('Product {current} of {total}'),
            previousText: $t('Preview'),
            nextText: $t('Next'),
            closeText: $t('Close'),
            type: 'popup',
            transition: "fade",
            speed: "300",
            maxWidth: "980",
            initialWidth: "75",
            initialHeight: "75",
            group: true,
            itemClass: '.products.grid .item.product-item, .products.list .item.product-item',
            btnLabel: $t('Quick View'),
            btnContainer: '.product-item-info',
            handlerClassName: 'ub-quick-view-button',
            additionClass: '',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: $t('Adding...'),
            addToCartButtonTextAdded: $t('Added'),
            addToCartButtonTextDefault: $t('Add to Cart'),
            addToCartStatusSelector: 'ub-add-cart-status',
            minicartSelector: '[data-block="minicart"]'

        },
        _create: function () {
            //we don't apply quick view functions in product detail page.
            if (!$('body').hasClass('catalog-product-view')) {
                //init quick view buttons
                this._initialButtons(this.options);
                //bind events for quick view popup with colorbox
                this._bindColorbox(this.options);
            }
        },
        _initialButtons: function (config) {
            $(config.itemClass).each(function () {
                if (!$(this).find('.' + config.handlerClassName).length) {
                    var productId = $(this).find('.price-final_price').data('product-id');
                    if (productId != undefined) {
                        var url = config.baseUrl + 'ubquickview/index/view/id/' + productId;
                        var btnQuickView = '<div class="ub-quick-view-btn-container">';
                        if (config.group) {
                            var groupName = $(this).parent().parent().attr('class').replace(" ", "-");
                            btnQuickView += '<a rel="' + groupName + '" class="' + config.handlerClassName + '" href="' + url + '"';
                        } else {
                            btnQuickView += '<a class="' + config.handlerClassName + '" href="' + url + '"';
                        }
                        if (config.showPopupTitle) {
                            btnQuickView += ' title="' + config.popupTitle + '"';
                        }
                        btnQuickView += ' >';
                        btnQuickView += '<span>' + config.btnLabel + '</span></a>';
                        btnQuickView += '</div>';
                        $(this).find(config.btnContainer).prepend(btnQuickView);
                    }
                }
            });
            //add extra classes css
            $('#colorbox').addClass(config.type).addClass(config.additionClass);
            $('body').addClass('body-' + config.type);
        },
        _bindColorbox: function (config) {
            var self = this;
            $('.' + config.handlerClassName).each(function (i, el) {
                $(this).colorbox({
                    className: config.additionClass,
                    maxWidth: config.maxWidth,
                    initialWidth: config.initialWidth,
                    initialHeight: config.initialHeight,
                    current: config.currentText,
                    previous: config.previousText,
                    next: config.nextText,
                    close: config.closeText,
                    transition: config.transition,
                    speed: config.speed,
                    onOpen: function () {
                        $('#colorbox').addClass('loading');
                    },
                    onLoad: function () {
                        //hide close button
                        $('#cboxClose').hide();

                        //off navigation when loading if exists
                        if ($('#cboxNavigation').length) {
                            $('#cboxNavigation').hide();
                        }

                        //off navigation when loading if exists
                        if ($('.ub-quick-view-media').length) {
                            $('.ub-quick-view-media').hide();
                        }


                        //add class loading to content tabs
                        if ($('#ub-quick-view-tabs').length) {
                            $('#ub-quick-view-tabs').hide().addClass('loading');
                        }
                        return false;
                    },
                    onComplete: function () {
                        //reposition some controls
                        self._repositionElements(config);

                        //if configurable product
                        self._configurableProcess();

                        //if bundle product
                        self._bundleProcess();

                        //if download product
                        self._downloadProcess();

                        //if has reviews
                        self._reviewProcess();

                        //ajax add cart process
                        self.bindAjaxAddToCart();

                        //trigger content updated
                        $('#cboxContent').trigger('contentUpdated');
                        $('#colorbox').removeClass('loading');
                        $('html').addClass('open-modal');
                        
                    },
                    onClosed: function () {
                        //fix conflict ui
                        $('.product-item-details .ub-price-box.price-final_price').each(function() {
                            $(this).removeClass('ub-price-box').addClass('price-box');
                            $(this).attr('data-role', 'priceBox');
                        });
                        $('html').removeClass('open-modal');
                        $('#colorbox').removeClass('colorbox');
                    }
                });
            });
        },
        _repositionElements: function (config) {
            if (!config.showPopupTitle) {
                $('#cboxContent').addClass('no-title');
            }
            $('#cboxLoadedContent').css({'height': 'auto'});
            $('#colorbox').addClass('colorbox');

            //make navigation
            if (!$('#cboxNavigation').length) {
                $('#cboxContent').append('<div id="cboxNavigation"></div>');
                $('#cboxNext').appendTo('#cboxNavigation');
                $('#cboxPrevious').appendTo('#cboxNavigation');
                $('#cboxCurrent').appendTo('#cboxNavigation');
            } else {
                if ($('#cboxNavigation #btnGotoProduct').length) {
                    $('#cboxNavigation #btnGotoProduct').remove();
                }
                if ($('.ub-quick-view-media').length) {
                    $('.ub-quick-view-media').show();
                }
                //show navigation again
                if ($('#cboxNavigation').length) {
                    $('#cboxNavigation').show();
                }
            }

            //move button go to product to navigation container
            if ($('#cboxContent #btnGotoProduct').length) {
                $('#cboxContent #btnGotoProduct').appendTo('#cboxNavigation');
            }

            //add class loading to tab contents
            $('#ub-quick-view-tabs').removeClass('loading').show();

            $('#cboxClose').appendTo('#cboxWrapper').show();
        },
        _configurableProcess: function () {
            //fix conflict ui
            $('.product-item-details .price-box.price-final_price').each(function() {
                $(this).removeClass('price-box').addClass('ub-price-box');
                $(this).attr('data-role', 'ubPriceBox');
            });
            if ($('#cboxContent').find('.swatch-opt').length) {
                $('#cboxContent').find('.field.configurable').hide();
                setTimeout(function () {
                    $('#cboxContent').find('.swatch-option').each(function () {
                        var $elm = $(this);
                        $elm.on('click', function () {
                            $('#cboxContent').find('#product-addtocart-button').addClass('disabled').prop('disabled', true);
                            var opId = $elm.attr('option-id');
                            var $curOpt = $('#cboxContent').find('select.super-attribute-select option[value="' + opId + '"]').first();
                            if ($elm.hasClass('selected')) {
                                $curOpt.parent().val('').trigger('change');
                            } else {
                                $curOpt.parent().val(opId).trigger('change');
                            }
                            $('#cboxContent').find('#product-addtocart-button').removeClass('disabled').prop('disabled', false);
                        });
                    });
                }, 500);
            }
        },
        _bundleProcess: function () {
            if ($('#cboxContent').find('#bundle-slide').length) {
                var $bundleBtn = $('#cboxContent').find('#bundle-slide');
                var $bundleTabLink = $('#tab-label-ub-quick-view-product-bundle-title');
                $bundleTabLink.parent().hide();
                $bundleBtn.off('click').on('click', function (e) {
                    e.preventDefault();
                    $bundleTabLink.parent().show();
                    $bundleTabLink.click();
                    return false;
                });
            }
        },
        _downloadProcess: function () {
            if ($('#cboxContent').find('#downloadable-links-list').length) {
                //hide qty filed
                $('.box-tocart .field.qty').hide();
            }
        },
        _reviewProcess: function () {
            if ($('#cboxContent').find('#tab-label-reviews-title').length) {
                var $reviewsTabLink = $('#cboxContent').find('#tab-label-reviews-title');
                $('#cboxContent').find('.reviews-actions .action.view').click(function () {
                    $reviewsTabLink.click();
                });
                $('#cboxContent').find('.reviews-actions .action.add').click(function () {
                    $reviewsTabLink.click();
                    $('#cboxContent').find('#nickname_field').focus();
                })
            }
        },
        bindAjaxAddToCart: function () {
            var self = this;
            if ($('#product_addtocart_form').length) {
                $('#product_addtocart_form').mage('validation', {
                    radioCheckboxClosest: '.nested',
                    submitHandler: function (form) {
                        self.submitForm($(form));
                        return false;
                    }
                });
            }
        },
        submitForm: function (form) {
            var self = this;
            if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                self.element.off('submit');
                form.submit();
            } else {
                self.ajaxAddCart(form);
            }
        },
        ajaxAddCart: function (form) {
            var self = this;

            var url = form.attr('action');
            url = url.replace("checkout/cart", "ubquickview/cart");
            $.ajax({
                url: url,
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    self.beforeAddToCart(form);
                },
                success: function (rs) {
                    if (rs.messages) {
                        if (!$('#' + self.options.addToCartStatusSelector).length) {
                            $('.ub-quick-view-tab-content .product-add-form').prepend('<div id="' + self.options.addToCartStatusSelector + '">&nbsp;</div>');
                        }
                        $('#' + self.options.addToCartStatusSelector).fadeOut(100, function () {
                            var msg = '<div class="message ' + ((rs.status) ? 'success' : 'error') + '"><span>' + rs.messages + '</span></div>';
                            if($(window).width() < 767) {
                                if ($('#ub-quick-view-tabs').length) {
                                    var elmnt = document.getElementById("ub-quick-view-tabs");
                                    elmnt.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }                                
                            } else {
                                if ($('#ub-quick-view-product-bundle').length) {
                                    var elmnt = document.getElementById("product-options-wrapper");
                                    elmnt.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            }
                            $('#' + self.options.addToCartStatusSelector).html(msg).fadeIn(200);
                        });
                    }

                    if (rs.minicart) {
                        $(self.options.minicartSelector).replaceWith(rs.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    self.afterAddToCart(form);
                }
            });
        },
        beforeAddToCart: function (form) {
            var self = this;
            $(self.options.minicartSelector).trigger('contentLoading');

            var addToCartButton = $(form).find(self.options.addToCartButtonSelector);
            addToCartButton.addClass(self.options.addToCartButtonDisabledClass);
            addToCartButton.attr('title', self.options.addToCartButtonTextWhileAdding);
            addToCartButton.find('span').text(self.options.addToCartButtonTextWhileAdding);
        },
        afterAddToCart: function (form) {
            var self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(this.options.addToCartButtonTextAdded);
            addToCartButton.attr('title', this.options.addToCartButtonTextAdded);

            setTimeout(function () {
                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(self.options.addToCartButtonTextDefault);
                addToCartButton.attr('title', self.options.addToCartButtonTextDefault);
            }, 1000);

            setTimeout(function () {
                $('#' + self.options.addToCartStatusSelector).fadeOut('slow');
            }, 5000);
        }

    });

    return $.ub.UBQuickView;
}));