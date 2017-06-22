var getCountryCode = null;

$(document).ready(function() {
   // try to find user country (may be blocked by adblocker)
   // getCountryCode is a promise
   getCountryCode = $.getJSON('http://freegeoip.net/json/');

   // laravel pagination generate boostrap 3 code,
   // as we use version 4, we add some aditionnal classes
   $(".pagination li")
      .addClass('page-item')
      .children("a, span")
         .addClass('page-link');
});

/**
 * format select2 options (type state) with flags
 * @param {object} state represents the current parsed select2 option
 */
var formatState = function(state) {
   if (!state.id) {
      return state.text;
   }
   var $state = $(
      '<span class="flag flag-'
      + state.element.value.toLowerCase()
      + '" /></span>'
      + '<span>&nbsp;' +state.text + '</span>'
   );
   return $state;
};

/**
 * Retrieve the boostrap glyphicon from alert's type
 * @param  string alert_type should be [warning|danger|succes|info]
 *                           in case of other value, info will be chosen
 * @return string            the full glpyhicon class
 */
var getAlertIcon = function(alert_type) {
    var icon = 'glyphicon glyphicon-';

    switch (alert_type) {
        case 'warning':
        case 'danger':
            icon += 'warning-sign'
            break;
        case 'success':
        case 'info':
        default:
            icon += 'info-sign'
            break;
    }

    return icon;
}

var getAlertDelay = function (alert_type) {
   if (alert_type == 'danger') {
      return 0;
   }

   return 5000;
}

