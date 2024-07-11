define([
    'jquery'
], function ($) {
    $(document).ready(function () {
        $('li').click(function(){
            setTimeout(function() {
                checkItem();
            }, 4000);
        });
        checkItem();
    });

    function checkItem(){
        let i = 0;
        let j = 0;
        $(".item.product.product-item").each(function () {
            if ($(this).attr("class").includes("normal-item")) {
                i++;
            }
            if ($(this).attr("class").includes("addon-item")) {
                if (i % 2 == 0) {
                    $(this).addClass(" grey");
                }
                return true;
            }
            if ($(this).attr("class").includes("bundle-item")) {
                j++;
                if (j == 1) {
                    i++;
                }
                if (j == 3) {
                    j = 0;
                }
            }
            if (i % 2 == 0) {
                $(this).addClass(" grey");
            }
        });
    }
});
