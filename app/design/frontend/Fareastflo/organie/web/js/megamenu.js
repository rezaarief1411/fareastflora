define([
    "jquery",
    "tabs",
    'domReady!'
], function ($) {
    'use strict';

    var objMenu = {
        options: {
            navTabAll: '.nav-tabs li',
            contentTabAll: '.tab-content .tab-pane'
        },

        init: function () {
            this.showMenuImage();
            this.hoverMenuImage();
        },

        showMenuImage: function () {
            $('.mega-menu-content').append('<div class="menu-image"><img class="front-image" src="/" /> </span></div>');

            $('li.level1').each(function() {
                var el = $(this).find('.mega-menu-sub-title').attr('data-megaimage');
                if (el && el != 'no-image') {
                    $(this).addClass('has-image');
                    var imageMenu = $(this).parents('.mega-menu-content');
                    imageMenu.find('.front-image').attr('src', el);
                    imageMenu.addClass('menu-img');
                }
            });
        },

        hoverMenuImage: function() {
            $('li.level1').each(function() {
                let el = $(this).find('.mega-menu-sub-title').attr('data-megaimage');
                $(this).hover(function () {
                    if (el && el != 'no-image') {
                        var imageMenu = $(this).parents('.mega-menu-content');
                        imageMenu.find('.front-image').attr('src', el);
                    }
                });
            });
        }
    };

    return objMenu.init();
});
