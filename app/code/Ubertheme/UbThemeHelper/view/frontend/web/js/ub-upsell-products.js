define([
    'jquery',
    'jquery/ui',
    'upsellProducts'
], function($){

    $.widget('ub.upsellProducts', $.mage.upsellProducts, {

        /**
         * Show upsell products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {Number} limit
         * @param {Boolean} shuffle
         * @private
         */
        _showUpsellProducts: function (elements, limit, shuffle) {
            var index;

            if (shuffle) {
                this._shuffle(elements);
            }

            if (limit === 0) {
                limit = elements.length;
            }

            for (index = 0; index < elements.length; index++) {
                if (index < limit) {
                    $(elements[index]).show();
                } else {
                    $(elements[index]).remove();
                }
            }
        },

    });

    return $.ub.upsellProducts;
});
