define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'jquery/ui'
], function ($, getTotalsAction, customerData) {
    'use strict';
    $.widget('sm.quantityCart', {
        options: {
            "elementId": '',
            "minAllowed": 1,
            "maxAllowed": 10000,
            "disabled": true,
            urlUpdatePost: '/checkout/cart/updatePost'
        },

        _create: function () {
            let form = $('form.form-cart');
            let self = this;

            $( '[data-role="cart-item-qty"]' ).each(function( index ) {
                let quantityInput = $(this),
                    id = quantityInput.attr('data-id'),
                    minusAction = $(this).parent().find('[data-role=minusAction-' + id + ']'),
                    plusAction = $(this).parent().find('[data-role=plusAction-' + id + ']');

                self._disabledActionButton(quantityInput, minusAction, plusAction);
            });

            $(document).on('click', '.edit-qty', function (event){
                let quantityInput = $(this).parent().find('[data-role="cart-item-qty"]'),
                    id = quantityInput.attr('data-id'),
                    minusAction = $(this).parent().find('[data-role=minusAction-' + id + ']'),
                    plusAction = $(this).parent().find('[data-role=plusAction-' + id + ']');

                self._disabledActionButton(quantityInput, minusAction, plusAction);

                if( $(this).hasClass('disabled')) {
                    return false;
                }
                else if( $(this).hasClass('minus') ) {
                    // minus
                    if (quantityInput.val() < self.options.minAllowed) {
                        event.preventDefault();
                        return false;
                    } else {
                        self._getValueQty(event, quantityInput, 'minus');
                        quantityInput.trigger('change');
                    }
                }
                else if( $(this).hasClass('plus') ) {
                    // plus
                    if (quantityInput.val() > self.options.maxAllowed) {
                        event.preventDefault();
                        return false;
                    } else {
                        self._getValueQty(event, quantityInput, 'plus');
                        quantityInput.trigger('change');
                    }
                }

                event.stopImmediatePropagation();
            });

            $(document).on('change keyup', function (event) {
                let classList = event.target.classList;
                let quantityInput = $(event.target),
                    minusAction = $(event.target).parent().find('.edit-qty.minus'),
                    plusAction = $(event.target).parent().find('.edit-qty.plus'),
                    currentVal = quantityInput.val();

                if($.inArray("input-qty", classList) !== -1) {
                    if ( quantityInput.val() < self.options.minAllowed
                        || quantityInput.val() > self.options.maxAllowed) {
                        event.preventDefault();
                        // quantityInput.val(currentVal);
                        location.reload(true);
                    }
                    else {
                        self._disabledActionButton(quantityInput, minusAction, plusAction);
                        self._updateShoppingCart(quantityInput);
                    }
                }
                event.stopImmediatePropagation();
            });
        },

        _updateCartQuantity: function (quantityInput, minusAction, plusAction) {
            this._disabledActionButton(quantityInput, minusAction, plusAction);
            this._decrementValue(quantityInput, minusAction);
            this._incrementValue(quantityInput, plusAction);
            this._onChangeInput(quantityInput, minusAction, plusAction);
        },

        _getValueQty: function (event, quantityInput, action) {
            let currentVal = quantityInput.val();

            switch (action) {
                case 'plus':
                    currentVal++;
                    quantityInput.val(currentVal);
                    break;
                case 'minus':
                    currentVal--;
                    quantityInput.val(currentVal);
                    break;
            }
        },

        _incrementValue: function (quantityInput, plusAction) {
            let self = this;
            plusAction.on('click', function (event) {
                if (quantityInput.val() > self.options.maxAllowed) {
                    event.preventDefault();
                    return false;
                } else {
                    self._getValueQty(event, quantityInput, 'plus');
                    quantityInput.trigger('change');
                }
            });
        },

        _decrementValue: function (quantityInput, minusAction) {
            let self = this;

            $(document).on('click', "[data-role=minusAction-" + self.options.elementId + "]", function (event) {
                if (quantityInput.val() < self.options.minAllowed) {
                    event.preventDefault();
                    return false;
                } else {
                    self._getValueQty(event, quantityInput, 'minus');
                    quantityInput.trigger('change');
                }
            });
        },

        _onChangeInput: function (quantityInput, minusAction, plusAction) {
            let self = this,
                currentVal = quantityInput.val();

            quantityInput.on('change keyup', function (event) {
                if (quantityInput.val() < self.options.minAllowed || quantityInput.val() > self.options.maxAllowed) {
                    event.preventDefault();
                    quantityInput.val(currentVal);
                    location.reload(true);
                }
                else {
                    self._disabledActionButton(quantityInput, minusAction, plusAction);
                    self._updateShoppingCart(quantityInput);
                }

            });
        },

        _disabledActionButton: function (quantityInput, minusAction, plusAction) {
            let self = this;
            if (minusAction.data('off') && plusAction.data('off')) {
                minusAction.addClass('disabled');
                plusAction.addClass('disabled')
            } else {
                quantityInput.val() <= self.options.minAllowed ? minusAction.addClass('disabled') : minusAction.removeClass('disabled');
                quantityInput.val() >= self.options.maxAllowed ? plusAction.addClass('disabled') : plusAction.removeClass('disabled');
            }
        },

        _updateShoppingCart: function (quantityInput) {
            var self = this;
            let form = $('form.form-cart');

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                method: 'POST',
                success: function (res) {
                    var parsedResponse = $.parseHTML(res);
                    var result = $(parsedResponse).find(".form-cart");
                    var sections = ['cart'];
                    var currentVal = quantityInput.val();
                    var updateVal = $(parsedResponse).find("#" + quantityInput.attr('id')).val();
                    if(currentVal > updateVal) {
                        location.reload(true);
                    }

                    $(".form-cart").replaceWith(result);

                    // /* Minicart reloading */
                    customerData.reload(sections, true);

                    // /* Totals summary reloading */
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                },
                error: function (xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        },
    });

    return $.sm.quantityCart;
});
