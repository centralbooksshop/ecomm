/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */


define([
    'jquery',
    'matchMedia',
    'mage/tabs',
    'domReady!'
], function ($, mediaCheck) {
    'use strict';

    mediaCheck({
        media: '(max-width: 767px)',
        entry:function() {

            $(".account-nav").mouseenter(function (){
                $(this).children().addClass("active");
            }).mouseleave(function (){
                $(this).children().removeClass("active");
            }); 

            $(".mobile-filter").on('click', function () {
                $(".block.filter").addClass('active');
                $("body").addClass('filter-active');
                window.scrollTo(0,0);
            });

            $(".block-title.filter-title").on('click', function () {
                $(".block.filter").removeClass('active');
                $("body").removeClass('filter-active');
            });

            if ($('.block.filter').length != 0) {
                $(".mobile-filter").css("display", "block");
            }
            
            $(".block-account-nav").on('click', function () {
                $(this).toggleClass("active");
            });

            $(window).scroll(function() {
                if ($(window).scrollTop() > 0) {
                    $('#minicart .inner-toggle').css('display','none');
                } else {
                    $('#minicart .inner-toggle').css('display','');
                }
            });
        }   
    }); 

    mediaCheck({
        media: '(min-width: 768px)',
        entry:function() {
            $(window).scroll(function(){
                var search = $(".has-toggle.quick-search");
                if ($(window).scrollTop() >= 20) {
                    search.css("display","block");
                } else {
                    search.css("display","block");
                }
            });
        }   
    });      
});