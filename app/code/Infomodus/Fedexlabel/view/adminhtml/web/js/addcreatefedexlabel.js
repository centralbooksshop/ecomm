require(["jquery"], function ($) {
    $(document).ready(function () {
        if ($('#ship_items_container .admin__page-section-item.order-totals.order-totals-actions .admin__page-section-item-content').size() > 0) {
            $('#ship_items_container .admin__page-section-item.order-totals.order-totals-actions .admin__page-section-item-content').eq(0).prepend('<div class="field choice admin__field admin__field-option">\
            <input id="infomodus_fedex_label" class="admin__control-checkbox" name="shipment[infomodus_fedex_label]" value="1" type="checkbox">\
            <label class="admin__field-label" for="infomodus_fedex_label">\
            <span>Create FedEx label</span></label>\
        </div>');
        }

        if ($('#creditmemo_item_container .admin__page-section-item.order-totals.creditmemo-totals .order-totals-actions').size() > 0) {
            $('#creditmemo_item_container .admin__page-section-item.order-totals.creditmemo-totals .order-totals-actions').eq(0).prepend('<div class="field choice admin__field admin__field-option">\
            <input id="infomodus_fedex_label" class="admin__control-checkbox" name="creditmemo[infomodus_fedex_label]" value="1" type="checkbox">\
            <label class="admin__field-label" for="infomodus_fedex_label">\
            <span>Create FedEx label</span></label>\
        </div>');
        }
    });
});