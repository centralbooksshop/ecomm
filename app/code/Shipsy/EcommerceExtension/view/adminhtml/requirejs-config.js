var config = {
    paths: {
        'label': 'Shipsy_EcommerceExtension/js/getLabel',
        'cancel': 'Shipsy_EcommerceExtension/js/cancelConsignment',
        'address': 'Shipsy_EcommerceExtension/js/address',
        'address': 'Shipsy_EcommerceExtension/js/softdatasync'
    },
    shim: {
        'label': {
            deps: ['jquery']
        },
        'cancel': {
            deps: ['jquery']
        },
        'address': {
            deps: ['jquery']
        },
        'softdatasync': {
            deps: ['jquery']
        }
    }
};