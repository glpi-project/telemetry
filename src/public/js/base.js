var getCountryCode = null;

$(document).ready(function() {
   // try to find user country (may be blocked by adblocker)
   getCountryCode = $.getJSON('http://freegeoip.net/json/');
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


