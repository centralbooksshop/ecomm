/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
  'use strict';    
  if ($('body').hasClass('checkout-cart-index')) {
      if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
          $('#block-shipping').on('collapsiblecreate', function () {
              $('#block-shipping').collapsible('forceActivate');
          });
      }
  }

  $('.cart-summary').mage('sticky', {
      container: '#maincontent'
  });

  $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    
  keyboardHandler.apply();

  function detectmob() { 
     if( navigator.userAgent.match(/Android/i)
     || navigator.userAgent.match(/webOS/i)
     || navigator.userAgent.match(/iPhone/i)
     || navigator.userAgent.match(/iPad/i)
     || navigator.userAgent.match(/iPod/i)
     || navigator.userAgent.match(/BlackBerry/i)
     || navigator.userAgent.match(/Windows Phone/i)
     ){
        return true;
      }
     else {
        return false;
      }
    }
    if(detectmob()){
        $('.has-toggle').on('click', function(e){
            if ($(e.target).hasClass('btn-toggle')) {
                $(this).toggleClass('active');
                if ($(this).hasClass('active')) {
                    $(this).children().addClass('active');
                } else {
                    $(this).children().removeClass('active');
                }
                $(this).siblings('.has-toggle').each(function() {
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active').children().removeClass('active');
                    }
                });
            }
        });
    } else {
        $(".has-toggle").mouseenter(function (){
            $(this).children().addClass("active");
        }).mouseleave(function (){
            $(this).children().removeClass("active");
        });
    } 
  
  $('#qty_down').addClass('disabled');
  $(".qty-box-count #qty_up").click(function(){
    var qty = parseInt($(".qty-box-count #qty").val());
    if (qty) {
      $(".qty-box-count #qty").val(qty+1);
      $('#qty_down').removeClass('disabled');
    } else {
      $(".qty-box-count #qty").val(1); 
      $('#qty_down').addClass('disabled');
    }
  });
  $(".qty-box-count #qty_down").click(function() {
    var qty = parseInt($(".qty-box-count #qty").val());
    if (qty>1) {
      $(".qty-box-count #qty").val(qty-1);
      $('#qty_down').removeClass('disabled');
    } else {
      $(".qty-box-count #qty").val(1);
      $('#qty_down').addClass('disabled');
    }

    if (qty==2) {
      $('#qty_down').addClass('disabled');
    }

  });

  $('.qty-box-count #qty').on('blur', function() {
    if (!$(this).val().length || $(this).val() == 0) {
        $(this).val(1);
    }
});
  
  
  $(".sections.nav-sections").mouseenter(function (){
    $(".magnify-lens").css("display", "none");
    $(".magnifier-preview").css("display", "none");      
  }).mouseleave(function (){
    $(".magnify-lens").css("display", "");
    $(".magnifier-preview").css("display", "");
  });
  

  $(".greet.welcome").appendTo(".links").insertBefore('.item.link.compare');


  $(".product-social-links").appendTo(".product-info-main").after($(".product.attribute.overview"));

  //Loading short description and tab when content ready
  $(".product.attribute.overview").css("display","block");

  $(function(){
        var isUbTouch = 'ontouchstart' in window && !(/hp-tablet/gi).test(navigator.appVersion);
        $('html').addClass(isUbTouch ? 'touch' : 'no-touch');
        if (isUbTouch) {
            $(document).on('click touchstart', function (e) {
                if (!$(e.target).closest('.has-toggle.active').length) {
                    if ($('.has-toggle.active').length) {
                        $('.has-toggle.active').removeClass('active').children().removeClass('active');
                    }
                }
            });
        }
    });
});
