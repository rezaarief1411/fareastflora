define([ "jquery"], function ($) {
    'use strict';

    return {
        defaults: {
            classError : '.field-error',
            fieldPickupNote: 'pickup_comments',
            fieldDeliveryNote: 'delivery_note',
            errorMessage: '<div class="field-error" style="margin-top: 0;"><span>You enter exceed limit %1 characters</span></div>'
        },

        showErrorMessage: function(node, limit) {
            var self = this,
                errorMessage = self.defaults.errorMessage,
                errorDom = $(node).parent().find(self.defaults.classError),
                content = $(node).val(),
                result = true;

            if ($(node).context.name === self.defaults.fieldPickupNote || $(node).context.name === self.defaults.fieldDeliveryNote) {
                if (content.length > limit) {
                    if (!errorDom.length > 0) {
                        $(node).parent().append(errorMessage.replace('%1', limit));
                        $(node).focus();
                    }
                    result = false;
                } else {
                    errorDom.remove();
                }
            }

            return result;
        }
    };
});
