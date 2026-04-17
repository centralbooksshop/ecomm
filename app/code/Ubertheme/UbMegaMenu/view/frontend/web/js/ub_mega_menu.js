require([
    'jquery',
    'matchMedia',
    "domReady!"
], function($, mediaCheck) {

    var BREAK_POINT =  '767px';
    var isTouch = $('html').hasClass('touch') ? true : false;

    $(".mega-menu li").on('click touchstart', function (e) {
        if ($(e.target).is("span")) {
            if (!$(e.target).closest('.menu-title').length) {
                $('.ub-mega-menu a.mega').data('status', '');
            } else {
                //update current clicked menu item id to use on other contexts
                if ($(e.target).closest('a.mega').length) {
                    var menuId = $(e.target).closest('li.mega').attr('id');
                    sessionStorage.setItem('ubCurrentMenuItemId', menuId);
                }
            }
        } else {
            var menuId = $(e.target).closest('li.mega').attr('id');
            sessionStorage.setItem('ubCurrentMenuItemId', menuId);
        }
    });

    //hide default TopMenu
    $('.nav-sections-item-content .navigation').hide();

    var fixedHeight = function($tabHead) {
        //auto set min-height for wrapper of current tabs
        var $tabContent = $tabHead.children('.child-content');
        $tabHead.closest('div.child-content').first().css('min-height', $tabContent.outerHeight());
    }

    var activeMenuItemTab = function($tabHead) {
        //reset tabs
        //$tabHead.siblings('li.tab-head').removeClass('active');
        $tabHead.siblings('li.tab-head').removeClass('active');
        //active current tab
        $tabHead.addClass('active');
        //fix height of tab content
        fixedHeight($tabHead);
    }

    var bindMenuItemEvents = function() {
        var $ubMegaMenuLinks = $('.ub-mega-menu a.mega').add('.ub-mega-menu span.mega');
        var eventName = (isTouch) ? 'click' : 'mouseenter';
        $ubMegaMenuLinks.on(eventName, function(e) {

            e.preventDefault();
            //e.stopPropagation();

            //if menu item has tab style
            if ($(this).hasClass('style-tabs')) {
                //check and get the needed tab to active
                var ubLastOpenedTabId = sessionStorage.getItem('ubLastOpenedTabId');
                var $activeTab = null;
                if (ubLastOpenedTabId) {
                    $activeTab = $('#' + ubLastOpenedTabId);
                } else {
                    var $tabsContainer = $(this).siblings('.child-content').find('ul.mega-menu.level2');
                    var $tabHeads = $tabsContainer.children('li.tab-head');
                    $activeTab = $tabHeads.filter(function(k) {
                        return $(this).children('a.active').length;
                    });
                    if (!$activeTab.length) {
                        $activeTab = $tabHeads.first();
                    }
                }
                if (!$activeTab.hasClass('active')) {
                    activeMenuItemTab($activeTab);
                } else {
                    fixedHeight($activeTab);
                }
            }

            //if menu item is a tab head
            if ($(this).hasClass('tab-head')) {
                activeMenuItemTab($(this).parent('li.tab-head'));
                sessionStorage.setItem('ubLastOpenedTabId', $(this).parent('li.tab-head').attr('id'));
            }

            //get current status
            var status = $(this).data('status');
            //reset status of all menu items
            $ubMegaMenuLinks.data('status', '');
            if (isTouch) {
                if (!$(this).hasClass('has-child')
                    || ($(this).hasClass('has-child') && status != undefined &&  status === 'touched')
                ) {
                    window.location.href = $(this).attr('href');
                    return true;
                } else {
                    $(this).data('status', 'touched');
                }
            } else {
                return true;
            }

            return false;
        });
    }

    //remove CSS class mega-hover in li.mega tags when mouse hover
    var removeMegaHover = function() {
        $('.sidebar .ub-mega-menu').find('li.mega').off('mouseenter').off('mouseleave');
    };

    var setActiveState = function() {
        //reset
        $('.ub-mega-menu').find('.active').removeClass('active');

        var $activeMenuItem = null;
        //check has clicked menu item id in session storage
        var currentMenuItemId = (sessionStorage) ? sessionStorage.getItem('ubCurrentMenuItemId') : false;
        var urlWithoutParam = window.location.protocol + '//' + window.location.host + window.location.pathname;
        var urlPath = window.location.pathname;
        var urlPathWithSearch = window.location.pathname + window.location.search;

        if (currentMenuItemId) {
            $activeMenuItem = $('.ub-mega-menu #' + currentMenuItemId);
            if ( $activeMenuItem.find('a[href="'+ urlWithoutParam +'"]').length
                || $activeMenuItem.find('a[href="'+ urlPath +'"]').length
                || $activeMenuItem.find('a[href="'+ urlPathWithSearch +'"]').length
            ) {
                if ($activeMenuItem.children('a.mega').length) {
                    $activeMenuItem = $activeMenuItem.children('a.mega');
                }
                if ($activeMenuItem.children('span.mega').length) {
                    $activeMenuItem = $activeMenuItem.children('span.mega');
                }
            } else {
                $activeMenuItem = null;
            }
        } else {
            $activeMenuItem = $('.ub-mega-menu a[href="' + urlWithoutParam + '"]');
            if (!$activeMenuItem.length) {
                $activeMenuItem = $('.ub-mega-menu a[href="' + urlPath + '"]');
            }
            if (!$activeMenuItem.length) {
                $activeMenuItem = $('.ub-mega-menu a[href="' + urlPathWithSearch + '"]');
            }
        }

        //set active state for current selected menu item and all parent elements
        if ($activeMenuItem && $activeMenuItem.length) {
            if ($activeMenuItem.length > 1) {
                $activeMenuItem = $activeMenuItem.first();
            }
            $activeMenuItem.addClass('active');
            $activeMenuItem.parentsUntil('.ub-mega-menu').addClass('active');
            //active for related elements
            $('li.mega.active').children().addClass('active');
            $('li.mega.has-child.active').children().addClass('active');
        }
    }

    $(function() {

        setActiveState();

        /**
         * only apply for main-menu
         */
        mediaCheck({
            media: '(max-width: ' + BREAK_POINT + ')',
            entry:function() {
                //show/hide sub menu items: Mobile
                $('ul.mega-menu li.has-child span.menu-parent-icon').on('click', function(e) {
                    //close siblings elements
                    $(this).parent().siblings('.has-child').children().removeClass('active');
                    //open/close current element
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active').siblings().addClass('active');
                    } else {
                        $(this).removeClass('active').siblings().removeClass('active');
                    }
                });
                //bind click event on menu item group links
                $('ul.mega-menu li.has-child a.has-child').add('ul.mega-menu li.has-child span.has-child').on('click', function (e) {
                    e.preventDefault();
                    //close siblings elements
                    $(this).parent().siblings('.has-child').children().removeClass('active');
                    //open/close current element
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active').siblings().addClass('active');
                    } else {
                        $(this).removeClass('active').siblings().removeClass('active');
                    }
                });

                $('span.menu-group-link').on('click', function (e) {
                    var url = $(this).siblings('a').attr('href');
                    if (url.length && url != '#') {
                        sessionStorage.setItem('ubCurrentMenuItemId', $(this).parent('li').attr('id'));
                        window.location.href = url;
                    }
                });

                removeMegaHover();
            },
            exit: function() {
                $('ul.mega-menu li.has-child').children().off('click');
                bindMenuItemEvents();

                //for touch devices
                if (isTouch) {
                    //close sub menu items when touch to outside
                    $(document).on('click touchstart', function (e) {
                        if (!$(e.target).closest('.ub-mega-menu-wrapper').length) {
                            var $activatedItem = $('.level0 li.mega.active');
                            $activatedItem.find('.menu-parent-icon, .child-content').removeClass('active');
                        }
                    });
                } else {
                    //add extra class 'mega-hover' when mouse hover on menu item on desktop
                    $('.ub-mega-menu').find('li.mega').each(function(i, el) {
                        if (!$(el).hasClass('tab-head')) {
                            $(el).mouseenter(function() {
                                $(this).addClass('mega-hover');
                                if ($(this).hasClass('has-child') || $(this).parents('li.mega.has-child').length) {
                                    $('body').addClass('ub-sub-menu-opened');
                                }
                            }).mouseleave(function() {
                                $(this).removeClass('mega-hover');
                                if (!$(this).parents('li.mega.has-child').length) {
                                    $('body').removeClass('ub-sub-menu-opened');
                                }
                            });
                        }
                    });
                }
            }
        });
    });
});