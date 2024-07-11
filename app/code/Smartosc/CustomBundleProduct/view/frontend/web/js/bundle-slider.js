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
                    $(element).find('.bundle-option-slider').on('init', function(event, slick, direction){
                        $(this).addClass('on-init');
                    });
                    $(element).find('.bundle-option-slider').slick({
                        infinite: false,
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        arrows: true,
                        prevArrow: "<span class='arrow-prev fa fa-angle-left'>Prev</span>",
                        nextArrow: "<span class='arrow-next fa fa-angle-right'>Next</span>",
                        responsive: [
                            {
                                breakpoint: 1025,
                                settings: {
                                    slidesToShow: 4,
                                    slidesToScroll: 4
                                }
                            },
                            {
                                breakpoint: 769,
                                settings: {
                                    slidesToShow: 5,
                                    slidesToScroll: 5
                                }
                            },
                            {
                                breakpoint: 480,
                                settings: {
                                    slidesToShow: 5,
                                    slidesToScroll: 5
                                }
                            }
                        ]
                    });
                }
            };
            return object.init();
        }
    }
);

