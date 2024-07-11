/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['uiComponent', 'mage/url', 'jquery'], function (Component, urlHelper, $) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;
    var defaultImage = "";

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/thumbnail'
        },
        displayArea: 'before_details',
        imageData: imageData,

        /**
         * @param {Object} item
         * @return {Array}
         */
        getImageItem: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']];
            }

            return [];
        },

        /**
         * @param {Int} quote_item_id
         * @return {Array}
         */
        _getImageFromAjax: function(quote_item_id) {
            var imageDetail=[];

            $.ajax({
                showLoader: true,
                url:  urlHelper.build('smart_checkout/customer_ajax/getproductimage'),
                data: {'quoteItemId' : quote_item_id},
                type: "POST",
                dataType: 'json',
                async: false
            }).done(function (data) {
                if (!data.errors) {
                    imageDetail = data;
                }
            });

            return imageDetail;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function (item) {
            var res;
            if (this.imageData[item['item_id']]) {
                res= this.imageData[item['item_id']].src;
            } else {
                var quoteItemId = item['item_id'];
                // var imageDetail = this._getImageFromAjax(quoteItemId);
                var imageDetail = defaultImage;

                if (imageDetail) {
                    // res = imageDetail.imageUrl;
                    res = imageDetail;
                }
                else {
                    var data = this._getImageFromAjax(quoteItemId);
                    if (data) {
                        res = data.imageUrl;
                        defaultImage = res;
                    }
                }
            }

            return res;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidth: function (item) {
            var res;
            if (this.imageData[item['item_id']]) {
                res = this.imageData[item['item_id']].width;
            } else {
                var quoteItemId = item['item_id'];
                // @todo var imageDetail = this._getImageFromAjax(quoteItemId);
                if (quoteItemId) {
                    res = 90;
                }
            }

            return res;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeight: function (item) {
            var res;
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].height;
            } else {
                var quoteItemId = item['item_id'];
                // @todo var imageDetail = this._getImageFromAjax(quoteItemId);
                if (quoteItemId) {
                    res = 90;
                }
            }

            return res;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function (item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].alt;
            }

            return null;
        }
    });
});
