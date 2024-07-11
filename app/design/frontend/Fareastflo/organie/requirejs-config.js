var config = {
    map: {
        '*': {
            megaMenu: 'js/megamenu',
            countdownTime: 'js/plugins/countdown/jquery.countdown',
            menuToggle: 'js/menu',
        }
    },
    paths: {
        'js/plugins/slider/slick.min': 'js/plugins/slider/slick.min'
    },
    shim: {
        'js/plugins/slider/slick.min': {
            deps: ['jquery']
        }
    }
}
