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

// On dropdown open
$(document).on('shown.bs.dropdown', function(event) {
    var dropdown = $(event.target);
    
    // Set aria-expanded to true
    dropdown.find('.dropdown-menu').attr('aria-expanded', true);
    
    // Set focus on the first link in the dropdown
    setTimeout(function() {
        dropdown.find('.dropdown-menu li:first-child a').focus();
    }, 10);
});

// On dropdown close
$(document).on('hidden.bs.dropdown', function(event) {
    var dropdown = $(event.target);
    
    // Set aria-expanded to false        
    dropdown.find('.dropdown-menu').attr('aria-expanded', false);
    
    // Set focus back to dropdown toggle
    dropdown.find('.dropdown-toggle').focus();
});

// Prevent Bootstrap dropdown from closing on clicks
$('.dropdown-menu').click(function(e) {
    e.stopPropagation();
});

// Check all checkbox with class="check". Used for massive actions
$("#checkAll").click(function () {
    $(".check").prop('checked', $(this).prop('checked'));
});

// Load and set sending mails modal in admin reference page
$('.button-admin-view-mail').on("click", function(e) {
  e.preventDefault();
  $("#add_action_comment").modal('show');
  $(".button-admin-view-mail").dropdown("toggle");
  value = JSON.parse($('.button-admin-view-mail').val());
  ref_id = $(e.target).attr("id");
  mails = JSON.parse(value.mails);
  $('#ref_id_input_id').val(ref_id);
  $('#table_mails_action').prepend(createTable(mails[ref_id]));
  $('#table_mails_action').html(createTable(mails[ref_id]));
});

// Load and set sending mails modal in admin reference page
$('.button-admin-view-reference').on("click", function(e) {
  e.preventDefault();
  $("#register_reference_profile").modal('show');
  $(".button-admin-view-reference").dropdown("toggle");
});



function showReferenceModal(ref){
  var modalElement = $('#modal_body_view_reference')[0];
  var selectCountry = $('#countries_select_modal')[0];
  $('#ref_id_form_update').val(ref.id);
  for( var i=0 ; i<selectCountry.options.length ; i++){
    if(selectCountry.item(i).value == ref.country.toUpperCase()){
      selectCountry.options[i].selected = true;
          $("#warning-country").remove();
          $("#countries_select_modal")
             .val(selectCountry.item(i).value)
             .trigger("change");
    }
  }

  modalElement.children[0].children[1].value = ref.name;
  modalElement.children[1].children[1].value = ref.url;
  modalElement.children[3].children[1].value = ref.phone;
  modalElement.children[4].children[1].value = ref.email;
  modalElement.children[5].children[1].value = ref.referent;
  modalElement.children[6].children[1].value = ref.num_assets;
  modalElement.children[7].children[1].value = ref.num_helpdesk;
  modalElement.children[8].children[1].value = ref.comment;
}

function buildPathForButtunSearch(url){
  var input_val = $('#research_input_admin_users_management_id').val();
  if (input_val == '') {
    url = url+"/search/null";
  } else {
    url = url+"/search/"+input_val;
  }

  window.location.href=url;
}