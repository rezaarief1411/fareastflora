define([
    'jquery',
    'domReady!'
], function ($) {
    const obj = {
        options: {
            normalProduct: 'normal-item',
            addonProduct: 'addon-item',
            bundleProduct: 'bundle-item'
        },

        init: function () {
            const self = this;
            let check = setInterval(function () {
                self.checkItem();
            }, 3000)
            $(window).on('load',function(){
                clearInterval(check);
            })
        },

        checkItem: function () {
            let self = this;
            let i = 0, j = 0;
            $("li.product-item").each(function () {
                if ($(this).attr("class").includes(self.options.normalProduct)) {
                    i++;
                }
                if ($(this).attr("class").includes(self.options.bundleProduct)) {
                    j++;
                    if (j == 1) {
                        i++;
                    }
                    if (j == 3) {
                        j = 0;
                    }
                }
                if ($(this).attr("class").includes(self.options.addonProduct)) {
                    if (i % 2 != 0) {
                        $(this).addClass(" white");
                    }
                    return true;
                }
                if (i % 2 != 0) {
                    $(this).addClass(" white");
                }
            });
        }
    };

    return obj.init();
});
