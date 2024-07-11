define([
    'jquery',
    'tabs',
    'domReady!'
], function ($, tabs) {
    var obj = {
        options: {
            productName: '.product-item-name h4',
            productCode: '.item-code'
        },

        init: function () {
            var self = this;

            this.setHeightProductDetails(self.options.productName);
            this.setHeightProductDetails(self.options.productCode);

            $( window ).resize( function() {
                self.setHeightProductDetails(self.options.productName);
                self.setHeightProductDetails(self.options.productCode);
            });

            $(window).scroll(function () {
                self.headerSticky(false);
            });

            this.headerSticky(false);

            setTimeout(function(){
                $( document ).ready(function() {
                    $(".page-wrapper > .loading-mask").remove();
                });
            },500);
        },

        setHeightProductDetails: function (el) {
            $(".products-grid").each(function() {
                let heights = $(this).find(el).map(function ()
                {
                    return $(this).height();
                }).get();

                let maxHeight = Math.max.apply(null, heights);
                $(this).find(el).css('min-height', 'auto');
            });
        },
        posWl: function () {
            $(".products-grid").each(function() {
                var img = $(this).find('img.product-image-photo').height();
                $(this).find('.action.towishlist').css('top', img - 20);

                if( $(this).hasClass('products-related') ) {
                    $(this).find('.action.towishlist').css('top', img - 40);
                }

                if( $(this).hasClass('products-upsell') ) {
                    $(this).find('.action.towishlist').css('top', img - 40);
                }
            });
        },
        headerSticky: function (scrolled) {
            var headerHeight = $('.middle-header-container').height() + 30,
                self = this;

            if (headerHeight < $(window).scrollTop() && !self.scrolled) {
                $('.middle-header-container').addClass('sticky-header').slideDown( "slow" );
                self.scrolled = true;
            }
            if (headerHeight >= $(window).scrollTop() && self.scrolled) {
                $('.middle-header-container').removeClass("sticky-header");
                self.scrolled = false;
            }
        }
    };

    return obj.init();
});
