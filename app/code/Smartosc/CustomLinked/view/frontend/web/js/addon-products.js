define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.addonProducts', {
        options: {
            addonCheckbox: '.addon-checkbox', // Class name for a add-on product's input checkbox.
            addonProductsCheckFlag: false, // Add-On products checkboxes are initially unchecked.
            addonProductsField: '#addon-products-field', // Hidden input field that stores add-on products.
            selectAllMessage: $.mage.__('select all'),
            unselectAllMessage: $.mage.__('unselect all'),
            selectAllLink: '[data-role="select-all"]',
            elementsSelector: '.item.product'
        },

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function () {
            $(this.options.selectAllLink, this.element).on('click', $.proxy(this._selectAllAddon, this));
            $(this.options.addonCheckbox, this.element).on('click', $.proxy(this._addAddonToProduct, this));
            this._showAddonProducts(
                this.element.find(this.options.elementsSelector),
                this.element.data('limit'),
                this.element.data('shuffle')
            );
        },

        /**
         * This method either checks all checkboxes for a product's set of add-on products (select all)
         * or unchecks them (unselect all).
         * @private
         * @param {jQuery.Event} e - Click event on either the "select all" link or the "unselect all" link.
         * @return {Boolean} - Prevent default event action and event propagation.
         */
        _selectAllAddon: function (e) {
            var innerHTML = this.options.addonProductsCheckFlag ?
                this.options.selectAllMessage : this.options.unselectAllMessage;

            $(e.target).html(innerHTML);
            $(this.options.addonCheckbox).attr(
                'checked',
                this.options.addonProductsCheckFlag = !this.options.addonProductsCheckFlag
            );
            this._addAddonToProduct();

            return false;
        },

        /**
         * This method iterates through each checkbox for all add-on products and collects only those products
         * whose checkbox has been checked. The selected add-on products are stored in a hidden input field.
         * @private
         */
        _addAddonToProduct: function () {
            $(this.options.addonProductsField).val(
                $(this.options.addonCheckbox + ':checked').map(function () {
                    return this.value;
                }).get().join(',')
            );
        },

        /**
         * Show add-on products according to limit. Shuffle if needed.
         * @param {*} elements
         * @param {*} limit
         * @param {*} shuffle
         * @private
         */
        _showAddonProducts: function (elements, limit, shuffle) {
            var index;

            if (shuffle) {
                this._shuffle(elements);
            }

            if (limit === 0) {
                limit = elements.length;
            }

            for (index = 0; index < limit; index++) {
                $(elements[index]).show();
            }
        },

        /* jscs:disable */
        /* eslint-disable */
        /**
         * Shuffle an array
         * @param {Array} o
         * @returns {*}
         */
        _shuffle: function shuffle(o) { //v1.0
            for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
            return o;
        }

        /* jscs:disable */
        /* eslint:disable */
    });

    return $.mage.addonProducts;
});
