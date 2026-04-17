define([
    'jquery',
    'owlCarousel1',
    'domReady!'
], function ($) {

    return function (config, element) {

        if (parseInt(config.show_processbar) === 1) {
            var time = 5; // time in seconds
            var $progressBar,
                $bar,
                $elem,
                isPause,
                tick,
                percentTime;

            //Init progressBar
            progressBar = function (elem) {
                $elem = elem;
                //build progress bar elements
                buildProgressBar();
                //start counting
                start();
            }
            //create div#progressBar and div#bar then prepend to element
            buildProgressBar = function () {
                $progressBar = $("<div>", {
                    class: "progressBar"
                });
                $bar = $("<div>", {
                    class: "bar"
                });
                $progressBar.append($bar).prependTo($elem);
                //$progressBar.append($bar).appendTo($elem);
            }
            start = function () {
                //reset timer
                percentTime = 0;
                isPause = false;
                //run interval every 0.01 second
                tick = setInterval(runBar, 10);
            }
            runBar = function () {
                if (isPause === false) {
                    percentTime += 1 / time;
                    $bar.css({
                        width: percentTime + "%"
                    });
                    //if percentTime is equal or greater than 100
                    if (percentTime >= 100) {
                        //slide to next item
                        $elem.trigger('owl.next')
                    }
                }
            }
            //pause while dragging
            pauseOnDragging = function () {
                isPause = true;
            }
            //pause while hover
            pauseOnHover = function () {
                isPause = true;
            }
            //moved callback
            moved = function () {
                //clear interval
                clearTimeout(tick);
                //start again
                start();
            }
        }

        var $elements = $(element);
        var $thumbnailElements = $elements.siblings('.owl-carousel.thumb-items');

        if (parseInt(config.single_item) === 1 && parseInt(config.show_thumbnail) === 1) {
            function syncPosition(el) {
                var current = this.currentItem;
                $thumbnailElements.find(".owl-item").removeClass("synced").eq(current).addClass("synced")
                if ($thumbnailElements.data("owlCarousel") !== undefined) {
                    center(current);
                }
            }
            function center(number) {
                var sync2visible = $thumbnailElements.data("owlCarousel").owl.visibleItems;
                var num = number;
                var found = false;
                for (var i in sync2visible) {
                    if (num === sync2visible[i]) {
                        var found = true;
                    }
                }

                if (found === false) {
                    if (num > sync2visible[sync2visible.length - 1]) {
                        $thumbnailElements.trigger("owl.goTo", num - sync2visible.length + 2)
                    } else {
                        if (num - 1 === -1) {
                            num = 0;
                        }
                        $thumbnailElements.trigger("owl.goTo", num);
                    }
                } else if (num === sync2visible[sync2visible.length - 1]) {
                    $thumbnailElements.trigger("owl.goTo", sync2visible[1])
                } else if (num === sync2visible[0]) {
                    $thumbnailElements.trigger("owl.goTo", num - 1)
                }
            }
            $thumbnailElements.on("click", ".owl-item", function (e) {
                e.preventDefault();
                var number = $(this).data("owlItem");
                $elements.trigger("owl.goTo", number);
            });
        }

        var $owlCarouselOptions = {
            //Basic
            singleItem: (parseInt(config.single_item) === 1) ? true : false,
            itemsCustom: false,
            itemsScaleUp: true,
            slideSpeed: parseInt(config.slide_speed),
            paginationSpeed: 800,
            rewindSpeed: 1000,

            //Auto play
            autoPlay: (parseInt(config.auto_run) === 1) ? true : false,
            stopOnHover: (parseInt(config.stop_on_hover) === 1) ? true : false,

            // Navigation
            navigation: (parseInt(config.show_navigation) === 1) ? true : false, // Show next and prev buttons
            navigationText: config.navigation_text,
            rewindNav: true,
            scrollPerPage: false,

            //Pagination
            pagination: (parseInt(config.show_paging) === 1) ? true : false,
            paginationNumbers: (parseInt(config.paging_numbers) === 1) ? true : false,

            // Responsive
            responsive: true,
            responsiveRefreshRate: 200,
            responsiveBaseWidth: window,

            // CSS Styles
            baseClass: 'owl-carousel',
            theme: 'owl-theme',

            //Lazy load
            lazyLoad: (parseInt(config.enable_lazyload) === 1) ? true : false,
            lazyFollow: true,
            lazyEffect: "fade",

            //Auto height
            autoHeight : (parseInt(config.auto_height) === 1) ? true : false,

            //JSON
            jsonPath: false,
            jsonSuccess: false,

            //Mouse Events
            dragBeforeAnimFinish: true,
            mouseDrag: true,
            touchDrag: true,
            //Transitions
            transitionStyle: config.slide_transition,
            // Other
            addClassActive: false,
            //Callbacks
            //beforeUpdate: false,
            //afterUpdate: false,
            //beforeInit: false,
            //afterInit: false,
            //beforeMove: false,
            //afterMove: false,
            //startDragging : false,
            //afterLazyLoad : false,
            //afterAction: false
        };
        if (!$owlCarouselOptions.singleItem) {
            $owlCarouselOptions = $.extend({}, $owlCarouselOptions, {
                items: parseInt(config.number_items),
                itemsDesktop: [1199, parseInt(config.number_items_desktop)],
                itemsDesktopSmall: [979, parseInt(config.number_items_desktop_small)],
                itemsTablet: [768, parseInt(config.number_items_tablet)],
                itemsTabletSmall: [767, parseInt(config.number_items_tablet_small)],
                itemsMobile: [480, parseInt(config.number_items_mobile)]
            });
        } else { //is single item
            if (parseInt(config.auto_run) === 1 && parseInt(config.show_processbar) === 1) {
                $owlCarouselOptions = $.extend({}, $owlCarouselOptions, {
                    afterInit: progressBar,
                    afterMove: moved,
                    startDragging: pauseOnDragging
                });
            }
            //if enable thumbnail images
            if (parseInt(config.show_thumbnail) === 1) {
                $owlCarouselOptions = $.extend({}, $owlCarouselOptions, {
                    //pagination:false,
                    afterAction : syncPosition
                });
            }
        }

        $(document).ready(function(){
            //run owl-carousel for main items
            $elements.owlCarousel($owlCarouselOptions);

            //run owl-carousel for thumbnail images
            if (parseInt(config.single_item) === 1 && parseInt(config.show_thumbnail) === 1) {
                $thumbnailElements.owlCarousel({
                    items               : 6,
                    itemsDesktop        : [1199,6],
                    itemsDesktopSmall   : [979,6],
                    itemsTablet         : [768,5],
                    itemsTabletSmall:     [767,2],
                    itemsMobile         : [480,2],
                    pagination          :false,
                    responsiveRefreshRate : 100,
                    //Lazy load
                    lazyLoad            : (parseInt(config.enable_lazyload) === 1) ? true : false,
                    lazyFollow          : true,
                    lazyEffect          : "fade",
                    afterInit           : function(el){
                        el.find(".owl-item").eq(0).addClass("synced");
                    }
                });
            }//end apply for thumbnails
        });
    }

});
