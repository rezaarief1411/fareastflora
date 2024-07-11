define([
    'jquery',
    'jquery-ui-modules/menu',
    'mage/translate'
], function ($) {
    'use strict';

    /**
     * Menu Widget - this widget is a wrapper for the jQuery UI Menu
     */
    $.widget('sm.menu', $.ui.menu, {
        options: {
            showDelay: 42,
            hideDelay: 300,
            delay: 0,
        },

        /**
         * @private
         */
        _create: function () {
            this.delay = this.options.delay;
        },

        /**
         * @private
         */
        _init: function () {
            this._assignControls()._listen();
            this._closeMenu();
        },

        /**
         * @return {Object}
         * @private
         */
        _assignControls: function () {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]'),
                swipeArea: $('.top-bar-abs')
            };

            return this;
        },

        /**
         * @private
         */
        _listen: function () {
            var controls = this.controls,
                toggle = this.toggle;

            controls.toggleBtn.off('click');
            controls.toggleBtn.on('click', toggle.bind(this));
            controls.swipeArea.off('swiperight');
            controls.swipeArea.on('swiperight', toggle.bind(this));
        },

        /**
         * Toggle.
         */
        toggle: function () {
            var html = $('html');

            if (html.hasClass('nav-open')) {
                html.removeClass('nav-open');
                setTimeout(function () {
                    html.removeClass('nav-before-open');
                }, this.options.hideDelay);
            } else {
                html.addClass('nav-before-open');
                setTimeout(function () {
                    html.addClass('nav-open');
                }, this.options.showDelay);
            }
        },

        /**
         *
         * @private
         */
        _closeMenu: function() {
            var self = this;

            $(".nav-toggle-close").on("click", function() {
                self.toggle();
            });
        }

    });

    return $.sm.menu;
});
