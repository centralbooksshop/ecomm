/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

require([
    'jquery',
    'domReady!'
], function($) {
    'use strict';

    $("#prrma_general_return_placement").change(function () {
        var optionSelected = $(this).find("option:selected");
        if (optionSelected.val() == "none") {
            optionSelected.each(function() {
                this.selected = false;
            });

            $(this).children("option[value=none]").prop("selected", true);
        }
    });
});
