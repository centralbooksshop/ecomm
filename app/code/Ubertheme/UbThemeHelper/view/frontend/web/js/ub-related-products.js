define([
    'jquery',
    'jquery/ui',
    'relatedProducts'
], function($){

    $.widget('ub.relatedProducts', $.mage.relatedProducts, {

        /**
         * Show related products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {*} limit
         * @param {*} shuffle
         * @private
         */
        _showRelatedProducts: function (elements, limit, shuffle) {
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

    return $.ub.relatedProducts;
});
