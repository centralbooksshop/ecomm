define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Retailinsights_ModifiedSalesOrderGrid/ui/grid/listing'
        },
        getRowClass: function (row) {

            if(row.is_backeordered_items == 'Yes') {
                return 'backorder';
            }
            if(row.status == 'complete') {
                return 'complete';
            } else if(row.status == 'closed') {
                return 'closed';
            } else if(row.status == 'processing') {
                return 'processing';
            } else if(row.status == 'assigned_to_picker') {
                return 'assigned_to_picker';
            } else if(row.status == 'dispatched_to_courier') {
                return 'dispatched_to_courier';
            } else if(row.status == 'order_delivered') {
                return 'order_delivered';
            } else {
                return 'pending';
            }

        }
    });
});
