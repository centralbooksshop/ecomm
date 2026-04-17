var config = {};
if (typeof window.AVADA_EM !== 'undefined') {
    config = {
        config: {
            mixins: {
                'Magento_Checkout/js/view/billing-address': {
                    'Codilar_Gst/js/view/billing-address-mixins' : true
                }
            }
        }
    };
}
