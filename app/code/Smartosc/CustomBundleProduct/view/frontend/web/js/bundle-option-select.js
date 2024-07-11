define([
        'jquery',
        'domReady!',
        'js/plugins/slider/slick.min'
    ],
    function ($) {
        return function (config, element) {
            let object = {
                /**
                 * @private
                 */
                init: function () {
                    $(element).click(function (){
                        var repotPrice = $(this).closest('.product-top').find('.price-wrapper:first').attr('data-repot-price');
                        var optPrice = $(this).siblings().find('.price-wrapper').attr('data-price-amount');
                        var productPrice = $(this).closest('.product-top').find('.price-wrapper:first').attr('data-price-amount');
                        var minOptPrice = [];
                        var elm = $(this).closest('.slick-track').find('.field.choice');
                        $(elm).each(function () {
                            minOptPrice.push($(this).find('.price-wrapper').attr('data-price-amount'));
                        });
                        let originPrice = productPrice - Math.min.apply(Math,minOptPrice);
                        if ($(this).attr('data-checked') == 'true') {
                            $(this).closest('.fieldset-bundle-options').find('.bundle-option-image').removeClass('disable');
                            $(this).closest('.fieldset-bundle-options').find('.bundle-option-image').removeClass('active');
                            $(this).attr('data-checked', 'false');
                            $(this).prop('checked', false);
                            $(this).closest('.product-top').find('.price-wrapper:first').attr('data-pot-price', (parseFloat(originPrice)).toFixed(2));
                            if  (repotPrice == undefined) {
                                $(this).closest('.product-top').find('.price:first').text('$' + (parseFloat(originPrice)).toFixed(2));
                            } else {
                                $(this).closest('.product-top').find('.price:first').text('$' + (parseFloat(originPrice) + parseFloat(repotPrice)).toFixed(2));
                            };
                            if ($(this).closest('.product-top').find('.old-price').length > 0) {
                                let productPrice_old = $(this).closest('.product-top').find('.old-price .price-wrapper').attr('data-price-amount');
                                let originPrice_old = productPrice_old - Math.min.apply(Math,minOptPrice);
                                if  (repotPrice == undefined) {
                                    $(this).closest('.product-top').find('.old-price .price').text('$' + (parseFloat(originPrice_old)).toFixed(2));
                                } else {
                                    $(this).closest('.product-top').find('.old-price .price').text('$' + (parseFloat(originPrice_old) + parseFloat(repotPrice)).toFixed(2));
                                };
                            };
                            return ;
                        };
                        $(this).closest('.fieldset-bundle-options').find('.change-container-classname').attr('data-checked', 'false');
                        $(this).attr('data-checked', 'true');
                        $(this).closest('.fieldset-bundle-options').find('.bundle-option-image').addClass('disable');
                        $(this).closest('.fieldset-bundle-options').find('.bundle-option-image').removeClass('active');
                        $(this).siblings().find('.bundle-option-image').removeClass("disable");
                        $(this).siblings().find('.bundle-option-image').addClass('active');
                        $(this).closest('.product-top').find('.price-wrapper:first').attr('data-pot-price', (parseFloat(originPrice) + parseFloat(optPrice)).toFixed(2));

                        if  (repotPrice == undefined) {
                            $(this).closest('.product-top').find('.price:first').text('$' + (parseFloat(originPrice) + parseFloat(optPrice)).toFixed(2));
                        } else {
                            $(this).closest('.product-top').find('.price:first').text('$' + (parseFloat(originPrice) + parseFloat(repotPrice) + parseFloat(optPrice)).toFixed(2));
                        };
                        if ($(this).closest('.product-top').find('.old-price').length > 0) {
                            let productPrice_old = $(this).closest('.product-top').find('.old-price .price-wrapper').attr('data-price-amount');
                            let originPrice_old = productPrice_old - Math.min.apply(Math,minOptPrice);
                            if  (repotPrice == undefined) {
                                $(this).closest('.product-top').find('.old-price .price').text('$' + (parseFloat(originPrice_old) + parseFloat(optPrice)).toFixed(2));
                            } else {
                                $(this).closest('.product-top').find('.old-price .price').text('$' + (parseFloat(originPrice_old) + parseFloat(repotPrice) + parseFloat(optPrice)).toFixed(2));
                            };
                        }
                    })

                }
            };
            return object.init();
        }
    }
);

