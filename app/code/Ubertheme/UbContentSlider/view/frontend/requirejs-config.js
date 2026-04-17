var config = {
    paths: {
        "owlCarousel1": "Ubertheme_UbContentSlider/js/owl-carousel1/owl.carousel.min",
        "ubOwlCarousel1": "Ubertheme_UbContentSlider/js/ub-owlcarousel1"
    },
    shim: {
        'owlCarousel1':{
            'deps':['jquery']
        },
        'ubOwlCarousel1':{
            'deps':['owlCarousel1']
        }
    }
};
