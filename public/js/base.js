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

    var _notify = $('.notify');
    if (_notify.length) {
        _notify.hide();
        buildModal('Messages', _notify.html(), 'notify');
    }
});

var buildModal = function(title, body, extrabodyclass) {
    var html = '<div class="modal fade">' +
        '<div class="modal-dialog" role="document">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<h5 class="modal-title">' + title + '</h5>' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
        '</div>' +
        '<div class="modal-body ' + extrabodyclass + '">' +
        body +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    var modal = $(html);
    modal.modal("show");
}

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

