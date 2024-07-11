define([], function () {
    'use strict';

    return {
        getObservableValue: function(name) {
            var registry = require('uiRegistry'),
                result = '';

            if (registry.has(name)) {
                var val = registry.get(name).value;
                if (typeof val === 'function') {
                    result = val();
                }
            }

            return result;
        },
        formatDate: function (date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;

            if (day.length < 2)
                day = '0' + day;

            return [year, month, day].join('-');
        },
        displayDate: function(date) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];

            var d = new Date(date),
                month = '' + monthNames[d.getMonth()],
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [day, month, year].join('-');
        }
    }
});
