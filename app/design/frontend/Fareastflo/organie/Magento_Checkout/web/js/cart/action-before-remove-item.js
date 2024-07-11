define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'mage/dataPost',
    'domReady!'
], function ($, $t, confirm, dataPost) {
    return function main(config, element) {
        var params = $(element).data('post');
        $(element).on('click', function (event) {
            event.stopPropagation();
            confirm({
                content: $t('Are you sure you would like to remove this item from the shopping cart?'),
                actions: {
                    /** @inheritdoc */
                    confirm: function () {
                        dataPost().postData(params);
                    },

                    /** @inheritdoc */
                    always: function (e) {
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
            })
        });
    }

});
