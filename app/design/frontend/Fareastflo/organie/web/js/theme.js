define([
    "jquery",
    'domReady!'
], function ($) {
    'use strict';

    var theme = {
        options: {
            navTabAll: '.nav-tabs li',
            contentTabAll: '.tab-content .tab-pane'
        },

        init: function () {
            console.log('init');
        },
    };

    return theme.init();
});
