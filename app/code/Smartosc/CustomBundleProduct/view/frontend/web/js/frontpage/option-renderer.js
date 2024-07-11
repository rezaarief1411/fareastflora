define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mage.OptionRenderer', {
        options: {
            jsonConfig: {},
            selectorProduct: '.product-item-actions',
            classes: {
                selectorPotRadioBtn: '.fieldset-bundle-options input[type="radio"]',
                selectorRepotButton: '.fieldset-bundle-options input[type="button"][name^="repot-"]',
                selectorRepotCheckbox: '.fieldset-bundle-options input[type="checkbox"]',
                fieldPlant: '.fieldset-bundle-options input[type="hidden"]',
                fieldPot: '.fieldset-bundle-options input[name="bundlespot"]',
                fieldRepot: '.fieldset-bundle-options input[name="repotting_service"]'
            }
        },
        productForm: {},
        hiddenInputFields: {},
        potRadioButton: {},
        repotButton: {},
        repotCheckbox: {},
        fieldNamePot: {},
        fieldNameRepot: {},

        /**
         * @private
         */
        _init: function () {
            // Don't render the same set of swatches twice
            if ($(this.element).attr('data-rendered')) {
                return;
            }

            $(this.element).attr('data-rendered', true);

            if (this.options.jsonConfig !== '') {
                this._RenderControls();
                this._EventListener();
            }
        },


        /**
         * Declare variables
         *
         * @private
         */
        _create: function () {
            var classes = this.options.classes,
                wrapper = this.element.parents(this.options.selectorProduct);

            // plant, pot & repotting service html
            this.plantInput = wrapper.find(classes.fieldPlant);
            this.potRadioButton = wrapper.find(classes.selectorPotRadioBtn);
            this.repotButton = wrapper.find(classes.selectorRepotButton);
            this.repotCheckbox = wrapper.find(classes.selectorRepotCheckbox);
            this.fieldNamePot = wrapper.find(classes.fieldPot);
            this.fieldNameRepot = wrapper.find(classes.fieldRepot);

            // add-to-cart form
            this.productForm = wrapper.find('form:first');

        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var fieldNamePot = this.fieldNamePot.val(),
                fieldNameRepot = this.fieldNameRepot.val(),
                html = '';

            // this.options.jsonConfig.mappedOptions = _.clone(this.options.jsonConfig.options);
            // @todo: [future feature] render control dynamically

            html += this.plantInput[0].outerHTML;
            html += `<input type="hidden" name="${fieldNamePot}" value>`; // input field for pot
            html += `<input type="hidden" name="${fieldNameRepot}" value>`; // input field for repotting service

            this.productForm.append(html);
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var self = this,
                fieldNamePot = this.fieldNamePot.val(),
                fieldNameRepot = this.fieldNameRepot.val();

            self.potRadioButton.on('click', function () {
                var $input = self.productForm.find('input[name="' + fieldNamePot + '"]');
                $input.val($(this).val());
            });


            self.repotButton.on('click', function () {
                var $input = self.productForm.find('input[name="' + fieldNameRepot + '"]');
                if (self.repotCheckbox.is(":checked")) {
                    $input.val(self.repotCheckbox.val());
                } else {
                    $input.val('');
                }
            });

        }

    });

    return $.mage.OptionRenderer;
});
