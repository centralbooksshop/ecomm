define(['jquery'], function ($) {
    'use strict';

    $(function () {

        if (window.bundleInvalid !== true) {
            return;
        }

        function disableCheckoutButton() {
            const btn = $('button[data-role="proceed-to-checkout"]');

            if (!btn.length || btn.hasClass('bundle-disabled')) {
                return;
            }

            btn
                .prop('disabled', true)
                .addClass('bundle-disabled')
                .attr('title', 'Please review the bundle product before checkout')
                .on('click.bundleDisable', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                });
        }

        disableCheckoutButton();

        new MutationObserver(disableCheckoutButton).observe(document.body, {
            childList: true,
            subtree: true
        });
    });
});
