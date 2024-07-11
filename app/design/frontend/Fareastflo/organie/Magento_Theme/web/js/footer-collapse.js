define([
    "jquery",
    "tabs"
], function ($) {
    'use strict';

    var footer = {
        options: {
            containerContact: '.block-contact',
            containerInfo: '.block-info',
            containerAccount: '.block-account',
            containerLocation: '.block-location'
        },

        init: function () {
            var self = this;
            mediaCheck({
                media: '(max-width: 769px)',

                /**
                 * Is triggered when breakpoint enties.
                 */
                entry: function () {
                    self.activeFooterMobile(self.options.containerContact);
                    self.activeFooterMobile(self.options.containerInfo);
                    self.activeFooterMobile(self.options.containerAccount);
                    self.activeFooterMobile(self.options.containerLocation);
                }
            });
        },

        activeFooterMobile: function (opts) {
            $(opts).find('h4').collapsible({
                active: true,
                content: $(opts).find('.block-content'),
                openedState: "opened"
            });
        }
    };

    return footer.init();
});
