define(['jquery'], function($) {
    'use strict';

    var isNumber = function (value) {
        var array = Array.from(value);
        for (var i = 0; i < array.length; i++) {

            var check = $.inArray(array[i], [ "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"] );
            if (check  === -1)
                return false;
        }

        return true;
    };

    return function() {
        $.validator.addMethod(
            'validate-christmas-postalcode',
            function(value, element) {
                console.log(value)
                var arrayOfPostal = value.split(',');

                for (var i = 0; i < arrayOfPostal.length; i++) {
                    if (!isNumber(arrayOfPostal[i].trim()))
                        return false;
                }

                return true;
            },
            $.mage.__('Postal code string is not valid! Please follow this pattern: 12345,3333,5555 (numbers separated by comma character)')
        )
    }
});
